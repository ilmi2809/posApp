<?php

namespace App\Http\Controllers;

use App\Models\Uom;
use Illuminate\Http\Request;

class UomController extends Controller
{
    public function index()
    {
        $uoms = Uom::withCount('items')->get();
        return view('uoms.index', compact('uoms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:uoms,name'],
            'symbol' => ['nullable', 'string', 'max:20'],
        ]);

        Uom::create($request->only('name', 'symbol'));

        return redirect()->route('uoms.index')->with('success', 'Satuan (UoM) baru berhasil disimpan.');
    }

    public function update(Request $request, Uom $uom)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:uoms,name,' . $uom->id],
            'symbol' => ['nullable', 'string', 'max:20'],
        ]);

        $uom->update($request->only('name', 'symbol'));

        return redirect()->route('uoms.index')->with('success', 'Data UoM berhasil diperbarui.');
    }

    public function destroy(Uom $uom)
    {
        if ($uom->items()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus UoM yang sedang digunakan oleh item.');
        }

        $uom->delete();
        return redirect()->route('uoms.index')->with('success', 'UoM berhasil dihapus.');
    }
}
