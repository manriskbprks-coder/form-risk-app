<?php

namespace App\Domain\Enums;

/**
 * Kategori role — pengelompokan role Spatie ke dalam 4 kategori fungsional.
 *
 * Mapping:
 *   maker  → teller, ca, csr, security  (bisa bikin laporan)
 *   checker → kacab                      (bisa approve/reject)
 *   viewer → korwil                      (hanya lihat)
 *   admin  → manrisk                     (super admin)
 */
enum RoleCategory: string
{
    case Maker = 'maker';
    case Checker = 'checker';
    case Viewer = 'viewer';
    case Admin = 'admin';

    /**
     * Apakah kategori ini bisa membuat laporan risiko?
     */
    public function canCreateReport(): bool
    {
        return in_array($this, [self::Maker, self::Checker]);
    }

    /**
     * Apakah kategori ini bisa menyetujui laporan?
     */
    public function canApprove(): bool
    {
        return $this === self::Checker;
    }

    /**
     * Apakah kategori ini bisa meminta revisi?
     */
    public function canRequestRevision(): bool
    {
        return $this === self::Admin;
    }

    /**
     * Apakah kategori ini bisa menyetujui revisi?
     */
    public function canApproveRevision(): bool
    {
        return $this === self::Admin;
    }

    /**
     * Apakah kategori ini hanya bisa melihat (read-only)?
     */
    public function isViewer(): bool
    {
        return $this === self::Viewer;
    }

    /**
     * Apakah kategori ini adalah admin?
     */
    public function isAdmin(): bool
    {
        return $this === self::Admin;
    }

    /**
     * Apakah kategori ini adalah checker (Kacab)?
     */
    public function isChecker(): bool
    {
        return $this === self::Checker;
    }

    /**
     * Apakah kategori ini adalah maker?
     */
    public function isMaker(): bool
    {
        return $this === self::Maker;
    }

    /**
     * Label user-friendly.
     */
    public function label(): string
    {
        return match ($this) {
            self::Maker => 'Maker',
            self::Checker => 'Checker',
            self::Viewer => 'Viewer',
            self::Admin => 'Admin',
        };
    }
}
