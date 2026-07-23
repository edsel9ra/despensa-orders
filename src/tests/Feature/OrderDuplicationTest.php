<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\Order;
use App\Models\User;
use App\Services\ExcelParser;
use App\Services\OrderGenerator;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class OrderDuplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_rejects_duplicate_remision_for_the_resolved_sede(): void
    {
        $user = User::factory()->create();
        $this->createOrder('R-001', 'UNICENTRO');

        $this->mock(ExcelParser::class, function ($mock) {
            $mock->shouldReceive('parse')
                ->once()
                ->andReturn(collect([
                    ['codigo_item' => '1001', 'cantidad' => 1, 'co' => '002'],
                ]));
        });
        $this->mock(OrderGenerator::class, function ($mock) {
            $mock->shouldNotReceive('generate');
        });

        [$file, $path] = $this->makeSpreadsheetUpload();

        try {
            $this->actingAs($user)
                ->post(route('orders.preview'), [
                    'archivo' => $file,
                    'remision' => 'R-001',
                    'sede' => 'CIUDAD JARDIN',
                    'fecha' => '2026-07-23',
                ])
                ->assertSessionHasErrors([
                    'remision' => 'Ya existe una orden de compra con la remisión R-001 para la sede UNICENTRO. Revisa la orden existente antes de crear una nueva.',
                ]);
        } finally {
            @unlink($path);
        }
    }

    public function test_store_rejects_duplicate_remision_for_the_same_sede(): void
    {
        $user = User::factory()->create();
        $this->createOrder('R-002', 'FLORA');

        $this->mock(ExcelParser::class, function ($mock) {
            $mock->shouldReceive('parse')
                ->once()
                ->andReturn(collect([
                    ['codigo_item' => '1001', 'cantidad' => 1],
                ]));
        });
        $this->mock(OrderGenerator::class, function ($mock) {
            $mock->shouldNotReceive('store');
        });

        [$file, $path] = $this->makeSpreadsheetUpload();

        try {
            $this->actingAs($user)
                ->post(route('orders.store'), [
                    'archivo' => $file,
                    'remision' => 'R-002',
                    'sede' => 'FLORA',
                    'fecha' => '2026-07-23',
                ])
                ->assertSessionHasErrors('remision');

            $this->assertDatabaseCount('orders', 1);
        } finally {
            @unlink($path);
        }
    }

    public function test_store_allows_the_same_remision_for_a_different_sede(): void
    {
        $user = User::factory()->create();
        $this->createCatalogItem('1001');
        $this->createOrder('R-003', 'FLORA');

        $this->mock(ExcelParser::class, function ($mock) {
            $mock->shouldReceive('parse')
                ->once()
                ->andReturn(collect([
                    ['codigo_item' => '1001', 'cantidad' => 2],
                ]));
        });

        [$file, $path] = $this->makeSpreadsheetUpload();

        try {
            $this->actingAs($user)
                ->post(route('orders.store'), [
                    'archivo' => $file,
                    'remision' => 'R-003',
                    'sede' => 'CHIPICHAPE',
                    'fecha' => '2026-07-23',
                ])
                ->assertRedirect();

            $this->assertDatabaseHas('orders', [
                'remision' => 'R-003',
                'sede' => 'CHIPICHAPE',
            ]);
            $this->assertDatabaseCount('orders', 2);
        } finally {
            @unlink($path);
        }
    }

    public function test_database_enforces_unique_remision_for_the_same_sede(): void
    {
        $this->createOrder('R-004', 'FLORA');

        $this->expectException(QueryException::class);

        $this->createOrder('R-004', 'FLORA');
    }

    private function createOrder(string $remision, string $sede): Order
    {
        return Order::create([
            'remision' => $remision,
            'sede' => $sede,
            'fecha' => '2026-07-23',
            'subtotal' => 0,
            'iva' => 0,
            'total' => 0,
        ]);
    }

    private function createCatalogItem(string $codigoItem): Item
    {
        $category = Category::create([
            'nombre' => 'Granos',
            'orden' => 1,
            'aplica_iva' => false,
        ]);

        return Item::create([
            'codigo_item' => $codigoItem,
            'descripcion' => 'Arroz',
            'precio_unidad' => 1000,
            'presentacion' => 'Bolsa',
            'precio_presentacion' => 1000,
            'categoria_id' => $category->id,
        ]);
    }

    private function makeSpreadsheetUpload(): array
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Item');
        $sheet->setCellValue('F1', 'Cantidad');
        $sheet->setCellValue('A2', '1001');
        $sheet->setCellValue('F2', 1);

        $basePath = tempnam(sys_get_temp_dir(), 'order_duplicate_');
        @unlink($basePath);
        $path = $basePath.'.xlsx';

        (new Xlsx($spreadsheet))->save($path);

        return [
            new UploadedFile($path, 'pedido.xlsx', null, null, true),
            $path,
        ];
    }
}
