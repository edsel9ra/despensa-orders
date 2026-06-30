<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\OrderItem;
use App\Services\ReportPdfExporter;
use App\Services\ReportXlsxExporter;
use App\Services\SedeCatalog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index(Request $request, SedeCatalog $sedes)
    {
        $filters = $this->validatedFilters($request);

        $hasRequiredFilters = $filters['fecha_inicio'] && $filters['fecha_fin'] && $filters['sede'];

        return Inertia::render('Reports/Index', [
            'filters' => $filters,
            'sedes' => $sedes->names(),
            'categories' => Category::query()->orderBy('orden')->get(['id', 'nombre']),
            'items' => Item::query()
                ->with('category:id,nombre')
                ->orderBy('codigo_item')
                ->get(['id', 'codigo_item', 'descripcion', 'categoria_id']),
            'report' => $hasRequiredFilters ? $this->buildOrdersReport($filters) : null,
        ]);
    }

    public function exportXlsx(Request $request, ReportXlsxExporter $exporter)
    {
        $filters = $this->validatedFilters($request, requireBaseFilters: true);

        return $exporter->export(
            $this->buildOrdersReport($filters),
            $filters,
            $this->filterLabels($filters),
        );
    }

    public function exportPdf(Request $request, ReportPdfExporter $exporter)
    {
        $filters = $this->validatedFilters($request, requireBaseFilters: true);

        return $exporter->export(
            $this->buildOrdersReport($filters),
            $filters,
            $this->filterLabels($filters),
        );
    }

    private function validatedFilters(Request $request, bool $requireBaseFilters = false): array
    {
        $required = $requireBaseFilters ? 'required' : 'nullable';

        $validated = $request->validate([
            'fecha_inicio' => [$required, 'date'],
            'fecha_fin' => [$required, 'date', 'after_or_equal:fecha_inicio'],
            'sede' => [$required, 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'item_ids' => ['nullable', 'array'],
            'item_ids.*' => ['integer', 'exists:items,id'],
        ]);

        $itemIds = collect($validated['item_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        return [
            'fecha_inicio' => $validated['fecha_inicio'] ?? '',
            'fecha_fin' => $validated['fecha_fin'] ?? '',
            'sede' => $validated['sede'] ?? '',
            'category_id' => $validated['category_id'] ?? '',
            'item_ids' => $itemIds,
        ];
    }

    private function filterLabels(array $filters): array
    {
        $categoryName = $filters['category_id']
            ? Category::query()->whereKey($filters['category_id'])->value('nombre')
            : 'Todos los grupos';

        $itemNames = $filters['item_ids']
            ? Item::query()
                ->whereKey($filters['item_ids'])
                ->orderBy('codigo_item')
                ->get(['codigo_item', 'descripcion'])
                ->map(fn (Item $item) => $item->codigo_item.' - '.$item->descripcion)
                ->all()
            : [];

        return [
            'fecha_inicio' => $filters['fecha_inicio'],
            'fecha_fin' => $filters['fecha_fin'],
            'sede' => $filters['sede'],
            'category_name' => $categoryName ?: 'Todos los grupos',
            'items_summary' => $itemNames ? implode(', ', $itemNames) : 'Todos los productos',
        ];
    }

    private function buildOrdersReport(array $filters): array
    {
        $lines = OrderItem::query()
            ->with(['order.user:id,name', 'item.category:id,nombre,aplica_iva'])
            ->whereHas('order', function ($query) use ($filters) {
                $query->whereBetween('fecha', [$filters['fecha_inicio'], $filters['fecha_fin']])
                    ->where('sede', $filters['sede']);
            })
            ->when($filters['category_id'], function ($query, $categoryId) {
                $query->whereHas('item', fn ($itemQuery) => $itemQuery->where('categoria_id', $categoryId));
            })
            ->when($filters['item_ids'], fn ($query, $itemIds) => $query->whereIn('item_id', $itemIds))
            ->get();

        $rows = $lines->map(function (OrderItem $line) {
            $subtotal = (float) $line->total;
            $iva = $line->item->category?->aplica_iva ? $subtotal * 0.19 : 0;

            return [
                'line' => $line,
                'subtotal' => $subtotal,
                'iva' => $iva,
                'total' => $subtotal + $iva,
            ];
        });

        return [
            'summary' => [
                'orders_count' => $lines->pluck('order_id')->unique()->count(),
                'items_count' => $lines->pluck('item_id')->unique()->count(),
                'lines_count' => $lines->count(),
                'quantity' => round((float) $lines->sum('cantidad'), 2),
                'subtotal' => round((float) $rows->sum('subtotal'), 2),
                'iva' => round((float) $rows->sum('iva'), 2),
                'total' => round((float) $rows->sum('total'), 2),
            ],
            'orders' => $this->orderBreakdown($rows),
            'items' => $this->itemBreakdown($rows),
        ];
    }

    private function orderBreakdown($rows): array
    {
        return $rows->groupBy(fn ($row) => $row['line']->order_id)
            ->map(function ($entries) {
                $order = $entries->first()['line']->order;

                return [
                    'id' => $order->id,
                    'remision' => $order->remision,
                    'sede' => $order->sede,
                    'fecha' => $order->fecha->format('d/m/Y'),
                    'fecha_sort' => $order->fecha->format('Y-m-d'),
                    'user_name' => $order->user?->name ?? 'Sin registrar',
                    'items_count' => $entries->pluck('line.item_id')->unique()->count(),
                    'quantity' => round((float) $entries->sum(fn ($row) => $row['line']->cantidad), 2),
                    'subtotal' => round((float) $entries->sum('subtotal'), 2),
                    'iva' => round((float) $entries->sum('iva'), 2),
                    'total' => round((float) $entries->sum('total'), 2),
                ];
            })
            ->sortBy('fecha_sort')
            ->values()
            ->map(function ($order) {
                unset($order['fecha_sort']);

                return $order;
            })
            ->all();
    }

    private function itemBreakdown($rows): array
    {
        return $rows->groupBy(fn ($row) => $row['line']->item_id)
            ->map(function ($entries) {
                $item = $entries->first()['line']->item;

                return [
                    'id' => $item->id,
                    'codigo_item' => $item->codigo_item,
                    'descripcion' => $item->descripcion,
                    'category_name' => $item->category?->nombre ?? 'Sin categoría',
                    'orders_count' => $entries->pluck('line.order_id')->unique()->count(),
                    'quantity' => round((float) $entries->sum(fn ($row) => $row['line']->cantidad), 2),
                    'subtotal' => round((float) $entries->sum('subtotal'), 2),
                    'iva' => round((float) $entries->sum('iva'), 2),
                    'total' => round((float) $entries->sum('total'), 2),
                ];
            })
            ->sortBy('codigo_item')
            ->values()
            ->all();
    }
}
