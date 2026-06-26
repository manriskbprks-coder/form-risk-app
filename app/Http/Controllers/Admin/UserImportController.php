<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Branch;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Response;

class UserImportController extends Controller
{
    public function index()
    {
        return view('admin.users.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        
        // Membaca CSV
        $csvData = array_map('str_getcsv', file($file->getRealPath()));
        $header = array_shift($csvData);

        if (count($header) < 5) {
            return back()->with('error', 'Format CSV tidak valid. Harus memiliki minimal 5 kolom (NIK, Nama, Kode Cabang, Jabatan, Divisi).');
        }

        $exportData = [];
        $exportData[] = ['NIK', 'Nama', 'Kode Cabang', 'Jabatan', 'Divisi', 'Password Mentah', 'Status'];

        $successCount = 0;
        $failedCount = 0;

        foreach ($csvData as $row) {
            if (count($row) < 5) continue;

            $nik = trim($row[0]);
            $nama = trim($row[1]);
            $kode_cabang = trim($row[2]);
            $jabatan = trim($row[3]); // misal 'BRANCH MANAGER'
            $nama_divisi = trim($row[4]); // misal 'REGIONAL'

            if (empty($nik) || empty($nama)) {
                $failedCount++;
                continue;
            }

            // Cari Cabang
            $branch = Branch::where('kode_cabang', $kode_cabang)
                            ->orWhere('nickname_cabang', $kode_cabang)
                            ->first();

            if (!$branch) {
                $exportData[] = [$nik, $nama, $kode_cabang, $jabatan, $nama_divisi, '-', 'GAGAL: Cabang tidak ditemukan'];
                $failedCount++;
                continue;
            }

            // Cari Role
            $role = Role::where('name', $jabatan)->first();
            if (!$role) {
                $exportData[] = [$nik, $nama, $kode_cabang, $jabatan, $nama_divisi, '-', 'GAGAL: Jabatan tidak ada di sistem'];
                $failedCount++;
                continue;
            }

            // Cari atau Buat Divisi
            $division = null;
            if (!empty($nama_divisi)) {
                $division = \App\Models\Division::firstOrCreate(['nama_divisi' => strtoupper($nama_divisi)]);
            }

            // Generate password 6 chars
            $plainPassword = \Illuminate\Support\Str::random(6);

            $user = User::updateOrCreate(
                ['username' => $nik],
                [
                    'name' => $nama,
                    'branch_id' => $branch->id,
                    'division_id' => $division ? $division->id : ($role->division_id ?? null),
                    'password' => \Illuminate\Support\Facades\Hash::make($plainPassword),
                ]
            );

            $user->syncRoles([$role]);

            $exportData[] = [$nik, $nama, $kode_cabang, $jabatan, $nama_divisi, $plainPassword, 'SUKSES'];
            $successCount++;
        }

        // Generate file download hasil import
        $filename = "Hasil_Import_Users_" . date('Ymd_His') . ".csv";
        $handle = fopen('php://temp', 'w+');
        foreach ($exportData as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);
        
        $path = 'imports/' . $filename; // inside storage/app
        \Illuminate\Support\Facades\Storage::put($path, $content);
        
        return redirect()->route('admin.users.import.success', ['filename' => $filename])
                         ->with('success', "Import selesai! Berhasil: {$successCount}, Gagal: {$failedCount}");
    }

    public function success(Request $request)
    {
        $filename = $request->query('filename');
        return view('admin.users.import_success', compact('filename'));
    }

    public function download($filename)
    {
        $path = 'imports/' . $filename;
        if (!\Illuminate\Support\Facades\Storage::exists($path)) {
            abort(404);
        }
        return \Illuminate\Support\Facades\Storage::download($path);
    }
}
