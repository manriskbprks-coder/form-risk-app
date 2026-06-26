<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Branch;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ImportUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:users {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import users from a CSV file (nik, nama, kode_cabang, jabatan, divisi) and generate passwords.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error("File not found at path: {$file}");
            return Command::FAILURE;
        }

        $csvData = array_map('str_getcsv', file($file));
        $header = array_shift($csvData);

        if (count($header) < 4) {
            $this->error("Invalid CSV format. Expected: nik,nama,kode_cabang,jabatan");
            return Command::FAILURE;
        }

        $exportData = [];
        $exportData[] = ['NIK', 'Nama', 'Kode Cabang', 'Jabatan', 'Password Mentah'];

        $this->info("Importing users...");
        $bar = $this->output->createProgressBar(count($csvData));

        foreach ($csvData as $row) {
            if (count($row) < 4) continue; // Skip bad rows

            $nik = trim($row[0]);
            $nama = trim($row[1]);
            $kode_cabang = trim($row[2]);
            $jabatan = trim($row[3]); // e.g., 'BRANCH MANAGER'

            // Temukan cabang
            $branch = Branch::where('kode_cabang', $kode_cabang)
                            ->orWhere('nickname_cabang', $kode_cabang)
                            ->first();

            if (!$branch) {
                $this->error("\nBranch not found for kode_cabang: {$kode_cabang}");
                continue;
            }

            // Temukan Role di database
            $role = Role::where('name', $jabatan)->first();
            if (!$role) {
                $this->warn("\nRole/Jabatan '{$jabatan}' tidak ditemukan di tabel roles. Pastikan nama jabatan sama persis dengan yang ada di RoleSeeder. User {$nama} di-skip.");
                continue;
            }

            // Generate random password 6 chars alphanumeric
            $plainPassword = Str::random(6);

            $user = User::updateOrCreate(
                ['username' => $nik],
                [
                    'name' => $nama,
                    'branch_id' => $branch->id,
                    'password' => Hash::make($plainPassword),
                ]
            );

            // Assign role (akan mereset role lama jika ada dan menimpa dengan yang baru)
            $user->syncRoles([$role]);

            $exportData[] = [$nik, $nama, $kode_cabang, $jabatan, $plainPassword];

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        // Export to CSV
        $exportPath = storage_path('app/public/user_credentials.csv');
        $fp = fopen($exportPath, 'w');
        foreach ($exportData as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);

        $this->info("Import selesai! Data kredensial tersimpan di: {$exportPath}");
        return Command::SUCCESS;
    }
}
