<?php
namespace App\Http\Controllers;

use App\Http\Requests\GenerateOrderRequest;
use App\Models\Item;
use App\Models\Order;
use App\Services\ExcelParser;
use App\Services\OrderGenerator;
use App\Services\XlsxExporter;
use App\Services\PdfExporter;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function create()
    {
        return Inertia::render('Orders/Create');
    }

    public function preview(GenerateOrderRequest $request, ExcelParser $parser, OrderGenerator $generator)
    {
        $parsed = $parser->parse($request->file('archivo'));
        $orderData = $generator->generate($parsed);

        return Inertia::render('Orders/Preview', [
            'orderData' => $orderData,
            'remision' => $request->remision,
            'sede' => $request->sede,
            'fecha' => $request->fecha,
            'items' => Item::with('category')->orderBy('codigo_item')->get(),
        ]);
    }

    public function store(GenerateOrderRequest $request, ExcelParser $parser, OrderGenerator $generator)
    {
        $parsed = $parser->parse($request->file('archivo'));

        if ($request->filled('manual_items')) {
            foreach ($request->manual_items as $manual) {
                $parsed->push([
                    'codigo_item' => $manual['codigo_item'],
                    'cantidad' => (int) $manual['cantidad'],
                ]);
            }
        }

        $order = $generator->store($parsed, $request->only(['remision', 'sede', 'fecha']));

        return redirect()->route('orders.show', $order)->with('success', 'Pedido creado exitosamente.');
    }

    public function show(Order $order)
    {
        $order->load('orderItems.item.category');
        return Inertia::render('Orders/Show', ['order' => $order]);
    }

    public function exportXlsx(Order $order, XlsxExporter $exporter)
    {
        return $exporter->export($order);
    }

    public function exportPdf(Order $order, PdfExporter $exporter)
    {
        return $exporter->export($order);
    }
}
