<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pedido {{ $pedido->folio_pedido }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .info-row {
            margin-bottom: 20px;
        }
        .info-label {
            font-weight: bold;
            width: 120px;
            display: inline-block;
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
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 10px;
            border-radius: 4px;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #000;
        }
        .badge-success {
            background-color: #28a745;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PEDIDO</h1>
        <p>{{ $pedido->folio_pedido }}</p>
    </div>

    <div class="info-row">
        <div><span class="info-label">Cotización:</span> {{ $pedido->cotizacion->folio ?? '-' }}</div>
        <div><span class="info-label">Fecha:</span> {{ $pedido->fecha_pedido ? $pedido->fecha_pedido->format('d/m/Y H:i') : '-' }}</div>
        <div><span class="info-label">Status:</span> {{ $pedido->status_nombre }}</div>
    </div>

    <div class="info-row">
        <div><span class="info-label">Cliente:</span> {{ $pedido->cotizacion->nombre_cliente ?? '-' }}</div>
        <div><span class="info-label">Teléfono:</span> {{ $pedido->cotizacion->cliente->telefono1 ?? '-' }}</div>
        <div><span class="info-label">Email:</span> {{ $pedido->cotizacion->cliente->email1 ?? '-' }}</div>
    </div>

    <div class="info-row">
        <div><span class="info-label">Repartidor:</span> 
            {{ $pedido->repartidor ? $pedido->repartidor->Nombre . ' ' . $pedido->repartidor->apPaterno : 'Sin asignar' }}
        </div>
        <div><span class="info-label">Fecha entrega:</span> {{ $pedido->fecha_entrega_real ? $pedido->fecha_entrega_real->format('d/m/Y H:i') : 'Pendiente' }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Código</th>
                <th>Descripción</th>
                <th class="text-center">Cant.</th>
                <th class="text-end">Precio</th>
                <th class="text-end">Desc.</th>
                <th class="text-end">Importe</th>
                <th>Sucursal</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @forelse($pedido->detalles as $index => $detalle)
                @php 
                    $importe = $detalle->cantidad * $detalle->precio_unitario * (1 - ($detalle->descuento / 100));
                    $total += $importe;
                    $esExterno = $detalle->es_externo == 1;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $detalle->codbar ?? '-' }}</td>
                    <td>
                        {{ $detalle->nombre ?? $detalle->descripcion ?? 'Producto' }}
                        @if($esExterno)
                            <br><span class="badge badge-warning">Sobre pedido</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $detalle->cantidad }}</td>
                    <td class="text-end">${{ number_format($detalle->precio_unitario, 2) }}</td>
                    <td class="text-end">{{ $detalle->descuento > 0 ? $detalle->descuento . '%' : '-' }}</td>
                    <td class="text-end">${{ number_format($importe, 2) }}</td>
                    <td>
                        @if($detalle->sucursalSurtido)
                            {{ $detalle->sucursalSurtido->nombre }}
                        @else
                            No asignada
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No hay productos en este pedido</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-end"><strong>Total:</strong></td>
                <td class="text-end"><strong>${{ number_format($total, 2) }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    @if($pedido->comentarios)
    <div style="margin-top: 20px;">
        <strong>Comentarios:</strong>
        <p>{{ $pedido->comentarios }}</p>
    </div>
    @endif

    <div class="footer">
        <p>Documento generado el {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>CRM - Sistema de Gestión de Pedidos</p>
    </div>
</body>
</html>