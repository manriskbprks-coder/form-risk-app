<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::with(['branch', 'roles'])->orderBy('name', 'asc')->get();
        $branches = Branch::all();
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'branches', 'roles'));
    }

    // 1. TAMBAH USER BARU
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'branch_id' => ['required', 'uuid', 'exists:branches,id'],
            'role' => ['required', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'branch_id' => $request->branch_id,
            'is_active' => true, // Default aktif
        ]);

        $user->assignRole($request->role);

        return back()->with('success', 'User berhasil ditambahkan ke sistem!');
    }

    // 2. UPDATE USER (MUTASI, PROMOSI, STATUS)
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'branch_id' => ['required', 'uuid', 'exists:branches,id'],
            'role' => ['required', 'exists:roles,name'],
        ]);

        // Logika Update Data Dasar & Mutasi Cabang
        $user->update([
            'name' => $request->name,
            'branch_id' => $request->branch_id,
        ]);

        // Logika Ganti Jabatan (Promosi/Demosi)
        $user->syncRoles($request->role);

        return back()->with('success', 'Data user berhasil diperbarui!');
    }

    // 3. TOGGLE AKTIF/NON-AKTIF (KILL SWITCH)
    public function toggleStatus(User $user)
    {
        // Proteksi: ManRisk nggak boleh non-aktifin dirinya sendiri
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Tindakan ditolak! Anda tidak bisa menonaktifkan akun sendiri.');
        }

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Akun {$user->name} berhasil {$status}!");
    }

    // 4. RESET PASSWORD (2-STEP VERIFICATION DARI VIEW)
    public function resetPassword(Request $request, User $user)
    {
        // Proteksi: ManRisk gak bisa reset dirinya sendiri
        if (auth()->id() === $user->id) {
            return response()->json([
                'error' => 'Anda tidak bisa mereset password akun sendiri!'
            ], 403);
        }

        // Generate password sementara 12 karakter
        $tempPassword = Str::random(12);

        // Update password + reset password_changed_at biar user WAJIB ganti pas login
        // Juga reset failed_login_attempts agar gembok terbuka
        $user->update([
            'password' => Hash::make($tempPassword),
            'password_changed_at' => null,
            'failed_login_attempts' => 0,
        ]);

        // Log aktivitas
        Log::info('Reset password', [
            'reseted_by' => auth()->user()->name,
            'reseted_user' => $user->name,
            'reseted_user_id' => $user->id,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'temp_password' => $tempPassword,
            'user_name' => $user->name,
        ]);
    }
}
