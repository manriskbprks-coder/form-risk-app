<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_laporan',
        'user_id', 'branch_id', 'tanggal_kejadian', 'tanggal_diketahui',
        'risk_item_id', 'other_item_description', 'risk_cause_id',
        'other_cause_description', 'kronologis_kejadian', 'mitigasi_tambahan',
        'durasi_penyelesaian', 'durasi_satuan',
        'dampak_finansial', 
        'dampak_non_finansial',
        'skala_dampak',
        'kategori',
        'sumber_risiko',
        'approval_status', 'resolution_status',
        'revision_note'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function branch() {
        return $this->belongsTo(Branch::class);
    }

    public function item() {
        return $this->belongsTo(RiskItem::class, 'risk_item_id');
    }

    public function cause() {
        return $this->belongsTo(RiskCause::class, 'risk_cause_id');
    }
    public function logs() {
    return $this->hasMany(RiskReportLog::class)->latest();
}
}
