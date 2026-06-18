<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RiskItem;
use App\Models\RiskCause;
use App\Models\RiskMitigation;
use Illuminate\Http\Request;

class RiskMasterController extends Controller
{
    // 1. LIHAT SEMUA DAFTAR PERTANYAAN
    public function index()
    {
        $riskItems = RiskItem::with('causes.mitigations')->orderBy('role_target')->get();
        return view('admin.risk_master.index', compact('riskItems'));
    }

    // 2. SIMPAN PERTANYAAN BARU
    public function storeItem(Request $request)
    {
        $request->validate([
            'nama_risiko' => 'required|string|max:255',
            'kategori' => 'required|in:finansial,non-finansial',
            'sumber_risiko' => 'required|in:manusia,proses_internal,sistem_teknologi,faktor_eksternal',
            'role_target' => 'required|exists:roles,name',
        ]);

        RiskItem::create($request->only(['nama_risiko', 'kategori', 'sumber_risiko', 'role_target']));
        return back()->with('success', 'Pertanyaan risiko baru berhasil ditambahkan!');
    }

    // 3. SIMPAN PENYEBAB & MITIGASI (Bundling)
    public function storeCause(Request $request, $itemId)
    {
        $request->validate([
            'penyebab' => 'required|string|max:255',
            'sumber_risiko' => 'required|in:manusia,proses_internal,sistem_teknologi,faktor_eksternal',
            'mitigasi' => 'nullable|string|max:255',
        ]);

        $cause = RiskCause::create([
            'risk_item_id' => $itemId,
            'penyebab' => $request->penyebab,
            'sumber_risiko' => $request->sumber_risiko,
        ]);

        if ($request->mitigasi) {
            RiskMitigation::create([
                'risk_cause_id' => $cause->id,
                'mitigasi' => $request->mitigasi
            ]);
        }

        return back()->with('success', 'Penyebab dan Mitigasi berhasil ditambahkan!');
    }

    // 3b. SIMPAN MITIGASI KE CAUSE YANG SUDAH ADA (dipisah dari storeCause)
    public function storeMitigation(Request $request, $causeId)
    {
        $validated = $request->validate([
            'mitigasi' => 'required|string|max:255',
        ]);

        RiskMitigation::create([
            'risk_cause_id' => $causeId,
            'mitigasi' => $validated['mitigasi'],
        ]);

        return back()->with('success', 'Mitigasi berhasil ditambahkan!');
    }

    // 4. HAPUS PERTANYAAN
    public function destroyItem($id)
    {
        RiskItem::findOrFail($id)->delete();
        return back()->with('success', 'Pertanyaan berhasil dihapus dari sistem.');
    }

    // 5. UPDATE PENYEBAB & MITIGASI
    public function updateCause(Request $request, $id)
    {
        $request->validate([
            'penyebab' => 'required|string|max:255',
            'sumber_risiko' => 'required|in:manusia,proses_internal,sistem_teknologi,faktor_eksternal',
            'mitigasi' => 'nullable|string|max:255'
        ]);

        $cause = RiskCause::findOrFail($id);
        $cause->update([
            'penyebab' => $request->penyebab,
            'sumber_risiko' => $request->sumber_risiko,
        ]);

        if ($request->filled('mitigasi')) {
            $mitigation = $cause->mitigations()->first();
            if ($mitigation) {
                $mitigation->update(['mitigasi' => $request->mitigasi]);
            } else {
                $cause->mitigations()->create(['mitigasi' => $request->mitigasi]);
            }
        } else {
            $cause->mitigations()->delete();
        }

        return back()->with('success', 'Data Penyebab & Mitigasi berhasil diperbarui!');
    }
}
