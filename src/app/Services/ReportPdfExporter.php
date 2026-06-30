<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class ReportPdfExporter
{
    public function export(array $report, array $filters, array $filterLabels)
    {
        $pdf = Pdf::loadView('pdf.reporte-pedidos', [
            'report' => $report,
            'filters' => $filters,
            'filterLabels' => $filterLabels,
        ]);

        $pdf->setPaper('letter', 'landscape');

        return $pdf->download($this->filename($filters).'.pdf');
    }

    private function filename(array $filters): string
    {
        $sede = preg_replace('/[^A-Za-z0-9_-]+/', '_', $filters['sede']);

        return 'REPORTE_PEDIDOS_'.$sede.'_'.str_replace('-', '', $filters['fecha_inicio']).'_'.str_replace('-', '', $filters['fecha_fin']);
    }
}
