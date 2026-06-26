<?php

namespace Tests\Unit;

use App\Services\ExcelParser;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPUnit\Framework\TestCase;

class ExcelParserTest extends TestCase
{
    public function test_it_parses_an_xlsx_file_without_operation_center_column(): void
    {
        [$file, $path] = $this->makeSpreadsheet([
            ['Item', 'Producto', '', '', '', 'Cantidad'],
            ['1001', 'Producto de prueba', '', '', '', 2],
        ]);

        try {
            $rows = (new ExcelParser())->parse($file);

            $this->assertCount(1, $rows);
            $this->assertSame('1001', $rows->first()['codigo_item']);
            $this->assertSame(2.0, $rows->first()['cantidad']);
            $this->assertArrayNotHasKey('co', $rows->first());
        } finally {
            @unlink($path);
        }
    }

    public function test_it_parses_an_xls_file_with_operation_center_column(): void
    {
        [$file, $path] = $this->makeSpreadsheet([
            ['C.O.', 'Item', 'Producto', '', '', '', 'Cantidad'],
            ['002', '1001', 'Producto de prueba', '', '', '', 3],
        ], 'xls');

        try {
            $rows = (new ExcelParser())->parse($file);

            $this->assertCount(1, $rows);
            $this->assertSame('1001', $rows->first()['codigo_item']);
            $this->assertSame(3.0, $rows->first()['cantidad']);
            $this->assertSame('002', $rows->first()['co']);
        } finally {
            @unlink($path);
        }
    }

    private function makeSpreadsheet(array $rows, string $extension = 'xlsx'): array
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($rows as $rowIndex => $row) {
            foreach ($row as $columnIndex => $value) {
                $coordinate = Coordinate::stringFromColumnIndex($columnIndex + 1) . ($rowIndex + 1);

                if (is_string($value) && str_starts_with($value, '0')) {
                    $sheet->setCellValueExplicit($coordinate, $value, DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue($coordinate, $value);
                }
            }
        }

        $basePath = tempnam(sys_get_temp_dir(), 'orders_parser_');
        @unlink($basePath);
        $path = $basePath . '.' . $extension;
        $writer = $extension === 'xls' ? new Xls($spreadsheet) : new Xlsx($spreadsheet);
        $writer->save($path);

        return [
            new UploadedFile($path, 'pedido.' . $extension, null, null, true),
            $path,
        ];
    }
}
