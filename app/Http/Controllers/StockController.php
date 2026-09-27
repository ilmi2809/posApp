<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class StockController extends Controller
{
    public function __construct(
        protected StockService $stockService
    ) {}

    public function index(Request $request)
    {
        $query = Stock::with(['item.uom', 'item.price']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('item', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'empty') {
                $query->where('quantity', '<=', 0);
            } elseif ($request->status === 'low') {
                $query->where('quantity', '>', 0)->where('quantity', '<=', 10);
            }
        }

        $stocks = $query->paginate(15)->withQueryString();

        return view('stocks.index', compact('stocks'));
    }

    public function createStockIn(Item $item)
    {
        $item->load(['uom', 'stock']);
        return view('stocks.add', compact('item'));
    }

    public function storeStockIn(Request $request, Item $item)
    {
        $request->validate([
            'quantity' => ['required', 'numeric', 'gt:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [
            'quantity.required' => 'Jumlah stok masuk wajib diisi.',
            'quantity.gt' => 'Jumlah stok masuk harus lebih besar dari 0.',
        ]);

        try {
            $this->stockService->addStock(
                itemId: $item->id,
                quantity: (float)$request->input('quantity'),
                userId: Auth::id(),
                note: $request->input('note')
            );

            return redirect()->route('stocks.index')
                ->with('success', "Stok untuk item '{$item->name}' berhasil ditambahkan.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function movements(Request $request)
    {
        $query = StockMovement::with(['item.uom', 'user', 'order'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('item', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $movements = $query->paginate(20)->withQueryString();

        return view('stocks.movements', compact('movements'));
    }
}
