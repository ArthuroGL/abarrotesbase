<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class AuditService
{
    public function __construct(
        private readonly CurrentContext $context,
    ) {
    }

    public function record(
        string $action,
        ?string $auditableType = null,
        ?string $auditableId = null,
        ?array $before = null,
        ?array $after = null,
        ?string $branchId = null,
    ): void {
        $request = request();
        $user = $request->user();

        $organizationId = $this->context->organizationId();

        $resolvedBranchId = $branchId;

        if ($resolvedBranchId === null) {
            $resolvedBranchId = session('branch_id');
        }

        DB::table('audit_logs')->insert([
            'id' => (string) Str::uuid(),

            'organization_id' => $organizationId,

            'branch_id' => $resolvedBranchId,

            'actor_id' => $user?->id,

            'action' => $action,

            'auditable_type' => $auditableType,

            'auditable_id' => $auditableId,

            'before_values' => $before !== null
                ? json_encode(
                    $before,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
                )
                : null,

            'after_values' => $after !== null
                ? json_encode(
                    $after,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
                )
                : null,

            'request_id' => $this->requestId($request),

            'ip_address' => $request->ip(),

            'user_agent' => $request->userAgent(),

            'occurred_at' => now(),
        ]);
    }

    private function requestId(Request $request): string
    {
        $header = $request->header('X-Request-ID');

        if ($header && is_string($header)) {
            return $header;
        }

        return (string) Str::uuid();
    }
}
