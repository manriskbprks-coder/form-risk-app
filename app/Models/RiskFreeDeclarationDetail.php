<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class RiskFreeDeclarationDetail extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'risk_free_declaration_id',
        'jabatan',
        'is_clean',
        'keterangan',
    ];

    protected $casts = [
        'is_clean' => 'boolean',
    ];

    public function declaration()
    {
        return $this->belongsTo(RiskFreeDeclaration::class, 'risk_free_declaration_id');
    }
}
