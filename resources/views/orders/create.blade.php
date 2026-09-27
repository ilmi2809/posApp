@extends('layouts.app')

@section('title', 'Tambah Order Baru - Toko Material A')
@section('page-header', 'Tambah Order Penjualan')

@section('content')
<div style="margin-bottom: 14px;">
  <a href="{{ route('orders.index') }}" class="btn btn-secondary btn-sm">← Kembali ke Data Transaksi</a>
</div>
<div class="pos-container">
  <!-- Left Side: Items Catalog & Search -->
  <div>
    <div class="card" style="padding: 16px; margin-bottom: 16px;">
      <div style="display: flex; gap: 12px; align-items: center;">
        <input 
          type="text" 
          id="itemSearchInput" 
          class="form-control" 
          placeholder="🔍 Cari SKU, Nama Barang, atau Kategori..." 
          onkeyup="filterItems()"
          autofocus
        >
      </div>
    </div>

    <div class="card" style="padding: 0; overflow: hidden;">
      <div class="table-responsive">
        <table class="table" id="itemsTable">
          <thead>
            <tr>
              <th>SKU</th>
              <th>Nama Barang</th>
              <th>Kategori</th>
              <th style="text-align: right;">Harga Jual</th>
              <th style="text-align: center;">Stok</th>
              <th style="text-align: center;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($items as $item)
            @php
              $currentStock = $item->stock->quantity ?? 0;
              $price = $item->price->selling_price ?? 0;
            @endphp
            <tr data-item-id="{{ $item->id }}" data-sku="{{ $item->sku }}" data-name="{{ $item->name }}" data-price="{{ $price }}" data-stock="{{ $currentStock }}" data-uom="{{ $item->uom->symbol ?? $item->uom->name }}">
              <td class="font-mono">{{ $item->sku }}</td>
              <td style="font-weight: 600;">{{ $item->name }}</td>
              <td><span class="badge badge-secondary">{{ $item->category ?? 'Umum' }}</span></td>
              <td style="text-align: right;" class="font-mono">Rp {{ number_format($price, 0, ',', '.') }}</td>
              <td style="text-align: center;" class="font-mono">
                @if($currentStock <= 0)
                  <span class="badge badge-danger">0 {{ $item->uom->symbol ?? '' }}</span>
                @elseif($currentStock <= 10)
                  <span class="badge badge-warning">{{ number_format($currentStock, 0) }} {{ $item->uom->symbol ?? '' }}</span>
                @else
                  <span class="badge badge-success">{{ number_format($currentStock, 0) }} {{ $item->uom->symbol ?? '' }}</span>
                @endif
              </td>
              <td style="text-align: center;">
                <button 
                  type="button" 
                  class="btn btn-primary btn-sm"
                  onclick="addToCart({{ $item->id }}, '{{ addslashes($item->sku) }}', '{{ addslashes($item->name) }}', {{ $price }}, {{ $currentStock }}, '{{ addslashes($item->uom->symbol ?? $item->uom->name) }}')"
                  {{ $currentStock <= 0 ? 'disabled' : '' }}
                >
                  + Tambah
                </button>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Right Side: Sticky Cart Summary -->
  <div class="pos-cart-sticky">
    <form action="{{ route('orders.store') }}" method="POST" id="orderForm">
      @csrf
      <div class="card" style="border-top: 4px solid var(--accent);">
        <div class="card-header" style="margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid var(--border);">
          <div class="card-title">🛒 Ringkasan Order</div>
          <span style="font-size: 12px; color: var(--text-secondary);" id="cartItemCount">0 item</span>
        </div>

        <!-- Stock Error Alert Banner -->
        <div id="stockErrorAlert" class="alert alert-danger" style="display: none; padding: 10px; font-size: 12.5px; margin-bottom: 12px;">
          ⚠️ <strong>Stok Tidak Mencukupi!</strong> Ada item dengan jumlah pesanan melebihi stok yang tersedia.
        </div>

        <!-- Cart Items List -->
        <div id="cartEmptyState" style="text-align: center; padding: 30px 10px; color: var(--text-secondary);">
          <div style="font-size: 32px; margin-bottom: 8px;">🛒</div>
          <div>Keranjang masih kosong.</div>
          <div style="font-size: 12px; margin-top: 4px;">Pilih barang dari daftar di sebelah kiri.</div>
        </div>

        <div id="cartList" style="max-height: 280px; overflow-y: auto; margin-bottom: 16px;">
          <!-- Cart rows populated dynamically -->
        </div>

        <!-- Order Totals Breakdown -->
        <div style="background: #F8FAFC; padding: 14px; border-radius: 6px; border: 1px solid var(--border); margin-bottom: 16px;">
          <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13px;">
            <span style="color: var(--text-secondary);">Subtotal Item</span>
            <span class="font-mono font-weight-bold" id="cartSubtotalText">Rp 0</span>
          </div>

          <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;">
            <span style="color: var(--text-secondary);">Pajak PPn (11%)</span>
            <span class="font-mono font-weight-bold" id="cartTaxText">Rp 0</span>
          </div>

          <div style="display: flex; justify-content: space-between; padding-top: 8px; border-top: 1px dashed var(--border); font-size: 16px; font-weight: 700;">
            <span>TOTAL</span>
            <span class="font-mono" style="color: var(--accent);" id="cartTotalText">Rp 0</span>
          </div>
        </div>

        <!-- Payment Method -->
        <div class="form-group">
          <label for="payment_method_id" class="form-label">Metode Pembayaran</label>
          <select name="payment_method_id" id="payment_method_id" class="form-select" required>
            @foreach($paymentMethods as $pm)
              <option value="{{ $pm->id }}" {{ strtolower($pm->name) === 'cash' ? 'selected' : '' }}>{{ $pm->name }}</option>
            @endforeach
          </select>
        </div>

        <!-- Process Transaction Button -->
        <button type="submit" id="btnProcessOrder" class="btn btn-primary btn-block" style="padding: 12px; font-size: 15px;" disabled>
          ✅ Proses Transaksi Order
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  let cart = {};

  function filterItems() {
    const query = document.getElementById('itemSearchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#itemsTable tbody tr');
    rows.forEach(row => {
      const sku = row.getAttribute('data-sku').toLowerCase();
      const name = row.getAttribute('data-name').toLowerCase();
      const category = row.getAttribute('data-category') ? row.getAttribute('data-category').toLowerCase() : '';
      if (sku.includes(query) || name.includes(query) || category.includes(query)) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
  }

  function addToCart(id, sku, name, price, stock, uom) {
    if (cart[id]) {
      if (cart[id].qty + 1 <= stock) {
        cart[id].qty += 1;
      } else {
        cart[id].qty += 1; // allow input to show error validation
      }
    } else {
      cart[id] = { id, sku, name, price, stock, uom, qty: 1 };
    }
    renderCart();
  }

  function updateQty(id, delta) {
    if (!cart[id]) return;
    cart[id].qty += delta;
    if (cart[id].qty <= 0) {
      delete cart[id];
    }
    renderCart();
  }

  function setQty(id, value) {
    if (!cart[id]) return;
    const qty = parseFloat(value) || 0;
    if (qty <= 0) {
      delete cart[id];
    } else {
      cart[id].qty = qty;
    }
    renderCart();
  }

  function removeFromCart(id) {
    delete cart[id];
    renderCart();
  }

  function renderCart() {
    const cartList = document.getElementById('cartList');
    const cartEmptyState = document.getElementById('cartEmptyState');
    const btnProcessOrder = document.getElementById('btnProcessOrder');
    const stockErrorAlert = document.getElementById('stockErrorAlert');
    
    cartList.innerHTML = '';
    const keys = Object.keys(cart);
    
    let subtotal = 0;
    let hasStockError = false;
    let totalItemCount = 0;

    if (keys.length === 0) {
      cartEmptyState.style.display = 'block';
      cartList.style.display = 'none';
      btnProcessOrder.disabled = true;
      stockErrorAlert.style.display = 'none';
      document.getElementById('cartSubtotalText').innerText = 'Rp 0';
      document.getElementById('cartTaxText').innerText = 'Rp 0';
      document.getElementById('cartTotalText').innerText = 'Rp 0';
      document.getElementById('cartItemCount').innerText = '0 item';
      return;
    }

    cartEmptyState.style.display = 'none';
    cartList.style.display = 'block';

    keys.forEach((id, index) => {
      const item = cart[id];
      const itemSubtotal = item.price * item.qty;
      subtotal += itemSubtotal;
      totalItemCount += item.qty;

      const isOverStock = item.qty > item.stock;
      if (isOverStock) {
        hasStockError = true;
      }

      const itemRow = document.createElement('div');
      itemRow.style.cssText = `
        padding: 10px; 
        border-radius: 6px; 
        margin-bottom: 8px; 
        border: 1px solid ${isOverStock ? 'var(--danger)' : 'var(--border)'};
        background: ${isOverStock ? 'var(--danger-bg)' : '#FAFBFD'};
      `;

      itemRow.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
          <div>
            <div style="font-weight: 600; font-size: 13px; color: var(--text-primary);">${item.name}</div>
            <div class="font-mono" style="font-size: 11px; color: var(--text-secondary);">${item.sku} • Rp ${item.price.toLocaleString('id-ID')} / ${item.uom}</div>
          </div>
          <button type="button" onclick="removeFromCart(${item.id})" style="background: none; border: none; color: var(--danger); cursor: pointer; font-size: 14px; font-weight: bold;">✕</button>
        </div>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
          <div style="display: flex; align-items: center; gap: 4px;">
            <button type="button" class="btn btn-secondary btn-sm" style="padding: 2px 8px;" onclick="updateQty(${item.id}, -1)">-</button>
            <input 
              type="number" 
              name="items[${index}][qty]" 
              value="${item.qty}" 
              min="1"
              max="${item.stock}"
              step="any"
              onchange="setQty(${item.id}, this.value)"
              class="form-control font-mono" 
              style="width: 60px; text-align: center; padding: 2px 4px; height: 28px;"
            >
            <input type="hidden" name="items[${index}][item_id]" value="${item.id}">
            <button type="button" class="btn btn-secondary btn-sm" style="padding: 2px 8px;" onclick="updateQty(${item.id}, 1)">+</button>
            <span style="font-size: 11px; color: var(--text-secondary); margin-left: 4px;">(Stok: ${item.stock})</span>
          </div>

          <div class="font-mono font-weight-bold" style="font-size: 13px; color: ${isOverStock ? 'var(--danger)' : 'var(--text-primary)'};">
            Rp ${itemSubtotal.toLocaleString('id-ID')}
          </div>
        </div>

        ${isOverStock ? `<div style="font-size: 11px; color: var(--danger); margin-top: 4px; font-weight: 600;">⚠️ Melebihi stok tersedia (${item.stock} ${item.uom})</div>` : ''}
      `;

      cartList.appendChild(itemRow);
    });

    const tax = Math.round(subtotal * 0.11);
    const total = subtotal + tax;

    document.getElementById('cartSubtotalText').innerText = `Rp ${subtotal.toLocaleString('id-ID')}`;
    document.getElementById('cartTaxText').innerText = `Rp ${tax.toLocaleString('id-ID')}`;
    document.getElementById('cartTotalText').innerText = `Rp ${total.toLocaleString('id-ID')}`;
    document.getElementById('cartItemCount').innerText = `${keys.length} jenis (${totalItemCount} item)`;

    if (hasStockError) {
      stockErrorAlert.style.display = 'block';
      btnProcessOrder.disabled = true;
    } else {
      stockErrorAlert.style.display = 'none';
      btnProcessOrder.disabled = false;
    }
  }
</script>
@endpush
