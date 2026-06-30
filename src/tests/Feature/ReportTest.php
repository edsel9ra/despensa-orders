<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_orders_report_calculates_totals_by_date_range_and_sede(): void
    {
        $user = User::factory()->create();
        [$itemWithIva, $itemWithoutIva] = $this->createItems();

        $firstOrder = Order::create([
            'user_id' => $user->id,
            'remision' => 'R-001',
            'sede' => 'Norte',
            'fecha' => '2026-06-10',
            'subtotal' => 150,
            'iva' => 19,
            'total' => 169,
        ]);
        OrderItem::create(['order_id' => $firstOrder->id, 'item_id' => $itemWithIva->id, 'cantidad' => 1, 'precio_unitario' => 100, 'precio_presentacion' => 100, 'total' => 100]);
        OrderItem::create(['order_id' => $firstOrder->id, 'item_id' => $itemWithoutIva->id, 'cantidad' => 1, 'precio_unitario' => 50, 'precio_presentacion' => 50, 'total' => 50]);

        $secondOrder = Order::create([
            'user_id' => $user->id,
            'remision' => 'R-002',
            'sede' => 'Norte',
            'fecha' => '2026-06-20',
            'subtotal' => 200,
            'iva' => 38,
            'total' => 238,
        ]);
        OrderItem::create(['order_id' => $secondOrder->id, 'item_id' => $itemWithIva->id, 'cantidad' => 2, 'precio_unitario' => 100, 'precio_presentacion' => 100, 'total' => 200]);

        $otherSedeOrder = Order::create([
            'user_id' => $user->id,
            'remision' => 'R-003',
            'sede' => 'Sur',
            'fecha' => '2026-06-15',
            'subtotal' => 500,
            'iva' => 95,
            'total' => 595,
        ]);
        OrderItem::create(['order_id' => $otherSedeOrder->id, 'item_id' => $itemWithIva->id, 'cantidad' => 5, 'precio_unitario' => 100, 'precio_presentacion' => 100, 'total' => 500]);

        $this->actingAs($user)
            ->get(route('reports.index', [
                'fecha_inicio' => '2026-06-01',
                'fecha_fin' => '2026-06-30',
                'sede' => 'Norte',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Reports/Index')
                ->where('report.summary.orders_count', 2)
                ->where('report.summary.subtotal', 350)
                ->where('report.summary.iva', 57)
                ->where('report.summary.total', 407)
            );
    }

    public function test_orders_report_can_be_filtered_by_specific_items(): void
    {
        $user = User::factory()->create();
        [$itemWithIva, $itemWithoutIva] = $this->createItems();

        $order = Order::create([
            'user_id' => $user->id,
            'remision' => 'R-004',
            'sede' => 'Norte',
            'fecha' => '2026-06-10',
            'subtotal' => 150,
            'iva' => 19,
            'total' => 169,
        ]);
        OrderItem::create(['order_id' => $order->id, 'item_id' => $itemWithIva->id, 'cantidad' => 1, 'precio_unitario' => 100, 'precio_presentacion' => 100, 'total' => 100]);
        OrderItem::create(['order_id' => $order->id, 'item_id' => $itemWithoutIva->id, 'cantidad' => 1, 'precio_unitario' => 50, 'precio_presentacion' => 50, 'total' => 50]);

        $this->actingAs($user)
            ->get(route('reports.index', [
                'fecha_inicio' => '2026-06-01',
                'fecha_fin' => '2026-06-30',
                'sede' => 'Norte',
                'item_ids' => [$itemWithoutIva->id],
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Reports/Index')
                ->where('report.summary.orders_count', 1)
                ->where('report.summary.items_count', 1)
                ->where('report.summary.subtotal', 50)
                ->where('report.summary.iva', 0)
                ->where('report.summary.total', 50)
            );
    }

    public function test_orders_report_can_be_filtered_by_category_group(): void
    {
        $user = User::factory()->create();
        [$itemWithIva, $itemWithoutIva] = $this->createItems();

        $order = Order::create([
            'user_id' => $user->id,
            'remision' => 'R-005',
            'sede' => 'Norte',
            'fecha' => '2026-06-10',
            'subtotal' => 150,
            'iva' => 19,
            'total' => 169,
        ]);
        OrderItem::create(['order_id' => $order->id, 'item_id' => $itemWithIva->id, 'cantidad' => 1, 'precio_unitario' => 100, 'precio_presentacion' => 100, 'total' => 100]);
        OrderItem::create(['order_id' => $order->id, 'item_id' => $itemWithoutIva->id, 'cantidad' => 1, 'precio_unitario' => 50, 'precio_presentacion' => 50, 'total' => 50]);

        $this->actingAs($user)
            ->get(route('reports.index', [
                'fecha_inicio' => '2026-06-01',
                'fecha_fin' => '2026-06-30',
                'sede' => 'Norte',
                'category_id' => $itemWithIva->categoria_id,
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Reports/Index')
                ->where('report.summary.orders_count', 1)
                ->where('report.summary.items_count', 1)
                ->where('report.summary.subtotal', 100)
                ->where('report.summary.iva', 19)
                ->where('report.summary.total', 119)
            );
    }

    public function test_orders_report_can_be_exported_as_xlsx(): void
    {
        $user = User::factory()->create();
        [$itemWithIva] = $this->createItems();

        $order = Order::create([
            'user_id' => $user->id,
            'remision' => 'R-006',
            'sede' => 'Norte',
            'fecha' => '2026-06-10',
            'subtotal' => 100,
            'iva' => 19,
            'total' => 119,
        ]);
        OrderItem::create(['order_id' => $order->id, 'item_id' => $itemWithIva->id, 'cantidad' => 1, 'precio_unitario' => 100, 'precio_presentacion' => 100, 'total' => 100]);

        $this->actingAs($user)
            ->get(route('reports.export-xlsx', [
                'fecha_inicio' => '2026-06-01',
                'fecha_fin' => '2026-06-30',
                'sede' => 'Norte',
                'category_id' => $itemWithIva->categoria_id,
                'item_ids' => [$itemWithIva->id],
            ]))
            ->assertOk()
            ->assertDownload('REPORTE_PEDIDOS_Norte_20260601_20260630.xlsx');
    }

    public function test_orders_report_can_be_exported_as_pdf(): void
    {
        $user = User::factory()->create();
        [$itemWithIva] = $this->createItems();

        $order = Order::create([
            'user_id' => $user->id,
            'remision' => 'R-007',
            'sede' => 'Norte',
            'fecha' => '2026-06-10',
            'subtotal' => 100,
            'iva' => 19,
            'total' => 119,
        ]);
        OrderItem::create(['order_id' => $order->id, 'item_id' => $itemWithIva->id, 'cantidad' => 1, 'precio_unitario' => 100, 'precio_presentacion' => 100, 'total' => 100]);

        $this->actingAs($user)
            ->get(route('reports.export-pdf', [
                'fecha_inicio' => '2026-06-01',
                'fecha_fin' => '2026-06-30',
                'sede' => 'Norte',
            ]))
            ->assertOk()
            ->assertDownload('REPORTE_PEDIDOS_Norte_20260601_20260630.pdf');
    }

    private function createItems(): array
    {
        $categoryWithIva = Category::create(['nombre' => 'CON IVA', 'orden' => 1, 'aplica_iva' => true]);
        $categoryWithoutIva = Category::create(['nombre' => 'SIN IVA', 'orden' => 2, 'aplica_iva' => false]);

        return [
            Item::create(['codigo_item' => 'I-001', 'descripcion' => 'Producto con IVA', 'precio_unidad' => 100, 'presentacion' => 'Caja', 'precio_presentacion' => 100, 'categoria_id' => $categoryWithIva->id]),
            Item::create(['codigo_item' => 'I-002', 'descripcion' => 'Producto sin IVA', 'precio_unidad' => 50, 'presentacion' => 'Bolsa', 'precio_presentacion' => 50, 'categoria_id' => $categoryWithoutIva->id]),
        ];
    }
}
