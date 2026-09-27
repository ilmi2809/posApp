@extends('layouts.app')

@section('title', 'Master Role & Privilege - Toko Material A')
@section('page-header', 'Superadmin — Master Role & Management Privilege (RBAC)')

@section('content')
<div class="card">
  <div class="card-header">
    <div class="card-title">Daftar Role & Matrix Privilege</div>
    <button onclick="document.getElementById('createRoleModal').style.display='block'" class="btn btn-primary">
      + Tambah Role Baru
    </button>
  </div>

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Nama Role</th>
          <th>Jumlah Privilege</th>
          <th>Daftar Hak Akses</th>
          <th style="text-align: center;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($roles as $role)
        <tr>
          <td style="font-weight: 700; width: 160px;">
            @if($role->name === 'Superadmin')
              <span class="badge badge-danger">Superadmin</span>
            @elseif($role->name === 'Admin Stock')
              <span class="badge badge-warning">Admin Stock</span>
            @else
              <span class="badge badge-success">Kasir</span>
            @endif
          </td>
          <td class="font-mono">{{ $role->permissions->count() }} Hak Akses</td>
          <td>
            <div style="display: flex; flex-wrap: wrap; gap: 4px; max-width: 600px;">
              @foreach($role->permissions->take(8) as $perm)
                <span class="badge badge-secondary" style="font-size: 10px;">{{ $perm->name }}</span>
              @endforeach
              @if($role->permissions->count() > 8)
                <span class="badge badge-secondary" style="font-size: 10px;">+{{ $role->permissions->count() - 8 }} lainnya</span>
              @endif
            </div>
          </td>
          <td style="text-align: center; width: 140px;">
            <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-primary btn-sm">⚙️ Atur Privilege</a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Create Role -->
<div id="createRoleModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 200; align-items: center; justify-content: center; padding: 20px;">
  <div style="background: #FFF; width: 100%; max-width: 480px; border-radius: 8px; padding: 24px; margin: 40px auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
      <h3 style="font-size: 16px; font-weight: 700;">Tambah Role Baru</h3>
      <button onclick="document.getElementById('createRoleModal').style.display='none'" style="background: none; border: none; font-size: 18px; cursor: pointer;">✕</button>
    </div>

    <form action="{{ route('roles.store') }}" method="POST">
      @csrf
      <div class="form-group">
        <label class="form-label">Nama Role</label>
        <input type="text" name="name" class="form-control" placeholder="Contoh: Supervisor" required>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
        <button type="button" onclick="document.getElementById('createRoleModal').style.display='none'" class="btn btn-secondary">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Role</button>
      </div>
    </form>
  </div>
</div>
@endsection
