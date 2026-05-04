<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskCause extends Model
{
    use HasFactory;

    protected $fillable = [
        'risk_item_id',
        'penyebab',
        'sumber_risiko',
    ];

    // Relasi balik: Penyebab ini milik 1 Item
    public function item()
    {
        return $this->belongsTo(RiskItem::class);
    }

    // Relasi ke bawah: 1 Penyebab Punya Banyak Mitigasi
    public function mitigations()
    {
        return $this->hasMany(RiskMitigation::class);
    }
}
