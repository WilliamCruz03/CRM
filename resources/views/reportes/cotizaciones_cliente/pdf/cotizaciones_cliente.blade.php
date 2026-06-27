<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cotizaciones por Cliente</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
        }
        .header {
            margin-bottom: 30px;
            text-align: center;
        }
        .header h3 {
            margin: 0;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #005697;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h3>Reporte de Cotizaciones por Cliente</h3>
        <p>Período: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</p>
        <p>Estado: 
            @switch($statusFilter)
                @case('proceso') En proceso @break
                @case('completadas') Completadas @break
                @case('canceladas') Canceladas @break
                @default Todos
            @endswitch
        </p>
        <p>Fecha de generación: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>ID</th>
                <th>Cliente</th>
                <th class="text-center">Total Cotizaciones</th>
                <th class="text-right">Importe Total</th>
                <th class="text-right">Ticket Promedio</th>
                <th class="text-center">Última Cotización</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clientes as $cliente)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $cliente->id_Cliente }}</td>
                <td>{{ $cliente->Nombre }} {{ $cliente->apPaterno }} {{ $cliente->apMaterno ?? '' }}</td>
                <td class="text-center">{{ number_format($cliente->total_cotizaciones) }}</td>
                <td class="text-right">${{ number_format($cliente->importe_total, 2) }}</td>
                <td class="text-right">${{ number_format($cliente->ticket_promedio, 2) }}</td>
                <td class="text-center">{{ $cliente->ultima_cotizacion ? \Carbon\Carbon::parse($cliente->ultima_cotizacion)->format('d/m/Y') : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Reporte generado por el sistema CRM</p>
    </div>
</body>
</html>