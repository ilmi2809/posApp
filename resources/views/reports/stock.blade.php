@extends('layouts.app')

@section('title', 'Laporan Stok Barang - Toko Material A')
@section('page-header', 'Laporan Stok Barang')

@section('content')
<div class="card">
  <div class="card-header">
    <div class="card-title">Laporan Stok Barang Hari Ini ({{ $date->format('d/m/Y') }})</div>
    <button onclick="window.print()" class="btn btn-secondary btn-sm no-print">Cetak Laporan</button>
  </div>

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>SKU</th>
          <th>Stock</th>
          <th>UoM</th>
          <th style="text-align: right;">Qty</th>
        </tr>
      </thead>
      <tbody>
        @forelse($stocks as $stk)
        <tr>
          <td class="font-mono font-weight-bold">{{ $stk->item->sku }}</td>
          <td style="font-weight: 600;">{{ $stk->item->name }}</td>
          <td>{{ $stk->item->uom->name }}</td>
          <td style="text-align: right;" class="font-mono font-weight-bold">
            {{ number_format($stk->quantity, 0) }}
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="4" style="text-align: center; color: var(--text-secondary); padding: 30px;">
            Belum ada data stok barang.
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div style="margin-top: 16px;" class="no-print">
    {{ $stocks->links() }}
  </div>
</div>
@endsection
