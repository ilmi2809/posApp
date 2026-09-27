@extends('layouts.app')

@section('title', 'Riwayat Mutasi Stok - Toko Material A')
@section('page-header', 'Admin Stock — Riwayat Pergerakan Stok')

@section('content')
<div class="card">
  <div class="card-header">
    <div class="card-title">Audit Trail Pergerakan Stok (In / Out)</div>
  </div>

  <form action="{{ route('stocks.movements') }}" method="GET" style="margin-bottom: 20px;">
    <div style="display: flex; gap: 12px; align-items: flex-end;">
      <div style="flex: 1;">
        <label for="search" class="form-label">Cari SKU / Nama Barang</label>
        <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control" placeholder="Search item...">
      </div>
      <div style="width: 180px;">
        <label for="type" class="form-label">Jenis Mutasi</label>
        <select name="type" id="type" class="form-select">
          <option value="">Semua Mutasi</option>
          <option value="IN" {{ request('type') === 'IN' ? 'selected' : '' }}>Stok Masuk (IN)</option>
          <option value="OUT" {{ request('type') === 'OUT' ? 'selected' : '' }}>Stok Keluar (OUT)</option>
        </select>
      </div>
      <button type="submit" class="btn btn-secondary">Filter</button>
      @if(request()->hasAny(['search', 'type']))
        <a href="{{ route('stocks.movements') }}" class="btn btn-secondary">Reset</a>
      @endif
    </div>
  </form>

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Tanggal & Waktu</th>
          <th>SKU</th>
          <th>Nama Barang</th>
          <th>Jenis Mutasi</th>
          <th style="text-align: right;">Jumlah</th>
          <th style="text-align: right;">Stok Awal</th>
          <th style="text-align: right;">Stok Akhir</th>
          <th>User / Operator</th>
          <th>Catatan / Ref</th>
        </tr>
      </thead>
      <tbody>
        @forelse($movements as $mvt)
        <tr>
          <td class="font-mono" style="font-size: 12.5px;">{{ $mvt->created_at->format('d/m/Y H:i:s') }}</td>
          <td class="font-mono font-weight-bold">{{ $mvt->item->sku }}</td>
          <td style="font-weight: 500;">{{ $mvt->item->name }}</td>
          <td>
            @if($mvt->type === 'IN')
              <span class="badge badge-success">🟢 STOK MASUK</span>
            @else
              <span class="badge badge-danger">🔴 STOK KELUAR</span>
            @endif
          </td>
          <td style="text-align: right;" class="font-mono font-weight-bold {{ $mvt->type === 'IN' ? 'text-success' : 'text-danger' }}">
            {{ $mvt->type === 'IN' ? '+' : '-' }}{{ number_format($mvt->quantity, 0) }} {{ $mvt->item->uom->symbol ?? '' }}
          </td>
          <td style="text-align: right;" class="font-mono">{{ number_format($mvt->stock_before, 0) }}</td>
          <td style="text-align: right;" class="font-mono font-weight-bold">{{ number_format($mvt->stock_after, 0) }}</td>
          <td>{{ $mvt->user->name }}</td>
          <td style="font-size: 12.5px; color: var(--text-secondary);">
            {{ $mvt->note ?? '-' }}
            @if($mvt->order)
              (<a href="{{ route('orders.receipt', $mvt->order->id) }}" style="color: var(--accent);">{{ $mvt->order->invoice_number }}</a>)
            @endif
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="9" style="text-align: center; color: var(--text-secondary); padding: 30px;">
            Belum ada riwayat mutasi stok.
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div style="margin-top: 16px;">
    {{ $movements->links() }}
  </div>
</div>
@endsection
