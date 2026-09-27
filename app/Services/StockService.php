<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use ValidationException;

class StockService
{
    /**
     * Add stock (Stock In)
     */
    public function addStock(int $itemId, float $quantity, int $userId, ?string $note = null): StockMovement
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Jumlah stok masuk harus lebih dari 0.');
        }

        return DB::transaction(function () use ($itemId, $quantity, $userId, $note) {
            $stock = Stock::where('item_id', $itemId)->lockForUpdate()->first();

            if (!$stock) {
                $stock = Stock::create([
                    'item_id' => $itemId,
                    'quantity' => 0,
                ]);
            }

            $stockBefore = (float)$stock->quantity;
            $stockAfter = $stockBefore + $quantity;

            $stock->quantity = $stockAfter;
            $stock->save();

            return StockMovement::create([
                'item_id' => $itemId,
                'user_id' => $userId,
                'order_id' => null,
                'type' => 'IN',
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'note' => $note,
            ]);
        });
    }

    /**
     * Deduct stock for Sales Order with row locking & atomic check
     */
    public function deductStock(int $itemId, float $quantity, int $userId, int $orderId, ?string $note = null): StockMovement
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Jumlah stok keluar harus lebih dari 0.');
        }

        $stock = Stock::where('item_id', $itemId)->lockForUpdate()->first();

        if (!$stock || $stock->quantity < $quantity) {
            $available = $stock ? $stock->quantity : 0;
            throw new \Exception("Stok barang tidak mencukupi. Stok tersedia: {$available}, diminta: {$quantity}.");
        }

        $stockBefore = (float)$stock->quantity;
        $stockAfter = $stockBefore - $quantity;

        if ($stockAfter < 0) {
            throw new \Exception('Stok tidak boleh bernilai negatif.');
        }

        $stock->quantity = $stockAfter;
        $stock->save();

        return StockMovement::create([
            'item_id' => $itemId,
            'user_id' => $userId,
            'order_id' => $orderId,
            'type' => 'OUT',
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'note' => $note ?? 'Pengurangan stok dari transaksi order #' . $orderId,
        ]);
    }
}
