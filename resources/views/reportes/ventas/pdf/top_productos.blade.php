{{-- resources/views/reportes/ventas/pdf/top_productos.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Top Productos</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
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
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #2196F3;
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
        .descripcion {
            max-width: 250px;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Top Productos más Vendidos</h1>
        <p>Fecha de generación: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="filtros">
        <strong>Período:</strong> {{ \Carbon\Carbon::parse($fechas['inicio'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechas['fin'])->format('d/m/Y') }}
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Top:</strong> {{ $productos->count() }} productos
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Total Ventas:</strong> ${{ number_format($productos->sum('monto_total'), 2) }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>EAN</th>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Monto Total</th>
                <th>Ticket Promedio</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $index => $producto)
            <tr>
                <td style="text-align: center">{{ $index + 1 }}</td>
                <td>{{ $producto->ean }}</td>
                <td class="descripcion">{{ $producto->descripcion }}</td>
                <td style="text-align: center">{{ number_format($producto->cantidad_vendida) }}</td>
                <td style="text-align: right">${{ number_format($producto->monto_total, 2) }}</td>
                <td style="text-align: right">
                    ${{ number_format($producto->cantidad_vendida > 0 ? $producto->monto_total / $producto->cantidad_vendida : 0, 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Este reporte fue generado automáticamente por el sistema CRM.</p>
    </div>
</body>
</html>