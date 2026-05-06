<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RiskReport;
use App\Models\User;
use App\Models\Branch;
use App\Models\RiskItem;
use App\Models\RiskCause;

class DummyRiskReportSeeder extends Seeder
{
    /**
     * Generate data dummy laporan risiko dari Januari 2026 sampai sekarang.
     * Setiap laporan dibuat OLEH user yang role-nya SESUAI dengan role_target risk item.
     *
     * Rules:
     * - Korwil & ManRisk TIDAK BIKIN LAPORAN
     * - Kacab kalo input langsung auto-approved
     * - Data sampe hari ini (Mei 2026)
     * - 1-3 laporan per bulan
     */
    public function run(): void
    {
        // Hapus data dummy sebelumnya biar gak duplicate key pas deploy ulang
        RiskReport::truncate();

        $users = User::all();
        $branches = Branch::where('is_active', true)->get();
        $riskItems = RiskItem::all();
        $riskCauses = RiskCause::all();

        if ($users->isEmpty() || $branches->isEmpty() || $riskItems->isEmpty()) {
            $this->command->warn('Data master (users/branches/risk_items) belum ada. Jalankan db:seed dulu!');
            return;
        }

        $this->command->info('Mulai generate data dummy...');

        // Kronologis template biar variatif
        $kronologisTemplates = [
            'Kejadian ini terjadi pada saat jam operasional dimana terdapat selisih yang cukup signifikan setelah dilakukan pengecekan ulang oleh supervisor. Tindakan awal sudah dilakukan dengan melakukan koordinasi dengan pihak terkait.',
            'Pada saat dilakukan pemeriksaan rutin, ditemukan adanya ketidaksesuaian antara data sistem dengan fisik. Langkah perbaikan sedang dalam proses investigasi lebih lanjut oleh tim terkait.',
            'Berdasarkan laporan dari nasabah, terjadi kendala pada saat proses transaksi yang mengakibatkan ketidaknyamanan. Pihak bank telah memberikan konfirmasi dan sedang menindaklanjuti.',
            'Terjadi gangguan pada sistem aplikasi yang mengakibatkan terhambatnya pelayanan kepada nasabah. Tim IT telah ditugaskan untuk melakukan perbaikan dan pemulihan sistem.',
            'Ditemukan adanya kelalaian dalam proses verifikasi dokumen yang mengakibatkan ketidaksesuaian data. Tindakan korektif telah dilakukan untuk meminimalisir dampak lebih lanjut.',
            'Pada saat closing operasional, ditemukan selisih perhitungan yang memerlukan penyesuaian. Investigasi internal sedang dilakukan untuk mengetahui penyebab pasti kejadian.',
            'Nasabah menyampaikan komplain terkait pelayanan yang kurang memuaskan. Pihak manajemen telah memberikan tanggapan dan melakukan evaluasi terhadap prosedur pelayanan.',
            'Terjadi kendala teknis pada perangkat yang digunakan dalam proses operasional sehari-hari. Penggantian perangkat sedang dalam proses pengadaan.',
        ];

        // Filter user: hanya yang BUKAN korwil dan BUKAN manrisk
        $reporters = $users->filter(function ($user) {
            return !$user->hasRole('korwil') && !$user->hasRole('manrisk');
        });

        if ($reporters->isEmpty()) {
            $this->command->warn('Tidak ada user non-korwil untuk membuat laporan.');
            return;
        }

        // Group risk items per role_target
        $itemsByRole = [];
        foreach ($riskItems as $item) {
            $itemsByRole[$item->role_target][] = $item;
        }

        // Group causes per risk_item_id
        $causesByItem = [];
        foreach ($riskCauses as $cause) {
            $causesByItem[$cause->risk_item_id][] = $cause;
        }

        // Group reporters per role
        $reportersByRole = [];
        foreach ($reporters as $user) {
            $role = $user->primaryRoleName();
            if ($role) {
                $reportersByRole[$role][] = $user;
            }
        }

        $totalGenerated = 0;

        // Loop per bulan: Januari 2026 sampai bulan sekarang (Mei 2026)
        $startMonth = now()->setYear(2026)->setMonth(1)->startOfMonth();
        $endMonth = now()->startOfMonth();

        for ($month = $startMonth->copy(); $month->lte($endMonth); $month->addMonth()) {
            // Random 1-3 laporan per bulan
            $targetPerMonth = rand(1, 3);
            $daysInMonth = $month->copy()->endOfMonth()->day;

            for ($r = 0; $r < $targetPerMonth; $r++) {
                $day = rand(1, $daysInMonth);
                $reportDate = $month->copy()->setDay($day);

                // Kalo bulan ini, batasin sampe hari ini aja
                if ($reportDate->isFuture()) {
                    continue;
                }

                // Pilih role secara random dari role yang ada reportesnya
                $availableRoles = array_keys($reportersByRole);
                $selectedRole = $availableRoles[array_rand($availableRoles)];

                // Pastikan ada risk items untuk role ini
                if (empty($itemsByRole[$selectedRole])) {
                    continue;
                }

                // Pilih user dengan role tersebut
                $roleUsers = $reportersByRole[$selectedRole];
                $user = $roleUsers[array_rand($roleUsers)];

                // Pilih risk item yang sesuai dengan role user
                $roleItems = $itemsByRole[$selectedRole];
                $riskItem = $roleItems[array_rand($roleItems)];

                // Pilih cause yang sesuai dengan risk item
                $itemCauses = $causesByItem[$riskItem->id] ?? [];
                $cause = !empty($itemCauses) ? $itemCauses[array_rand($itemCauses)] : null;

                $kategori = $riskItem->kategori ?? 'non-finansial';
                $dampakFinansial = ($kategori === 'finansial') ? rand(100000, 15000000) : null;
                $skalaDampak = ($kategori === 'non-finansial') ? rand(1, 5) : null;

                // Approval status logic
                $isKacab = $user->hasRole('kacab');
                if ($isKacab) {
                    $approvalStatus = 'approved';
                } else {
                    $approvalStatus = $this->weightedRandom([
                        'approved' => 50,
                        'pending_kacab' => 35,
                        'rejected' => 15,
                    ]);
                }

                $resolutionStatus = $this->weightedRandom([
                    'closed' => 40,
                    'in_progress' => 35,
                    'open' => 25,
                ]);

                $createdAt = $reportDate->copy()->addHours(rand(7, 16))->addMinutes(rand(0, 59));

                RiskReport::create([
                    'kode_laporan' => 'RISK-DUMMY-' . $createdAt->format('Ymd') . '-' . str_pad($totalGenerated + 1, 4, '0', STR_PAD_LEFT),
                    'user_id' => $user->id,
                    'branch_id' => $user->branch_id,
                    'tanggal_kejadian' => $reportDate->copy()->subDays(rand(0, 3)),
                    'tanggal_diketahui' => $reportDate->copy(),
                    'risk_item_id' => $riskItem->id,
                    'risk_cause_id' => $cause?->id,
                    'kronologis_kejadian' => $kronologisTemplates[array_rand($kronologisTemplates)],
                    'kategori' => $kategori,
                    'dampak_finansial' => $dampakFinansial,
                    'skala_dampak' => $skalaDampak,
                    'approval_status' => $approvalStatus,
                    'resolution_status' => $resolutionStatus,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $totalGenerated++;
            }
        }

        $this->command->info("Selesai! {$totalGenerated} laporan dummy berhasil digenerate dari Januari 2026 sampai " . now()->format('F Y') . ".");
    }

    private function weightedRandom(array $weights): string
    {
        $total = array_sum($weights);
        $rand = rand(1, $total);
        $cumulative = 0;

        foreach ($weights as $key => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $key;
            }
        }

        return array_key_first($weights);
    }
}
