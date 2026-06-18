<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'nama_divisi',
        'kode_divisi',
    ];

    /**
     * Relasi: Divisi punya banyak Role.
     */
    public function roles(): HasMany
    {
        return $this->hasMany(\Spatie\Permission\Models\Role::class, 'division_id');
    }
}
