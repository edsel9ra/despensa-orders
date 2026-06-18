<?php
namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfExporter
{
    public function export(Order $order)
    {
        $order->load('orderItems.item.category');

        $pdf = Pdf::loadView('pdf.pedido', [
            'order' => $order,
            'grouped' => $order->orderItems->groupBy(fn($oi) => $oi->item->category->id),
        ]);

        $pdf->setPaper('letter', 'landscape');

        return $pdf->download('PEDIDO_' . $order->remision . '_' . $order->fecha->format('Ymd') . '.pdf');
    }
}
