<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_id_three_can_access_dashboard_and_new_order(): void
    {
        $user = User::factory()->create(['id' => 3]);

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
        $this->actingAs($user)->get(route('orders.create'))->assertOk();
    }

    public function test_user_with_id_three_cannot_access_or_modify_categories(): void
    {
        $user = User::factory()->create(['id' => 3]);
        $category = Category::create(['nombre' => 'Granos', 'orden' => 1, 'aplica_iva' => false]);

        $this->actingAs($user)->get(route('categories.index'))->assertForbidden();
        $this->actingAs($user)->get(route('categories.create'))->assertForbidden();
        $this->actingAs($user)->post(route('categories.store'), [
            'nombre' => 'Abarrotes',
            'orden' => 2,
            'aplica_iva' => true,
        ])->assertForbidden();
        $this->actingAs($user)->get(route('categories.edit', $category))->assertForbidden();
        $this->actingAs($user)->put(route('categories.update', $category), [
            'nombre' => 'Granos actualizados',
            'orden' => 3,
            'aplica_iva' => true,
        ])->assertForbidden();
        $this->actingAs($user)->delete(route('categories.destroy', $category))->assertForbidden();
    }

    public function test_user_with_id_three_cannot_access_or_modify_items(): void
    {
        $user = User::factory()->create(['id' => 3]);
        $category = Category::create(['nombre' => 'Granos', 'orden' => 1, 'aplica_iva' => false]);
        $item = Item::create([
            'codigo_item' => 'P-001',
            'descripcion' => 'Arroz',
            'precio_unidad' => 1000,
            'presentacion' => 'Bolsa',
            'precio_presentacion' => 1000,
            'categoria_id' => $category->id,
        ]);

        $this->actingAs($user)->get(route('items.index'))->assertForbidden();
        $this->actingAs($user)->get(route('items.create'))->assertForbidden();
        $this->actingAs($user)->post(route('items.store'), [
            'codigo_item' => 'P-002',
            'descripcion' => 'Frijol',
            'precio_unidad' => 2000,
            'presentacion' => 'Bolsa',
            'precio_presentacion' => 2000,
            'categoria_id' => $category->id,
        ])->assertForbidden();
        $this->actingAs($user)->get(route('items.edit', $item))->assertForbidden();
        $this->actingAs($user)->put(route('items.update', $item), [
            'codigo_item' => 'P-001',
            'descripcion' => 'Arroz actualizado',
            'precio_unidad' => 1500,
            'presentacion' => 'Bolsa',
            'precio_presentacion' => 1500,
            'categoria_id' => $category->id,
        ])->assertForbidden();
        $this->actingAs($user)->delete(route('items.destroy', $item))->assertForbidden();
        $this->actingAs($user)->get(route('items.import.form'))->assertForbidden();
        $this->actingAs($user)->post(route('items.import'))->assertForbidden();
    }

    public function test_user_with_id_three_cannot_access_reports(): void
    {
        $user = User::factory()->create(['id' => 3]);

        $this->actingAs($user)->get(route('reports.index'))->assertForbidden();
        $this->actingAs($user)->get(route('reports.export-xlsx', [
            'fecha_inicio' => '2026-06-01',
            'fecha_fin' => '2026-06-30',
            'sede' => 'Norte',
        ]))->assertForbidden();
        $this->actingAs($user)->get(route('reports.export-pdf', [
            'fecha_inicio' => '2026-06-01',
            'fecha_fin' => '2026-06-30',
            'sede' => 'Norte',
        ]))->assertForbidden();
    }

    public function test_other_users_can_access_categories_items_and_reports(): void
    {
        $user = User::factory()->create(['id' => 4]);

        $this->actingAs($user)->get(route('categories.index'))->assertOk();
        $this->actingAs($user)->get(route('items.index'))->assertOk();
        $this->actingAs($user)->get(route('reports.index'))->assertOk();
    }
}
