@extends('layouts.app')

@section('title', 'Kelola Barang & Stok - Toko Material A')
@section('page-header', 'Kelola Barang & Stok')

@section('content')
<div class="card">
  <div class="card-header">
    <div class="card-title">Daftar Barang & Stok</div>
    @can('item.create')
    <button onclick="document.getElementById('createItemModal').style.display='block'" class="btn btn-primary">
      + Tambah Item Baru
    </button>
    @endcan
  </div>

  <form action="{{ route('items.index') }}" method="GET" style="margin-bottom: 20px;">
    <div style="display: flex; gap: 12px; align-items: flex-end;">
      <div style="flex: 1;">
        <label for="search" class="form-label">Cari SKU / Nama Barang / Kategori</label>
        <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control" placeholder="Cari SKU, Nama Barang, Kategori...">
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
          <th style="text-align: right;">Stok saat Ini</th>
          <th>Status Stok</th>
          <th style="text-align: center;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $item)
        @php
          $qty = $item->stock->quantity ?? 0;
        @endphp
        <tr>
          <td class="font-mono font-weight-bold">{{ $item->sku }}</td>
          <td style="font-weight: 600;">{{ $item->name }}</td>
          <td><span class="badge badge-secondary">{{ $item->category ?? '-' }}</span></td>
          <td>{{ $item->uom->name }} ({{ $item->uom->symbol }})</td>
          <td style="text-align: right;" class="font-mono font-weight-bold">
            Rp {{ number_format($item->price->selling_price ?? 0, 0, ',', '.') }}
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
          <td style="text-align: center;">
            <div style="display: flex; gap: 6px; justify-content: center;">
              @can('stock.add')
              <a href="{{ route('stocks.add', $item->id) }}" class="btn btn-primary btn-sm" title="Input Stok Masuk">
                + Stok
              </a>
              @endcan
              @if(auth()->user()->can('item.update') || auth()->user()->can('item.edit'))
              <button 
                onclick="openEditModal({{ json_encode($item) }}, {{ $item->price->selling_price ?? 0 }})" 
                class="btn btn-secondary btn-sm"
              >
                Edit
              </button>
              @endif
              @can('item.delete')
              <form action="{{ route('items.destroy', $item->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Yakin ingin menghapus item ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
              </form>
              @endcan
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 30px;">
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
        <input type="text" id="edit_sku" class="form-control font-mono" readonly
          style="background-color: var(--bg); color: var(--text-secondary); cursor: not-allowed;">
        <small style="color: var(--text-secondary); font-size: 11.5px;">SKU tidak dapat diubah setelah dibuat.</small>
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
