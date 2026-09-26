<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>PEDIDO {{ $pedido->folio_pedido }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans Mono', 'Courier New', monospace;
            font-size: 10px;
            line-height: 1.35;
            color: #1a1a1a;
            margin: 0;
            padding: 18px 20px;
            background: #ffffff;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
        }

        /* ============================================ */
        /* HEADER TIPO TICKET                           */
        /* ============================================ */
        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #1a1a1a;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .company-slogan {
            font-size: 9px;
            font-style: italic;
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .company-divider {
            border-top: 2px solid #1a1a1a;
            border-bottom: 1px solid #1a1a1a;
            height: 3px;
            margin: 6px 0;
        }

        /* ============================================ */
        /* TÍTULO                                       */
        /* ============================================ */
        .title {
            text-align: center;
            margin: 10px 0 8px 0;
        }

        .title h1 {
            font-size: 15px;
            letter-spacing: 4px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .title .subtitle {
            font-size: 8px;
            letter-spacing: 1px;
            margin-top: 2px;
            text-transform: uppercase;
        }

        /* ============================================ */
        /* INFORMACIÓN DEL PEDIDO                       */
        /* ============================================ */
        .doc-info {
            padding: 8px 0;
            margin-bottom: 10px;
            border-top: 1px dashed #1a1a1a;
            border-bottom: 1px dashed #1a1a1a;
            font-size: 9px;
        }

        .doc-info table {
            width: 100%;
        }

        .doc-info td {
            padding: 1px 0;
            vertical-align: top;
        }

        .doc-info td:first-child {
            font-weight: bold;
            width: 130px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ============================================ */
        /* TABLA DE PRODUCTOS                           */
        /* ============================================ */
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9px;
        }

        .products-table thead th {
            background: #1a1a1a;
            color: #ffffff;
            padding: 5px 3px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: bold;
            font-size: 8.5px;
            border: none;
        }

        .products-table tbody td {
            padding: 4px 3px;
            text-align: center;
            border-bottom: 1px dotted #b0b0b0;
            vertical-align: middle;
        }

        .products-table tbody tr:last-child td {
            border-bottom: 1px solid #1a1a1a;
        }

        .products-table tbody td.col-desc {
            text-align: left;
        }

        /* Etiqueta de producto externo */
        .tag-externo {
            display: inline-block;
            background-color: #fff3cd;
            color: #856404;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-left: 4px;
            border: 1px solid #ffeeba;
        }

        /* ============================================ */
        /* TOTALES (alineados a la derecha)             */
        /* ============================================ */
        .totals {
            width: 280px;
            margin-left: auto;
            margin-bottom: 12px;
            padding-top: 6px;
            border-top: 1px dashed #1a1a1a;
            font-size: 9px;
        }

        .totals table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals td {
            padding: 3px 4px;
            vertical-align: middle;
        }

        .totals td:first-child {
            text-align: right;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .totals td:last-child {
            text-align: right;
            font-weight: bold;
        }

        .total-row td {
            background: #1a1a1a;
            color: #ffffff;
            padding: 6px 4px;
            font-size: 10px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .total-row td:first-child {
            text-align: right;
        }

        .total-row td:last-child {
            text-align: right;
        }

        /* ============================================ */
        /* FOOTER                                       */
        /* ============================================ */
        .footer {
            margin-top: 10px;
            padding-top: 6px;
            border-top: 1px dashed #1a1a1a;
            text-align: center;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .footer .thanks {
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-bottom: 3px;
        }

        /* Utilidades */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-end { text-align: right; }
        .bold { font-weight: bold; }

        .page-content {
            page-break-after: avoid;
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <div class="container page-content">
        <!-- Encabezado estilo ticket -->
        <div class="header">
            <div class="company-name">FARMACIAS FARMAPRONTO</div>
            <div class="company-slogan">"La Botica del Pueblo"</div>
            <div class="company-divider"></div>
        </div>

        <!-- Título -->
        <div class="title">
            <h1>PEDIDO</h1>
            <div class="subtitle">Documento interno</div>
        </div>

        <!-- Información del pedido -->
        <div class="doc-info">
            <table>
                <!-- Cotización Origen comentada
                <tr>
                    <td>Cotizacion Origen:</td>
                    <td>{{ $pedido->cotizacion->folio ?? '-' }}</td>
                </tr>
                -->
                <tr>
                    <td>Folio:</td>
                    <td>{{ $pedido->folio_pedido }}</td>
                </tr>
                <tr>
                    <td>Fecha:</td>
                    <td>{{ $pedido->fecha_pedido ? $pedido->fecha_pedido->format('d/m/Y H:i') : '-' }}</td>
                </tr>
                <tr>
                    <td>Cliente:</td>
                    <td>{{ $pedido->cotizacion->nombre_cliente ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Fecha entrega:</td>
                    <td>{{ $pedido->fecha_entrega_real ? $pedido->fecha_entrega_real->format('d/m/Y H:i') : 'Pendiente' }}</td>
                </tr>
            </table>
        </div>

        <!-- Tabla de productos -->
        <table class="products-table">
            <thead>
                <tr>
                    <th style="width: 22px;">#</th>
                    <th style="width: 70px;">Codigo</th>
                    <th style="width: 70px;">Descripcion</th>
                    <th style="width: 40px;">Cant.</th>
                    <th style="width: 65px;">P. Unit.</th>
                    <th style="width: 75px;">Importe</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $subtotal = 0;
                    $totalDescuento = 0;
                @endphp
                @forelse($pedido->detalles as $index => $detalle)
                    @php
                        $importeBruto = $detalle['cantidad'] * $detalle['precio_unitario'];
                        $descuentoProducto = $importeBruto * ($detalle['descuento'] / 100);
                        $importe = $importeBruto - $descuentoProducto;
                        $subtotal += $importeBruto;
                        $totalDescuento += $descuentoProducto;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $detalle['codbar'] ?? '-' }}</td>
                        <td class="col-desc">
                            {{ $detalle['nombre'] }}
                            @if($detalle['es_externo'])
                                <span class="tag-externo">Sobre pedido</span>
                            @endif
                        </td>
                        <td>{{ $detalle['cantidad'] }}</td>
                        <td>${{ number_format($detalle['precio_unitario'], 2) }}</td>
                        <td>${{ number_format($importe, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 15px 0; border-bottom: 1px solid #1a1a1a;">
                            No hay productos en este pedido
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Totales alineados a la derecha -->
        <div class="totals">
            <table>
                <tr>
                    <td>Subtotal:</td>
                    <td>${{ number_format($subtotal, 2) }}</td>
                </tr>
                @if($totalDescuento > 0)
                <tr>
                    <td>Descuentos:</td>
                    <td>-${{ number_format($totalDescuento, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>TOTAL:</td>
                    <td>${{ number_format($subtotal - $totalDescuento, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="thanks">*** GRACIAS POR SU PREFERENCIA ***</div>
            <p>Documento generado el {{ now()->format('d/m/Y H:i:s') }}</p>
            <p>CRM - Sistema de Gestion de Pedidos</p>
        </div>
    </div>
</body>
</html>