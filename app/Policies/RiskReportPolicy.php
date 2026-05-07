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
        $role = $user->primaryRoleName();

        // ManRisk bisa lihat semua
        if ($role === 'manrisk') {
            return true;
        }

        // Kacab — lihat laporan cabang sendiri
        if ($role === 'kacab') {
            return (int) $report->branch_id === (int) $user->branch_id;
        }

        // Korwil — lihat laporan di wilayahnya
        if ($role === 'korwil') {
            $branchIds = Branch::where('korwil_id', $user->id)->pluck('id');
            return $branchIds->contains((int) $report->branch_id);
        }

        // Staff — lihat laporan sendiri
        if (in_array($role, ['teller', 'ca', 'csr', 'security'], true)) {
            return (int) $report->user_id === (int) $user->id;
        }

        return false;
    }

    /**
     * Siapa yang bisa approve laporan (Kacab).
     */
    public function approve(User $user, RiskReport $report): bool
    {
        $role = $user->primaryRoleName();

        if ($role !== 'kacab') {
            return false;
        }

        // Kacab cuma bisa approve laporan cabang sendiri
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
        $role = $user->primaryRoleName();

        // ManRisk & Korwil hanya pantau
        if (in_array($role, ['manrisk', 'korwil'], true)) {
            return false;
        }

        // Kacab & Staff — harus bisa lihat laporan dulu
        return $this->view($user, $report);
    }

    /**
     * Siapa yang bisa close laporan (Kacab).
     */
    public function close(User $user, RiskReport $report): bool
    {
        $role = $user->primaryRoleName();

        if ($role !== 'kacab') {
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

        // Kacab bisa submit revisi untuk laporan cabangnya
        if ($user->primaryRoleName() === 'kacab') {
            return (int) $report->branch_id === (int) $user->branch_id;
        }

        // Staff bisa submit revisi untuk laporannya sendiri
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
        // Semua role bisa export, tapi data discope sesuai role
        return in_array($user->primaryRoleName(), ['kacab', 'korwil', 'manrisk', 'teller', 'ca', 'csr', 'security']);
    }
}
