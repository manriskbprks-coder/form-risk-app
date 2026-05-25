<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class RiskItem extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'nama_risiko',
        'kategori',
        'sumber_risiko',
        'role_target',
    ];
    
    // Relasi: 1 Item Punya Banyak Penyebab
    public function causes()
    {
        return $this->hasMany(RiskCause::class);
    }
}
