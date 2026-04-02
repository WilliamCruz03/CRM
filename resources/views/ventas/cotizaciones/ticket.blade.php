<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización {{ $cotizacion->folio }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 15px;
        }
        
        .container {
            max-width: 100%;
            margin: 0 auto;
        }
        
        /* Encabezado más compacto - SIN LOGO */
        .header {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2c3e50;
            text-align: center;
        }
        
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .company-slogan {
            font-size: 10px;
            color: #666;
            margin-top: 3px;
        }
        
        /* Título */
        .title {
            text-align: center;
            margin: 10px 0;
        }
        
        .title h1 {
            font-size: 22px;
            color: #2c3e50;
            letter-spacing: 2px;
        }
        
        /* Información del documento */
        .doc-info {
            background: #f8f9fa;
            padding: 8px 12px;
            margin-bottom: 12px;
            border-radius: 4px;
            font-size: 10px;
        }
        
        .doc-info table {
            width: 100%;
        }
        
        .doc-info td {
            padding: 3px;
        }
        
        .doc-info td:first-child {
            font-weight: bold;
            width: 110px;
        }
        
        /* Datos del cliente */
        .client-info {
            background: #e8f4f8;
            padding: 8px 12px;
            margin-bottom: 12px;
            border-radius: 4px;
            font-size: 10px;
        }
        
        .client-info h3 {
            margin-bottom: 6px;
            color: #2c3e50;
            font-size: 12px;
        }
        
        /* Tabla de productos */
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 9px;
        }
        
        .products-table th {
            background: #2c3e50;
            color: white;
            padding: 6px 4px;
            text-align: center;
        }
        
        .products-table td {
            border: 1px solid #ddd;
            padding: 5px 4px;
            text-align: center;
        }
        
        .products-table td:nth-child(3) {
            text-align: left;
        }
        
        /* Totales */
        .totals {
            width: 260px;
            margin-left: auto;
            margin-bottom: 12px;
            font-size: 10px;
        }
        
        .totals table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .totals td {
            padding: 4px 6px;
        }
        
        .totals td:first-child {
            font-weight: bold;
            text-align: right;
        }
        
        .totals td:last-child {
            text-align: right;
        }
        
        .total-row {
            background: #2c3e50;
            color: white;
            font-weight: bold;
        }
        
        /* Términos y condiciones */
        .terms {
            margin-top: 12px;
            padding: 8px 10px;
            background: #f8f9fa;
            border-radius: 4px;
            font-size: 8px;
            page-break-inside: avoid;
        }
        
        .terms h4 {
            margin-bottom: 5px;
            color: #2c3e50;
            font-size: 10px;
        }
        
        .terms p {
            margin-bottom: 3px;
        }
        
        /* Cierre */
        .signature {
            margin-top: 15px;
            text-align: center;
        }
        
        .signature p {
            margin-top: 20px;
            font-size: 10px;
        }
        
        .footer {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 8px;
            color: #777;
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
        <!-- Encabezado - SIN LOGO, solo texto -->
        <div class="header">
            <div class="company-name">
                FARMACIAS FARMAPRONTO
            </div>
            <div class="company-slogan">
                "La Botica del Pueblo"
            </div>
        </div>

        <!-- Título -->
        <div class="title">
            <h1>COTIZACIÓN</h1>
        </div>

        <!-- Información del documento -->
        <div class="doc-info">
            <table>
                <tr>
                    <td>Folio:</td>
                    <td><strong>{{ $cotizacion->folio }}</strong></td>
                    <td>Vigencia:</td>
                    <td><strong>{{ \Carbon\Carbon::parse($cotizacion->fecha_creacion)->addDays(30)->format('d/m/Y') }}</strong></td>
                </tr>
                <tr>
                    <td>Fecha de emisión:</td>
                    <td><strong>{{ $cotizacion->fecha_creacion ? \Carbon\Carbon::parse($cotizacion->fecha_creacion)->format('d/m/Y H:i') : '-' }}</strong></td>
                    <td>Versión:</td>
                    <td><strong>{{ $cotizacion->version }}</strong></td>
                </tr>
            </table>
        </div>

        <!-- Datos del cliente -->
        <div class="client-info">
            <h3>DATOS DEL CLIENTE</h3>
            <table style="width: 100%">
                <tr>
                    <td style="width: 100px; font-weight: bold;">Nombre:</td>
                    <td><strong>{{ $cotizacion->nombre_cliente }}</strong></td>
                </tr>
                @if($cotizacion->cliente && $cotizacion->cliente->email1)
                <tr>
                    <td style="font-weight: bold;">Email:</td>
                    <td>{{ $cotizacion->cliente->email1 }}</td>
                </tr>
                @endif
                {{-- Dirección y teléfono del cliente (comentado para futuro)
                @if($cotizacion->cliente && $cotizacion->cliente->Domicilio)
                <tr>
                    <td style="font-weight: bold;">Dirección:</td>
                    <td>{{ $cotizacion->cliente->Domicilio }}</td>
                </tr>
                @endif
                @if($cotizacion->cliente && $cotizacion->cliente->telefono1)
                <tr>
                    <td style="font-weight: bold;">Teléfono:</td>
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
                    <th>#</th>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th>Cantidad</th>
                    <th>Precio Unit.</th>
                    <th>Importe</th>
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
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $detalle->codbar ?? '-' }}</td>
                    <td class="text-left">{{ Str::limit($detalle->descripcion ?? '-', 50) }}</td>
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
                    <td><strong>TOTAL NETO:</strong></td>
                    <td><strong>${{ number_format($cotizacion->importe_total, 2) }}</strong></td>
                </tr>
            </table>
        </div>

        <!-- Términos y condiciones -->
        <div class="terms">
            <h4>TÉRMINOS Y CONDICIONES</h4>
            <p><strong>Tiempo de entrega sugerido:</strong> {{ $cotizacion->fecha_entrega_sugerida ? \Carbon\Carbon::parse($cotizacion->fecha_entrega_sugerida)->format('d/m/Y') : 'No especificada' }}</p>
            {{-- Forma de pago (comentado para futuro)
            <p><strong>Forma de pago:</strong> No especificada</p>
            --}}
            {{-- Garantías (comentado para futuro)
            <p><strong>Garantías:</strong> No especificadas</p>
            --}}
            @if($cotizacion->comentarios)
            <p><strong>Notas adicionales:</strong> {{ Str::limit($cotizacion->comentarios, 100) }}</p>
            @endif
            <p style="margin-top: 5px; font-size: 8px;">Este documento es una cotización sujeta a cambios. Los precios y disponibilidad están sujetos a confirmación.</p>
        </div>

        <!-- Cierre -->
        <div class="signature">
            <p>_________________________</p>
            <p><strong>FARMACIAS FARMAPRONTO</strong></p>
            <p style="font-size: 9px;">"La Botica del Pueblo"</p>
        </div>

        <div class="footer">
            <p>Documento generado el {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>