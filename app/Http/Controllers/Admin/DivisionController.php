<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DivisionController extends Controller
{
    public function index()
    {
        $divisions = Division::withCount('roles')->orderBy('nama_divisi')->get();
        return view('admin.divisions.index', compact('divisions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_divisi' => ['required', 'string', 'max:255'],
            'kode_divisi' => ['required', 'string', 'max:10', 'unique:divisions,kode_divisi'],
        ]);

        $division = Division::create([
            'nama_divisi' => $request->nama_divisi,
            'kode_divisi' => strtoupper($request->kode_divisi),
        ]);

        return back()->with('success', "Divisi '{$division->nama_divisi}' berhasil ditambahkan!");
    }

    public function update(Request $request, Division $division)
    {
        $request->validate([
            'nama_divisi' => ['required', 'string', 'max:255'],
            'kode_divisi' => ['required', 'string', 'max:10', Rule::unique('divisions')->ignore($division->id)],
        ]);

        $division->update([
            'nama_divisi' => $request->nama_divisi,
            'kode_divisi' => strtoupper($request->kode_divisi),
        ]);

        return back()->with('success', "Divisi '{$division->nama_divisi}' berhasil diperbarui!");
    }

    public function destroy(Division $division)
    {
        if ($division->roles()->count() > 0) {
            return back()->with('error', "Divisi '{$division->nama_divisi}' masih digunakan oleh {$division->roles()->count()} role. Tidak bisa dihapus.");
        }

        $nama = $division->nama_divisi;
        $division->delete();

        return back()->with('success', "Divisi '{$nama}' berhasil dihapus!");
    }
}
