<?php

namespace App\Policies;

use App\Models\RiskReport;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Auth\Access\HandlesAuthorization;

class RiskReportPolicy
{
    use HandlesAuthorization;

    /**
     * Siapa aja yang bisa lihat laporan tertentu.
     */
    public function view(User $user, RiskReport $report): bool
    {
        $category = $user->role_category;

        // Viewer — ManRisk bisa lihat semua, Korwil hanya cabang yang diawasi
        if ($category === 'viewer') {
            if ($user->hasRole('manrisk')) {
                return true;
            }

            // Korwil — hanya cabang yang diawasi
            $branch = $report->branch;
            return $branch && (int) $branch->korwil_id === (int) $user->id;
        }

        // Checker — lihat laporan cabang sendiri
        if ($category === 'checker') {
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
        if ($user->role_category !== 'checker') {
            return false;
        }

        // Checker cuma bisa approve laporan cabang sendiri
        if ((int) $report->branch_id !== (int) $user->branch_id) {
            return false;
        }

        // Cuma laporan yang pending_kacab atau need_revision yang bisa diapprove
        return in_array($report->approval_status, ['pending_kacab', 'need_revision']);
    }

    /**
     * Siapa yang bisa update progress/resolution.
     */
    public function updateProgress(User $user, RiskReport $report): bool
    {
        // Viewer (manrisk, korwil) hanya pantau
        if ($user->isViewer()) {
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
        if ($user->role_category !== 'checker') {
            return false;
        }

        return (int) $report->branch_id === (int) $user->branch_id;
    }

    /**
     * Siapa yang bisa minta revisi (ManRisk).
     */
    public function requestRevision(User $user, RiskReport $report): bool
    {
        return $user->hasRole('manrisk') && $report->approval_status === 'approved';
    }

    /**
     * Siapa yang bisa submit revisi (pembuat laporan atau Kacab).
     */
    public function submitRevision(User $user, RiskReport $report): bool
    {
        // Cuma laporan yang need_revision
        if ($report->approval_status !== 'need_revision') {
            return false;
        }

        // Checker (kacab) bisa submit revisi untuk laporan cabangnya
        if ($user->role_category === 'checker') {
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
        return $user->hasRole('manrisk') && $report->approval_status === 'pending_revision';
    }

    /**
     * Siapa yang bisa export data.
     */
    public function export(User $user): bool
    {
        return true; // Semua role bisa export, data discope sesuai role di controller
    }
}
