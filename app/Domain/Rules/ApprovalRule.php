<?php

namespace App\Domain\Rules;

use App\Domain\Enums\RiskReportStatus;
use App\Domain\Enums\RoleCategory;

/**
 * Domain rule untuk approval workflow laporan risiko.
 *
 * Rule ini PURE LOGIC — tidak ada dependency ke Laravel, database, atau service.
 * Bisa di-test tanpa framework, tinggal new ApprovalRule() lalu panggil method-nya.
 *
 * Analogi: Ini kayak "buku resep" yang ngatur langkah-langkah approval.
 * Service Layer (RiskReportService) tinggal "ngikutin resep" ini.
 *
 * NOTE: Sekarang pake RiskReportStatus (single status) bukan ApprovalStatus lagi.
 */
class ApprovalRule
{
    /**
     * Tentukan status awal laporan saat dibuat.
     *
     * - Jika user adalah Checker (Kacab), laporan langsung approved
     * - Jika user adalah Maker, laporan perlu persetujuan Kacab
     *
     * @param RoleCategory $roleCategory Kategori role pembuat laporan
     * @return RiskReportStatus Status awal yang sesuai
     */
    public function determineInitialStatus(RoleCategory $roleCategory): RiskReportStatus
    {
        return $roleCategory->isChecker()
            ? RiskReportStatus::ApprovedInProgress
            : RiskReportStatus::PendingAtasan;
    }

    /**
     * Tentukan target status setelah revisi disubmit.
     *
     * - Jika revisi diminta oleh Kacab → submit ke Kacab lagi (pending_atasan)
     * - Jika revisi diminta oleh ManRisk → submit ke ManRisk (pending_revision)
     *
     * @param string|null $lastLogNote Isi note dari log terakhir (untuk deteksi siapa yang minta revisi)
     * @return RiskReportStatus Status tujuan setelah revisi
     */
    public function determineRevisionTarget(?string $lastLogNote): RiskReportStatus
    {
        if ($lastLogNote && str_contains($lastLogNote, 'Kacab')) {
            return RiskReportStatus::PendingAtasan;
        }

        return RiskReportStatus::PendingRevision;
    }

    /**
     * Cek apakah user bisa approve laporan berdasarkan status saat ini.
     *
     * @param RiskReportStatus $currentStatus Status laporan saat ini
     * @return bool
     */
    public function canApprove(RiskReportStatus $currentStatus): bool
    {
        return in_array($currentStatus, [
            RiskReportStatus::PendingAtasan,
            RiskReportStatus::NeedRevision,
        ]);
    }

    /**
     * Cek apakah user bisa minta revisi berdasarkan status saat ini.
     *
     * @param RiskReportStatus $currentStatus Status laporan saat ini
     * @return bool
     */
    public function canRequestRevision(RiskReportStatus $currentStatus): bool
    {
        return in_array($currentStatus, [
            RiskReportStatus::ApprovedInProgress,
            RiskReportStatus::Closed,
        ]);
    }

    /**
     * Cek apakah user bisa approve revisi berdasarkan status saat ini.
     *
     * @param RiskReportStatus $currentStatus Status laporan saat ini
     * @return bool
     */
    public function canApproveRevision(RiskReportStatus $currentStatus): bool
    {
        return $currentStatus === RiskReportStatus::PendingRevision;
    }

    /**
     * Validasi bahwa transisi status diperbolehkan.
     *
     * @throws \DomainException Jika transisi tidak valid
     */
    public function validateTransition(RiskReportStatus $from, RiskReportStatus $to): void
    {
        if (!$from->canTransitionTo($to)) {
            throw new \DomainException(
                "Transisi status tidak valid: dari '{$from->value}' ke '{$to->value}'. " .
                "Transisi yang diizinkan: " . implode(', ', array_map(
                    fn(RiskReportStatus $s) => $s->value,
                    RiskReportStatus::allowedTransitions()[$from->value] ?? []
                ))
            );
        }
    }
}
