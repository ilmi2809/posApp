@extends('layouts.app')

@section('title', 'Master Stok Barang - Toko Material A')
@section('page-header', 'Admin Stock — Master Item & Stok Barang')

@section('content')
<div class="card">
  <div class="card-header">
    <div class="card-title">Daftar Stok Barang</div>
    @can('item.create')
    <a href="{{ route('items.index') }}" class="btn btn-secondary">Kelola Master Item</a>
    @endcan
  </div>

  <form action="{{ route('stocks.index') }}" method="GET" style="margin-bottom: 20px;">
    <div style="display: flex; gap: 12px; align-items: flex-end;">
      <div style="flex: 1;">
        <label for="search" class="form-label">Cari SKU / Nama Barang / Kategori</label>
        <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control" placeholder="Kata kunci...">
      </div>
      <div style="width: 200px;">
        <label for="status" class="form-label">Status Stok</label>
        <select name="status" id="status" class="form-select">
          <option value="">Semua Status</option>
          <option value="empty" {{ request('status') === 'empty' ? 'selected' : '' }}>Stok Habis (0)</option>
          <option value="low" {{ request('status') === 'low' ? 'selected' : '' }}>Stok Menipis (1-10)</option>
        </select>
      </div>
      <button type="submit" class="btn btn-secondary">Filter</button>
      @if(request()->hasAny(['search', 'status']))
        <a href="{{ route('stocks.index') }}" class="btn btn-secondary">Reset</a>
      @endif
    </div>
  </form>

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>SKU</th>
          <th>Nama Barang</th>
          <th>Kategori</th>
          <th>Satuan</th>
          <th style="text-align: right;">Harga Jual</th>
          <th style="text-align: right;">Stok saat Ini</th>
          <th>Status Stok</th>
          @can('stock.add')
          <th style="text-align: center;">Aksi</th>
          @endcan
        </tr>
      </thead>
      <tbody>
        @forelse($stocks as $stk)
        @php
          $qty = $stk->quantity;
        @endphp
        <tr>
          <td class="font-mono font-weight-bold">{{ $stk->item->sku }}</td>
          <td style="font-weight: 600;">{{ $stk->item->name }}</td>
          <td><span class="badge badge-secondary">{{ $stk->item->category ?? 'Umum' }}</span></td>
          <td>{{ $stk->item->uom->name }} ({{ $stk->item->uom->symbol }})</td>
          <td style="text-align: right;" class="font-mono">
            Rp {{ number_format($stk->item->price->selling_price ?? 0, 0, ',', '.') }}
          </td>
          <td style="text-align: right;" class="font-mono font-weight-bold">
            {{ number_format($qty, 0) }}
          </td>
          <td>
            @if($qty <= 0)
              <span class="badge badge-danger">Stok Habis</span>
            @elseif($qty <= 10)
              <span class="badge badge-warning">Stok Menipis</span>
            @else
              <span class="badge badge-success">Stok Normal</span>
            @endif
          </td>
          @can('stock.add')
          <td style="text-align: center;">
            <a href="{{ route('stocks.add', $stk->item->id) }}" class="btn btn-primary btn-sm">
              + Input Stok Masuk
            </a>
          </td>
          @endcan
        </tr>
        @empty
        <tr>
          <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 30px;">
            Tidak ada data stok ditemukan.
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div style="margin-top: 16px;">
    {{ $stocks->links() }}
  </div>
</div>
@endsection
