@extends('layouts.app')

@section('title', 'Montos Promedio de Compra por Cliente')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="bi bi-calculator"></i> Montos Promedio de Compra por Cliente
            </h3>
            <div class="btn-group" id="botonesExportacion" style="display: none;">
                <button type="button" class="btn btn-success btn-sm" onclick="exportarReporte('excel')">
                    <i class="bi bi-filetype-xls"></i> Excel
                </button>
                <button type="button" class="btn btn-danger btn-sm" onclick="exportarReporte('pdf')">
                    <i class="bi bi-filetype-pdf"></i> PDF
                </button>
            </div>
        </div>
        <div class="card-body">
            @include('reportes.partials.filtros_fecha', ['route' => 'reportes.ventas.montos-promedio-compra'])

            <div class="row mt-3">
                <div class="col-md-3">
                    <label>Top <span class="text-danger">*</span></label>
                    <select class="form-control" id="topSelect">
                        <option value="">-- Seleccione --</option>
                        <option value="10">Top 10</option>
                        <option value="25">Top 25</option>
                        <option value="50">Top 50</option>
                        <option value="100">Top 100</option>
                        <option value="todos">Todos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Ordenar por <span class="text-danger">*</span></label>
                    <select class="form-control" id="sortBySelect">
                        <option value="">-- Seleccione --</option>
                        <option value="monto_promedio">Mayor Promedio</option>
                        <option value="monto_promedio_asc">Menor Promedio</option>
                        <option value="total_compras">Más Compras</option>
                        <option value="total_compras_asc">Menos Compras</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Buscar cliente (opcional)</label>
                    <input type="text" class="form-control" id="buscarCliente" placeholder="Nombre o apellido">
                    <input type="hidden" id="cliente_id">
                </div>
                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-primary btn-block" id="btnAplicarFiltros">
                        <i class="bi bi-funnel"></i> Aplicar Filtros
                    </button>
                </div>
            </div>

            <div id="loadingIndicator" class="text-center my-4" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2">Cargando datos...</p>
            </div>

            <div id="resultadosContainer">
                <div class="alert alert-secondary text-center">
                    <i class="bi bi-funnel"></i> 
                    Seleccione los filtros y presione <strong>"Aplicar Filtros"</strong> para ver los resultados.
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let clienteSeleccionadoId = null;

    function getFechasByFiltro(filtro) {
        const hoy = new Date();
        let inicio, fin;
        
        switch(filtro) {
            case 'hoy':
                inicio = hoy.toISOString().split('T')[0];
                fin = hoy.toISOString().split('T')[0];
                break;
            case 'esta_semana':
                const dia = hoy.getDay();
                const diff = dia === 0 ? 6 : dia - 1;
                const inicioSemana = new Date(hoy);
                inicioSemana.setDate(hoy.getDate() - diff);
                const finSemana = new Date(inicioSemana);
                finSemana.setDate(inicioSemana.getDate() + 6);
                inicio = inicioSemana.toISOString().split('T')[0];
                fin = finSemana.toISOString().split('T')[0];
                break;
            case 'este_mes':
                inicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().split('T')[0];
                fin = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0).toISOString().split('T')[0];
                break;
            case 'este_ano':
                inicio = new Date(hoy.getFullYear(), 0, 1).toISOString().split('T')[0];
                fin = new Date(hoy.getFullYear(), 11, 31).toISOString().split('T')[0];
                break;
            default:
                return null;
        }
        
        return { inicio, fin };
    }

    async function cargarDatos() {
        const top = document.getElementById('topSelect').value;
        const sortBy = document.getElementById('sortBySelect').value;
        const filtroFecha = document.getElementById('filtroFecha').value;
        
        if (!top || !sortBy || !filtroFecha) {
            if (window.mostrarToast) window.mostrarToast('Debe seleccionar todos los filtros', 'warning');
            return;
        }
        
        let fechaInicio, fechaFin;
        
        if (filtroFecha === 'personalizado') {
            fechaInicio = document.getElementById('fechaInicio').value;
            fechaFin = document.getElementById('fechaFin').value;
            if (!fechaInicio || !fechaFin) {
                if (window.mostrarToast) window.mostrarToast('Debe seleccionar ambas fechas', 'warning');
                return;
            }
        } else {
            const fechas = getFechasByFiltro(filtroFecha);
            if (!fechas) return;
            fechaInicio = fechas.inicio;
            fechaFin = fechas.fin;
        }
        
        document.getElementById('loadingIndicator').style.display = 'block';
        document.getElementById('resultadosContainer').innerHTML = '';
        document.getElementById('botonesExportacion').style.display = 'none';
        
        try {
            const params = new URLSearchParams({
                top: top,
                sort_by: sortBy,
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin
            });
            
            if (clienteSeleccionadoId) {
                params.append('search_cliente', clienteSeleccionadoId);
            }
            
            const response = await fetch(`{{ route("reportes.ventas.montos-promedio-compra.data") }}?${params.toString()}`);
            const data = await response.json();
            
            if (data.success && data.data && data.data.length > 0) {
                mostrarResultados(data);
                document.getElementById('botonesExportacion').style.display = 'inline-flex';
            } else {
                document.getElementById('resultadosContainer').innerHTML = `
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle"></i> 
                        No se encontraron clientes en el período seleccionado.
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('resultadosContainer').innerHTML = `
                <div class="alert alert-danger text-center">
                    <i class="bi bi-exclamation-triangle"></i> 
                    Error al cargar los datos
                </div>
            `;
        } finally {
            document.getElementById('loadingIndicator').style.display = 'none';
        }
    }
    
function mostrarResultados(data) {
    const clientes = data.data;
    
    let html = `
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i> 
            Mostrando <strong>${clientes.length}</strong> clientes
            <br><small>Período: ${data.filtros.fecha_inicio} al ${data.filtros.fecha_fin}</small>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Compras</th>
                        <th>Total</th>
                        <th>Promedio</th>
                        <th>Primera Compra</th>
                        <th>Última Compra</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    clientes.forEach(cliente => {
        html += `
            <tr>
                <td>${cliente.Nombre} ${cliente.apPaterno} ${cliente.apMaterno || ''}<br>
                    <small class="text-muted">ID: ${cliente.id_Cliente}</small>
                 </td>
                <td class="text-center">${Number(cliente.total_compras).toLocaleString()}</td>
                <td class="text-right">$${Number(cliente.monto_total).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                <td class="text-right">$${Number(cliente.monto_promedio).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                <td>
                    ${cliente.fecha_primera_compra ? new Date(cliente.fecha_primera_compra).toLocaleDateString() : 'N/A'}<br>
                    <small>$${Number(cliente.monto_primera_compra || 0).toLocaleString('es-MX', {minimumFractionDigits: 2})}</small>
                </td>
                <td>
                    ${cliente.fecha_ultima_compra ? new Date(cliente.fsecha_ultima_compra).toLocaleDateString() : 'N/A'}<br>
                    <small>$${Number(cliente.monto_ultima_compra || 0).toLocaleString('es-MX', {minimumFractionDigits: 2})}</small>
                </td>
                <td class="text-center">
                    <a href="/reportes/ventas/montos-promedio-compra/detalle/${cliente.id_Cliente}" class="btn btn-info btn-sm">
                        <i class="bi bi-receipt"></i> Ver Detalle
                    </a>
                </td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    document.getElementById('resultadosContainer').innerHTML = html;
}
    
    window.exportarReporte = function(tipo) {
        const top = document.getElementById('topSelect').value;
        const sortBy = document.getElementById('sortBySelect').value;
        const filtroFecha = document.getElementById('filtroFecha').value;
        
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
            top: top,
            sort_by: sortBy,
            filtro_fecha: filtroFecha,
            fecha_inicio: fechaInicio,
            fecha_fin: fechaFin
        });
        
        if (clienteSeleccionadoId) {
            params.append('search_cliente', clienteSeleccionadoId);
        }
        
        let url;
        if (tipo === 'excel') {
            url = `{{ route("reportes.ventas.montos-promedio-compra.exportar.excel") }}?${params.toString()}`;
        } else {
            url = `{{ route("reportes.ventas.montos-promedio-compra.exportar.pdf") }}?${params.toString()}`;
        }
        
        if (window.mostrarToast) window.mostrarToast(`Generando ${tipo.toUpperCase()}...`, 'warning');
        window.open(url, '_blank');
    };
    
    document.getElementById('btnAplicarFiltros').addEventListener('click', cargarDatos);
</script>
@endpush
@endsection