<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>COTIZACION {{ $cotizacion->folio }}</title>
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
        /* INFORMACIÓN DEL DOCUMENTO                    */
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
        /* DATOS DEL CLIENTE                            */
        /* ============================================ */
        .client-info {
            padding: 8px 0;
            margin-bottom: 10px;
            border-bottom: 1px dashed #1a1a1a;
            font-size: 9px;
        }

        .client-info h3 {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .client-info table {
            width: 100%;
        }

        .client-info td {
            padding: 1px 0;
            vertical-align: top;
        }

        .client-info td:first-child {
            width: 110px;
            font-weight: bold;
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

        /* ============================================ */
        /* TOTALES                                      */
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
        /* SECCIÓN DE CÓDIGO DE BARRAS (DECORATIVO)     */
        /* ============================================ */
        .barcode-section {
            text-align: center;
            margin: 10px 0;
            padding-top: 8px;
            border-top: 1px dashed #1a1a1a;
        }

        .barcode {
            display: inline-block;
            font-family: 'Libre Barcode 39', 'Code 39', monospace;
            font-size: 32px;
            letter-spacing: 2px;
            line-height: 1;
            color: #1a1a1a;
            transform: scaleY(1.4);
            transform-origin: center;
        }

        .barcode-text {
            font-size: 8px;
            letter-spacing: 3px;
            margin-top: 2px;
            text-transform: uppercase;
        }

        /* ============================================ */
        /* TÉRMINOS Y CONDICIONES                       */
        /* ============================================ */
        .terms {
            margin-top: 10px;
            padding: 6px 0;
            border-top: 1px dashed #1a1a1a;
            font-size: 7.5px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            page-break-inside: avoid;
        }

        .terms p {
            margin-bottom: 2px;
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
        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .bold {
            font-weight: bold;
        }

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
            <h1>COTIZACION</h1>
            <!-- <div class="subtitle">Documento no fiscal</div> -->
        </div>

        <!-- Información del documento -->
        <div class="doc-info">
            <table>
                <tr>
                    <td>Folio:</td>
                    <td>{{ $cotizacion->folio }}</td>
                </tr>
                <tr>
                    <td>Fecha de emision:</td>
                    <td>{{ $cotizacion->fecha_creacion ? \Carbon\Carbon::parse($cotizacion->fecha_creacion)->format('d/m/Y H:i') : '-' }}
                    </td>
                </tr>
            </table>
        </div>

        <!-- Datos del cliente -->
        <div class="client-info">
            <h3>Datos del Cliente</h3>
            <table>
                <tr>
                    <td>Nombre:</td>
                    <td>{{ $cotizacion->nombre_cliente }}</td>
                </tr>
                {{-- Email comentado
                @if($cotizacion->cliente && $cotizacion->cliente->email1)
                <tr>
                    <td>Email:</td>
                    <td>{{ $cotizacion->cliente->email1 }}</td>
                </tr>
                @endif
                --}}
                {{-- Dirección y teléfono del cliente (comentado para futuro)
                @if($cotizacion->cliente && $cotizacion->cliente->Domicilio)
                <tr>
                    <td>Direccion:</td>
                    <td>{{ $cotizacion->cliente->Domicilio }}</td>
                </tr>
                @endif
                @if($cotizacion->cliente && $cotizacion->cliente->telefono1)
                <tr>
                    <td>Telefono:</td>
                    <td>{{ $cotizacion->cliente->telefono1 }}</td>
                </tr>
                @endif
                --}}
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
                    <th style="width: 60px;">P. Unit.</th>
                    <th style="width: 70px;">Importe</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $subtotal = 0;
                    $totalDescuento = 0;
                @endphp
                @foreach($cotizacion->detalles as $index => $detalle)
                    @php
                        $importe = $detalle->cantidad * $detalle->precio_unitario;
                        $descuentoProducto = $importe * ($detalle->descuento / 100);
                        $subtotal += $importe;
                        $totalDescuento += $descuentoProducto;

                        // Obtener la descripción correcta según el origen del producto
                        $descripcionProducto = $detalle->descripcion ?? '-';
                        if ($detalle->es_externo == 1) {
                            $tmpProducto = DB::connection('sqlsrv')->table('tmp_catalogo')->where('ean', $detalle->codbar)->first();
                            if ($tmpProducto) {
                                $descripcionProducto = $tmpProducto->descripcion;
                            }
                        } elseif ($detalle->codbar) {
                            $producto = DB::connection('sqlsrvM')->table('catalogo_general')->where('ean', $detalle->codbar)->first();
                            if ($producto) {
                                $descripcionProducto = $producto->descripcion;
                            }
                        }
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $detalle->codbar ?? '-' }}</td>
                        <td class="col-desc">{{ Str::limit($descripcionProducto, 50) }}</td>
                        <td>{{ $detalle->cantidad }}</td>
                        <td>${{ number_format($detalle->precio_unitario, 2) }}</td>
                        <td>${{ number_format($importe, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totales -->
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
                {{-- Impuestos (comentado para futuro)
                <tr>
                    <td>IVA (16%):</td>
                    <td>${{ number_format(($subtotal - $totalDescuento) * 0.16, 2) }}</td>
                </tr>
                --}}
                <tr class="total-row">
                    <td>TOTAL:</td>
                    <td>${{ number_format($cotizacion->importe_total, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Código de barras decorativo -->
        <div class="barcode-section">
            <!-- <div class="barcode">*{{ $cotizacion->folio }}*</div> -->
            <div class="barcode-text">{{ $cotizacion->folio }}</div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="thanks">*** GRACIAS POR SU PREFERENCIA ***</div>
            <p>Documento generado el {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>

</html>