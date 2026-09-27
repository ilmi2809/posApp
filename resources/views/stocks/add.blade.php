@extends('layouts.app')

@section('title', 'Input Stok Masuk - Toko Material A')
@section('page-header', 'Admin Stock — Input Stok Masuk')

@section('content')
<div style="max-width: 600px; margin: 0 auto;">
  <div class="card">
    <div class="card-header">
      <div class="card-title">Tambah Stok Masuk (Stock In)</div>
      <a href="{{ route('stocks.index') }}" class="btn btn-secondary btn-sm">← Batal</a>
    </div>

    <!-- Item Information Summary -->
    <div style="background: #F8FAFC; padding: 14px; border-radius: 6px; border: 1px solid var(--border); margin-bottom: 20px;">
      <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
        <span style="color: var(--text-secondary);">SKU Barang</span>
        <span class="font-mono font-weight-bold">{{ $item->sku }}</span>
      </div>
      <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
        <span style="color: var(--text-secondary);">Nama Barang</span>
        <span style="font-weight: 600;">{{ $item->name }}</span>
      </div>
      <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
        <span style="color: var(--text-secondary);">Satuan (UoM)</span>
        <span>{{ $item->uom->name }} ({{ $item->uom->symbol }})</span>
      </div>
      <div style="display: flex; justify-content: space-between;">
        <span style="color: var(--text-secondary);">Stok Saat Ini</span>
        <span class="font-mono font-weight-bold" style="color: var(--accent);">
          {{ number_format($item->stock->quantity ?? 0, 0) }} {{ $item->uom->symbol }}
        </span>
      </div>
    </div>

    <form action="{{ route('stocks.store', $item->id) }}" method="POST">
      @csrf
      <div class="form-group">
        <label for="quantity" class="form-label">Jumlah Stok Masuk (Wajib > 0)</label>
        <input 
          type="number" 
          name="quantity" 
          id="quantity" 
          step="any"
          min="0.01" 
          class="form-control font-mono" 
          placeholder="Contoh: 50" 
          required 
          autofocus
        >
      </div>

      <div class="form-group">
        <label for="note" class="form-label">Catatan / Referensi Penerimaan (Opsional)</label>
        <textarea 
          name="note" 
          id="note" 
          rows="3" 
          class="form-control" 
          placeholder="Contoh: Penerimaan dari Supplier PT Semen Gresik Surat Jalan SJ-99182"
        ></textarea>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
        <a href="{{ route('stocks.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">➕ Simpan Stok Masuk</button>
      </div>
    </form>
  </div>
</div>
@endsection
