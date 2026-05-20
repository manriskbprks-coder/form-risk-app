<?php

namespace App\Services;

use App\Domain\Enums\RoleCategory;
use App\Domain\Rules\DeclarationRule;
use App\Models\RiskFreeDeclaration;
use App\Models\RiskFreeDeclarationDetail;
use App\Models\RiskReport;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class DeklarasiNihilService
{
    public function __construct(
        protected NotificationService $notificationService,
        protected DeclarationRule $declarationRule,
    ) {}

    /**
     * Tentukan periode saat ini berdasarkan tanggal.
     * Periode 1: tgl 1-14, Periode 2: tgl 15-akhir
     */
    public function getCurrentPeriode(): string
    {
        return $this->declarationRule->getCurrentPeriode(now()->day);
    }

    /**
     * Daftar jabatan yang wajib dideklarasikan.
     */
    public function getJabatanList(): array
    {
        return $this->declarationRule->getJabatanList();
    }

    /**
     * Cek apakah Kacab sudah pernah deklarasi untuk periode ini.
     */
    public function sudahDeklarasi(int $branchId, string $periode, int $bulan, int $tahun): bool
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
    public function adaLaporanDiPeriode(int $branchId, string $periode, int $bulan, int $tahun): bool
    {
        $dateRange = $this->declarationRule->getPeriodeDateRange($periode, $bulan, $tahun);

        return RiskReport::where('branch_id', $branchId)
            ->whereBetween('tanggal_kejadian', [$dateRange['start'], $dateRange['end']])
            ->exists();
    }

    /**
     * Simpan deklarasi nihil risiko baru.
     *
     * @param User $user
     * @param array $data Data tervalidasi (jabatan, statement_text)
     * @return RiskFreeDeclaration
     */
    public function store(User $user, array $data): RiskFreeDeclaration
    {
        $periode = $this->getCurrentPeriode();
        $bulan = now()->month;
        $tahun = now()->year;

        // Validasi duplikat deklarasi via domain rule
        $this->declarationRule->validateNoDuplicateDeclaration(
            $this->sudahDeklarasi($user->branch_id, $periode, $bulan, $tahun)
        );

        // Buat deklarasi header
        $declaration = RiskFreeDeclaration::create([
            'branch_id' => $user->branch_id,
            'user_id' => $user->id,
            'periode' => $periode,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'statement_text' => $data['statement_text'],
            'status' => 'active',
        ]);

        // Simpan detail per jabatan
        foreach ($data['jabatan'] as $jabatan => $jabatanData) {
            RiskFreeDeclarationDetail::create([
                'risk_free_declaration_id' => $declaration->id,
                'jabatan' => $jabatan,
                'is_clean' => $jabatanData['is_clean'],
                'keterangan' => $jabatanData['keterangan'] ?? null,
            ]);
        }

        // Notifikasi ke Admin (ManRisk)
        $this->notifyManRisk($declaration, $user);

        return $declaration;
    }

    /**
     * Tolak deklarasi nihil risiko (ManRisk).
     *
     * @param int $id Declaration ID
     * @param User $user ManRisk user
     * @return RiskFreeDeclaration
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function reject(int $id, User $user): RiskFreeDeclaration
    {
        $declaration = RiskFreeDeclaration::findOrFail($id);

        // Validasi reject duplikat via domain rule
        $this->declarationRule->validateNotAlreadyRejected($declaration->status === 'rejected');

        $declaration->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => $user->id,
        ]);

        // Catat aktivitas reject ke log harian
        Log::channel('daily')->info('[AUDIT] Declaration rejected by ManRisk', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'declaration_id' => $declaration->id,
            'branch_id' => $declaration->branch_id,
            'periode' => $declaration->periode,
            'bulan' => $declaration->bulan,
            'tahun' => $declaration->tahun,
        ]);

        // Notifikasi ke Kacab
        $this->notifyKacabRejected($declaration);

        return $declaration;
    }

    /**
     * Dapatkan riwayat deklarasi berdasarkan role user.
     *
     * @param User $user
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getHistory(User $user)
    {
        $query = RiskFreeDeclaration::with(['branch', 'user', 'details']);

        if (RoleCategory::tryFrom($user->roleCategory() ?? '')?->isChecker()) {
            $query->where('branch_id', $user->branch_id);
        }

        return $query->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->orderBy('periode', 'desc')
            ->paginate(20);
    }

    // ========================================================================
    // PRIVATE HELPERS
    // ========================================================================

    /**
     * Kirim notifikasi ke semua user ManRisk.
     */
    private function notifyManRisk(RiskFreeDeclaration $declaration, User $user): void
    {
        $this->notificationService->notifyManRisk(
            'declaration',
            "Cabang {$user->branch->nama_cabang} telah melakukan deklarasi nihil risiko periode {$declaration->periode} bulan " . now()->translatedFormat('F Y') . "."
        );
    }

    /**
     * Kirim notifikasi ke Kacab bahwa deklarasi ditolak.
     */
    private function notifyKacabRejected(RiskFreeDeclaration $declaration): void
    {
        $this->notificationService->notifyKacabBranch(
            $declaration->branch_id,
            'declaration_rejected',
            "Deklarasi nihil risiko cabang Anda untuk periode {$declaration->periode} bulan " . now()->setMonth($declaration->bulan)->translatedFormat('F Y') . " telah ditolak karena ditemukan laporan risiko."
        );
    }

}


