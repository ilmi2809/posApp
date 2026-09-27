@extends('layouts.app')

@section('title', 'Laporan Penjualan - Toko Material A')
@section('page-header', 'Laporan Penjualan (Sales Report)')

@section('content')
<div class="card">
  <div class="card-header">
    <div class="card-title">Filter Rentang Tanggal Penjualan</div>
    <button onclick="window.print()" class="btn btn-secondary btn-sm no-print">🖨️ Cetak Laporan</button>
  </div>

  <form action="{{ route('reports.sales') }}" method="GET" class="no-print" style="margin-bottom: 24px;">
    <div style="display: flex; gap: 16px; align-items: flex-end;">
      <div>
        <label for="start_date" class="form-label">Tanggal Awal (dd/mm/yyyy)</label>
        <input type="date" name="start_date" id="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}" class="form-control">
      </div>
      <div>
        <label for="end_date" class="form-label">Tanggal Akhir (dd/mm/yyyy)</label>
        <input type="date" name="end_date" id="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}" class="form-control">
      </div>
      <button type="submit" class="btn btn-primary">📊 Filter Laporan</button>
      @if(request()->hasAny(['start_date', 'end_date']))
        <a href="{{ route('reports.sales') }}" class="btn btn-secondary">Reset Filter</a>
      @endif
    </div>
  </form>

  <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 12px;">
    Periode Laporan: <strong>{{ $startDate->format('d-m-Y') }}</strong> s/d <strong>{{ $endDate->format('d-m-Y') }}</strong>
  </div>

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Tgl Penjualan</th>
          <th>SKU</th>
          <th>Item</th>
          <th style="text-align: right;">Qty</th>
          <th>UoM</th>
          <th style="text-align: right;">Harga Sebelum Pajak</th>
          <th style="text-align: right;">Harga Setelah Pajak</th>
        </tr>
      </thead>
      <tbody>
        @forelse($orderDetails as $detail)
        @php
          $hargaSebelumPajak = $detail->subtotal;
          $hargaSetelahPajak = round($detail->subtotal * 1.11);
        @endphp
        <tr>
          <td class="font-mono" style="font-size: 13px;">{{ $detail->order->transaction_date->format('d-m-Y') }}</td>
          <td class="font-mono font-weight-bold">{{ $detail->item->sku }}</td>
          <td style="font-weight: 600;">{{ $detail->item->name }}</td>
          <td style="text-align: right;" class="font-mono font-weight-bold">{{ number_format($detail->qty, 0) }}</td>
          <td>{{ $detail->item->uom->name }}</td>
          <td style="text-align: right;" class="font-mono">Rp {{ number_format($hargaSebelumPajak, 0, ',', '.') }}</td>
          <td style="text-align: right;" class="font-mono font-weight-bold" style="color: var(--accent);">
            Rp {{ number_format($hargaSetelahPajak, 0, ',', '.') }}
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 30px;">
            Tidak ada data transaksi penjualan pada rentang tanggal ini.
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div style="margin-top: 16px;" class="no-print">
    {{ $orderDetails->links() }}
  </div>
</div>
@endsection
