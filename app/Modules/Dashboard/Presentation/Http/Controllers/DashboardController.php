<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Presentation\Http\Controllers;

use Illuminate\Contracts\View\View;

final class DashboardController
{
    public function __invoke(): View
    {
        return view('modules.dashboard.index', [
            'metrics' => [
                ['label' => 'Ventas de hoy', 'value' => '$0.00', 'detail' => 'Sin ventas registradas', 'tone' => 'emerald'],
                ['label' => 'Tickets', 'value' => '0', 'detail' => 'Ticket promedio $0.00', 'tone' => 'sky'],
                ['label' => 'Inventario bajo mínimo', 'value' => '0', 'detail' => 'Sin alertas pendientes', 'tone' => 'amber'],
                ['label' => 'Caja', 'value' => 'Sin abrir', 'detail' => 'Abre una sesión para vender', 'tone' => 'violet'],
            ],
        ]);
    }
}
