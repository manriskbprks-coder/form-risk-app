<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\RiskReport;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * NotificationService — Sentralisasi pembuatan notifikasi in-app.
 *
 * Semua logic pembuatan notifikasi (ke Kacab, ManRisk, dll) dipusatkan di sini
 * biar nggak duplicate di RiskReportService dan DeklarasiNihilService.
 *
 * Analogi restoran: ini kayak "bagian kasir" yang ngurus semua struk/pemberitahuan
 * ke pelanggan, jadi server (service lain) tinggal bilang "tolong kasih tahu meja 3"
 * tanpa perlu tahu detail cara cetak struknya.
 */
class NotificationService
{
    /**
     * Buat notifikasi untuk semua user Kacab di suatu cabang.
     *
     * @param string $branchId
     * @param string $type
     * @param string $message
     * @param string|null $riskReportId
     * @return Collection
     */
    public function notifyKacabBranch(string $branchId, string $type, string $message, ?string $riskReportId = null, ?string $divisionId = null): Collection
    {
        $query = User::whereHas('roles', function ($q) {
            $q->where('role_category', 'checker');
        })->where('branch_id', $branchId);

        if ($branchId === '000' && $divisionId) {
            $query->where('division_id', $divisionId);
        }

        $kacabUsers = $query->get();

        return $this->createForUsers($kacabUsers, $type, $message, $riskReportId);
    }

    /**
     * Buat notifikasi untuk semua user ManRisk (admin).
     *
     * @param string $type
     * @param string $message
     * @param string|null $riskReportId
     * @return Collection
     */
    public function notifyManRisk(string $type, string $message, ?string $riskReportId = null): Collection
    {
        $manriskUsers = User::whereHas('roles', function ($q) {
            $q->where('role_category', 'admin');
        })->get();

        return $this->createForUsers($manriskUsers, $type, $message, $riskReportId);
    }

    /**
     * Buat notifikasi untuk user tertentu.
     *
     * @param User $user
     * @param string $type
     * @param string $message
     * @param string|null $riskReportId
     * @return Notification
     */
    public function notifyUser(User $user, string $type, string $message, ?string $riskReportId = null): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'message' => $message,
            'risk_report_id' => $riskReportId,
        ]);
    }

    /**
     * Buat notifikasi untuk user yang membuat laporan (maker).
     *
     * @param RiskReport $report
     * @param string $type
     * @param string $message
     * @return Notification
     */
    public function notifyMaker(RiskReport $report, string $type, string $message): Notification
    {
        return $this->notifyUser($report->user, $type, $message, $report->id);
    }

    /**
     * Buat notifikasi untuk semua user dengan role_category tertentu.
     *
     * @param string $roleCategory
     * @param string $type
     * @param string $message
     * @param string|null $riskReportId
     * @return Collection
     */
    public function notifyByRoleCategory(string $roleCategory, string $type, string $message, ?string $riskReportId = null): Collection
    {
        $users = User::whereHas('roles', function ($q) use ($roleCategory) {
            $q->where('role_category', $roleCategory);
        })->get();

        return $this->createForUsers($users, $type, $message, $riskReportId);
    }

    /**
     * Buat notifikasi untuk semua user Kacab di cabang tertentu + ManRisk.
     * Dipakai saat ada laporan baru yang butuh perhatian.
     *
     * @param string $branchId
     * @param string $type
     * @param string $message
     * @param string|null $riskReportId
     * @return Collection
     */
    public function notifyKacabAndManRisk(string $branchId, string $type, string $message, ?string $riskReportId = null): Collection
    {
        $notifications = collect();

        $notifications = $notifications->merge(
            $this->notifyKacabBranch($branchId, $type, $message, $riskReportId)
        );

        $notifications = $notifications->merge(
            $this->notifyManRisk($type, $message, $riskReportId)
        );

        return $notifications;
    }

    // ========================================================================
    // PRIVATE HELPERS
    // ========================================================================

    /**
     * Buat notifikasi批量 untuk koleksi user.
     *
     * @param Collection|User[] $users
     * @param string $type
     * @param string $message
     * @param string|null $riskReportId
     * @return Collection
     */
    private function createForUsers($users, string $type, string $message, ?string $riskReportId = null): Collection
    {
        $notifications = collect();

        foreach ($users as $user) {
            $notifications->push(
                Notification::create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'message' => $message,
                    'risk_report_id' => $riskReportId,
                ])
            );
        }

        return $notifications;
    }
}
