<?php

namespace App\Models;

use App\Domain\Enums\RiskReportStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class RiskReport extends Model
{
    use HasFactory, HasUuids;

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
        'status',
        'revision_note',
        'tindakan_penyelesaian'
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

    public function skmrAnalysis() {
        return $this->hasOne(SkmrAnalysis::class);
    }

    // ─── Status Helper Methods ───────────────────────────────────────

    /**
     * Scope: filter by status value.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function isPendingAtasan(): bool
    {
        return $this->status === RiskReportStatus::PendingAtasan->value;
    }

    public function isNeedRevision(): bool
    {
        return $this->status === RiskReportStatus::NeedRevision->value;
    }

    public function isPendingRevision(): bool
    {
        return $this->status === RiskReportStatus::PendingRevision->value;
    }

    public function isApprovedInProgress(): bool
    {
        return $this->status === RiskReportStatus::ApprovedInProgress->value;
    }

    public function isInProgress(): bool
    {
        return $this->status === RiskReportStatus::InProgress->value;
    }

    public function isClosed(): bool
    {
        return $this->status === RiskReportStatus::Closed->value;
    }

    /**
     * Cek apakah bisa transisi ke status target.
     */
    public function canTransitionTo(string $target): bool
    {
        $current = RiskReportStatus::tryFrom($this->status);
        $targetEnum = RiskReportStatus::tryFrom($target);

        if (!$current || !$targetEnum) {
            return false;
        }

        return $current->canTransitionTo($targetEnum);
    }
}

