<?php

declare(strict_types=1);

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Application\Services\CurrentContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class AuditController extends Controller
{
    public function __construct(
        private readonly CurrentContext $context,
    ) {}

    public function index(Request $request): View
    {
        $organizationId = $this->context->organizationId();

        $search = trim((string) $request->input('search'));
        $action = trim((string) $request->input('action'));

        $perPage = (int) $request->input('per_page', 10);

        $perPage = in_array($perPage, [10, 25, 50, 100], true)
            ? $perPage
            : 10;

        $logs = DB::table('audit_logs as a')
            ->leftJoin(
                'users as u',
                'u.id',
                '=',
                'a.actor_id'
            )
            ->where('a.organization_id', $organizationId)

            /*
             * BUSCADOR GENERAL
             */
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('u.name', 'ilike', "%{$search}%")
                        ->orWhere('u.email', 'ilike', "%{$search}%")
                        ->orWhere('a.action', 'ilike', "%{$search}%")
                        ->orWhere('a.auditable_type', 'ilike', "%{$search}%")
                        ->orWhere('a.auditable_id', 'ilike', "%{$search}%")
                        ->orWhere('a.ip_address', 'ilike', "%{$search}%");
                });
            })

            /*
             * FILTRO POR ACCIÓN
             */
            ->when(
                $action !== '',
                fn ($query) => $query->where('a.action', $action)
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

            ->paginate($perPage)

            ->withQueryString();

        $actions = DB::table('audit_logs')
            ->where('organization_id', $organizationId)
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('modules.identity.audit.index', [
            'logs' => $logs,
            'actions' => $actions,
            'search' => $search,
            'action' => $action,
            'perPage' => $perPage,
        ]);
    }
}
