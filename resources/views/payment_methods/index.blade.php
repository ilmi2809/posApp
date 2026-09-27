@extends('layouts.app')

@section('title', 'Metode Pembayaran - Toko Material A')
@section('page-header', 'Master Data — Master Metode Pembayaran')

@section('content')
<div class="grid grid-cols-3">
  <!-- List -->
  <div style="grid-column: span 2;">
    <div class="card">
      <div class="card-title" style="margin-bottom: 16px;">Daftar Metode Pembayaran</div>
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nama Metode Pembayaran</th>
              <th>Jumlah Transaksi</th>
              <th style="text-align: center;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($methods as $pm)
            <tr>
              <td class="font-mono">{{ $pm->id }}</td>
              <td style="font-weight: 600;">
                <span class="badge badge-secondary" style="font-size: 13px;">{{ $pm->name }}</span>
              </td>
              <td class="font-mono">{{ $pm->orders_count }} Transaksi</td>
              <td style="text-align: center;">
                <button onclick="editPm({{ json_encode($pm) }})" class="btn btn-secondary btn-sm">Edit</button>
                @if($pm->orders_count == 0)
                <form action="{{ route('payment-methods.destroy', $pm->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Hapus metode ini?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                </form>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Form Create / Edit -->
  <div>
    <div class="card">
      <div class="card-title" id="pmFormTitle" style="margin-bottom: 16px;">Tambah Metode Pembayaran</div>
      <form id="pmForm" action="{{ route('payment-methods.store') }}" method="POST">
        @csrf
        <div id="pmMethodContainer"></div>

        <div class="form-group">
          <label class="form-label">Nama Metode Pembayaran</label>
          <input type="text" name="name" id="pm_name" class="form-control" placeholder="Contoh: QRIS / Transfer BCA" required>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 20px;">
          <button type="button" onclick="resetPmForm()" id="pmBtnReset" class="btn btn-secondary" style="display: none;">Batal</button>
          <button type="submit" class="btn btn-primary btn-block">Simpan Metode</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  function editPm(pm) {
    document.getElementById('pmFormTitle').innerText = 'Edit Metode Pembayaran';
    document.getElementById('pmForm').action = "/payment-methods/" + pm.id;
    document.getElementById('pmMethodContainer').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('pm_name').value = pm.name;
    document.getElementById('pmBtnReset').style.display = 'inline-block';
  }

  function resetPmForm() {
    document.getElementById('pmFormTitle').innerText = 'Tambah Metode Pembayaran';
    document.getElementById('pmForm').action = "{{ route('payment-methods.store') }}";
    document.getElementById('pmMethodContainer').innerHTML = '';
    document.getElementById('pm_name').value = '';
    document.getElementById('pmBtnReset').style.display = 'none';
  }
</script>
@endpush
