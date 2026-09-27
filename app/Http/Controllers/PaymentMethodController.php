<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $methods = PaymentMethod::withCount('orders')->get();

        return view('payment_methods.index', compact('methods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:payment_methods,name'],
        ]);

        PaymentMethod::create($request->only('name'));

        return redirect()->route('payment-methods.index')->with('success', 'Metode Pembayaran baru berhasil disimpan.');
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:payment_methods,name,'.$paymentMethod->id],
        ]);

        $paymentMethod->update($request->only('name'));

        return redirect()->route('payment-methods.index')->with('success', 'Metode Pembayaran berhasil diperbarui.');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        if ($paymentMethod->orders()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus metode pembayaran yang memiliki histori transaksi.');
        }

        $paymentMethod->delete();

        return redirect()->route('payment-methods.index')->with('success', 'Metode Pembayaran berhasil dihapus.');
    }
}
