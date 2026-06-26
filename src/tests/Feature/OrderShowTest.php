<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OrderShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_detail_includes_the_name_of_the_user_who_created_the_order(): void
    {
        $user = User::factory()->create(['name' => 'Carlos Ruiz']);

        $order = Order::create([
            'user_id' => $user->id,
            'remision' => 'R-200',
            'sede' => 'Principal',
            'fecha' => '2026-06-26',
            'subtotal' => 10000,
            'iva' => 1900,
            'total' => 11900,
        ]);

        $this->actingAs($user)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Orders/Show')
                ->where('order.user.name', 'Carlos Ruiz')
            );
    }
}
