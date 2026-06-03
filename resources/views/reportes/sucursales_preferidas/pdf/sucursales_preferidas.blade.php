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
            background-color: #4CAF50;
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
        <strong>Período:</strong> {{ \Carbon\Carbon::parse($fechas['inicio'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechas['fin'])->format('d/m/Y') }}
          |  
        <strong>Total Sucursales:</strong> {{ $sucursales->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Sucursal</th>
                <th>Ventas</th>
                <th>Monto Total</th>
                <th>Ticket Promedio</th>
                <th>Clientes Únicos</th>
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
        <p>Este reporte fue generado automáticamente por el sistema CRM.</p>
    </div>
</body>
</html>