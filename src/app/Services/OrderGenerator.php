<?php
namespace App\Services;

use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderGenerator
{
    public function generate(Collection $parsedItems): array
    {
        $codes = $parsedItems->pluck('codigo_item')->unique();
        $items = Item::with('category')->whereIn('codigo_item', $codes)->get()->keyBy('codigo_item');

        $notFound = $codes->diff($items->keys());
        $grouped = [];

        foreach ($parsedItems as $parsed) {
            $item = $items->get($parsed['codigo_item']);
            if (!$item) continue;

            $total = $parsed['cantidad'] * $item->precio_presentacion;
            $catId = $item->category->id;
            $catName = $item->category->nombre;

            if (!isset($grouped[$catId])) {
                $grouped[$catId] = [
                    'category_id' => $catId,
                    'category_name' => $catName,
                    'items' => [],
                    'subtotal' => 0,
                ];
            }

            $grouped[$catId]['items'][] = [
                'codigo_item' => $item->codigo_item,
                'descripcion' => $item->descripcion,
                'precio_unidad' => (float) $item->precio_unidad,
                'presentacion' => $item->presentacion,
                'precio_presentacion' => (float) $item->precio_presentacion,
                'cantidad' => $parsed['cantidad'],
                'total' => $total,
            ];
            $grouped[$catId]['subtotal'] += $total;
        }

        // Sort by category order (fetch from DB)
        $categories = \App\Models\Category::orderBy('orden')->pluck('id')->toArray();
        $sorted = [];
        foreach ($categories as $catId) {
            if (isset($grouped[$catId])) {
                $sorted[] = $grouped[$catId];
            }
        }

        $subtotalGeneral = collect($sorted)->sum('subtotal');
        $ivaCategories = \App\Models\Category::where('aplica_iva', true)->pluck('id');
        $baseIva = 0;
        foreach ($grouped as $catId => $group) {
            if ($ivaCategories->contains($catId)) {
                $baseIva += $group['subtotal'];
            }
        }
        $iva = $baseIva * 0.19;
        $totalGeneral = $subtotalGeneral + $iva;

        return [
            'grouped' => $sorted,
            'subtotal' => $subtotalGeneral,
            'iva' => $iva,
            'total' => $totalGeneral,
            'not_found' => $notFound->values(),
        ];
    }

    public function store(Collection $parsedItems, array $meta): Order
    {
        $data = $this->generate($parsedItems);

        return DB::transaction(function () use ($data, $meta) {
            $order = Order::create([
                'remision' => $meta['remision'],
                'sede' => $meta['sede'],
                'fecha' => $meta['fecha'],
                'subtotal' => $data['subtotal'],
                'iva' => $data['iva'],
                'total' => $data['total'],
            ]);

            $items = \App\Models\Item::whereIn('codigo_item', collect($data['grouped'])->pluck('items')->flatten(1)->pluck('codigo_item'))->get()->keyBy('codigo_item');

            foreach ($data['grouped'] as $group) {
                foreach ($group['items'] as $itemData) {
                    $item = $items->get($itemData['codigo_item']);
                    if (!$item) continue;
                    OrderItem::create([
                        'order_id' => $order->id,
                        'item_id' => $item->id,
                        'cantidad' => $itemData['cantidad'],
                        'precio_unitario' => $itemData['precio_unidad'],
                        'precio_presentacion' => $itemData['precio_presentacion'],
                        'total' => $itemData['total'],
                    ]);
                }
            }

            return $order;
        });
    }
}
