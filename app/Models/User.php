<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'branch_id',
        'role_category',
        'is_active',
        'password_changed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'password_changed_at' => 'datetime',
        ];
    }

    // Kasih tau Laravel kalau User ini kerja di sebuah Cabang
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // Kalau User ini adalah Korwil, dia megang cabang mana aja?
    public function supervisedBranches()
    {
        return $this->hasMany(Branch::class, 'korwil_id');
    }

    public function primaryRoleName(): ?string
    {
        $role = $this->getRoleNames()->first();
        return $role ? (string) $role : null;
    }

    /**
     * Cek apakah password user sudah expired (lebih dari 90 hari).
     */
    public function mustChangePassword(): bool
    {
        if (is_null($this->password_changed_at)) {
            return true;
        }

        return $this->password_changed_at->addDays(90)->isPast();
    }

    // ============================================================
    // ROLE CATEGORY HELPERS
    // ============================================================

    /**
     * Cek apakah user termasuk kategori Maker (bisa bikin laporan).
     */
    public function isMaker(): bool
    {
        return $this->role_category === 'maker' || $this->role_category === 'checker';
    }

    /**
     * Cek apakah user termasuk kategori Checker (bisa approve/reject).
     */
    public function isChecker(): bool
    {
        return $this->role_category === 'checker';
    }

    /**
     * Cek apakah user termasuk kategori Viewer (hanya lihat).
     */
    public function isViewer(): bool
    {
        return $this->role_category === 'viewer';
    }

    /**
     * Cek apakah user bisa bikin laporan (maker + checker).
     */
    public function canCreateReport(): bool
    {
        return in_array($this->role_category, ['maker', 'checker']);
    }
}
