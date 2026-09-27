<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function create()
    {
        $items = Item::with(['uom', 'price', 'stock'])
            ->orderBy('name')
            ->get();

        $paymentMethods = PaymentMethod::orderBy('name')->get();

        return view('orders.create', compact('items', 'paymentMethods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
        ], [
            'payment_method_id.required' => 'Metode pembayaran wajib dipilih.',
            'items.required' => 'Minimal satu item harus dipilih.',
            'items.min' => 'Minimal satu item harus dipilih.',
            'items.*.qty.gt' => 'Jumlah barang harus lebih dari 0.',
        ]);

        try {
            $order = $this->orderService->createOrder(
                itemsArray: $request->input('items'),
                paymentMethodId: (int) $request->input('payment_method_id'),
                userId: Auth::id()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi order berhasil diproses.',
                    'order_id' => $order->id,
                    'receipt_url' => route('orders.receipt', $order->id),
                ]);
            }

            return redirect()->route('orders.receipt', $order->id)
                ->with('success', "Transaksi {$order->invoice_number} berhasil diproses!");

        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $query = Order::with(['user', 'paymentMethod', 'orderDetails.item'])
            ->latest();

        // If Kasir, show their own transactions unless they have full permission
        if (Auth::user()->hasRole('Kasir') && ! Auth::user()->can('user.view')) {
            $query->where('user_id', Auth::id());
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('transaction_date', $request->input('date'));
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        return redirect()->route('orders.receipt', $order->id);
    }

    public function receipt(Order $order)
    {
        $order->load(['user', 'paymentMethod', 'orderDetails.item.uom']);

        return view('orders.receipt', compact('order'));
    }
}
