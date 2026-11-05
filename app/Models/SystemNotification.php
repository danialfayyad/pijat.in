<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemNotification extends Model
{
    use HasFactory, Prunable;

    protected $fillable = [
        'user_id',
        'event_code',
        'title',
        'body',
        'priority',
        'data',
        'read_at',
    ];

    /**
     * Get the prunable model query.
     * Automatically prunes notifications older than 30 days (1 month).
     *
     * @return Builder<$this>
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subDays(30));
    }

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for unread notifications.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for read notifications.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeRead(Builder $query): Builder
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(): bool
    {
        if ($this->read_at === null) {
            return $this->update(['read_at' => now()]);
        }

        return true;
    }

    /**
     * Get target action URL for deep linking.
     */
    public function getActionUrlAttribute(): ?string
    {
        if (! empty($this->data['action_url'])) {
            return (string) $this->data['action_url'];
        }

        if (str_contains($this->event_code, 'EMERGENCY')) {
            return route('admin.orders.index');
        }

        if (str_contains(strtolower($this->title), 'verifikasi') || str_contains(strtolower($this->title), 'mitra')) {
            return route('admin.mitra.index');
        }

        if (str_contains(strtolower($this->title), 'aduan')) {
            return route('admin.complaints.index');
        }

        if (str_contains(strtolower($this->title), 'banding')) {
            return route('admin.appeals.index');
        }

        if (str_contains(strtolower($this->title), 'legal') || str_contains(strtolower($this->title), 'dokumen')) {
            return route('superadmin.legal-documents.index');
        }

        return null;
    }

    /**
     * Get action button label for the notification.
     */
    public function getActionLabelAttribute(): string
    {
        if (str_contains($this->event_code, 'EMERGENCY')) {
            return 'Tangani Pesanan';
        }

        if (str_contains(strtolower($this->title), 'verifikasi') || str_contains(strtolower($this->title), 'mitra')) {
            return 'Tinjau Berkas';
        }

        if (str_contains(strtolower($this->title), 'aduan')) {
            return 'Investigasi Aduan';
        }

        if (str_contains(strtolower($this->title), 'banding')) {
            return 'Tinjau Banding';
        }

        if (str_contains(strtolower($this->title), 'legal') || str_contains(strtolower($this->title), 'dokumen')) {
            return 'Buka Dokumen';
        }

        return 'Buka Rincian';
    }
}
