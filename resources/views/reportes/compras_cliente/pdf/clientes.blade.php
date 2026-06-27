<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Clientes</title>
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
        .filtros table {
            width: 100%;
        }
        .filtros td {
            padding: 3px;
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
            background-color: #005697;
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
        .total {
            margin-top: 15px;
            text-align: right;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Compras por Cliente</h1>
        <p>Fecha de generación: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="filtros">
        <table>
            <tr>
                <td><strong>Período:</strong></td>
                <td>{{ \Carbon\Carbon::parse($fechas['inicio'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechas['fin'])->format('d/m/Y') }}</td>
                <td><strong>Total Clientes:</strong></td>
                <td>{{ $clientes->count() }}</td>
            </tr>
            <tr>
                <td><strong>Monto Total:</strong></td>
                <td>${{ number_format($clientes->sum('monto_total'), 2) }}</td>
                <td><strong>Ventas Totales:</strong></td>
                <td>{{ number_format($clientes->sum('total_transacciones')) }}</td>
            </tr>
            @if(isset($sortBy))
            <tr>
                <td><strong>Ordenado por:</strong></td>
                <td colspan="3">
                    @if($sortBy == 'monto_total') Mayor Monto
                    @elseif($sortBy == 'monto_total_asc') Menor Monto
                    @elseif($sortBy == 'total_transacciones') Más Compras
                    @elseif($sortBy == 'total_transacciones_asc') Menos Compras
                    @else Mayor Monto
                    @endif
                </td>
            </tr>
            @endif
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre Completo</th>
                <th>Ventas Totales</th>
                <th>Monto Total</th>
                <th>Ticket Promedio</th>
                <th>Última Compra</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clientes as $cliente)
            <tr>
                <td>{{ $cliente->id_Cliente }}</td>
                <td>{{ trim($cliente->Nombre . ' ' . $cliente->apPaterno . ' ' . ($cliente->apMaterno ?? '')) }}</td>
                <td style="text-align: center">{{ number_format($cliente->total_transacciones) }}</td>
                <td style="text-align: right">${{ number_format($cliente->monto_total, 2) }}</td>
                <td style="text-align: right">${{ number_format($cliente->ticket_promedio, 2) }}</td>
                <td style="text-align: center">{{ $cliente->ultima_compra ? \Carbon\Carbon::parse($cliente->ultima_compra)->format('d/m/Y') : 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if(isset($mensajeAdvertencia) && $mensajeAdvertencia)
    <div style="background-color: #fff3cd; border: 1px solid #ffeeba; padding: 10px; margin-bottom: 15px; border-radius: 5px; color: #856404;">
        <strong>⚠️ {{ $mensajeAdvertencia }}</strong>
    </div>
    @endif

    <div class="footer">
        <p>Este reporte fue generado por el sistema CRM.</p>
    </div>

<script>
window.exportarReporte = function(tipo) {
    const top = document.getElementById('topSelect').value;
    const sortBy = document.getElementById('sortBySelect').value;
    const filtroFecha = document.getElementById('filtroFecha').value;
    const indicacionId = document.getElementById('indicacionSelect').value;
    const clienteId = document.getElementById('cliente_id').value;
    
    let fechaInicio, fechaFin;
    
    if (filtroFecha === 'personalizado') {
        fechaInicio = document.getElementById('fechaInicio').value;
        fechaFin = document.getElementById('fechaFin').value;
    } else {
        const fechas = getFechasByFiltro(filtroFecha);
        if (fechas) {
            fechaInicio = fechas.inicio;
            fechaFin = fechas.fin;
        }
    }
    
    const params = new URLSearchParams({
        tipo: 'clientes',
        top: top,
        sort_by: sortBy,
        filtro_fecha: filtroFecha,
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFin
    });
    
    if (indicacionId) {
        params.append('indicacion_id', indicacionId);
    }
    
    if (clienteId) {
        params.append('search_cliente', clienteId);
    }
    
    let url;
    if (tipo === 'excel') {
        url = `{{ route("reportes.compras_cliente.exportar.excel") }}?${params.toString()}`;
    } else {
        url = `{{ route("reportes.compras_cliente.exportar.pdf") }}?${params.toString()}`;
    }
    
    // Mostrar loading
    if (window.mostrarToast) {
        window.mostrarToast('Generando archivo... Esto puede tomar varios segundos.', 'warning');
    }
    
    // Abrir en nueva ventana con timeout para evitar bloqueos
    const win = window.open(url, '_blank');
    
    // Si la ventana se abre, mostramos mensaje de éxito
    if (win) {
        setTimeout(() => {
            if (window.mostrarToast) {
                window.mostrarToast('El archivo se está generando. Por favor espere...', 'success');
            }
        }, 2000);
    } else {
        // Si el popup fue bloqueado, usar el método alternativo
        if (window.mostrarToast) {
            window.mostrarToast('Descarga iniciada. Por favor espere...', 'info');
        }
        window.location.href = url;
    }
};
</script>
</body>
</html>