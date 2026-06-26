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
    public function index(Request $request)
    {
        // 1. Ambil input sort dan filter
        $sortColumn = $request->get('sort', 'kode_cabang'); // Default sort
        $sortDir = $request->get('dir', 'asc');
        $filterKorwil = $request->get('korwil_id');

        // 2. Tarik semua cabang dengan query
        $query = \App\Models\Branch::query();

        if ($filterKorwil) {
            if ($filterKorwil === 'none') {
                $query->whereNull('korwil_id');
            } else {
                $query->where('korwil_id', $filterKorwil);
            }
        }

        $allowedSorts = ['kode_cabang', 'nama_cabang', 'is_active'];
        if (in_array($sortColumn, $allowedSorts)) {
            // Sort by integer value of kode_cabang if needed, but it's string so standard order is fine (001, 002)
            $query->orderBy($sortColumn, $sortDir);
        } else {
            $query->orderBy('kode_cabang', 'asc');
        }

        $branches = $query->get();

        // 3. Tarik semua user yang jabatannya viewer (Korwil)
        $listKorwil = \App\Models\User::whereHas('roles', function ($q) {
            $q->where('role_category', 'viewer');
        })->orderBy('name', 'asc')->get();

        return view('branches.index', compact('branches', 'listKorwil', 'sortColumn', 'sortDir', 'filterKorwil'));
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
