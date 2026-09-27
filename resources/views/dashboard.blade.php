@extends('layouts.app')

@section('title', 'Dashboard - Toko Material A')
@section('page-header', 'Dashboard Eksekutif')

@section('content')
<div class="grid grid-cols-4" style="margin-bottom: 24px;">
  <div class="card" style="margin-bottom: 0;">
    <div style="font-size: 12px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase;">Penjualan Hari Ini</div>
    <div style="font-size: 22px; font-weight: 700; color: var(--accent); margin-top: 6px;" class="font-mono">
      Rp {{ number_format($totalSalesToday, 0, ',', '.') }}
    </div>
  </div>

  <div class="card" style="margin-bottom: 0;">
    <div style="font-size: 12px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase;">Transaksi Hari Ini</div>
    <div style="font-size: 22px; font-weight: 700; color: var(--text-primary); margin-top: 6px;" class="font-mono">
      {{ $totalOrdersToday }} Order
    </div>
  </div>

  <div class="card" style="margin-bottom: 0;">
    <div style="font-size: 12px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase;">Total Master Item</div>
    <div style="font-size: 22px; font-weight: 700; color: var(--text-primary); margin-top: 6px;" class="font-mono">
      {{ $totalItems }} Item
    </div>
  </div>

  <div class="card" style="margin-bottom: 0;">
    <div style="font-size: 12px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase;">Peringatan Stok Menipis</div>
    <div style="font-size: 22px; font-weight: 700; color: {{ count($lowStockItems) > 0 ? 'var(--warning)' : 'var(--success)' }}; margin-top: 6px;" class="font-mono">
      {{ count($lowStockItems) }} Item
    </div>
  </div>
</div>

<div class="grid grid-cols-2">
  <!-- Peringatan Stok -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">⚠️ Item Dengan Stok Menipis (≤ 10)</div>
      <a href="{{ route('stocks.index') }}" class="btn btn-secondary btn-sm">Kelola Stok</a>
    </div>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>SKU</th>
            <th>Nama Item</th>
            <th style="text-align: right;">Stok</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($lowStockItems as $stk)
          <tr>
            <td class="font-mono">{{ $stk->item->sku }}</td>
            <td style="font-weight: 500;">{{ $stk->item->name }}</td>
            <td style="text-align: right;" class="font-mono">{{ number_format($stk->quantity, 0) }} {{ $stk->item->uom->symbol ?? $stk->item->uom->name }}</td>
            <td>
              @if($stk->quantity <= 0)
                <span class="badge badge-danger">Stok Habis</span>
              @else
                <span class="badge badge-warning">Stok Menipis</span>
              @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="4" style="text-align: center; color: var(--text-secondary); padding: 20px;">
              ✅ Semua stok barang dalam kondisi aman.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- Transaksi Terakhir -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">🧾 Transaksi Penjualan Terbaru</div>
      <a href="{{ route('orders.index') }}" class="btn btn-secondary btn-sm">Lihat Semua</a>
    </div>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>No Invoice</th>
            <th>Kasir</th>
            <th style="text-align: right;">Total</th>
            <th>Waktu</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentOrders as $ord)
          <tr>
            <td class="font-mono font-weight-bold">
              <a href="{{ route('orders.receipt', $ord->id) }}" style="color: var(--accent); text-decoration: none;">
                {{ $ord->invoice_number }}
              </a>
            </td>
            <td>{{ $ord->user->name }}</td>
            <td style="text-align: right;" class="font-mono font-weight-bold">
              Rp {{ number_format($ord->total, 0, ',', '.') }}
            </td>
            <td style="font-size: 12px; color: var(--text-secondary);">
              {{ $ord->created_at->format('d/m/Y H:i') }}
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="4" style="text-align: center; color: var(--text-secondary); padding: 20px;">
              Belum ada transaksi hari ini.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
