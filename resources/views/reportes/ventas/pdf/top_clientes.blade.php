{{-- resources/views/reportes/ventas/pdf/top_clientes.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Top Clientes</title>
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
            background-color: #ff9800;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Top Clientes</h1>
        <p>Fecha de generación: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="filtros">
        <strong>Período:</strong> {{ \Carbon\Carbon::parse($fechas['inicio'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechas['fin'])->format('d/m/Y') }}
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Top:</strong> {{ $top }} clientes
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>ID</th>
                <th>Nombre Completo</th>
                <th>Transacciones</th>
                <th>Monto Total</th>
                <th>Ticket Promedio</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clientes as $index => $cliente)
            <tr>
                <td style="text-align: center">{{ $index + 1 }}</td>
                <td>{{ $cliente->id_Cliente }}</td>
                <td>{{ trim($cliente->Nombre . ' ' . $cliente->apPaterno . ' ' . ($cliente->apMaterno ?? '')) }}</td>
                <td style="text-align: center">{{ number_format($cliente->total_transacciones) }}</td>
                <td style="text-align: right">${{ number_format($cliente->monto_total, 2) }}</td>
                <td style="text-align: right">${{ number_format($cliente->ticket_promedio, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Este reporte fue generado automáticamente por el sistema CRM.</p>
    </div>
</body>
</html>