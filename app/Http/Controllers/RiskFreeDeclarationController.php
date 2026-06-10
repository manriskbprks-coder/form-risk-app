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

        try {
            $this->deklarasiNihilService->store($user, $request->all());
            
            return redirect()->route('dashboard')
                ->with('success', 'Deklarasi nihil risiko berhasil disimpan.');
        } catch (\DomainException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Riwayat deklarasi (hanya untuk Admin/ManRisk).
     */
    public function history(Request $request)
    {
        $user = Auth::user();

        if (!$user || (!$user->isAdmin() && !$user->isChecker())) {
            abort(Response::HTTP_FORBIDDEN, 'Akses ditolak. Anda tidak memiliki izin untuk halaman ini.');
        }

        // Ambil filter bulan/tahun (default bulan/tahun ini)
        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);

        // Jika Kacab (Checker), hanya lihat cabang sendiri
        $branchId = $user->isChecker() ? $user->branch_id : null;
        $groupedData = $this->deklarasiNihilService->getHistoryGrouped($bulan, $tahun, $branchId);

        // Siapkan opsi dropdown filter (1 tahun ke belakang)
        $availableMonths = [];
        for ($i = 0; $i < 12; $i++) {
            $date = now()->subMonths($i);
            $availableMonths[] = [
                'bulan' => $date->month,
                'tahun' => $date->year,
                'label' => $date->translatedFormat('F Y'),
            ];
        }

        return view('risk_free_declarations.history', compact('groupedData', 'bulan', 'tahun', 'availableMonths'));
    }

    /**
     * Reject deklarasi nihil risiko (ManRisk).
     */
    public function reject($id)
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            abort(Response::HTTP_FORBIDDEN, 'Hanya ManRisk yang bisa menolak deklarasi.');
        }

        try {
            $this->deklarasiNihilService->reject($id, $user);
            return redirect()->back()->with('success', 'Deklarasi nihil risiko berhasil ditolak.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
