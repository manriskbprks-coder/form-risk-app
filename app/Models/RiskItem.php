<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskItem extends Model
{
    use HasFactory;

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
