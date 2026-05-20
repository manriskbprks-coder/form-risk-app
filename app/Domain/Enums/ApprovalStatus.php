<?php

namespace App\Domain\Enums;

/**
 * Status approval laporan risiko — state machine untuk approval workflow.
 *
 * Flow transisi yang valid:
 *   pending_kacab ──┬──→ approved       (approve oleh Kacab)
 *                   └──→ need_revision   (reject oleh Kacab → minta revisi)
 *   need_revision ──┬──→ pending_kacab   (submit revisi → review Kacab lagi)
 *                   └──→ pending_revision (submit revisi → review ManRisk)
 *   pending_revision → approved          (approve revisi oleh ManRisk)
 *   approved ───────→ need_revision      (ManRisk request revision dari laporan yg sudah approved)
 */
enum ApprovalStatus: string
{
    case PendingKacab = 'pending_kacab';
    case NeedRevision = 'need_revision';
    case PendingRevision = 'pending_revision';
    case Approved = 'approved';

    /**
     * Cek apakah transisi dari status ini ke status target valid.
     *
     * Contoh: ApprovalStatus::PendingKacab->canTransitionTo(ApprovalStatus::Approved) // true
     *         ApprovalStatus::Approved->canTransitionTo(ApprovalStatus::PendingKacab) // false
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
            'pending_kacab' => [self::Approved, self::NeedRevision],
            'need_revision' => [self::PendingKacab, self::PendingRevision],
            'pending_revision' => [self::Approved],
            'approved' => [self::NeedRevision],
        ];
    }

    /**
     * Apakah status ini termasuk "final" (tidak bisa berubah lagi)?
     *
     * NOTE: Approved bukan final karena ManRisk bisa request revision
     * dari laporan yang sudah di-approve Kacab.
     */
    public function isFinal(): bool
    {
        return false;
    }

    /**
     * Apakah status ini membutuhkan tindakan dari user?
     */
    public function needsAction(): bool
    {
        return in_array($this, [self::PendingKacab, self::NeedRevision, self::PendingRevision]);
    }

    /**
     * Label yang user-friendly untuk ditampilkan di UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::PendingKacab => 'Menunggu Persetujuan Kacab',
            self::NeedRevision => 'Perlu Revisi',
            self::PendingRevision => 'Menunggu Persetujuan Revisi',
            self::Approved => 'Disetujui',
        };
    }
}
