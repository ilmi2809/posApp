@extends('layouts.app')

@section('title', 'Master Item - Toko Material A')
@section('page-header', 'Master Data — Master Item Barang')

@section('content')
<div class="card">
  <div class="card-header">
    <div class="card-title">Daftar Item Barang</div>
    <button onclick="document.getElementById('createItemModal').style.display='block'" class="btn btn-primary">
      + Tambah Item Baru
    </button>
  </div>

  <form action="{{ route('items.index') }}" method="GET" style="margin-bottom: 20px;">
    <div style="display: flex; gap: 12px;">
      <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari SKU, Nama Item, Kategori...">
      <button type="submit" class="btn btn-secondary">Cari</button>
      @if(request('search'))
        <a href="{{ route('items.index') }}" class="btn btn-secondary">Reset</a>
      @endif
    </div>
  </form>

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>SKU</th>
          <th>Nama Item</th>
          <th>Kategori</th>
          <th>Satuan (UoM)</th>
          <th style="text-align: right;">Harga Jual</th>
          <th style="text-align: right;">Stok</th>
          <th style="text-align: center;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $item)
        <tr>
          <td class="font-mono font-weight-bold">{{ $item->sku }}</td>
          <td style="font-weight: 600;">{{ $item->name }}</td>
          <td><span class="badge badge-secondary">{{ $item->category ?? '-' }}</span></td>
          <td>{{ $item->uom->name }} ({{ $item->uom->symbol }})</td>
          <td style="text-align: right;" class="font-mono font-weight-bold">
            Rp {{ number_format($item->price->selling_price ?? 0, 0, ',', '.') }}
          </td>
          <td style="text-align: right;" class="font-mono">
            {{ number_format($item->stock->quantity ?? 0, 0) }}
          </td>
          <td style="text-align: center;">
            <button 
              onclick="openEditModal({{ json_encode($item) }}, {{ $item->price->selling_price ?? 0 }})" 
              class="btn btn-secondary btn-sm"
            >
              Edit
            </button>
            <form action="{{ route('items.destroy', $item->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Yakin ingin menghapus item ini?')">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
            </form>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 30px;">
            Belum ada item barang.
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div style="margin-top: 16px;">
    {{ $items->links() }}
  </div>
</div>

<!-- Modal Create Item -->
<div id="createItemModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 200; align-items: center; justify-content: center; padding: 20px;">
  <div style="background: #FFF; width: 100%; max-width: 500px; border-radius: 8px; padding: 24px; margin: 40px auto; position: relative;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
      <h3 style="font-size: 16px; font-weight: 700;">Tambah Item Barang Baru</h3>
      <button onclick="document.getElementById('createItemModal').style.display='none'" style="background: none; border: none; font-size: 18px; cursor: pointer;">✕</button>
    </div>

    <form action="{{ route('items.store') }}" method="POST">
      @csrf
      <div class="form-group">
        <label class="form-label">SKU Barang (Unik)</label>
        <input type="text" name="sku" class="form-control font-mono" placeholder="Contoh: MAT-SMN-002" required>
      </div>

      <div class="form-group">
        <label class="form-label">Nama Barang</label>
        <input type="text" name="name" class="form-control" placeholder="Contoh: Semen Holcim 50kg" required>
      </div>

      <div class="grid grid-cols-2">
        <div class="form-group">
          <label class="form-label">Kategori</label>
          <input type="text" name="category" class="form-control" placeholder="Contoh: Semen">
        </div>

        <div class="form-group">
          <label class="form-label">Satuan (UoM)</label>
          <select name="uom_id" class="form-select" required>
            @foreach($uoms as $u)
              <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->symbol }})</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="grid grid-cols-2">
        <div class="form-group">
          <label class="form-label">Harga Jual (Rp)</label>
          <input type="number" name="selling_price" step="any" min="0" class="form-control font-mono" placeholder="75000" required>
        </div>

        <div class="form-group">
          <label class="form-label">Stok Awal</label>
          <input type="number" name="initial_stock" step="any" min="0" value="0" class="form-control font-mono" placeholder="0">
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
        <button type="button" onclick="document.getElementById('createItemModal').style.display='none'" class="btn btn-secondary">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Item</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit Item -->
<div id="editItemModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 200; align-items: center; justify-content: center; padding: 20px;">
  <div style="background: #FFF; width: 100%; max-width: 500px; border-radius: 8px; padding: 24px; margin: 40px auto; position: relative;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
      <h3 style="font-size: 16px; font-weight: 700;">Edit Item Barang</h3>
      <button onclick="document.getElementById('editItemModal').style.display='none'" style="background: none; border: none; font-size: 18px; cursor: pointer;">✕</button>
    </div>

    <form id="editItemForm" method="POST">
      @csrf
      @method('PUT')
      <div class="form-group">
        <label class="form-label">SKU Barang</label>
        <input type="text" id="edit_sku" name="sku" class="form-control font-mono" required>
      </div>

      <div class="form-group">
        <label class="form-label">Nama Barang</label>
        <input type="text" id="edit_name" name="name" class="form-control" required>
      </div>

      <div class="grid grid-cols-2">
        <div class="form-group">
          <label class="form-label">Kategori</label>
          <input type="text" id="edit_category" name="category" class="form-control">
        </div>

        <div class="form-group">
          <label class="form-label">Satuan (UoM)</label>
          <select id="edit_uom_id" name="uom_id" class="form-select" required>
            @foreach($uoms as $u)
              <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->symbol }})</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Harga Jual (Rp)</label>
        <input type="number" id="edit_selling_price" name="selling_price" step="any" min="0" class="form-control font-mono" required>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
        <button type="button" onclick="document.getElementById('editItemModal').style.display='none'" class="btn btn-secondary">Batal</button>
        <button type="submit" class="btn btn-primary">Update Item</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  function openEditModal(item, price) {
    document.getElementById('editItemForm').action = "/items/" + item.id;
    document.getElementById('edit_sku').value = item.sku;
    document.getElementById('edit_name').value = item.name;
    document.getElementById('edit_category').value = item.category || '';
    document.getElementById('edit_uom_id').value = item.uom_id;
    document.getElementById('edit_selling_price').value = price;
    document.getElementById('editItemModal').style.display = 'block';
  }
</script>
@endpush
