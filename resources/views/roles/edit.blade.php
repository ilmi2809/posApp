@extends('layouts.app')

@section('title', 'Atur Privilege - ' . $role->name)
@section('page-header', 'Superadmin — Pengaturan Privilege Role: ' . $role->name)

@section('content')
<div style="max-width: 900px; margin: 0 auto;">
  <form action="{{ route('roles.update', $role->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card">
      <div class="card-header">
        <div>
          <div class="card-title">Role: {{ $role->name }}</div>
          <div style="font-size: 12px; color: var(--text-secondary); margin-top: 2px;">
            Centang fitur yang dapat diakses oleh pengguna dengan role ini.
          </div>
        </div>
        <div style="display: flex; gap: 10px;">
          <a href="{{ route('roles.index') }}" class="btn btn-secondary">Batal</a>
          <button type="submit" class="btn btn-primary">💾 Simpan Perubahan Privilege</button>
        </div>
      </div>

      <div class="form-group" style="margin-bottom: 24px;">
        <label for="name" class="form-label">Nama Role</label>
        <input 
          type="text" 
          name="name" 
          id="name" 
          value="{{ old('name', $role->name) }}" 
          class="form-control" 
          required
        >
      </div>

      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid var(--border);">
        <h4 style="font-size: 14px; font-weight: 700;">Pengaturan Hak Akses (Privileges)</h4>
        <button type="button" class="btn btn-secondary btn-sm" onclick="toggleAllCheckboxes()">Centang Semua / Hapus Semua</button>
      </div>

      <div class="grid grid-cols-2">
        @foreach($allPermissions as $group => $permissions)
        <div style="background: #F8FAFC; border: 1px solid var(--border); border-radius: 6px; padding: 14px;">
          <div style="font-weight: 700; text-transform: uppercase; font-size: 12px; color: var(--accent); margin-bottom: 10px; border-bottom: 1px solid var(--border); padding-bottom: 4px;">
            Modul: {{ strtoupper($group) }}
          </div>

          @foreach($permissions as $perm)
          @php $hasPerm = $role->hasPermissionTo($perm->name); @endphp
          <div style="margin-bottom: 8px;">
            <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer; color: var(--text-primary);">
              <input 
                type="checkbox" 
                name="permissions[]" 
                value="{{ $perm->name }}" 
                class="perm-checkbox"
                {{ $hasPerm ? 'checked' : '' }}
              >
              <span>{{ $perm->name }}</span>
            </label>
          </div>
          @endforeach
        </div>
        @endforeach
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
        <a href="{{ route('roles.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">💾 Simpan Perubahan Privilege</button>
      </div>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
  function toggleAllCheckboxes() {
    const checkboxes = document.querySelectorAll('.perm-checkbox');
    const firstState = checkboxes[0] ? !checkboxes[0].checked : true;
    checkboxes.forEach(cb => cb.checked = firstState);
  }
</script>
@endpush
