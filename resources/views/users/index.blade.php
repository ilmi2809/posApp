@extends('layouts.app')

@section('title', 'Master User - Toko Material A')
@section('page-header', 'Superadmin — Kelola Master User & Akun')

@section('content')
<div class="card">
  <div class="card-header">
    <div class="card-title">Daftar Pengguna Sistem</div>
    <button onclick="document.getElementById('createUserModal').style.display='block'" class="btn btn-primary">
      + Tambah User Baru
    </button>
  </div>

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Nama Lengkap</th>
          <th>Username</th>
          <th>Email</th>
          <th>Role / Jabatan</th>
          <th>Tanggal Dibuat</th>
          <th style="text-align: center;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($users as $usr)
        <tr>
          <td style="font-weight: 600;">{{ $usr->name }}</td>
          <td class="font-mono">{{ $usr->username }}</td>
          <td>{{ $usr->email ?? '-' }}</td>
          <td>
            @php $roleName = $usr->getRoleNames()->first() ?? 'Tidak ada'; @endphp
            @if($roleName === 'Superadmin')
              <span class="badge badge-danger">Superadmin</span>
            @elseif($roleName === 'Admin Stock')
              <span class="badge badge-warning">Admin Stock</span>
            @else
              <span class="badge badge-success">Kasir</span>
            @endif
          </td>
          <td style="font-size: 12.5px; color: var(--text-secondary);">{{ $usr->created_at->format('d/m/Y') }}</td>
          <td style="text-align: center;">
            <button 
              onclick="openEditUserModal({{ json_encode($usr) }}, '{{ $roleName }}')" 
              class="btn btn-secondary btn-sm"
            >
              Edit
            </button>
            @if($usr->id !== auth()->id())
            <form action="{{ route('users.destroy', $usr->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Yakin hapus user ini?')">
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

<!-- Modal Create User -->
<div id="createUserModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 200; align-items: center; justify-content: center; padding: 20px;">
  <div style="background: #FFF; width: 100%; max-width: 460px; border-radius: 8px; padding: 24px; margin: 40px auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
      <h3 style="font-size: 16px; font-weight: 700;">Tambah User Baru</h3>
      <button onclick="document.getElementById('createUserModal').style.display='none'" style="background: none; border: none; font-size: 18px; cursor: pointer;">✕</button>
    </div>

    <form action="{{ route('users.store') }}" method="POST">
      @csrf
      <div class="form-group">
        <label class="form-label">Nama Lengkap</label>
        <input type="text" name="name" class="form-control" placeholder="Contoh: Budi Santoso" required>
      </div>

      <div class="form-group">
        <label class="form-label">Username (Untuk Login)</label>
        <input type="text" name="username" class="form-control font-mono" placeholder="budi_kasir" required>
      </div>

      <div class="form-group">
        <label class="form-label">Email (Opsional)</label>
        <input type="email" name="email" class="form-control" placeholder="budi@material.com">
      </div>

      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
      </div>

      <div class="form-group">
        <label class="form-label">Role Akses</label>
        <select name="role" class="form-select" required>
          @foreach($roles as $r)
            <option value="{{ $r->name }}">{{ $r->name }}</option>
          @endforeach
        </select>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
        <button type="button" onclick="document.getElementById('createUserModal').style.display='none'" class="btn btn-secondary">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan User</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit User -->
<div id="editUserModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 200; align-items: center; justify-content: center; padding: 20px;">
  <div style="background: #FFF; width: 100%; max-width: 460px; border-radius: 8px; padding: 24px; margin: 40px auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
      <h3 style="font-size: 16px; font-weight: 700;">Edit User</h3>
      <button onclick="document.getElementById('editUserModal').style.display='none'" style="background: none; border: none; font-size: 18px; cursor: pointer;">✕</button>
    </div>

    <form id="editUserForm" method="POST">
      @csrf
      @method('PUT')
      <div class="form-group">
        <label class="form-label">Nama Lengkap</label>
        <input type="text" id="edit_user_name" name="name" class="form-control" required>
      </div>

      <div class="form-group">
        <label class="form-label">Username</label>
        <input type="text" id="edit_user_username" name="username" class="form-control font-mono" required>
      </div>

      <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" id="edit_user_email" name="email" class="form-control">
      </div>

      <div class="form-group">
        <label class="form-label">Password Baru (Kosongkan jika tidak diganti)</label>
        <input type="password" name="password" class="form-control" placeholder="••••••••">
      </div>

      <div class="form-group">
        <label class="form-label">Role Akses</label>
        <select id="edit_user_role" name="role" class="form-select" required>
          @foreach($roles as $r)
            <option value="{{ $r->name }}">{{ $r->name }}</option>
          @endforeach
        </select>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
        <button type="button" onclick="document.getElementById('editUserModal').style.display='none'" class="btn btn-secondary">Batal</button>
        <button type="submit" class="btn btn-primary">Update User</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  function openEditUserModal(user, currentRole) {
    document.getElementById('editUserForm').action = "/users/" + user.id;
    document.getElementById('edit_user_name').value = user.name;
    document.getElementById('edit_user_username').value = user.username;
    document.getElementById('edit_user_email').value = user.email || '';
    document.getElementById('edit_user_role').value = currentRole;
    document.getElementById('editUserModal').style.display = 'block';
  }
</script>
@endpush
