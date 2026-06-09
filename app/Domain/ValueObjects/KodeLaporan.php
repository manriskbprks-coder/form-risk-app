<?php

namespace App\Domain\ValueObjects;

/**
 * Value Object untuk kode laporan risiko.
 *
 * Format: RISK-{kodeCabang}{kodeRole}-{YYYYMM}-{0001}
 * Contoh: RISK-HQTL-202605-0001
 *
 * Value Object bersifat immutable — sekali dibuat, tidak bisa diubah.
 * Self-validating — constructor akan throw exception jika format tidak valid.
 */
class KodeLaporan
{
    private const PREFIX = 'RISK';
    private const PATTERN = '/^RISK-([A-Z0-9]+)-(\d{6})-(\d{4})$/';

    /**
     * Mapping role name ke kode singkat.
     */
    private const ROLE_MAP = [
        'teller' => 'TL',
        'ca' => 'CA',
        'csr' => 'CSR',
        'security' => 'SC',
        'kacab' => 'KC',
    ];

    public function __construct(
        public readonly string $kodeCabang,
        public readonly string $kodeRole,
        public readonly string $tahunBulan,
        public readonly string $nomorUrut,
    ) {
        if (!preg_match('/^[A-Z0-9]{2,5}$/', $kodeRole)) {
            throw new \InvalidArgumentException("Kode role tidak valid: {$kodeRole}");
        }

        if (!preg_match('/^\d{6}$/', $tahunBulan)) {
            throw new \InvalidArgumentException("Format tahunBulan harus YYYYMM: {$tahunBulan}");
        }

        if (!preg_match('/^\d{4}$/', $nomorUrut)) {
            throw new \InvalidArgumentException("Nomor urut harus 4 digit: {$nomorUrut}");
        }
    }

    /**
     * Buat KodeLaporan dari string lengkap.
     *
     * @throws \InvalidArgumentException Jika format string tidak sesuai
     */
    public static function fromString(string $kode): self
    {
        if (!preg_match(self::PATTERN, $kode, $matches)) {
            throw new \InvalidArgumentException(
                "Format kode laporan tidak valid: {$kode}. " .
                "Format yang diharapkan: RISK-{kodeCabang}{kodeRole}-{YYYYMM}-{0001}"
            );
        }

        // Parse kodeCabang + kodeRole dari group 1
        // Contoh: "HQTL" → kodeCabang="HQ", kodeRole="TL"
        $cabangRole = $matches[1];
        $tahunBulan = $matches[2];
        $nomorUrut = $matches[3];

        // Role selalu 2 karakter terakhir
        $kodeRole = substr($cabangRole, -2);
        $kodeCabang = substr($cabangRole, 0, -2);

        return new self($kodeCabang, $kodeRole, $tahunBulan, $nomorUrut);
    }

    /**
     * Buat KodeLaporan baru dengan nomor urut berikutnya.
     */
    public static function generate(string $kodeCabang, string $roleName, int $nextSequence): self
    {
        $kodeRole = self::ROLE_MAP[$roleName] ?? 'XX';
        $tahunBulan = now()->format('Ym');
        $nomorUrut = str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);

        return new self($kodeCabang, $kodeRole, $tahunBulan, $nomorUrut);
    }

    /**
     * Output string lengkap.
     */
    public function toString(): string
    {
        return implode('-', [
            self::PREFIX,
            $this->kodeCabang . $this->kodeRole,
            $this->tahunBulan,
            $this->nomorUrut,
        ]);
    }

    /**
     * Magic method untuk string conversion.
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Dapatkan role map (untuk referensi).
     */
    public static function getRoleMap(): array
    {
        return self::ROLE_MAP;
    }
}
