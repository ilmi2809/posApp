@extends('layouts.app')

@section('title', 'Transaksi Penjualan - Toko Material A')
@section('page-header', 'Transaksi Penjualan')

@section('content')
<div class="card">
  <div class="card-header">
    <div class="card-title">Data Transaksi Penjualan</div>
    @can('order.create')
    <a href="{{ route('orders.create') }}" class="btn btn-primary">+ Tambah Order Baru</a>
    @endcan
  </div>

  <!-- Filter Form -->
  <form action="{{ route('orders.index') }}" method="GET" style="margin-bottom: 20px;">
    <div style="display: flex; gap: 12px; align-items: flex-end;">
      <div style="flex: 1;">
        <label for="search" class="form-label">Cari Invoice / Kasir</label>
        <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control" placeholder="No Invoice / Nama Kasir...">
      </div>
      <div style="width: 180px;">
        <label for="date" class="form-label">Tanggal Transaksi</label>
        <input type="date" name="date" id="date" value="{{ request('date') }}" class="form-control">
      </div>
      <button type="submit" class="btn btn-secondary">Filter</button>
      @if(request()->hasAny(['search', 'date']))
        <a href="{{ route('orders.index') }}" class="btn btn-secondary">Reset</a>
      @endif
    </div>
  </form>

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>No. Invoice</th>
          <th>Tanggal & Waktu</th>
          <th>Kasir</th>
          <th>Metode Bayar</th>
          <th style="text-align: right;">Subtotal</th>
          <th style="text-align: right;">Pajak (11%)</th>
          <th style="text-align: right;">Total</th>
          <th style="text-align: center;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($orders as $ord)
        <tr>
          <td class="font-mono font-weight-bold" style="color: var(--accent);">
            {{ $ord->invoice_number }}
          </td>
          <td class="font-mono" style="font-size: 13px;">
            {{ $ord->transaction_date->format('d/m/Y H:i') }}
          </td>
          <td style="font-weight: 500;">{{ $ord->user->name }}</td>
          <td><span class="badge badge-secondary">{{ $ord->paymentMethod->name }}</span></td>
          <td style="text-align: right;" class="font-mono">Rp {{ number_format($ord->subtotal, 0, ',', '.') }}</td>
          <td style="text-align: right;" class="font-mono">Rp {{ number_format($ord->tax, 0, ',', '.') }}</td>
          <td style="text-align: right;" class="font-mono font-weight-bold">Rp {{ number_format($ord->total, 0, ',', '.') }}</td>
          <td style="text-align: center;">
            <a href="{{ route('orders.receipt', $ord->id) }}" class="btn btn-secondary btn-sm">🖨️ Struk</a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 30px;">
            Belum ada transaksi ditemukan.
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div style="margin-top: 16px;">
    {{ $orders->links() }}
  </div>
</div>
@endsection
