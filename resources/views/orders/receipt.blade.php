@extends('layouts.app')

@section('title', 'Struk Transaksi - ' . $order->invoice_number)
@section('page-header', 'Struk Transaksi Selesai')

@section('content')
<div class="no-print" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
  <a href="{{ route('orders.index') }}" class="btn btn-secondary">← Kembali ke Riwayat</a>
  <div style="display: flex; gap: 10px;">
    <button onclick="window.print()" class="btn btn-primary">🖨️ Cetak Struk / Invoice</button>
    <a href="{{ route('orders.create') }}" class="btn btn-secondary">🛒 Order Baru</a>
  </div>
</div>

<div class="card" style="max-width: 500px; margin: 0 auto; padding: 28px; background: #FFF; border: 1px solid var(--border);">
  <div style="text-align: center; border-bottom: 2px dashed var(--border); padding-bottom: 16px; margin-bottom: 16px;">
    <div style="font-size: 20px; font-weight: 800; letter-spacing: -0.5px;">TOKO MATERIAL A</div>
    <div style="font-size: 12px; color: var(--text-secondary); margin-top: 2px;">Jl. Raya Bahan Bangunan No. 88, Jakarta</div>
    <div style="font-size: 12px; color: var(--text-secondary);">Telp: (021) 555-0199</div>
  </div>

  <div style="font-size: 12.5px; border-bottom: 1px solid var(--border); padding-bottom: 12px; margin-bottom: 14px;">
    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
      <span style="color: var(--text-secondary);">No. Invoice</span>
      <span class="font-mono" style="font-weight: 700; color: var(--accent);">{{ $order->invoice_number }}</span>
    </div>
    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
      <span style="color: var(--text-secondary);">Tanggal & Waktu</span>
      <span class="font-mono">{{ $order->transaction_date->format('d/m/Y H:i') }}</span>
    </div>
    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
      <span style="color: var(--text-secondary);">Kasir</span>
      <span>{{ $order->user->name }}</span>
    </div>
    <div style="display: flex; justify-content: space-between;">
      <span style="color: var(--text-secondary);">Metode Bayar</span>
      <span class="badge badge-secondary">{{ $order->paymentMethod->name }}</span>
    </div>
  </div>

  <!-- Items Table -->
  <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 13px;">
    <thead>
      <tr style="border-bottom: 1px solid var(--border); text-align: left;">
        <th style="padding-bottom: 8px;">Item</th>
        <th style="padding-bottom: 8px; text-align: center;">Qty</th>
        <th style="padding-bottom: 8px; text-align: right;">Harga</th>
        <th style="padding-bottom: 8px; text-align: right;">Subtotal</th>
      </tr>
    </thead>
    <tbody>
      @foreach($order->orderDetails as $detail)
      <tr style="border-bottom: 1px dashed #F1F5F9;">
        <td style="padding: 8px 0; font-weight: 500;">
          {{ $detail->item->name }}
          <div class="font-mono" style="font-size: 11px; color: var(--text-secondary);">{{ $detail->item->sku }}</div>
        </td>
        <td style="padding: 8px 0; text-align: center;" class="font-mono">
          {{ number_format($detail->qty, 0) }} {{ $detail->item->uom->symbol ?? '' }}
        </td>
        <td style="padding: 8px 0; text-align: right;" class="font-mono">
          {{ number_format($detail->price, 0, ',', '.') }}
        </td>
        <td style="padding: 8px 0; text-align: right;" class="font-mono font-weight-bold">
          {{ number_format($detail->subtotal, 0, ',', '.') }}
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <!-- Financial Summary -->
  <div style="border-top: 2px dashed var(--border); padding-top: 12px; font-size: 13px;">
    <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
      <span style="color: var(--text-secondary);">Subtotal</span>
      <span class="font-mono">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
    </div>
    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
      <span style="color: var(--text-secondary);">Pajak PPn (11%)</span>
      <span class="font-mono">Rp {{ number_format($order->tax, 0, ',', '.') }}</span>
    </div>
    <div style="display: flex; justify-content: space-between; padding-top: 8px; border-top: 1px solid var(--border); font-size: 16px; font-weight: 800;">
      <span>TOTAL BAYAR</span>
      <span class="font-mono" style="color: var(--accent);">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
    </div>
  </div>

  <div style="text-align: center; margin-top: 24px; padding-top: 16px; border-top: 1px dashed var(--border); font-size: 12px; color: var(--text-secondary);">
    Terima kasih telah berbelanja di Toko Material A!<br>
    Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.
  </div>
</div>
@endsection
