<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Branch extends Model
{

    use HasFactory, HasUuids;

    protected $fillable = [
        'kode_cabang',       // <--- BARU
        'nickname_cabang',   // <--- BARU
        'nama_cabang', // (ini nama kolom lama lu, biarin aja)
        'is_active',   // <--- TAMBAHIN INI
        'korwil_id',   // <--- TAMBAHIN INI
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Cabang ini di bawah pengawasan siapa?
    public function korwil()
    {
        return $this->belongsTo(User::class, 'korwil_id');
    }

    // Scope buat Maker: Hanya nampilin cabang yang aktif
    public function scopeActive($query)
    {
        return $query->whereRaw('is_active = true');
    }
}
