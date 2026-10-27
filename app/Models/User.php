<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'nik', 'gender', 'date_of_birth', 'email', 'phone', 'role', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            'role' => Role::class,
            'date_of_birth' => 'date',
        ];
    }

    public function hasRole(string|Role ...$roles): bool
    {
        foreach ($roles as $role) {
            $value = $role instanceof Role ? $role->value : $role;
            if ($this->role?->value === $value || (is_string($this->role) && $this->role === $value)) {
                return true;
            }
        }

        return false;
    }

    public function isSuperadmin(): bool
    {
        return $this->hasRole(Role::Superadmin);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(Role::Admin);
    }

    public function isPelanggan(): bool
    {
        return $this->hasRole(Role::Pelanggan);
    }

    public function isMitra(): bool
    {
        return $this->hasRole(Role::Mitra);
    }

    /**
     * @return BelongsToMany<Region, $this>
     */
    public function assignedRegions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class, 'admin_region_assignments')
            ->withPivot('can_handle_emergency')
            ->withTimestamps();
    }

    /**
     * @return HasMany<AdminRegionAssignment, $this>
     */
    public function adminAssignments(): HasMany
    {
        return $this->hasMany(AdminRegionAssignment::class);
    }

    /**
     * @return HasMany<AdminMenuPermission, $this>
     */
    public function menuPermissions(): HasMany
    {
        return $this->hasMany(AdminMenuPermission::class);
    }

    /**
     * Check if user can access a specific region's data.
     */
    public function canAccessRegion(int $regionId): bool
    {
        if ($this->isSuperadmin()) {
            return true;
        }

        if (! $this->isAdmin()) {
            return false;
        }

        return $this->assignedRegions()->where('regions.id', $regionId)->exists();
    }

    /**
     * Check if admin can access a specific menu code.
     */
    public function canAccessMenu(string $menuCode): bool
    {
        if ($this->isSuperadmin()) {
            return true;
        }

        if (! $this->isAdmin()) {
            return false;
        }

        return $this->menuPermissions()->where('menu_code', $menuCode)->exists();
    }

    /**
     * Check if user has emergency duty permission for a specific region.
     */
    public function canHandleEmergency(int $regionId): bool
    {
        if ($this->isSuperadmin()) {
            return true;
        }

        if (! $this->isAdmin()) {
            return false;
        }

        return $this->canAccessRegion($regionId);
    }
}
