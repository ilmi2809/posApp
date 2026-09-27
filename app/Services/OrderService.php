<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Order;
use App\Models\OrderDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    public function __construct(
        protected InvoiceService $invoiceService,
        protected StockService $stockService
    ) {}

    /**
     * Create order atomically with database transaction & stock validation
     *
     * @param array $itemsArray Array of ['item_id' => int, 'qty' => float]
     * @param int $paymentMethodId
     * @param int $userId
     * @return Order
     * @throws Exception
     */
    public function createOrder(array $itemsArray, int $paymentMethodId, int $userId): Order
    {
        if (empty($itemsArray)) {
            throw new Exception('Order harus memiliki minimal 1 item.');
        }

        return DB::transaction(function () use ($itemsArray, $paymentMethodId, $userId) {
            $subtotal = 0;
            $orderItems = [];

            // 1. Validate items, prices, and pre-calculate subtotal
            foreach ($itemsArray as $entry) {
                $itemId = (int)$entry['item_id'];
                $qty = (float)$entry['qty'];

                if ($qty <= 0) {
                    throw new Exception('Jumlah item harus lebih besar dari 0.');
                }

                $item = Item::with(['price', 'stock'])->find($itemId);

                if (!$item) {
                    throw new Exception("Item ID {$itemId} tidak ditemukan.");
                }

                $priceObj = $item->price;
                if (!$priceObj) {
                    throw new Exception("Item '{$item->name}' belum memiliki harga jual.");
                }

                $price = (float)$priceObj->selling_price;
                $itemSubtotal = round($price * $qty, 2);
                $subtotal += $itemSubtotal;

                $orderItems[] = [
                    'item' => $item,
                    'qty' => $qty,
                    'price' => $price,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $tax = round($subtotal * 0.11, 2);
            $total = $subtotal + $tax;
            $invoiceNumber = $this->invoiceService->generateInvoiceNumber();
            $transactionDate = Carbon::now();

            // 2. Create Order Header
            $order = Order::create([
                'invoice_number' => $invoiceNumber,
                'user_id' => $userId,
                'payment_method_id' => $paymentMethodId,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'transaction_date' => $transactionDate,
            ]);

            // 3. Create Details and Deduct Stock with locking
            foreach ($orderItems as $itemData) {
                OrderDetail::create([
                    'order_id' => $order->id,
                    'item_id' => $itemData['item']->id,
                    'qty' => $itemData['qty'],
                    'price' => $itemData['price'],
                    'subtotal' => $itemData['subtotal'],
                ]);

                // Deduct stock (will throw exception and rollback if stock insufficient or negative)
                $this->stockService->deductStock(
                    itemId: $itemData['item']->id,
                    quantity: $itemData['qty'],
                    userId: $userId,
                    orderId: $order->id,
                    note: "Penjualan Order #{$order->invoice_number}"
                );
            }

            return $order->load(['orderDetails.item.uom', 'paymentMethod', 'user']);
        });
    }
}
