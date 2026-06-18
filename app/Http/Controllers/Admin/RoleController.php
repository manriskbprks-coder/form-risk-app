<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions', 'division')->withCount('users')->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();
        $divisions = \App\Models\Division::orderBy('nama_divisi')->get();

        return view('admin.roles.index', compact('roles', 'permissions', 'divisions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'role_category' => ['required', 'in:maker,checker,viewer,admin'],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'kode_role' => ['nullable', 'string', 'max:5'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role = Role::create([
            'name' => $request->name, 
            'role_category' => $request->role_category,
            'division_id' => $request->division_id,
            'kode_role' => $request->kode_role ? strtoupper($request->kode_role) : null,
        ]);

        if ($request->filled('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return back()->with('success', "Role '{$role->name}' berhasil ditambahkan!");
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
            'role_category' => ['required', 'in:maker,checker,viewer,admin'],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'kode_role' => ['nullable', 'string', 'max:5'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role->update([
            'name' => $request->name, 
            'role_category' => $request->role_category,
            'division_id' => $request->division_id,
            'kode_role' => $request->kode_role ? strtoupper($request->kode_role) : null,
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions ?? []);
        }

        return back()->with('success', "Role '{$role->name}' berhasil diperbarui!");
    }

    public function destroy(Role $role)
    {
        // Cek apakah role masih dipake user
        if ($role->users()->count() > 0) {
            return back()->with('error', "Role '{$role->name}' masih digunakan oleh {$role->users()->count()} user. Tidak bisa dihapus.");
        }

        $roleName = $role->name;
        $role->delete();

        return back()->with('success', "Role '{$roleName}' berhasil dihapus!");
    }
}
