<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Price;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Uom;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::with(['uom', 'price', 'stock']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'empty') {
                $query->whereHas('stock', function ($q) {
                    $q->where('quantity', '<=', 0);
                });
            } elseif ($request->status === 'low') {
                $query->whereHas('stock', function ($q) {
                    $q->where('quantity', '>', 0)->where('quantity', '<=', 10);
                });
            }
        }

        $items = $query->paginate(15)->withQueryString();
        $uoms = Uom::all();

        return view('items.index', compact('items', 'uoms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sku' => ['required', 'string', 'max:100', 'unique:items,sku'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'uom_id' => ['required', 'exists:uoms,id'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'initial_stock' => ['nullable', 'numeric', 'min:0'],
        ], [
            'sku.required' => 'SKU wajib diisi.',
            'sku.unique' => 'SKU sudah digunakan oleh item lain.',
            'name.required' => 'Nama item wajib diisi.',
            'uom_id.required' => 'Satuan (UoM) wajib dipilih.',
            'selling_price.required' => 'Harga jual wajib diisi.',
            'selling_price.min' => 'Harga jual tidak boleh negatif.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $item = Item::create([
                    'sku' => strtoupper($request->input('sku')),
                    'name' => $request->input('name'),
                    'category' => $request->input('category'),
                    'uom_id' => $request->input('uom_id'),
                ]);

                Price::create([
                    'item_id' => $item->id,
                    'selling_price' => $request->input('selling_price'),
                ]);

                $initialStock = (float) ($request->input('initial_stock') ?? 0);
                Stock::create([
                    'item_id' => $item->id,
                    'quantity' => $initialStock,
                ]);

                if ($initialStock > 0) {
                    StockMovement::create([
                        'item_id' => $item->id,
                        'user_id' => Auth::id(),
                        'type' => 'IN',
                        'quantity' => $initialStock,
                        'stock_before' => 0,
                        'stock_after' => $initialStock,
                        'note' => 'Stok awal pendaftaran item baru',
                    ]);
                }
            });

            return redirect()->route('items.index')
                ->with('success', 'Master Item baru berhasil ditambahkan.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal menambah item: '.$e->getMessage());
        }
    }

    public function update(Request $request, Item $item)
    {
        $request->validate([
            'sku' => ['required', 'string', 'max:100', 'unique:items,sku,'.$item->id],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'uom_id' => ['required', 'exists:uoms,id'],
            'selling_price' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            DB::transaction(function () use ($request, $item) {
                $item->update([
                    'sku' => strtoupper($request->input('sku')),
                    'name' => $request->input('name'),
                    'category' => $request->input('category'),
                    'uom_id' => $request->input('uom_id'),
                ]);

                $currentPrice = $item->price;
                if (! $currentPrice || (float) $currentPrice->selling_price !== (float) $request->input('selling_price')) {
                    Price::create([
                        'item_id' => $item->id,
                        'selling_price' => $request->input('selling_price'),
                    ]);
                }
            });

            return redirect()->route('items.index')
                ->with('success', 'Data item berhasil diperbarui.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(Item $item)
    {
        try {
            $item->delete();

            return redirect()->route('items.index')
                ->with('success', 'Item berhasil dihapus.');
        } catch (Exception $e) {
            return back()->with('error', 'Tidak dapat menghapus item yang sudah memiliki riwayat transaksi/stok.');
        }
    }
}
