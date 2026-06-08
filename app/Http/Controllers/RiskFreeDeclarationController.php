<?php

namespace App\Http\Controllers;

use App\Services\DeklarasiNihilService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class RiskFreeDeclarationController extends Controller
{
    public function __construct(
        protected DeklarasiNihilService $deklarasiNihilService,
    ) {}

    /**
     * Tampilkan form deklarasi nihil risiko (Kacab).
     */
    public function create()
    {
        $user = Auth::user();

        if (!$user || $user->roleCategory() !== 'checker') {
            abort(Response::HTTP_FORBIDDEN, 'Hanya Checker (Kacab) yang bisa mengakses halaman ini.');
        }

        $periode = $this->deklarasiNihilService->getCurrentPeriode();
        $bulan = now()->month;
        $tahun = now()->year;

        // Cek apakah sudah deklarasi
        if ($this->deklarasiNihilService->sudahDeklarasi($user->branch_id, $periode, $bulan, $tahun)) {
            return redirect()->route('dashboard')
                ->with('info', 'Deklarasi nihil risiko untuk periode ini sudah dilakukan.');
        }

        $jabatanList = $this->deklarasiNihilService->getJabatanList();
        $adaLaporan = $this->deklarasiNihilService->adaLaporanDiPeriode($user->branch_id, $periode, $bulan, $tahun);

        return view('risk_free_declarations.create', compact(
            'jabatanList',
            'periode',
            'bulan',
            'tahun',
            'adaLaporan'
        ));
    }

    /**
     * Simpan deklarasi nihil risiko.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->roleCategory() !== 'checker') {
            abort(Response::HTTP_FORBIDDEN, 'Hanya Checker (Kacab) yang bisa melakukan deklarasi.');
        }

        $periode = $this->deklarasiNihilService->getCurrentPeriode();
        $bulan = now()->month;
        $tahun = now()->year;

        // Cek duplikasi
        if ($this->deklarasiNihilService->sudahDeklarasi($user->branch_id, $periode, $bulan, $tahun)) {
            return redirect()->route('dashboard')
                ->with('error', 'Deklarasi untuk periode ini sudah ada.');
        }

        $request->validate([
            'jabatan' => 'required|array|min:1',
            'jabatan.*.is_clean' => 'required|boolean',
            'jabatan.*.keterangan' => 'nullable|string|max:500',
            'statement_text' => 'required|string|min:10',
        ]);

        $this->deklarasiNihilService->store($user, $request->all());

        return redirect()->route('dashboard')
            ->with('success', 'Deklarasi nihil risiko berhasil disimpan.');
    }

    /**
     * Tolak deklarasi nihil risiko (ManRisk).
     */
    public function reject($id)
    {
        $user = Auth::user();
        if (!$user->isAdmin()) {
            abort(Response::HTTP_FORBIDDEN, 'Hanya Admin (ManRisk) yang bisa melakukan ini.');
        }

        try {
            $this->deklarasiNihilService->reject((string) $id, $user);
            return back()->with('success', 'Deklarasi ditolak (rejected).');
        } catch (\RuntimeException | \DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Riwayat deklarasi (untuk Kacab & ManRisk).
     */
    public function history()
    {
        $user = Auth::user();

        if (!$user || !in_array($user->roleCategory(), ['checker', 'viewer', 'admin'])) {
            abort(Response::HTTP_FORBIDDEN, 'Akses ditolak.');
        }

        $declarations = $this->deklarasiNihilService->getHistory($user);

        return view('risk_free_declarations.history', compact('declarations'));
    }
}
