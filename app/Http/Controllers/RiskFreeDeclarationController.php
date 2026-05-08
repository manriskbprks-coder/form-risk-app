<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\RiskFreeDeclaration;
use App\Models\RiskFreeDeclarationDetail;
use App\Models\RiskReport;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RiskFreeDeclarationController extends Controller
{
    /**
     * Tentukan periode saat ini berdasarkan tanggal.
     * Periode 1: tgl 1-14, Periode 2: tgl 15-akhir
     */
    private function getCurrentPeriode(): string
    {
        $day = now()->day;
        return $day <= 14 ? '1' : '2';
    }

    /**
     * Daftar jabatan yang wajib dideklarasikan.
     */
    private function getJabatanList(): array
    {
        return ['Teller', 'CA', 'CS', 'Security', 'Kacab'];
    }

    /**
     * Cek apakah Kacab sudah pernah deklarasi untuk periode ini.
     */
    private function sudahDeklarasi($branchId, $periode, $bulan, $tahun): bool
    {
        return RiskFreeDeclaration::where('branch_id', $branchId)
            ->where('periode', $periode)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->exists();
    }

    /**
     * Cek apakah ada laporan risiko yang dibuat di periode ini oleh cabang tersebut.
     */
    private function adaLaporanDiPeriode($branchId, $periode, $bulan, $tahun): bool
    {
        $startDay = $periode === '1' ? 1 : 15;
        $endDay = $periode === '1' ? 14 : now()->daysInMonth;

        $startDate = "{$tahun}-{$bulan}-{$startDay}";
        $endDate = "{$tahun}-{$bulan}-{$endDay}";

        return RiskReport::where('branch_id', $branchId)
            ->whereBetween('tanggal_kejadian', [$startDate, $endDate])
            ->exists();
    }

    /**
     * Tampilkan form deklarasi nihil risiko (Kacab).
     */
    public function create()
    {
        $user = Auth::user();
        $role = $user?->primaryRoleName();

        if (!$user || $role !== 'kacab') {
            abort(Response::HTTP_FORBIDDEN, 'Hanya Kacab yang bisa mengakses halaman ini.');
        }

        $periode = $this->getCurrentPeriode();
        $bulan = now()->month;
        $tahun = now()->year;

        // Cek apakah sudah deklarasi
        if ($this->sudahDeklarasi($user->branch_id, $periode, $bulan, $tahun)) {
            return redirect()->route('dashboard')
                ->with('info', 'Deklarasi nihil risiko untuk periode ini sudah dilakukan.');
        }

        $jabatanList = $this->getJabatanList();
        $adaLaporan = $this->adaLaporanDiPeriode($user->branch_id, $periode, $bulan, $tahun);

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
        $role = $user?->primaryRoleName();

        if (!$user || $role !== 'kacab') {
            abort(Response::HTTP_FORBIDDEN, 'Hanya Kacab yang bisa melakukan deklarasi.');
        }

        $periode = $this->getCurrentPeriode();
        $bulan = now()->month;
        $tahun = now()->year;

        // Cek duplikasi
        if ($this->sudahDeklarasi($user->branch_id, $periode, $bulan, $tahun)) {
            return redirect()->route('dashboard')
                ->with('error', 'Deklarasi untuk periode ini sudah ada.');
        }

        $request->validate([
            'jabatan' => 'required|array|min:1',
            'jabatan.*.is_clean' => 'required|boolean',
            'jabatan.*.keterangan' => 'nullable|string|max:500',
            'statement_text' => 'required|string|min:10',
        ]);

        // Buat deklarasi header
        $declaration = RiskFreeDeclaration::create([
            'branch_id' => $user->branch_id,
            'user_id' => $user->id,
            'periode' => $periode,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'statement_text' => $request->statement_text,
            'status' => 'active',
        ]);

        // Simpan detail per jabatan
        foreach ($request->jabatan as $jabatan => $data) {
            RiskFreeDeclarationDetail::create([
                'risk_free_declaration_id' => $declaration->id,
                'jabatan' => $jabatan,
                'is_clean' => $data['is_clean'],
                'keterangan' => $data['keterangan'] ?? null,
            ]);
        }

        // Notifikasi ke ManRisk
        $manriskUsers = User::role('manrisk')->get();
        foreach ($manriskUsers as $mr) {
            Notification::create([
                'user_id' => $mr->id,
                'type' => 'declaration',
                'message' => "Cabang {$user->branch->nama_cabang} telah melakukan deklarasi nihil risiko periode {$periode} bulan " . now()->translatedFormat('F Y') . ".",
            ]);
        }

        return redirect()->route('dashboard')
            ->with('success', 'Deklarasi nihil risiko berhasil disimpan.');
    }

    /**
     * Tandai deklarasi sebagai violated (ManRisk).
     */
    public function violate($id)
    {
        $user = Auth::user();
        if (!$user->hasRole('manrisk')) {
            abort(Response::HTTP_FORBIDDEN, 'Hanya ManRisk yang bisa melakukan ini.');
        }

        $declaration = RiskFreeDeclaration::findOrFail($id);

        if ($declaration->status !== 'active') {
            return back()->with('error', 'Deklarasi ini sudah tidak aktif.');
        }

        $declaration->update([
            'status' => 'violated',
            'violated_at' => now(),
            'violated_by' => $user->id,
        ]);

        // Catat aktivitas violate ke log harian
        Log::channel('daily')->info('[AUDIT] Declaration violated by ManRisk', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'declaration_id' => $declaration->id,
            'branch_id' => $declaration->branch_id,
            'periode' => $declaration->periode,
            'bulan' => $declaration->bulan,
            'tahun' => $declaration->tahun,
        ]);

        // Notifikasi ke Kacab
        $kacabUsers = User::role('kacab')
            ->where('branch_id', $declaration->branch_id)
            ->get();
        foreach ($kacabUsers as $kacab) {
            Notification::create([
                'user_id' => $kacab->id,
                'type' => 'declaration_violated',
                'message' => "Deklarasi nihil risiko cabang Anda untuk periode {$declaration->periode} bulan " . now()->setMonth($declaration->bulan)->translatedFormat('F Y') . " telah dibatalkan karena ditemukan laporan risiko.",
            ]);
        }

        return back()->with('success', 'Deklarasi ditandai sebagai violated.');
    }

    /**
     * Riwayat deklarasi (untuk Kacab & ManRisk).
     */
    public function history()
    {
        $user = Auth::user();
        $role = $user?->primaryRoleName();

        if (!$user || !in_array($role, ['kacab', 'manrisk'])) {
            abort(Response::HTTP_FORBIDDEN, 'Akses ditolak.');
        }

        $query = RiskFreeDeclaration::with(['branch', 'user', 'details']);

        if ($role === 'kacab') {
            $query->where('branch_id', $user->branch_id);
        }

        $declarations = $query->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->orderBy('periode', 'desc')
            ->paginate(20);

        return view('risk_free_declarations.history', compact('declarations'));
    }
}
