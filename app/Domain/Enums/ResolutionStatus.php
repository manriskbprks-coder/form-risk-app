<?php

namespace App\Domain\Enums;

/**
 * Status penyelesaian (tindak lanjut) laporan risiko.
 *
 * Flow transisi:
 *   open → in_progress → closed
 *   open → closed (langsung, jika laporan ditutup tanpa progress)
 */
enum ResolutionStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Closed = 'closed';

    /**
     * Cek apakah transisi ke status target valid.
     */
    public function canTransitionTo(self $target): bool
    {
        return in_array($target, self::allowedTransitions()[$this->value] ?? []);
    }

    /**
     * Daftar transisi yang valid.
     */
    public static function allowedTransitions(): array
    {
        return [
            'open' => [self::InProgress, self::Closed],
            'in_progress' => [self::Closed],
            'closed' => [],
        ];
    }

    /**
     * Apakah status ini final (laporan sudah selesai)?
     */
    public function isFinal(): bool
    {
        return $this === self::Closed;
    }

    /**
     * Label user-friendly.
     */
    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::InProgress => 'Dalam Proses',
            self::Closed => 'Selesai',
        };
    }
}
