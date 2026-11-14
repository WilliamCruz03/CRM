<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comprobante sobre pedido {{ $pedido->folio_pedido }}</title>
    <style>
        @page { margin: 4mm 6mm 4mm 5mm; }
        body { margin: 0; color: #000; background: #fff; font: 9pt/1.3 "DejaVu Sans", sans-serif; }
        p { margin: 0 0 1mm; overflow-wrap: break-word; }
        h1, h2 { margin: 0; font-weight: bold; }
        h1 { font-size: 15pt; }
        h2 { font-size: 9pt; margin: 0 0 1.5mm; }
        header { text-align: center; margin-bottom: 3mm; }
        header img { max-width: 32mm; max-height: 14mm; }
        .titulo { font-weight: bold; margin: 2mm 0 1mm; }
        .seccion { border-top: 0.6pt dashed #000; padding-top: 2mm; margin-top: 2mm; }
        .articulo, .firma { page-break-inside: avoid; }
        .articulo { margin-bottom: 2mm; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 0; vertical-align: top; }
        .importe { text-align: right; white-space: nowrap; padding-left: 1mm; }
        .nota, .folio { font-size: 8pt; }
        .firma { text-align: center; margin-top: 3mm; }
        .sello { border: 0.6pt solid #000; width: 30mm; height: 12mm; margin: 0 auto 7mm; font-size: 8pt; }
        .linea { border-top: 0.6pt solid #000; padding-top: 1mm; }
    </style>
</head>
<body>
    @php
        $cliente = $pedido->clienteDirecto;
        $telefono = $cliente?->telefono1 ?: $cliente?->telefono2;
        $moneda = static fn ($importe) => '$' . number_format($importe, 2);
    @endphp
    <header>
        @if($logo)
            <img src="{{ $logo }}" alt="Farmapronto">
        @endif
        <h1>Farmapronto</h1>
        <p>La botica del pueblo</p>
        <p class="titulo">COMPROBANTE DE PEDIDO CON ANTICIPO</p>
        <p class="folio">{{ $pedido->folio_pedido }} · {{ ($pedido->fecha_encargo ?? $pedido->fecha_pedido)?->format('d/m/Y') }}</p>
        @if($pedido->sucursalOrigen)
            <p>{{ $pedido->sucursalOrigen->nombre }}</p>
        @endif
    </header>

    @if($pedido->anticipo_cliente !== null)
        <p>Recibí de <strong>{{ ($cliente?->nombre_completo ?: 'Sin registro') }}</strong> la cantidad de <strong>{{ $moneda($pedido->anticipo_cliente) }} MXN</strong>.</p>
    @else
        <p>Anticipo sin registro.</p>
    @endif

    <section class="seccion">
        <h2>Por concepto de</h2>
        @foreach($detalles as $detalle)
            <div class="articulo">
                <p><strong>{{ $detalle->descripcion_sobre_pedido ?? $detalle->ean }}</strong></p>
                <table><tr>
                    <td>{{ $detalle->cantidad }} × {{ $moneda($detalle->precio_unitario) }}</td>
                    <td class="importe">{{ $moneda($detalle->importe) }}</td>
                </tr></table>
                @php
                    $porcentaje = $detalle->porcentaje_promocion;
                    if ($detalle->id_convenio === null && $cumpleUmbralPublico && (int) round($total * 100) === \App\Services\Ventas\RegistrarPedidoDirecto::UMBRAL_PROMOCION_PUBLICO_CENTAVOS) {
                        $porcentaje ??= $categoriasPublico[$detalle->categoria_promocion]['porcentaje'] ?? null;
                    }
                @endphp
                @if($porcentaje !== null && ($detalle->id_convenio !== null || $cumpleUmbralPublico))
                    <p class="nota"><strong>APLICA DESCUENTO DEL {{ $porcentaje }}%</strong></p>
                @endif
            </div>
        @endforeach
        <table>
            <tr><td>Total estimado</td><td class="importe">{{ $moneda($total) }}</td></tr>
            <tr><td><strong>Anticipo recibido</strong></td><td class="importe"><strong>{{ $pedido->anticipo_cliente === null ? 'Sin registro' : $moneda($pedido->anticipo_cliente) }}</strong></td></tr>
        </table>
        @if($pedido->fecha_entrega_sugerida)
            <p>Entrega estimada: {{ $pedido->fecha_entrega_sugerida->format('d/m/Y') }}</p>
        @endif
    </section>

    <section class="seccion">
        <h2>Datos del cliente</h2>
        <p>Nombre: {{ ($cliente?->nombre_completo ?: 'Sin registro') }}</p>
        <p>Dirección: {{ $cliente?->direccion_completa ?: 'Sin registro' }}</p>
        <p>Teléfono: {{ $telefono ?: 'Sin registro' }}</p>
    </section>

    <section class="seccion nota">
        @if($mensajePromocional !== null)
            <p><strong>{{ $mensajePromocional }}</strong></p>
        @endif
        <p>Precios sujetos a cambio sin previo aviso.</p>
        <p>No se realiza la devolución del efectivo en caso de algún detalle o confusión por parte del cliente.</p>
        @if(filled($pedido->sucursalOrigen?->direccion))
            <p><strong>Aclaraciones:</strong> {{ $pedido->sucursalOrigen->direccion }}</p>
        @endif
    </section>

    <div class="firma">
        <div class="sello">Sello de sucursal</div>
        <p class="linea">Recibí de conformidad</p>
        <p>{{ $pedido->creador?->nombre_completo ?: 'Vendedor sin registro' }}</p>
    </div>
    <div id="fin-ticket"></div>
</body>
</html>
