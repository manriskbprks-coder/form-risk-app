<?php

namespace App\Services;

use App\Domain\Enums\RoleCategory;
use App\Domain\Rules\DeclarationRule;
use App\Models\RiskFreeDeclaration;
use App\Models\RiskFreeDeclarationDetail;
use App\Models\RiskReport;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

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
     * Daftar jabatan yang wajib dideklarasikan — DINAMIS dari database.
     *
     * Logikanya:
     * 1. Ambil divisi dari role si pejabat (Checker) yang sedang login.
     * 2. Tarik semua Role yang ada di divisi tersebut dengan kategori 'maker'.
     * 3. Tambahkan juga jabatan si pejabat itu sendiri (Checker).
     *
     * Hasilnya: ['Teller', 'CA', 'CSR', 'Security', 'Kacab'] (misalnya untuk Divisi Operasional)
     * Tapi kalau Manager Akunting yang login: ['Staff Akunting', 'Manager Akunting']
     */
    public function getJabatanList(User $user): array
    {
        $userRole = $user->roles->first();
        
        if (!$userRole || !$userRole->division_id) {
            return [];
        }

        $divisionId = $userRole->division_id;

        // Ambil semua role (maker) yang satu divisi + role si Checker itu sendiri
        $roles = Role::where('division_id', $divisionId)
            ->where(function ($query) use ($userRole) {
                $query->where('role_category', 'maker')
                      ->orWhere('id', $userRole->id);
            })
            ->pluck('name')
            ->map(fn($name) => ucfirst($name))
            ->toArray();

        return $roles;
    }

    /**
     * Cek apakah Kacab sudah pernah deklarasi untuk periode ini.
     */
    public function sudahDeklarasi(string $branchId, string $periode, int $bulan, int $tahun, ?string $divisionId = null): bool
    {
        $query = RiskFreeDeclaration::where('branch_id', $branchId)
            ->where('periode', $periode)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->whereIn('status', ['active', 'rejected']);
            
        if ($branchId === '000' && $divisionId) {
            $query->where('division_id', $divisionId);
        }
        
        return $query->exists();
    }

    /**
     * Cek apakah ada laporan risiko yang dibuat di periode ini oleh cabang tersebut.
     */
    public function adaLaporanDiPeriode(string $branchId, string $periode, int $bulan, int $tahun): bool
    {
        $dateRange = $this->declarationRule->getPeriodeDateRange($periode, $bulan, $tahun);

        return RiskReport::where('branch_id', $branchId)
            ->whereBetween('tanggal_kejadian', [$dateRange['start'], $dateRange['end']])
            ->exists();
    }

    /**
     * Validasi kejujuran deklarasi: Kacab tidak boleh mencentang Nihil untuk jabatan 
     * yang sebenarnya memiliki RiskReport pada periode tersebut.
     *
     * NOTE: Sekarang DINAMIS — tidak lagi pakai hardcode roleMapping.
     * Sistem langsung mencocokkan nama jabatan dari form (ucfirst) ke nama role (lowercase) di database.
     */
    public function validateJabatanHonesty(string $branchId, string $periode, int $bulan, int $tahun, array $jabatanData, ?string $divisionId = null)
    {
        $dateRange = $this->declarationRule->getPeriodeDateRange($periode, $bulan, $tahun);

        foreach ($jabatanData as $jabatan => $data) {
            // Jika Kacab mencentang Nihil Risiko
            if ($data['is_clean'] ?? false) {
                // Konversi nama jabatan ke lowercase (sesuai format di tabel roles)
                $roleName = strtolower($jabatan);
                
                $query = RiskReport::where('branch_id', $branchId)
                    ->whereBetween('tanggal_kejadian', [$dateRange['start'], $dateRange['end']])
                    ->whereHas('user.roles', function ($query) use ($roleName) {
                        $query->where('name', $roleName);
                    });

                if ($branchId === '000' && $divisionId) {
                    $query->whereHas('user', function($q) use ($divisionId) {
                        $q->where('division_id', $divisionId);
                    });
                }

                $hasReport = $query->exists();

                if ($hasReport) {
                    throw new \DomainException("Validasi Gagal: Anda tidak dapat mendeklarasikan posisi {$jabatan} sebagai Nihil Risiko, karena terdapat laporan yang masuk dari posisi tersebut pada periode ini.");
                }
            }
        }
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
            $this->sudahDeklarasi($user->branch_id, $periode, $bulan, $tahun, $user->division_id)
        );

        // Validasi Kejujuran per Jabatan
        $this->validateJabatanHonesty($user->branch_id, $periode, $bulan, $tahun, $data['jabatan'], $user->division_id);

        // Buat deklarasi header
        $declaration = RiskFreeDeclaration::create([
            'branch_id' => $user->branch_id,
            'division_id' => $user->division_id,
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
     * Mendapatkan data riwayat yang digrouping per cabang dan periode untuk 1 bulan & tahun tertentu.
     */
    public function getHistoryGrouped(int $bulan, int $tahun, ?string $branchId = null): \Illuminate\Support\Collection
    {
        // 1. Ambil semua cabang aktif, atau hanya cabang spesifik jika branchId diberikan
        $query = \App\Models\Branch::where('is_active', true)->orderBy('kode_cabang', 'asc');
        
        if ($branchId) {
            $query->where('id', $branchId);
        }
        
        $branches = $query->get();

        // 2. Ambil semua deklarasi untuk bulan & tahun yang dipilih beserta detailnya
        $declarations = RiskFreeDeclaration::with('details')
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->get()
            ->groupBy('branch_id');

        // 3. Kelompokkan data per cabang
        $groupedData = collect();

        foreach ($branches as $branch) {
            $branchDecls = $declarations->get($branch->id, collect());
            
            // Helper function untuk mengambil data periode
            $getPeriodeData = function($periodeNum) use ($branchDecls) {
                $decl = $branchDecls->firstWhere('periode', (string)$periodeNum);
                
                if (!$decl) {
                    return [
                        'status' => 'belum',
                        'jabatan_nihil' => '-',
                        'keterangan' => 'Belum Lapor'
                    ];
                }

                // Ambil daftar jabatan yang dicentang Nihil (is_clean = true)
                $jabatanNihil = $decl->details->where('is_clean', true)->pluck('jabatan')->toArray();
                $jabatanString = empty($jabatanNihil) ? '-' : implode(', ', $jabatanNihil);

                // Ambil keterangan dari jabatan yang berisiko (jika ada)
                $risikoDetails = $decl->details->where('is_clean', false);
                $keteranganString = $risikoDetails->isEmpty() 
                    ? 'Aman semua' 
                    : $risikoDetails->map(fn($d) => "{$d->jabatan}: {$d->keterangan}")->implode('; ');

                return [
                    'status' => 'sudah',
                    'jabatan_nihil' => $jabatanString,
                    'keterangan' => $keteranganString
                ];
            };

            $groupedData->push([
                'kode_cabang' => $branch->kode_cabang,
                'nama_cabang' => $branch->nama_cabang,
                'periode1' => $getPeriodeData(1),
                'periode2' => $getPeriodeData(2),
            ]);
        }

        return $groupedData;
    }

    // ========================================================================
    // PRIVATE HELPERS
    // ========================================================================

    /**
     * Reject deklarasi nihil risiko (ManRisk).
     */
    public function reject(string $id, User $user): void
    {
        $declaration = RiskFreeDeclaration::findOrFail($id);

        if ($declaration->status === 'rejected') {
            throw new \DomainException('Deklarasi ini sudah dalam status ditolak.');
        }

        $declaration->update([
            'status' => 'rejected',
            'rejected_by' => $user->id,
            'rejected_at' => now(),
        ]);

        $this->notifyKacabRejected($declaration);
        Log::channel('daily')->info('[AUDIT] Declaration rejected by ManRisk', [
            'action' => 'reject_declaration',
            'declaration_id' => $declaration->id,
            'branch_id' => $declaration->branch_id,
            'periode' => $declaration->periode,
            'bulan' => $declaration->bulan,
            'tahun' => $declaration->tahun,
            'user_id' => $user->id,
            'user_name' => $user->name,
        ]);
    }

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
