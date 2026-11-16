<?php

namespace App\Support;

use App\Models\{Agency, AuditLog};
use Illuminate\Database\Eloquent\Model;

/** Writes the audit log: who did what, to which record, from where. Never breaks the action itself. */
class Audit
{
    public static function log(string $event, ?Model $record = null, array $old = [], array $new = []): void
    {
        try {
            unset($old['password'], $new['password'], $old['remember_token'], $new['remember_token']);

            AuditLog::withoutGlobalScopes()->create([
                'agency_id' => $record instanceof Agency ? $record->getKey() : ($record?->agency_id ?: null),
                'user_id' => session('impersonator_id') ?: auth()->id(),
                'event' => $event,
                'auditable_type' => $record ? class_basename($record) : 'System',
                'auditable_id' => (int) ($record?->getKey() ?? 0),
                'old_values' => $old ?: null,
                'new_values' => $new ?: null,
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 500),
            ]);
        } catch (\Throwable) {
            // the log is a record of the action, not a condition of it
        }
    }
}
