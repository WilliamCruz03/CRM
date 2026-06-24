<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sucursales Preferidas</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
            font-size: 11px;
        }
        .filtros {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 5px;
            font-size: 11px;
        }
        .filtros table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        .filtros table td {
            border: none;
            padding: 4px 8px;
            background: transparent;
        }
        .filtros table td:first-child {
            font-weight: bold;
            width: 120px;
        }
        .filtros table td:nth-child(2) {
            padding-right: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #005697;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sucursales Preferidas</h1>
        <p>Fecha de generación: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="filtros">
        <table>
            <tr>
                <td><strong>Período:</strong></td>
                <td>{{ \Carbon\Carbon::parse($fechas['inicio'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechas['fin'])->format('d/m/Y') }}</td>
                <td><strong>Total Sucursales:</strong></td>
                <td>{{ $sucursales->count() }}</td>
            </tr>
            <tr>
                <td><strong>Ventas Totales:</strong></td>
                <td>{{ number_format($sucursales->sum('total_ventas')) }}</td>
                <td><strong>Monto Total:</strong></td>
                <td>${{ number_format($sucursales->sum('monto_total'), 2) }}</td>
            </tr>
            @if(isset($sortBy))
            <tr>
                <td><strong>Ordenado por:</strong></td>
                <td colspan="3">
                    @if($sortBy == 'ventas') Más Visitada
                    @elseif($sortBy == 'ventas_asc') Menos Visitada
                    @elseif($sortBy == 'monto') Mayor Monto
                    @elseif($sortBy == 'monto_asc') Menor Monto
                    @elseif($sortBy == 'ticket') Mayor Ticket Promedio
                    @elseif($sortBy == 'ticket_asc') Menor Ticket Promedio
                    @else Más Visitada
                    @endif
                </td>
            </tr>
            @endif
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Sucursal</th>
                <th>Ventas</th>
                <th>Monto Total</th>
                <th>Ticket Promedio</th>
                <th>Clientes Atendidos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sucursales as $index => $sucursal)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $sucursal->nombre }}</td>
                <td class="text-center">{{ number_format($sucursal->total_ventas) }}</td>
                <td class="text-right">${{ number_format($sucursal->monto_total, 2) }}</td>
                <td class="text-right">${{ number_format($sucursal->ticket_promedio, 2) }}</td>
                <td class="text-center">{{ number_format($sucursal->clientes_atendidos) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Este reporte fue generado por el sistema CRM.</p>
    </div>
</body>
</html>