<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskFreeDeclaration extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'user_id',
        'periode',
        'bulan',
        'tahun',
        'statement_text',
        'status',
        'violated_at',
        'violated_by',
    ];

    protected $casts = [
        'violated_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function violator()
    {
        return $this->belongsTo(User::class, 'violated_by');
    }

    public function details()
    {
        return $this->hasMany(RiskFreeDeclarationDetail::class);
    }

    /**
     * Scope: deklarasi untuk periode tertentu di bulan & tahun tertentu
     */
    public function scopeForPeriod($query, $periode, $bulan, $tahun)
    {
        return $query->where('periode', $periode)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun);
    }

    /**
     * Scope: deklarasi yang masih active (belum violated/cancelled)
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
