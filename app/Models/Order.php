<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_code',
        'order_type',
        'customer_id',
        'customer_address_id',
        'mitra_id',
        'region_id',
        'service_id',
        'service_package_id',
        'service_name',
        'duration_minutes',
        'total_price',
        'commission_percent',
        'recipient_type',
        'recipient_name',
        'recipient_gender',
        'recipient_phone',
        'service_address_text',
        'service_coordinate',
        'scheduled_start_time',
        'medical_notes',
        'risk_agreement_version',
        'consent_timestamp',
        'status',
        'cancellation_reason',
        'cancelled_by_id',
        'cancelled_by_role',
        'reschedule_count',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'order_type' => OrderType::class,
            'duration_minutes' => 'integer',
            'total_price' => 'decimal:2',
            'commission_percent' => 'decimal:2',
            'scheduled_start_time' => 'datetime',
            'consent_timestamp' => 'datetime',
            'medical_notes' => 'encrypted',
            'reschedule_count' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<CustomerAddress, $this>
     */
    public function customerAddress(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'customer_address_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function mitra(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mitra_id');
    }

    /**
     * @return BelongsTo<Region, $this>
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    /**
     * @return BelongsTo<ServicePackage, $this>
     */
    public function servicePackage(): BelongsTo
    {
        return $this->belongsTo(ServicePackage::class, 'service_package_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_id');
    }

    /**
     * @return HasMany<OrderStatusHistory, $this>
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at', 'asc');
    }

    /**
     * Scope orders to specific region ID or list of IDs.
     */
    public function scopeInRegions(Builder $query, array|int $regionIds): Builder
    {
        if (is_array($regionIds)) {
            return $query->whereIn('region_id', $regionIds);
        }

        return $query->where('region_id', $regionIds);
    }

    /**
     * Scope query to active orders.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            OrderStatus::MencariMitra,
            OrderStatus::Dijadwalkan,
            OrderStatus::MenujuLokasi,
            OrderStatus::Tiba,
            OrderStatus::SedangBerlangsung,
        ]);
    }

    /**
     * Scope query to stuck orders (ADM-04).
     */
    public function scopeStuck(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            // Instant order looking for partner > 30 minutes
            $q->where('order_type', OrderType::Instan)
                ->where('status', OrderStatus::MencariMitra)
                ->where('created_at', '<=', Carbon::now()->subMinutes(30));
        })->orWhere(function (Builder $q) {
            // Scheduled order looking for partner with less than 2 hours before scheduled time
            $q->where('order_type', OrderType::Terjadwal)
                ->where('status', OrderStatus::MencariMitra)
                ->whereNotNull('scheduled_start_time')
                ->where('scheduled_start_time', '<=', Carbon::now()->addHours(2));
        });
    }

    /**
     * Check if this order is considered stuck.
     */
    public function isStuck(): bool
    {
        if ($this->status !== OrderStatus::MencariMitra) {
            return false;
        }

        if ($this->order_type === OrderType::Instan) {
            return $this->created_at <= Carbon::now()->subMinutes(30);
        }

        if ($this->order_type === OrderType::Terjadwal && $this->scheduled_start_time) {
            return $this->scheduled_start_time <= Carbon::now()->addHours(2);
        }

        return false;
    }

    /**
     * Whether this order can be cancelled by an admin.
     */
    public function canBeCancelledByAdmin(): bool
    {
        return ! $this->status->isTerminal();
    }

    /**
     * Get medical notes with privacy guard for partner views.
     * Hidden during broadcast/offers, revealed only after partner assignment/acceptance.
     */
    public function getMedicalNotesForPartner(): ?string
    {
        if (in_array($this->status, [OrderStatus::MencariMitra, OrderStatus::MenungguPembayaran], true)) {
            return null;
        }

        return $this->medical_notes;
    }

    /**
     * Formatted Rupiah price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp '.number_format((float) $this->total_price, 0, ',', '.');
    }

    /**
     * Set the spatial service coordinate.
     */
    public function setServiceCoordinateAttribute(array|string|null $value): void
    {
        if (is_array($value) && isset($value['lat'], $value['lng'])) {
            $wkt = sprintf('POINT(%F %F)', (float) $value['lng'], (float) $value['lat']);
            $this->attributes['service_coordinate'] = DB::raw("ST_GeomFromText('{$wkt}', 4326, 'axis-order=long-lat')");
        } elseif (is_string($value)) {
            $this->attributes['service_coordinate'] = DB::raw("ST_GeomFromText('{$value}', 4326, 'axis-order=long-lat')");
        }
    }
}
