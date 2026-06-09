<?php

namespace App\Domain\Enums;

/**
 * Status laporan risiko — gabungan approval_status + resolution_status jadi SATU.
 *
 * State machine diagram:
 *   pending_kacab ──┬──→ approved ────┬──→ in_progress ────→ closed
 *                   │                  │
 *                   └──→ need_revision │
 *                        │             └──→ need_revision (ManRisk request)
 *                        │
 *                        ├──→ pending_kacab (revisi dari Kacab)
 *                        └──→ pending_revision (revisi dari ManRisk)
 *                                 │
 *                                 └──→ approved (ManRisk approve revisi)
 */
enum RiskReportStatus: string
{
    case PendingAtasan = 'pending_atasan';
    case NeedRevision = 'need_revision';
    case PendingRevision = 'pending_revision';
    case ApprovedInProgress = 'approved_in_progress';
    case Closed = 'closed';

    /**
     * Cek apakah transisi dari status ini ke status target valid.
     *
     * Contoh: RiskReportStatus::PendingKacab->canTransitionTo(RiskReportStatus::ApprovedStatus) // true
     *         RiskReportStatus::Closed->canTransitionTo(RiskReportStatus::ApprovedStatus) // false
     */
    public function canTransitionTo(self $target): bool
    {
        return in_array($target, self::allowedTransitions()[$this->value] ?? []);
    }

    /**
     * Daftar transisi yang valid untuk setiap status.
     */
    public static function allowedTransitions(): array
    {
        return [
            'pending_atasan' => [self::ApprovedInProgress, self::NeedRevision],
            'need_revision' => [self::PendingAtasan, self::PendingRevision],
            'pending_revision' => [self::ApprovedInProgress],
            'approved_in_progress' => [self::Closed, self::NeedRevision],
            'closed' => [self::NeedRevision],
        ];
    }

    /**
     * Apakah status ini termasuk "final" (tidak bisa berubah lagi)?
     * Hanya Closed yang final.
     */
    public function isFinal(): bool
    {
        return $this === self::Closed;
    }

    public function needsAction(): bool
    {
        return in_array($this, [
            self::PendingAtasan,
            self::NeedRevision,
            self::PendingRevision,
        ]);
    }

    /**
     * Label yang user-friendly untuk ditampilkan di UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::PendingAtasan => 'Menunggu Persetujuan Atasan',
            self::NeedRevision => 'Perlu Revisi',
            self::PendingRevision => 'Menunggu Persetujuan Revisi',
            self::ApprovedInProgress => 'Disetujui & Diproses',
            self::Closed => 'Selesai',
        };
    }

    /**
     * Warna badge untuk UI (Tailwind CSS classes).
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::PendingAtasan => 'bg-yellow-100 text-yellow-800',
            self::NeedRevision => 'bg-red-100 text-red-800',
            self::PendingRevision => 'bg-orange-100 text-orange-800',
            self::ApprovedInProgress => 'bg-blue-100 text-blue-800',
            self::Closed => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Konversi dari 2 status lama (approval_status + resolution_status) ke status baru.
     *
     * Mapping logic:
     * - pending_kacab → pending_kacab
     * - need_revision → need_revision
     * - pending_revision → pending_revision
     * - approved + open → approved
     * - approved + in_progress → in_progress
     * - approved + closed → closed
     * - fallback → pending_kacab
     */
    public static function fromOldStatuses(string $approvalStatus, string $resolutionStatus): self
    {
        return match (true) {
            $approvalStatus === 'pending_kacab' || $approvalStatus === 'pending_atasan' => self::PendingAtasan,
            $approvalStatus === 'need_revision' => self::NeedRevision,
            $approvalStatus === 'pending_revision' => self::PendingRevision,
            $approvalStatus === 'approved' && $resolutionStatus === 'open' => self::ApprovedInProgress,
            $approvalStatus === 'approved' && $resolutionStatus === 'in_progress' => self::ApprovedInProgress,
            $approvalStatus === 'approved_in_progress' => self::ApprovedInProgress,
            $approvalStatus === 'approved' && $resolutionStatus === 'closed' => self::Closed,
            default => self::PendingAtasan,
        };
    }

    /**
     * Konversi balik ke approval_status lama (untuk backward compat di log).
     */
    public function toApprovalStatus(): string
    {
        return match ($this) {
            self::PendingAtasan => 'pending_atasan',
            self::NeedRevision => 'need_revision',
            self::PendingRevision => 'pending_revision',
            self::ApprovedInProgress => 'approved_in_progress',
            self::Closed => 'closed',
        };
    }

    /**
     * Konversi balik ke resolution_status lama (untuk backward compat di log).
     */
    public function toResolutionStatus(): string
    {
        return match ($this) {
            self::PendingAtasan => 'open',
            self::NeedRevision => 'open',
            self::PendingRevision => 'open',
            self::ApprovedInProgress => 'in_progress',
            self::Closed => 'closed',
        };
    }
}
