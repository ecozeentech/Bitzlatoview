<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogger
{
    public function log(
        string $action,
        ?Model $target = null,
        ?array $before = null,
        ?array $after = null,
        ?array $metadata = null,
        ?Request $request = null,
        ?int $actorId = null,
    ): AuditLog {
        $request ??= request();

        return AuditLog::query()->create([
            'actor_id' => $actorId ?? auth()->id(),
            'action' => $action,
            'target_type' => $target ? $target::class : null,
            'target_id' => $target?->getKey(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'before' => $before,
            'after' => $after,
            'metadata' => $metadata,
        ]);
    }
}
