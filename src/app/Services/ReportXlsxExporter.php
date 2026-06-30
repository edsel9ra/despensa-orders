<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ReportXlsxExporter
{
    public function export(array $report, array $filters, array $filterLabels): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('REPORTE');

        foreach (['A' => 16, 'B' => 18, 'C' => 34, 'D' => 16, 'E' => 14, 'F' => 16, 'G' => 16, 'H' => 16] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $row = 1;
        $sheet->setCellValue('A'.$row, 'Reporte de pedidos');
        $sheet->mergeCells('A'.$row.':H'.$row);
        $sheet->getStyle('A'.$row)->getFont()->setBold(true)->setSize(16);
        $row += 2;

        $sheet->setCellValue('A'.$row, 'Fecha inicio');
        $sheet->setCellValue('B'.$row, $filterLabels['fecha_inicio']);
        $sheet->setCellValue('D'.$row, 'Fecha fin');
        $sheet->setCellValue('E'.$row, $filterLabels['fecha_fin']);
        $row++;
        $sheet->setCellValue('A'.$row, 'Sede');
        $sheet->setCellValue('B'.$row, $filterLabels['sede']);
        $sheet->setCellValue('D'.$row, 'Grupo');
        $sheet->setCellValue('E'.$row, $filterLabels['category_name']);
        $row++;
        $sheet->setCellValue('A'.$row, 'Productos');
        $sheet->setCellValue('B'.$row, $filterLabels['items_summary']);
        $sheet->mergeCells('B'.$row.':H'.$row);
        $sheet->getStyle('A3:A5')->getFont()->setBold(true);
        $sheet->getStyle('D3:D4')->getFont()->setBold(true);
        $row += 2;

        $row = $this->writeSummary($sheet, $row, $report['summary']);
        $row += 2;
        $row = $this->writeOrders($sheet, $row, $report['orders']);
        $row += 2;
        $this->writeItems($sheet, $row, $report['items']);

        $tempFile = tempnam(sys_get_temp_dir(), 'reporte_pedidos_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        $response = new BinaryFileResponse($tempFile);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $this->filename($filters).'.xlsx'
        );
        $response->deleteFileAfterSend(true);

        return $response;
    }

    private function writeSummary(Worksheet $sheet, int $row, array $summary): int
    {
        $sheet->setCellValue('A'.$row, 'Resumen');
        $sheet->mergeCells('A'.$row.':H'.$row);
        $sheet->getStyle('A'.$row)->getFont()->setBold(true)->setSize(13);
        $row++;

        $data = [
            ['Pedidos incluidos', $summary['orders_count'], 'Productos incluidos', $summary['items_count']],
            ['Lineas incluidas', $summary['lines_count'], 'Cantidad', $summary['quantity']],
            ['Subtotal', $summary['subtotal'], 'IVA', $summary['iva']],
            ['Total', $summary['total'], '', ''],
        ];

        foreach ($data as $entry) {
            $sheet->fromArray($entry, null, 'A'.$row);
            $row++;
        }

        $start = $row - count($data);
        $sheet->getStyle('A'.$start.':A'.($row - 1))->getFont()->setBold(true);
        $sheet->getStyle('C'.$start.':C'.($row - 1))->getFont()->setBold(true);
        $sheet->getStyle('B'.$start.':B'.($row - 1))->getNumberFormat()->setFormatCode('#,##0.##');
        $sheet->getStyle('D'.$start.':D'.($row - 1))->getNumberFormat()->setFormatCode('#,##0.##');
        $sheet->getStyle('A'.$start.':D'.($row - 1))->applyFromArray($this->borderStyle());

        return $row;
    }

    private function writeOrders(Worksheet $sheet, int $row, array $orders): int
    {
        $sheet->setCellValue('A'.$row, 'Detalle por pedido');
        $sheet->mergeCells('A'.$row.':H'.$row);
        $sheet->getStyle('A'.$row)->getFont()->setBold(true)->setSize(13);
        $row++;

        $headers = ['Remision', 'Fecha', 'Realizado por', 'Productos', 'Cantidad', 'Subtotal', 'IVA', 'Total'];
        $headerRow = $row;
        $sheet->fromArray($headers, null, 'A'.$row);
        $this->styleTableHeader($sheet, $headerRow, 'H');
        $row++;

        foreach ($orders as $order) {
            $sheet->fromArray([
                $order['remision'],
                $order['fecha'],
                $order['user_name'],
                $order['items_count'],
                $order['quantity'],
                $order['subtotal'],
                $order['iva'],
                $order['total'],
            ], null, 'A'.$row);
            $row++;
        }

        $this->styleTableBody($sheet, $headerRow, max($row - 1, $headerRow), 'H');

        return $row;
    }

    private function writeItems(Worksheet $sheet, int $row, array $items): int
    {
        $sheet->setCellValue('A'.$row, 'Detalle por producto');
        $sheet->mergeCells('A'.$row.':H'.$row);
        $sheet->getStyle('A'.$row)->getFont()->setBold(true)->setSize(13);
        $row++;

        $headers = ['Codigo', 'Producto', 'Grupo', 'Pedidos', 'Cantidad', 'Subtotal', 'IVA', 'Total'];
        $headerRow = $row;
        $sheet->fromArray($headers, null, 'A'.$row);
        $this->styleTableHeader($sheet, $headerRow, 'H');
        $row++;

        foreach ($items as $item) {
            $sheet->fromArray([
                $item['codigo_item'],
                $item['descripcion'],
                $item['category_name'],
                $item['orders_count'],
                $item['quantity'],
                $item['subtotal'],
                $item['iva'],
                $item['total'],
            ], null, 'A'.$row);
            $row++;
        }

        $this->styleTableBody($sheet, $headerRow, max($row - 1, $headerRow), 'H');

        return $row;
    }

    private function styleTableHeader(Worksheet $sheet, int $row, string $lastColumn): void
    {
        $sheet->getStyle('A'.$row.':'.$lastColumn.$row)->getFont()->setBold(true);
        $sheet->getStyle('A'.$row.':'.$lastColumn.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    private function styleTableBody(Worksheet $sheet, int $headerRow, int $lastRow, string $lastColumn): void
    {
        $sheet->getStyle('A'.$headerRow.':'.$lastColumn.$lastRow)->applyFromArray($this->borderStyle());

        if ($lastRow > $headerRow) {
            $sheet->getStyle('D'.($headerRow + 1).':'.$lastColumn.$lastRow)->getNumberFormat()->setFormatCode('#,##0.##');
        }
    }

    private function borderStyle(): array
    {
        return [
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ];
    }

    private function filename(array $filters): string
    {
        $sede = preg_replace('/[^A-Za-z0-9_-]+/', '_', $filters['sede']);

        return 'REPORTE_PEDIDOS_'.$sede.'_'.str_replace('-', '', $filters['fecha_inicio']).'_'.str_replace('-', '', $filters['fecha_fin']);
    }
}
