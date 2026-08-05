<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class DashboardTest extends TestCase
{
    public function test_the_dashboard_is_available(): void
    {
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('ABARROTESBASE');
    }
}
