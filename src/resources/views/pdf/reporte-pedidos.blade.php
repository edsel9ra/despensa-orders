<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de pedidos</title>
    <style>
        body { font-family: sans-serif; font-size: 9pt; color: #1c1917; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #44403c; padding: 4px 6px; text-align: left; vertical-align: top; }
        th { background: #f5f5f4; font-weight: bold; }
        .header { margin-bottom: 14px; }
        .header h1 { margin: 0 0 6px; font-size: 16pt; }
        .header p { margin: 2px 0; }
        .summary td { width: 25%; }
        .section-title { margin: 14px 0 6px; font-size: 12pt; font-weight: bold; }
        .right { text-align: right; }
        .center { text-align: center; }
        .muted { color: #78716c; }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/logo_despensa.png');
        $logoBase64 = is_file($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
        $money = fn ($value) => '$ ' . number_format((float) $value, 0, ',', '.');
        $number = fn ($value) => rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',');
    @endphp

    <div class="header">
        <table style="border: none; margin-bottom: 5px;">
            <tr>
                <td style="border: none; width: 120px;">
                    @if($logoBase64)
                        <img src="data:image/png;base64,{{ $logoBase64 }}" alt="Logo" style="max-width: 100px; max-height: 80px;">
                    @endif
                </td>
                <td style="border: none;">
                    <h1>Reporte de pedidos</h1>
                    <p><strong>Fecha inicio:</strong> {{ $filterLabels['fecha_inicio'] }}</p>
                    <p><strong>Fecha fin:</strong> {{ $filterLabels['fecha_fin'] }}</p>
                    <p><strong>Sede:</strong> {{ $filterLabels['sede'] }}</p>
                    <p><strong>Grupo:</strong> {{ $filterLabels['category_name'] }}</p>
                    <p><strong>Productos:</strong> {{ $filterLabels['items_summary'] }}</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Resumen</div>
    <table class="summary">
        <tr>
            <td><strong>Pedidos incluidos</strong><br>{{ $report['summary']['orders_count'] }}</td>
            <td><strong>Productos incluidos</strong><br>{{ $report['summary']['items_count'] }}</td>
            <td><strong>Lineas incluidas</strong><br>{{ $report['summary']['lines_count'] }}</td>
            <td><strong>Cantidad</strong><br>{{ $number($report['summary']['quantity']) }}</td>
        </tr>
        <tr>
            <td><strong>Subtotal</strong><br>{{ $money($report['summary']['subtotal']) }}</td>
            <td><strong>IVA</strong><br>{{ $money($report['summary']['iva']) }}</td>
            <td colspan="2"><strong>Total</strong><br>{{ $money($report['summary']['total']) }}</td>
        </tr>
    </table>

    <div class="section-title">Detalle por pedido</div>
    <table>
        <thead>
            <tr>
                <th>Remision</th>
                <th>Fecha</th>
                <th>Realizado por</th>
                <th class="right">Productos</th>
                <th class="right">Cantidad</th>
                <th class="right">Subtotal</th>
                <th class="right">IVA</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['orders'] as $order)
                <tr>
                    <td>{{ $order['remision'] }}</td>
                    <td>{{ $order['fecha'] }}</td>
                    <td>{{ $order['user_name'] }}</td>
                    <td class="right">{{ $order['items_count'] }}</td>
                    <td class="right">{{ $number($order['quantity']) }}</td>
                    <td class="right">{{ $money($order['subtotal']) }}</td>
                    <td class="right">{{ $money($order['iva']) }}</td>
                    <td class="right">{{ $money($order['total']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="center muted">No hay pedidos para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Detalle por producto</div>
    <table>
        <thead>
            <tr>
                <th>Codigo</th>
                <th>Producto</th>
                <th>Grupo</th>
                <th class="right">Pedidos</th>
                <th class="right">Cantidad</th>
                <th class="right">Subtotal</th>
                <th class="right">IVA</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['items'] as $item)
                <tr>
                    <td>{{ $item['codigo_item'] }}</td>
                    <td>{{ $item['descripcion'] }}</td>
                    <td>{{ $item['category_name'] }}</td>
                    <td class="right">{{ $item['orders_count'] }}</td>
                    <td class="right">{{ $number($item['quantity']) }}</td>
                    <td class="right">{{ $money($item['subtotal']) }}</td>
                    <td class="right">{{ $money($item['iva']) }}</td>
                    <td class="right">{{ $money($item['total']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="center muted">No hay productos para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
