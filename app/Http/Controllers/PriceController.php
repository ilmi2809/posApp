<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Price;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::with(['uom', 'price']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $items = $query->paginate(15)->withQueryString();

        return view('prices.index', compact('items'));
    }

    public function update(Request $request, Item $item)
    {
        $request->validate([
            'selling_price' => ['required', 'numeric', 'min:0'],
        ]);

        Price::create([
            'item_id' => $item->id,
            'selling_price' => $request->input('selling_price'),
        ]);

        return redirect()->route('prices.index')->with('success', "Harga jual '{$item->name}' berhasil diperbarui.");
    }
}
