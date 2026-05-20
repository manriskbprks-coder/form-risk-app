<?php

namespace App\Domain\Rules;

use App\Domain\Enums\ApprovalStatus;
use App\Domain\Enums\RoleCategory;

/**
 * Domain rule untuk approval workflow laporan risiko.
 *
 * Rule ini PURE LOGIC — tidak ada dependency ke Laravel, database, atau service.
 * Bisa di-test tanpa framework, tinggal new ApprovalRule() lalu panggil method-nya.
 *
 * Analogi: Ini kayak "buku resep" yang ngatur langkah-langkah approval.
 * Service Layer (RiskReportService) tinggal "ngikutin resep" ini.
 */
class ApprovalRule
{
    /**
     * Tentukan status awal approval saat laporan dibuat.
     *
     * - Jika user adalah Checker (Kacab), laporan langsung approved
     * - Jika user adalah Maker, laporan perlu persetujuan Kacab
     *
     * @param RoleCategory $roleCategory Kategori role pembuat laporan
     * @return ApprovalStatus Status awal yang sesuai
     */
    public function determineInitialStatus(RoleCategory $roleCategory): ApprovalStatus
    {
        return $roleCategory->isChecker()
            ? ApprovalStatus::Approved
            : ApprovalStatus::PendingKacab;
    }

    /**
     * Tentukan target status setelah revisi disubmit.
     *
     * - Jika revisi diminta oleh Kacab → submit ke Kacab lagi (pending_kacab)
     * - Jika revisi diminta oleh ManRisk → submit ke ManRisk (pending_revision)
     *
     * @param string|null $lastLogNote Isi note dari log terakhir (untuk deteksi siapa yang minta revisi)
     * @return ApprovalStatus Status tujuan setelah revisi
     */
    public function determineRevisionTarget(?string $lastLogNote): ApprovalStatus
    {
        if ($lastLogNote && str_contains($lastLogNote, 'Kacab')) {
            return ApprovalStatus::PendingKacab;
        }

        return ApprovalStatus::PendingRevision;
    }

    /**
     * Cek apakah user bisa approve laporan berdasarkan status saat ini.
     *
     * @param ApprovalStatus $currentStatus Status approval saat ini
     * @return bool
     */
    public function canApprove(ApprovalStatus $currentStatus): bool
    {
        return in_array($currentStatus, [
            ApprovalStatus::PendingKacab,
            ApprovalStatus::NeedRevision,
        ]);
    }

    /**
     * Cek apakah user bisa minta revisi berdasarkan status saat ini.
     *
     * @param ApprovalStatus $currentStatus Status approval saat ini
     * @return bool
     */
    public function canRequestRevision(ApprovalStatus $currentStatus): bool
    {
        return $currentStatus === ApprovalStatus::Approved;
    }

    /**
     * Cek apakah user bisa approve revisi berdasarkan status saat ini.
     *
     * @param ApprovalStatus $currentStatus Status approval saat ini
     * @return bool
     */
    public function canApproveRevision(ApprovalStatus $currentStatus): bool
    {
        return $currentStatus === ApprovalStatus::PendingRevision;
    }

    /**
     * Validasi bahwa transisi status diperbolehkan.
     *
     * @throws \DomainException Jika transisi tidak valid
     */
    public function validateTransition(ApprovalStatus $from, ApprovalStatus $to): void
    {
        if (!$from->canTransitionTo($to)) {
            throw new \DomainException(
                "Transisi status tidak valid: dari '{$from->value}' ke '{$to->value}'. " .
                "Transisi yang diizinkan: " . implode(', ', array_map(
                    fn(ApprovalStatus $s) => $s->value,
                    ApprovalStatus::allowedTransitions()[$from->value] ?? []
                ))
            );
        }
    }
}
