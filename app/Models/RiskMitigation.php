<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class RiskMitigation extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'risk_cause_id',
        'mitigasi',
    ];

    // Relasi balik: Mitigasi ini milik 1 Penyebab
    public function cause()
    {
        return $this->belongsTo(RiskCause::class);
    }
}