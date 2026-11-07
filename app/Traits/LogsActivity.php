<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait LogsActivity
{
    /**
     * Flag to temporarily silence auditing (e.g. for bulk operations or mass seeders).
     */
    protected static bool $isAuditingDisabled = false;

    /**
     * Boot the trait and register Eloquent event listeners.
     */
    public static function bootLogsActivity(): void
    {
        static::created(function (Model $model): void {
            if (static::$isAuditingDisabled) {
                return;
            }

            $model->recordAuditActivity('create', null, $model->getAuditAttributesForCreate());
        });

        static::updated(function (Model $model): void {
            if (static::$isAuditingDisabled) {
                return;
            }

            $changes = $model->getChanges();
            $ignored = $model->getAuditIgnoredAttributes();

            // Filter out ignored fields from changes
            $filteredChanges = array_diff_key($changes, array_flip($ignored));

            // If only ignored fields or updated_at changed, skip logging
            if (empty($filteredChanges)) {
                return;
            }

            $oldValues = [];
            $newValues = [];

            foreach ($filteredChanges as $key => $newValue) {
                $oldValues[$key] = $model->getOriginal($key);
                $newValues[$key] = $newValue;
            }

            $action = array_key_exists('status', $filteredChanges) ? 'status_change' : 'update';

            $model->recordAuditActivity($action, $oldValues, $newValues);
        });

        static::deleted(function (Model $model): void {
            if (static::$isAuditingDisabled) {
                return;
            }

            $model->recordAuditActivity('delete', $model->getAuditAttributesForDelete(), null);
        });
    }

    /**
     * Disable auditing temporarily.
     */
    public static function withoutAuditing(callable $callback): mixed
    {
        static::$isAuditingDisabled = true;

        try {
            return $callback();
        } finally {
            static::$isAuditingDisabled = false;
        }
    }

    /**
     * Attributes to ignore during audit logging.
     *
     * @return array<int, string>
     */
    public function getAuditIgnoredAttributes(): array
    {
        $defaultIgnored = [
            'password',
            'remember_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
            'created_at',
            'updated_at',
            'deleted_at',
        ];

        if (property_exists($this, 'auditIgnore') && is_array($this->auditIgnore)) {
            return array_merge($defaultIgnored, $this->auditIgnore);
        }

        return $defaultIgnored;
    }

    /**
     * Get module name for the audit log.
     */
    public function getAuditModule(): string
    {
        if (property_exists($this, 'auditModule') && is_string($this->auditModule)) {
            return $this->auditModule;
        }

        return Str::snake(class_basename($this));
    }

    /**
     * Filter model attributes for create log.
     *
     * @return array<string, mixed>
     */
    protected function getAuditAttributesForCreate(): array
    {
        $ignored = array_flip($this->getAuditIgnoredAttributes());

        return array_diff_key($this->getAttributes(), $ignored);
    }

    /**
     * Filter model attributes for delete log.
     *
     * @return array<string, mixed>
     */
    protected function getAuditAttributesForDelete(): array
    {
        $ignored = array_flip($this->getAuditIgnoredAttributes());

        return array_diff_key($this->getOriginal(), $ignored);
    }

    /**
     * Helper to write an audit log entry safely.
     *
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    protected function recordAuditActivity(string $action, ?array $oldValues, ?array $newValues): void
    {
        try {
            $request = request();
            $reason = null;

            if ($request) {
                $reason = $request->input('reason') ?: $request->input('notes') ?: null;
            }

            AuditLog::create([
                'user_id' => Auth::id(),
                'module' => $this->getAuditModule(),
                'action' => $action,
                'auditable_type' => static::class,
                'auditable_id' => $this->getKey(),
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'reason' => is_string($reason) ? mb_substr($reason, 0, 500) : null,
                'ip_address' => $request?->ip(),
                'user_agent' => $request ? mb_substr((string) $request->userAgent(), 0, 500) : null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Never break main business process if audit logging fails
            report($e);
        }
    }
}
