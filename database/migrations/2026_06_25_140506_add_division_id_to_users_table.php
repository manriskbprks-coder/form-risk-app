<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom division_id
        Schema::table('users', function (Blueprint $table) {
            $table->foreignUuid('division_id')->nullable()->constrained('divisions')->onDelete('set null');
        });

        // 2. Data Patching: Update existing users
        // Dapatkan semua user
        $users = User::all();
        foreach ($users as $user) {
            // Cek role utamanya
            $roleName = $user->primaryRoleName();
            if ($roleName) {
                // Cari divisi dari role tersebut
                $role = Role::where('name', $roleName)->first();
                if ($role && $role->division_id) {
                    $user->division_id = $role->division_id;
                    $user->save();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['division_id']);
            $table->dropColumn('division_id');
        });
    }
};
