<?php

namespace Tests\Unit;

use App\Services\SedeCatalog;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class SedeCatalogTest extends TestCase
{
    public function test_it_resolves_the_sede_from_operation_center_when_selected_sede_uses_co(): void
    {
        $result = (new SedeCatalog())->resolveForParsedItems(new Collection([
            ['codigo_item' => '1001', 'cantidad' => 1, 'co' => '2'],
        ]), 'CIUDAD JARDIN');

        $this->assertSame('UNICENTRO', $result['sede']);
        $this->assertSame('002', $result['operation_center']['code']);
        $this->assertTrue($result['operation_center']['applied']);
        $this->assertFalse($result['operation_center']['filtered']);
        $this->assertCount(1, $result['parsed_items']);
    }

    public function test_it_keeps_selected_sede_when_file_has_no_operation_center(): void
    {
        $result = (new SedeCatalog())->resolveForParsedItems(new Collection([
            ['codigo_item' => '1001', 'cantidad' => 1],
        ]), 'CIUDAD JARDIN');

        $this->assertSame('CIUDAD JARDIN', $result['sede']);
        $this->assertNull($result['operation_center']);
        $this->assertCount(1, $result['parsed_items']);
    }

    public function test_it_ignores_operation_center_when_selected_sede_does_not_use_co(): void
    {
        $result = (new SedeCatalog())->resolveForParsedItems(new Collection([
            ['codigo_item' => '1001', 'cantidad' => 1, 'co' => '002'],
        ]), 'BODEGA PRINCIPAL');

        $this->assertSame('BODEGA PRINCIPAL', $result['sede']);
        $this->assertNull($result['operation_center']);
        $this->assertCount(1, $result['parsed_items']);
    }

    public function test_it_filters_items_by_selected_sede_when_file_has_multiple_operation_centers(): void
    {
        $result = (new SedeCatalog())->resolveForParsedItems(new Collection([
            ['codigo_item' => '1001', 'cantidad' => 1, 'co' => '001'],
            ['codigo_item' => '1002', 'cantidad' => 2, 'co' => '002'],
            ['codigo_item' => '1003', 'cantidad' => 3, 'co' => '004'],
        ]), 'UNICENTRO');

        $this->assertSame('UNICENTRO', $result['sede']);
        $this->assertSame('002', $result['operation_center']['code']);
        $this->assertTrue($result['operation_center']['filtered']);
        $this->assertSame(3, $result['operation_center']['original_items_count']);
        $this->assertSame(1, $result['operation_center']['filtered_items_count']);
        $this->assertCount(1, $result['parsed_items']);
        $this->assertSame('1002', $result['parsed_items']->first()['codigo_item']);
    }
}
