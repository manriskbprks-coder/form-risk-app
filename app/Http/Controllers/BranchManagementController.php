<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class BranchManagementController extends Controller
{
    // Update struktur cabang: Korwil dan Status Aktif
    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $request->validate([
            'kode_cabang' => 'nullable|string|max:10|unique:branches,kode_cabang,' . $id,
            'nickname_cabang' => 'nullable|string|max:50',
            'korwil_id' => 'nullable|exists:users,id',
            'is_active' => 'required|boolean'
        ]);

        $branch->update([
            'kode_cabang' => $request->kode_cabang,
            'nickname_cabang' => $request->nickname_cabang,
            'korwil_id' => $request->korwil_id,
            'is_active' => $request->is_active
        ]);

        return back()->with('success', 'Struktur Cabang ' . $branch->nama_cabang . ' berhasil diperbarui!');
    }
    public function index()
    {
        // 1. Tarik semua cabang
        $branches = \App\Models\Branch::orderBy('nama_cabang', 'asc')->get();

        // 2. Tarik semua user yang jabatannya viewer (Korwil)
        $listKorwil = \App\Models\User::whereHas('roles', function ($q) {
            $q->where('role_category', 'viewer');
        })->orderBy('name', 'asc')->get();

        return view('branches.index', compact('branches', 'listKorwil'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_cabang' => 'required|string|max:10|unique:branches,kode_cabang',
            'nama_cabang' => 'required|string|max:255',
            'nickname_cabang' => 'nullable|string|max:50',
            'korwil_id' => 'nullable|exists:users,id',
        ]);

        \App\Models\Branch::create([
            'kode_cabang' => $request->kode_cabang,
            'nama_cabang' => $request->nama_cabang,
            'nickname_cabang' => $request->nickname_cabang,
            'korwil_id' => $request->korwil_id,
            'is_active' => true, // Cabang baru otomatis aktif
        ]);

        return back()->with('success', 'Cabang baru berhasil didaftarkan ke dalam sistem!');
    }
}
