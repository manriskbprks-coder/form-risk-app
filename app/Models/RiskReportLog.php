<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskReportLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'risk_report_id',
        'user_id',
        'note',
        'status_after_note',
        'old_data',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
