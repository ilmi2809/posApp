@extends('layouts.app')

@section('title', 'Kelola Satuan (Uom) - Toko Material A')
@section('page-header', 'Kelola Satuan (Uom)')

@section('content')
<div class="grid grid-cols-3">
  <!-- List -->
  <div style="grid-column: span 2;">
    <div class="card">
      <div class="card-title" style="margin-bottom: 16px;">Daftar Satuan (UoM)</div>
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nama Satuan</th>
              <th>Simbol</th>
              <th>Penggunaan (Item)</th>
              <th style="text-align: center;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($uoms as $u)
            <tr>
              <td class="font-mono">{{ $u->id }}</td>
              <td style="font-weight: 600;">{{ $u->name }}</td>
              <td class="font-mono"><span class="badge badge-secondary">{{ $u->symbol ?? '-' }}</span></td>
              <td class="font-mono">{{ $u->items_count }} Item</td>
              <td style="text-align: center;">
                <button onclick="editUom({{ json_encode($u) }})" class="btn btn-secondary btn-sm">Edit</button>
                @if($u->items_count == 0)
                <form action="{{ route('uoms.destroy', $u->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Hapus UoM ini?')">
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
      <div class="card-title" id="formTitle" style="margin-bottom: 16px;">Tambah Satuan Baru</div>
      <form id="uomForm" action="{{ route('uoms.store') }}" method="POST">
        @csrf
        <div id="methodContainer"></div>

        <div class="form-group">
          <label class="form-label">Nama Satuan</label>
          <input type="text" name="name" id="uom_name" class="form-control" placeholder="Contoh: Kilogram" required>
        </div>

        <div class="form-group">
          <label class="form-label">Simbol / Singkatan</label>
          <input type="text" name="symbol" id="uom_symbol" class="form-control font-mono" placeholder="Contoh: kg">
        </div>

        <div style="display: flex; gap: 10px; margin-top: 20px;">
          <button type="button" onclick="resetForm()" id="btnReset" class="btn btn-secondary" style="display: none;">Batal</button>
          <button type="submit" class="btn btn-primary btn-block">Simpan UoM</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  function editUom(uom) {
    document.getElementById('formTitle').innerText = 'Edit Satuan (UoM)';
    document.getElementById('uomForm').action = "/uoms/" + uom.id;
    document.getElementById('methodContainer').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('uom_name').value = uom.name;
    document.getElementById('uom_symbol').value = uom.symbol || '';
    document.getElementById('btnReset').style.display = 'inline-block';
  }

  function resetForm() {
    document.getElementById('formTitle').innerText = 'Tambah Satuan Baru';
    document.getElementById('uomForm').action = "{{ route('uoms.store') }}";
    document.getElementById('methodContainer').innerHTML = '';
    document.getElementById('uom_name').value = '';
    document.getElementById('uom_symbol').value = '';
    document.getElementById('btnReset').style.display = 'none';
  }
</script>
@endpush
