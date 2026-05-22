{{-- resources/views/reportes/ventas/pdf/frecuencia_compra.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Frecuencia de Compra por Cliente</title>
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
            background-color: #9C27B0;
            color: white;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .frecuente {
            color: #4CAF50;
            font-weight: bold;
        }
        .regular {
            color: #FF9800;
            font-weight: bold;
        }
        .esporadico {
            color: #F44336;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Frecuencia de Compra por Cliente</h1>
        <p>Fecha de generación: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Total Compras</th>
                <th>Frecuencia (días)</th>
                <th>Monto Total</th>
                <th>Ticket Promedio</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clientes as $index => $cliente)
            <tr>
                <td style="text-align: center">{{ $index + 1 }}</td>
                <td>{{ $cliente->Nombre }} {{ $cliente->apPaterno }} {{ $cliente->apMaterno }}</td>
                <td style="text-align: center">{{ number_format($cliente->total_compras) }}</td>
                <td style="text-align: center">
                    @if($cliente->frecuencia_promedio > 0)
                        {{ number_format($cliente->frecuencia_promedio, 1) }}
                        @if($cliente->frecuencia_promedio <= 7)
                            <span class="frecuente">(Frecuente)</span>
                        @elseif($cliente->frecuencia_promedio <= 15)
                            <span class="regular">(Regular)</span>
                        @else
                            <span class="esporadico">(Esporádico)</span>
                        @endif
                    @else
                        N/A
                    @endif
                </td>
                <td style="text-align: right">${{ number_format($cliente->monto_total, 2) }}</td>
                <td style="text-align: right">${{ number_format($cliente->monto_total / $cliente->total_compras, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Este reporte fue generado automáticamente por el sistema CRM.</p>
    </div>
</body>
</html>