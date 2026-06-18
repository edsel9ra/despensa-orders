<?php
namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class ExcelParser
{
    public function parse(UploadedFile $file): Collection
    {
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->getRowIterator();
        $results = collect();
        $headerRow = null;

        foreach ($rows as $row) {
            $rowIndex = $row->getRowIndex();
            $cells = $sheet->getRowIterator($rowIndex, $rowIndex)->current()->getCellIterator();
            $cells->setIterateOnlyExistingCells(false);
            $values = [];
            foreach ($cells as $cell) {
                $values[] = trim((string) $cell->getValue());
            }

            // Find header row containing "Item" and "Cantidad"
            if ($headerRow === null) {
                foreach ($values as $v) {
                    if (strcasecmp($v, 'Item') === 0) {
                        $headerRow = $values;
                        break;
                    }
                }
                continue;
            }

            // Extract item code from column A (first column)
            $itemCode = $values[0] ?? '';
            if (!is_numeric($itemCode) || empty($itemCode)) {
                continue;
            }

            // Find Cantidad column index (column F = index 5)
            $cantidadRaw = $values[5] ?? '0';
            $cantidadRaw = str_replace(',', '.', $cantidadRaw);
            $cantidad = (float) preg_replace('/[^0-9.]/', '', $cantidadRaw);

            if ($cantidad <= 0) {
                continue;
            }

            $results->push([
                'codigo_item' => (int) $itemCode,
                'cantidad' => $cantidad,
            ]);
        }

        return $results;
    }
}
