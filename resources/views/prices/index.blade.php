@extends('layouts.app')

@section('title', 'Master Harga Jual - Toko Material A')
@section('page-header', 'Master Data — Master Harga Jual Item')

@section('content')
<div class="card">
  <div class="card-header">
    <div class="card-title">Pengaturan Harga Jual Barang</div>
  </div>

  <form action="{{ route('prices.index') }}" method="GET" style="margin-bottom: 20px;">
    <div style="display: flex; gap: 12px;">
      <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari SKU atau Nama Item...">
      <button type="submit" class="btn btn-secondary">Cari</button>
      @if(request('search'))
        <a href="{{ route('prices.index') }}" class="btn btn-secondary">Reset</a>
      @endif
    </div>
  </form>

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>SKU</th>
          <th>Nama Item</th>
          <th>Satuan</th>
          <th style="text-align: right;">Harga Jual Aktif</th>
          <th>Terakhir Diperbarui</th>
          <th style="text-align: center;">Aksi Edit Harga</th>
        </tr>
      </thead>
      <tbody>
        @foreach($items as $item)
        @php $price = $item->price->selling_price ?? 0; @endphp
        <tr>
          <td class="font-mono font-weight-bold">{{ $item->sku }}</td>
          <td style="font-weight: 600;">{{ $item->name }}</td>
          <td>{{ $item->uom->name }} ({{ $item->uom->symbol }})</td>
          <td style="text-align: right;" class="font-mono font-weight-bold" style="color: var(--accent);">
            Rp {{ number_format($price, 0, ',', '.') }}
          </td>
          <td style="font-size: 12.5px; color: var(--text-secondary);">
            {{ $item->price ? $item->price->updated_at->format('d/m/Y H:i') : '-' }}
          </td>
          <td style="text-align: center;">
            <button onclick="openPriceModal({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $price }})" class="btn btn-primary btn-sm">
              ✏️ Update Harga
            </button>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div style="margin-top: 16px;">
    {{ $items->links() }}
  </div>
</div>

<!-- Modal Update Harga -->
<div id="updatePriceModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 200; align-items: center; justify-content: center; padding: 20px;">
  <div style="background: #FFF; width: 100%; max-width: 400px; border-radius: 8px; padding: 24px; margin: 40px auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
      <h3 style="font-size: 16px; font-weight: 700;">Update Harga Jual</h3>
      <button onclick="document.getElementById('updatePriceModal').style.display='none'" style="background: none; border: none; font-size: 18px; cursor: pointer;">✕</button>
    </div>

    <form id="updatePriceForm" method="POST">
      @csrf
      @method('PUT')

      <div style="margin-bottom: 16px;">
        <span style="font-size: 12px; color: var(--text-secondary);">Nama Barang:</span>
        <div id="price_item_name" style="font-weight: 700; font-size: 14px; margin-top: 2px;"></div>
      </div>

      <div class="form-group">
        <label class="form-label">Harga Jual Baru (Rp)</label>
        <input type="number" id="price_selling_price" name="selling_price" step="any" min="0" class="form-control font-mono" required autofocus>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
        <button type="button" onclick="document.getElementById('updatePriceModal').style.display='none'" class="btn btn-secondary">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Harga</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  function openPriceModal(itemId, itemName, currentPrice) {
    document.getElementById('updatePriceForm').action = "/prices/" + itemId;
    document.getElementById('price_item_name').innerText = itemName;
    document.getElementById('price_selling_price').value = currentPrice;
    document.getElementById('updatePriceModal').style.display = 'block';
  }
</script>
@endpush
