<?php

namespace App\Policies;

use App\Domain\Enums\RiskReportStatus;
use App\Domain\Enums\RoleCategory;
use App\Domain\Rules\ApprovalRule;
use App\Models\RiskReport;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RiskReportPolicy
{
    use HandlesAuthorization;

    public function __construct(
        protected ApprovalRule $approvalRule,
    ) {}

    /**
     * Siapa aja yang bisa lihat laporan tertentu.
     */
    public function view(User $user, RiskReport $report): bool
    {
        $category = RoleCategory::tryFrom($user->roleCategory() ?? '');

        // Admin (ManRisk) — bisa lihat semua laporan
        if ($category === RoleCategory::Admin) {
            return true;
        }

        // Viewer (Korwil) — hanya cabang yang diawasi
        if ($category === RoleCategory::Viewer) {
            $branch = $report->branch;
            return $branch && (int) $branch->korwil_id === (int) $user->id;
        }

        // Checker — lihat laporan cabang sendiri
        if ($category === RoleCategory::Checker) {
            return (int) $report->branch_id === (int) $user->branch_id;
        }

        // Maker — lihat laporan sendiri
        return (int) $report->user_id === (int) $user->id;
    }

    /**
     * Siapa yang bisa approve laporan (Kacab).
     */
    public function approve(User $user, RiskReport $report): bool
    {
        // Hanya checker yang bisa approve
        if (!RoleCategory::tryFrom($user->roleCategory() ?? '')?->canApprove()) {
            return false;
        }

        // Checker cuma bisa approve laporan cabang sendiri
        if ((int) $report->branch_id !== (int) $user->branch_id) {
            return false;
        }

        // Cuma laporan yang pending_kacab atau need_revision yang bisa diapprove
        $currentStatus = RiskReportStatus::tryFrom($report->status) ?? RiskReportStatus::PendingKacab;
        return $this->approvalRule->canApprove($currentStatus);
    }

    /**
     * Siapa yang bisa update progress/resolution.
     */
    public function updateProgress(User $user, RiskReport $report): bool
    {
        // Admin (manrisk) & Viewer (korwil) hanya pantau
        $category = RoleCategory::tryFrom($user->roleCategory() ?? '');
        if ($category === RoleCategory::Admin || $category === RoleCategory::Viewer) {
            return false;
        }

        // Checker & Maker — harus bisa lihat laporan dulu
        return $this->view($user, $report);
    }

    /**
     * Siapa yang bisa close laporan (Kacab).
     */
    public function close(User $user, RiskReport $report): bool
    {
        // Hanya checker yang bisa close
        if (!RoleCategory::tryFrom($user->roleCategory() ?? '')?->isChecker()) {
            return false;
        }

        return (int) $report->branch_id === (int) $user->branch_id;
    }

    /**
     * Siapa yang bisa minta revisi (ManRisk).
     */
    public function requestRevision(User $user, RiskReport $report): bool
    {
        $currentStatus = RiskReportStatus::tryFrom($report->status) ?? RiskReportStatus::ApprovedStatus;
        return RoleCategory::tryFrom($user->roleCategory() ?? '')?->canRequestRevision()
            && $this->approvalRule->canRequestRevision($currentStatus);
    }

    /**
     * Siapa yang bisa submit revisi (pembuat laporan atau Kacab).
     */
    public function submitRevision(User $user, RiskReport $report): bool
    {
        // Cuma laporan yang need_revision
        if ($report->status !== RiskReportStatus::NeedRevision->value) {
            return false;
        }

        // Checker (kacab) bisa submit revisi untuk laporan cabangnya
        if (RoleCategory::tryFrom($user->roleCategory() ?? '')?->isChecker()) {
            return (int) $report->branch_id === (int) $user->branch_id;
        }

        // Maker bisa submit revisi untuk laporannya sendiri
        return (int) $report->user_id === (int) $user->id;
    }

    /**
     * Siapa yang bisa approve revisi (ManRisk).
     */
    public function approveRevision(User $user, RiskReport $report): bool
    {
        $currentStatus = RiskReportStatus::tryFrom($report->status) ?? RiskReportStatus::PendingRevision;
        return RoleCategory::tryFrom($user->roleCategory() ?? '')?->canApproveRevision()
            && $this->approvalRule->canApproveRevision($currentStatus);
    }

    /**
     * Siapa yang bisa export data.
     */
    public function export(User $user): bool
    {
        return true; // Semua role bisa export, data discope sesuai role di controller
    }
}


