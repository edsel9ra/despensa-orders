<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OrderIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_all_orders(): void
    {
        $user = User::factory()->create();

        Order::create([
            'user_id' => $user->id,
            'remision' => 'R-301',
            'sede' => 'Norte',
            'fecha' => '2026-06-10',
            'subtotal' => 100,
            'iva' => 19,
            'total' => 119,
        ]);
        Order::create([
            'user_id' => $user->id,
            'remision' => 'R-302',
            'sede' => 'Sur',
            'fecha' => '2026-06-20',
            'subtotal' => 200,
            'iva' => 38,
            'total' => 238,
        ]);

        $this->actingAs($user)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Orders/Index')
                ->where('orders.total', 2)
                ->where('canDeleteOrders', true)
            );
    }

    public function test_order_pagination_uses_forwarded_https_scheme(): void
    {
        $user = User::factory()->create();

        for ($i = 1; $i <= 16; $i++) {
            Order::create([
                'user_id' => $user->id,
                'remision' => 'R-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'sede' => 'Norte',
                'fecha' => '2026-06-10',
                'subtotal' => 100,
                'iva' => 19,
                'total' => 119,
            ]);
        }

        $this->actingAs($user)
            ->withServerVariables([
                'HTTP_HOST' => 'pedidos-despensa.misterwings.com',
                'REMOTE_ADDR' => '10.0.0.10',
                'SERVER_PORT' => '80',
                'HTTP_X_FORWARDED_HOST' => 'pedidos-despensa.misterwings.com',
                'HTTP_X_FORWARDED_PORT' => '443',
                'HTTP_X_FORWARDED_PROTO' => 'https',
            ])
            ->get('/orders')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Orders/Index')
                ->where('orders.links.2.url', 'https://pedidos-despensa.misterwings.com/orders?page=2')
            );
    }

    public function test_user_with_id_three_can_view_orders_without_delete_permission(): void
    {
        $user = User::factory()->create(['id' => 3]);

        $this->actingAs($user)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Orders/Index')
                ->where('canDeleteOrders', false)
            );
    }

    public function test_authenticated_user_can_delete_order(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'remision' => 'R-303',
            'sede' => 'Norte',
            'fecha' => '2026-06-10',
            'subtotal' => 100,
            'iva' => 19,
            'total' => 119,
        ]);

        $this->actingAs($user)
            ->delete(route('orders.destroy', $order))
            ->assertRedirect(route('orders.index'));

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function test_user_with_id_three_cannot_delete_orders(): void
    {
        $user = User::factory()->create(['id' => 3]);
        $order = Order::create([
            'user_id' => $user->id,
            'remision' => 'R-304',
            'sede' => 'Norte',
            'fecha' => '2026-06-10',
            'subtotal' => 100,
            'iva' => 19,
            'total' => 119,
        ]);

        $this->actingAs($user)
            ->delete(route('orders.destroy', $order))
            ->assertForbidden();

        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }
}
