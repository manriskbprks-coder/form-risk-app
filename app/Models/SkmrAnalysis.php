<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SkmrAnalysis extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'risk_report_id',
        'catatan_skmr',
        'ketersediaan_kebijakan',
        'kesesuaian_sop',
        'rekomendasi_1',
        'rekomendasi_2',
        'dampak_rekomendasi_1',
        'dampak_rekomendasi_2',
        'created_by'
    ];

    public function riskReport()
    {
        return $this->belongsTo(RiskReport::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
