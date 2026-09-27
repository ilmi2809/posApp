<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $allPermissions = Permission::all()->groupBy(function ($perm) {
            $parts = explode('.', $perm->name);

            return $parts[0] ?? 'general';
        });

        return view('roles.index', compact('roles', 'allPermissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $request->input('name'), 'guard_name' => 'web']);
        if ($request->has('permissions')) {
            $role->syncPermissions($request->input('permissions'));
        }

        return redirect()->route('roles.index')
            ->with('success', "Role '{$role->name}' berhasil dibuat.");
    }

    public function edit(Role $role)
    {
        $allPermissions = Permission::all()->groupBy(function ($perm) {
            $parts = explode('.', $perm->name);

            return $parts[0] ?? 'general';
        });

        return view('roles.edit', compact('role', 'allPermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name,'.$role->id],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role->name = $request->input('name');
        $role->save();

        $role->syncPermissions($request->input('permissions', []));

        return redirect()->route('roles.index')
            ->with('success', "Hak akses / privilege role '{$role->name}' berhasil diperbarui.");
    }

    public function destroy(Role $role)
    {
        if (in_array($role->name, ['Superadmin', 'Admin Stock', 'Kasir'])) {
            return back()->with('error', 'Role bawaan sistem tidak boleh dihapus.');
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Role berhasil dihapus.');
    }
}
