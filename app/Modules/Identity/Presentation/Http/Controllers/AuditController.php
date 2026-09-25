<?php

declare(strict_types=1);

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Application\Services\CurrentContext;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

final class AuditController extends Controller
{
    public function __construct(
        private readonly CurrentContext $context,
    ) {}

    public function index(Request $request): View
    {
        $organizationId = $this->context->organizationId();

        $action = trim((string) $request->input('action'));

        $logs = DB::table('audit_logs as a')
            ->leftJoin(
                'users as u',
                'u.id',
                '=',
                'a.actor_id'
            )
            ->where('a.organization_id', $organizationId)
            ->when(
                $action !== '',
                fn($query) => $query->where('a.action', $action)
            )
            ->select([
                'a.id',
                'a.action',
                'a.auditable_type',
                'a.auditable_id',
                'a.before_values as before',
                'a.after_values as after',
                'a.ip_address',
                'a.occurred_at',
                'u.name as actor_name',
                'u.email as actor_email',
            ])
            ->orderByDesc('a.occurred_at')
            ->paginate(30)
            ->withQueryString();

        $actions = DB::table('audit_logs')
            ->where('organization_id', $organizationId)
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('modules.identity.audit.index', [
            'logs' => $logs,
            'actions' => $actions,
            'action' => $action,
        ]);
    }
}
