<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Pedidos por Cliente</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header .subtitle {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .filters {
            background: #f5f5f5;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 11px;
        }
        .filters strong {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th {
            background: #005697;
            color: white;
            padding: 8px 10px;
            text-align: left;
            font-size: 11px;
        }
        table td {
            padding: 6px 10px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }
        table tr:nth-child(even) {
            background: #f9f9f9;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            background: #e8e8e8 !important;
            font-weight: bold;
        }
        .total-row td {
            border-top: 2px solid #333;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 12px;
            background: #3498db;
            color: white;
        }
        .badge-success {
            background: #27ae60;
        }
        .badge-warning {
            background: #f39c12;
        }
        .badge-danger {
            background: #e74c3c;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <h1>Pedidos por Cliente</h1>
        <div class="subtitle">Reporte de cotizaciones convertidas a pedido</div>
        <div class="subtitle">Generado: {{ now()->format('d/m/Y H:i:s') }}</div>
    </div>

    <!-- Filtros aplicados -->
    <div class="filters">
        <strong>Filtros aplicados:</strong><br>
        @if(isset($filtros))
            Top: {{ $filtros['top'] ?? 'N/A' }} |
            Orden: {{ $filtros['sort_by'] ?? 'N/A' }} |
            Período: {{ $filtros['fecha_inicio'] ?? 'N/A' }} al {{ $filtros['fecha_fin'] ?? 'N/A' }}
            @if(isset($filtros['cliente']))
                | Cliente: {{ $filtros['cliente'] }}
            @endif
        @endif
    </div>

    <!-- Tabla de resultados -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>ID</th>
                <th>Cliente</th>
                <th class="text-center">Total Pedidos</th>
                <th class="text-end">Monto Total</th>
                <th class="text-end">Promedio por Pedido</th>
            </tr>
        </thead>
        <tbody>
            @php $totalPedidos = 0; $montoTotal = 0; @endphp
            @forelse($data as $index => $item)
                @php
                    $totalPedidos += $item['total_pedidos'];
                    $montoTotal += $item['monto_total'];
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $cliente->id_Cliente }}</td>
                    <td>{{ $item['cliente_nombre'] ?? 'N/A' }}</td>
                    <td class="text-center">{{ $item['total_pedidos'] }}</td>
                    <td class="text-end">${{ number_format($item['monto_total'], 2) }}</td>
                    <td class="text-end">${{ number_format($item['monto_promedio'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No hay pedidos en el período seleccionado</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($data) > 0)
        <tfoot>
            <tr class="total-row">
                <td colspan="2" class="text-end">TOTALES:</td>
                <td class="text-center">{{ $totalPedidos }}</td>
                <td class="text-end">${{ number_format($montoTotal, 2) }}</td>
                <td class="text-end">${{ number_format($totalPedidos > 0 ? $montoTotal / $totalPedidos : 0, 2) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>Este reporte fue generado por el sistema CRM.</p>
    </div>

</body>
</html>