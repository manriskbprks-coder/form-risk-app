<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kategori default "Umum"
        $defaultCatId = \Illuminate\Support\Str::uuid()->toString();
        \Illuminate\Support\Facades\DB::table('risk_categories')->insert([
            'id' => $defaultCatId,
            'nama_kategori' => 'Umum',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Tambah kolom ke risk_items (nullable sementara buat handle existing data)
        Schema::table('risk_items', function (Blueprint $table) {
            $table->uuid('risk_category_id')->nullable()->after('id');
            $table->foreign('risk_category_id')->references('id')->on('risk_categories')->nullOnDelete();
        });

        // 3. Update semua data existing dengan kategori Umum
        \Illuminate\Support\Facades\DB::table('risk_items')->update([
            'risk_category_id' => $defaultCatId
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risk_items', function (Blueprint $table) {
            $table->dropForeign(['risk_category_id']);
            $table->dropColumn('risk_category_id');
        });

        // Hapus kategori default Umum
        \Illuminate\Support\Facades\DB::table('risk_categories')->where('nama_kategori', 'Umum')->delete();
    }
};
