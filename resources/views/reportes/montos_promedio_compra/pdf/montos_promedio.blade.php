<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Montos Promedio de Compra por Cliente</title>
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
        .text-left {
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Montos Promedio de Compra por Cliente</h1>
        <p>Fecha de generación: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="filtros">
        <table>
            <tr>
                <td><strong>Período:</strong></td>
                <td>{{ \Carbon\Carbon::parse($fechas['inicio'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechas['fin'])->format('d/m/Y') }}</td>
                <td><strong>Total Clientes:</strong></td>
                <td>{{ $clientes->count() }}</td>
            </tr>
            <tr>
                <td><strong>Monto Total:</strong></td>
                <td>${{ number_format($clientes->sum('monto_total'), 2) }}</td>
                <td><strong>Ventas Totales:</strong></td>
                <td>{{ number_format($clientes->sum('total_compras')) }}</td>
            </tr>
            @if(isset($sortBy))
            <tr>
                <td><strong>Ordenado por:</strong></td>
                <td colspan="3">
                    @if($sortBy == 'monto_promedio') Mayor Promedio
                    @elseif($sortBy == 'monto_promedio_asc') Menor Promedio
                    @elseif($sortBy == 'total_compras') Más Compras
                    @elseif($sortBy == 'total_compras_asc') Menos Compras
                    @else Mayor Promedio
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
                <th>Cliente</th>
                <th>Compras</th>
                <th>Total</th>
                <th>Promedio</th>
                <th>Primera Compra</th>
                <th>Última Compra</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clientes as $index => $cliente)
            @php
                $cliente = is_array($cliente) ? (object) $cliente : $cliente;
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $cliente->Nombre }} {{ $cliente->apPaterno }} {{ $cliente->apMaterno ?? '' }}</td>
                <td class="text-center">{{ number_format($cliente->total_compras) }}</td>
                <td class="text-right">${{ number_format($cliente->monto_total, 2) }}</td>
                <td class="text-right">${{ number_format($cliente->monto_promedio, 2) }}</td>
                <td class="text-left">
                    {{ \Carbon\Carbon::parse($cliente->fecha_primera_compra)->format('d/m/Y') }}<br>
                    <small>${{ number_format($cliente->monto_primera_compra ?? 0, 2) }}</small>
                </td>
                <td class="text-left">
                    {{ \Carbon\Carbon::parse($cliente->fecha_ultima_compra)->format('d/m/Y') }}<br>
                    <small>${{ number_format($cliente->monto_ultima_compra ?? 0, 2) }}</small>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if(isset($mensajeAdvertencia) && $mensajeAdvertencia)
    <div style="background-color: #fff3cd; border: 1px solid #ffeeba; padding: 10px; margin-bottom: 15px; border-radius: 5px; color: #856404;">
        <strong>⚠️ {{ $mensajeAdvertencia }}</strong>
    </div>
    @endif

    <div class="footer">
        <p>Este reporte fue generado por el sistema CRM.</p>
    </div>
</body>
</html>