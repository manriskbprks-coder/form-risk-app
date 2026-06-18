<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Domain\Enums\RoleCategory;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasUuids;

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
        'is_active',
        'password_changed_at',
        'has_seen_tour',
        'failed_login_attempts',
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
            'has_seen_tour' => 'boolean',
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
    // ROLE CATEGORY HELPERS (diambil dari role, bukan dari kolom user)
    // ============================================================

    /**
     * Ambil role_category dari role pertama user.

     */
    public function roleCategory(): ?string
    {
        $role = $this->roles->first();
        return $role?->role_category ?? null;
    }

    /**
     * Dapatkan RoleCategory enum dari user.
     */
    public function roleCategoryEnum(): ?RoleCategory
    {
        return RoleCategory::tryFrom($this->roleCategory() ?? '');
    }

    /**
     * Cek apakah user termasuk kategori Maker (bisa bikin laporan).
     */
    public function isMaker(): bool
    {
        return $this->roleCategoryEnum()?->isMaker() ?? false;
    }

    /**
     * Cek apakah user termasuk kategori Checker (bisa approve/reject).
     */
    public function isChecker(): bool
    {
        return $this->roleCategoryEnum()?->isChecker() ?? false;
    }

    /**
     * Cek apakah user termasuk kategori Viewer (hanya lihat).
     */
    public function isViewer(): bool
    {
        return $this->roleCategoryEnum()?->isViewer() ?? false;
    }

    /**
     * Cek apakah user termasuk kategori Admin (manrisk).
     */
    public function isAdmin(): bool
    {
        return $this->roleCategoryEnum()?->isAdmin() ?? false;
    }

    /**
     * Cek apakah user adalah Kacab.
     */
    public function isKacab(): bool
    {
        return $this->hasRole('kacab');
    }

    /**
     * Cek apakah user bisa bikin laporan (maker + checker).
     */
    public function canCreateReport(): bool
    {
        return $this->roleCategoryEnum()?->canCreateReport() ?? false;
    }

}
