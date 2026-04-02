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
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
        }
        
        /* Encabezado */
        .header {
            margin-bottom: 30px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 15px;
        }
        
        .logo-area {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .logo {
            max-width: 150px;
            max-height: 80px;
        }
        
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            text-align: center;
        }
        
        /* Título */
        .title {
            text-align: center;
            margin: 20px 0;
        }
        
        .title h1 {
            font-size: 28px;
            color: #2c3e50;
            letter-spacing: 2px;
        }
        
        /* Información del documento */
        .doc-info {
            background: #f8f9fa;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .doc-info table {
            width: 100%;
        }
        
        .doc-info td {
            padding: 4px;
        }
        
        .doc-info td:first-child {
            font-weight: bold;
            width: 120px;
        }
        
        /* Datos del cliente */
        .client-info {
            background: #e8f4f8;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .client-info h3 {
            margin-bottom: 10px;
            color: #2c3e50;
            font-size: 14px;
        }
        
        /* Tabla de productos */
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .products-table th {
            background: #2c3e50;
            color: white;
            padding: 10px 8px;
            text-align: center;
            font-size: 11px;
        }
        
        .products-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        
        .products-table td:first-child,
        .products-table th:first-child {
            width: 8%;
        }
        
        .products-table td:nth-child(2),
        .products-table th:nth-child(2) {
            width: 15%;
        }
        
        .products-table td:nth-child(3),
        .products-table th:nth-child(3) {
            text-align: left;
        }
        
        .products-table td:nth-child(4),
        .products-table th:nth-child(4) {
            width: 12%;
        }
        
        .products-table td:nth-child(5),
        .products-table th:nth-child(5) {
            width: 12%;
        }
        
        .products-table td:last-child,
        .products-table th:last-child {
            width: 15%;
        }
        
        /* Totales */
        .totals {
            width: 300px;
            margin-left: auto;
            margin-bottom: 20px;
        }
        
        .totals table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .totals td {
            padding: 6px 8px;
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
            font-size: 14px;
        }
        
        /* Términos y condiciones */
        .terms {
            margin-top: 30px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 5px;
            font-size: 10px;
        }
        
        .terms h4 {
            margin-bottom: 8px;
            color: #2c3e50;
        }
        
        .terms p {
            margin-bottom: 5px;
        }
        
        /* Cierre */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #777;
        }
        
        .signature {
            margin-top: 30px;
            text-align: center;
        }
        
        .signature p {
            margin-top: 30px;
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
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 11px;
            border-radius: 4px;
        }
        
        .badge-success {
            background: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Encabezado -->
        <div class="header">
            <div class="logo-area">
                @php
                    $logoPath = public_path('logo.png');
                    if (file_exists($logoPath)) {
                        $logoData = base64_encode(file_get_contents($logoPath));
                        $logoType = mime_content_type($logoPath);
                    }
                @endphp
                @if(isset($logoData) && isset($logoType))
                    <img src="data:{{ $logoType }};base64,{{ $logoData }}" class="logo" alt="Logo">
                @else
                    <div style="height: 80px;"></div>
                @endif
            </div>
            <div class="company-name">
                {{ $cotizacion->sucursalAsignada->nombre ?? 'Farmacia CRM' }}
            </div>
            <!-- Dirección y teléfono (comentado para futuro) -->
            {{-- 
            <div class="text-center" style="font-size: 11px; margin-top: 5px;">
                Dirección: {{ $cotizacion->sucursalAsignada->direccion ?? 'No especificada' }}<br>
                Teléfono: {{ $cotizacion->sucursalAsignada->telefono ?? 'No especificado' }}
            </div>
            --}}
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
                    <th>Precio Unitario</th>
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
                    <td class="text-left">{{ $detalle->descripcion ?? '-' }}</td>
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
            <p><strong>Notas adicionales:</strong> {{ $cotizacion->comentarios }}</p>
            @endif
            <p style="margin-top: 10px; font-size: 9px;">Este documento es una cotización sujeta a cambios. Los precios y disponibilidad están sujetos a confirmación.</p>
        </div>

        <!-- Cierre -->
        <div class="signature">
            <p>_________________________</p>
            <p><strong>{{ $cotizacion->sucursalAsignada->nombre ?? 'Farmacia CRM' }}</strong></p>
            <p style="font-size: 10px;">Firma y sello</p>
        </div>

        <div class="footer">
            <p>Documento generado el {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>