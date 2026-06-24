{{-- resources/views/reportes/ventas/pdf/clientes.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Clientes</title>
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
        }
        .filtros td {
            padding: 3px;
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
        .total {
            margin-top: 15px;
            text-align: right;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Compras por Cliente</h1>
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
                <td>{{ number_format($clientes->sum('total_transacciones')) }}</td>
            </tr>
            @if(isset($sortBy))
            <tr>
                <td><strong>Ordenado por:</strong></td>
                <td colspan="3">
                    @if($sortBy == 'monto_total') Mayor Monto
                    @elseif($sortBy == 'monto_total_asc') Menor Monto
                    @elseif($sortBy == 'total_transacciones') Más Compras
                    @elseif($sortBy == 'total_transacciones_asc') Menos Compras
                    @else Mayor Monto
                    @endif
                </td>
            </tr>
            @endif
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre Completo</th>
                <th>Ventas Totales</th>
                <th>Monto Total</th>
                <th>Ticket Promedio</th>
                <th>Última Compra</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clientes as $cliente)
            <tr>
                <td>{{ $cliente->id_Cliente }}</td>
                <td>{{ trim($cliente->Nombre . ' ' . $cliente->apPaterno . ' ' . ($cliente->apMaterno ?? '')) }}</td>
                <td style="text-align: center">{{ number_format($cliente->total_transacciones) }}</td>
                <td style="text-align: right">${{ number_format($cliente->monto_total, 2) }}</td>
                <td style="text-align: right">${{ number_format($cliente->ticket_promedio, 2) }}</td>
                <td style="text-align: center">{{ $cliente->ultima_compra ? \Carbon\Carbon::parse($cliente->ultima_compra)->format('d/m/Y') : 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        <p>Monto Total General: ${{ number_format($clientes->sum('monto_total'), 2) }}</p>
    </div>

    <div class="footer">
        <p>Este reporte fue generado por el sistema CRM.</p>
    </div>
</body>
</html>