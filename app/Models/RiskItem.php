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
        'risk_category_id',
    ];
    
    // Relasi: 1 Item Punya Banyak Penyebab
    public function causes()
    {
        return $this->hasMany(RiskCause::class);
    }

    // Relasi: 1 Item Milik 1 Kategori
    public function category()
    {
        return $this->belongsTo(RiskCategory::class, 'risk_category_id');
    }
}
