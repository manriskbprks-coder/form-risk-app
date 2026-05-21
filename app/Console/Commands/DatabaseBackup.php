<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup database MySQL ke folder storage/backups/';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $connection = config('database.default');
        $dbConfig = config("database.connections.{$connection}");

        if (!$dbConfig || $dbConfig['driver'] !== 'mysql') {
            $this->error("Backup hanya mendukung MySQL. Koneksi saat ini: {$connection}");
            return Command::FAILURE;
        }

        $host = $dbConfig['host'];
        $port = $dbConfig['port'];
        $database = $dbConfig['database'];
        $username = $dbConfig['username'];
        $password = $dbConfig['password'];

        // Folder backup
        $backupDir = storage_path('backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        // Nama file: backup-YYYYMMDD-HHmmss.sql
        $filename = 'backup-' . now()->format('Ymd-His') . '.sql';
        $filepath = "{$backupDir}/{$filename}";

        // Build mysqldump command
        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s --routines --single-transaction %s > "%s" 2>&1',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            $filepath
        );

        $this->info("Memulai backup database: {$database}");
        $this->line("Menyimpan ke: {$filepath}");

        $output = null;
        $exitCode = null;
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            $errorMsg = implode("\n", $output);
            $this->error("Backup gagal! Exit code: {$exitCode}");
            $this->error($errorMsg);

            Log::error('[BACKUP] Database backup gagal', [
                'database' => $database,
                'exit_code' => $exitCode,
                'error' => $errorMsg,
            ]);

            return Command::FAILURE;
        }

        // Cek ukuran file
        $fileSize = filesize($filepath);
        $sizeFormatted = $this->formatBytes($fileSize);

        $this->info("✅ Backup berhasil! Ukuran: {$sizeFormatted}");
        $this->line("File: {$filename}");

        Log::info('[BACKUP] Database backup berhasil', [
            'database' => $database,
            'filename' => $filename,
            'size' => $sizeFormatted,
            'path' => $filepath,
        ]);

        // Hapus backup lebih dari 7 hari (retention)
        $this->cleanOldBackups($backupDir);

        return Command::SUCCESS;
    }

    /**
     * Hapus file backup yang lebih dari 7 hari.
     */
    private function cleanOldBackups(string $backupDir): void
    {
        $files = glob($backupDir . '/backup-*.sql');
        $retentionDays = 7;
        $cutoff = now()->subDays($retentionDays)->timestamp;

        $deleted = 0;
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->line("🧹 Membersihkan {$deleted} backup lama (> {$retentionDays} hari)");
            Log::info('[BACKUP] Backup lama dibersihkan', [
                'jumlah' => $deleted,
                'retention_days' => $retentionDays,
            ]);
        }
    }

    /**
     * Format bytes ke human readable.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
