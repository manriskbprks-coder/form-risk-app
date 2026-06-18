<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    /**
     * Get the division that owns the role.
     */
    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }
}
