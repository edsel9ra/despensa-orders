<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_recent_orders_include_the_name_of_the_user_who_created_the_order(): void
    {
        $user = User::factory()->create(['name' => 'Ana Perez']);

        Order::create([
            'user_id' => $user->id,
            'remision' => 'R-100',
            'sede' => 'Principal',
            'fecha' => '2026-06-26',
            'subtotal' => 10000,
            'iva' => 1900,
            'total' => 11900,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('stats.recent_orders.0.user_name', 'Ana Perez')
            );
    }
}
