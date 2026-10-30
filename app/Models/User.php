<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\OrderStatus;
use App\Enums\Role;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'nik', 'gender', 'date_of_birth', 'email', 'phone', 'role', 'status', 'region_id', 'password', 'phone_verified_at', 'rejection_reason', 'rejection_code', 'penalty_points', 'suspended_until'])]
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
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'status' => UserStatus::class,
            'date_of_birth' => 'date',
            'penalty_points' => 'integer',
            'suspended_until' => 'datetime',
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

    public function hasStatus(string|UserStatus ...$statuses): bool
    {
        foreach ($statuses as $status) {
            $value = $status instanceof UserStatus ? $status->value : strtolower($status);
            $current = $this->status instanceof UserStatus ? $this->status->value : strtolower((string) $this->status);
            if ($current === $value) {
                return true;
            }
        }

        return false;
    }

    public function isBelumLengkap(): bool
    {
        return $this->hasStatus(UserStatus::BelumLengkap);
    }

    public function isMenungguVerifikasi(): bool
    {
        return $this->hasStatus(UserStatus::MenungguVerifikasi);
    }

    public function isTerverifikasi(): bool
    {
        return $this->hasStatus(UserStatus::Terverifikasi);
    }

    public function isDitolak(): bool
    {
        return $this->hasStatus(UserStatus::Ditolak);
    }

    /**
     * @return BelongsTo<Region, $this>
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * @return HasMany<CustomerAddress, $this>
     */
    public function customerAddresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    /**
     * @return HasOne<CustomerAddress, $this>
     */
    public function primaryAddress(): HasOne
    {
        return $this->hasOne(CustomerAddress::class)->where('is_primary', true);
    }

    /**
     * @return HasOne<Wallet, $this>
     */
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * Retrieve existing wallet or initialize a zero-balance wallet atomically.
     */
    public function getOrCreateWallet(): Wallet
    {
        return $this->wallet()->firstOrCreate([], ['balance' => 0, 'currency' => 'IDR']);
    }

    /**
     * @return HasOne<PartnerProfile, $this>
     */
    public function partnerProfile(): HasOne
    {
        return $this->hasOne(PartnerProfile::class);
    }

    /**
     * @return HasMany<BankAccount, $this>
     */
    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }

    /**
     * @return HasOne<BankAccount, $this>
     */
    public function primaryBankAccount(): HasOne
    {
        return $this->hasOne(BankAccount::class)->latestOfMany();
    }

    /**
     * @return HasMany<ComplianceAgreement, $this>
     */
    public function complianceAgreements(): HasMany
    {
        return $this->hasMany(ComplianceAgreement::class);
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
     * @return HasMany<Order, $this>
     */
    public function ordersAsCustomer(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function ordersAsMitra(): HasMany
    {
        return $this->hasMany(Order::class, 'mitra_id');
    }

    /**

     * @return HasMany<Complaint, $this>
     */
    public function complaintsAsTarget(): HasMany
    {
        return $this->hasMany(Complaint::class, 'target_id');
    }

    /**
     * @return HasMany<Complaint, $this>
     */
    public function complaintsAsComplainant(): HasMany
    {
        return $this->hasMany(Complaint::class, 'complainant_id');


    }
    public function completedOrdersCount(): int
    {
        return $this->ordersAsMitra()->where('status', OrderStatus::Selesai)->count();
    }

    /**
     * Get the partner's rating (default 5.0).
     */
    public function partnerRating(): float
    {
        return 5.0;

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

    /**
     * Check if user is currently suspended.
     */
    public function isSuspended(): bool
    {
        return $this->suspended_until !== null && $this->suspended_until->isFuture();
    }

    /**
     * Get penalty ledgers for this user.
     *
     * @return HasMany<PenaltyLedger, $this>
     */
    public function penaltyLedgers(): HasMany
    {
        return $this->hasMany(PenaltyLedger::class);
    }
}
