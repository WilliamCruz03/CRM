@extends('layouts.app')

@section('title', 'Cotizaciones por Cliente')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="bi bi-file-earmark-text"></i> Cotizaciones por Cliente
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
            <!-- Filtros de Fecha -->
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h5 class="card-title">Filtros de Fecha <span class="text-danger">*</span></h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>Rápido:</label>
                                    <select class="form-control" id="filtroFecha">
                                        <option value="">-- Seleccione --</option>
                                        <option value="hoy">Hoy</option>
                                        <option value="esta_semana">Esta semana</option>
                                        <option value="este_mes">Este mes</option>
                                        <option value="este_ano">Este año</option>
                                        <option value="personalizado">Personalizado</option>
                                    </select>
                                </div>
                                <div class="col-md-3" id="fechaInicioDiv" style="display: none;">
                                    <label>Fecha Inicio:</label>
                                    <input type="date" class="form-control" id="fechaInicio">
                                </div>
                                <div class="col-md-3" id="fechaFinDiv" style="display: none;">
                                    <label>Fecha Fin:</label>
                                    <input type="date" class="form-control" id="fechaFin">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
                <div class="col-md-7">
                    <label>Buscar cliente (opcional)</label>
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" class="form-control" id="buscarCliente" 
                            placeholder="Escriba al menos 3 caracteres..."
                            autocomplete="off">
                    </div>
                    <div id="resultadosClientes" class="mt-2" style="display: none;">
                        <div class="card">
                            <div class="card-header bg-light py-2">
                                <small class="fw-bold">Clientes encontrados</small>
                            </div>
                            <div class="list-group list-group-flush" id="listaClientes"></div>
                        </div>
                    </div>
                    <input type="hidden" id="cliente_id" value="">
                    <div id="clienteSeleccionado" style="display: none;" class="mt-2">
                        <div class="alert alert-info d-flex justify-content-between align-items-center mb-0">
                            <div id="clienteInfo">
                                <strong>Cliente seleccionado:</strong> 
                                <span id="clienteNombre"></span>
                            </div>
                            <button type="button" class="btn-close" onclick="limpiarCliente()"></button>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-primary btn-block" id="btnAplicarFiltros">
                        <i class="bi bi-funnel"></i> Aplicar
                    </button>
                </div>
            </div>

            <!-- Loading -->
            <div id="loadingIndicator" class="text-center my-4" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2">Cargando datos...</p>
            </div>

            <!-- Resultados -->
            <div id="resultadosContainer" class="mt-4">
                <div class="alert alert-secondary text-center">
                    <i class="bi bi-funnel"></i> 
                    Seleccione los filtros y presione <strong>"Aplicar"</strong> para ver los resultados.
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let timeoutBusqueda = null;
    let clienteSeleccionadoId = null;
    let clienteSeleccionadoNombre = null;

    function formatearFechaLocal(fecha) {
        const año = fecha.getFullYear();
        const mes = String(fecha.getMonth() + 1).padStart(2, '0');
        const dia = String(fecha.getDate()).padStart(2, '0');
        return `${año}-${mes}-${dia}`;
    }
    
    // Cargar filtros desde la URL SOLO si hay parámetros (cuando se regresa desde detalle)
    function cargarFiltrosDesdeURL() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Solo cargar si hay parámetros en la URL
        if (window.location.search.length === 0) {
            return;
        }
        
        if (urlParams.has('top')) {
            document.getElementById('topSelect').value = urlParams.get('top');
        }
        if (urlParams.has('filtro_fecha')) {
            const filtroFecha = urlParams.get('filtro_fecha');
            document.getElementById('filtroFecha').value = filtroFecha;
            
            if (filtroFecha === 'personalizado') {
                document.getElementById('fechaInicioDiv').style.display = 'block';
                document.getElementById('fechaFinDiv').style.display = 'block';
            }
        }
        if (urlParams.has('fecha_inicio')) {
            document.getElementById('fechaInicio').value = urlParams.get('fecha_inicio');
        }
        if (urlParams.has('fecha_fin')) {
            document.getElementById('fechaFin').value = urlParams.get('fecha_fin');
        }
        if (urlParams.has('search_cliente')) {
            const clienteId = urlParams.get('search_cliente');
            document.getElementById('cliente_id').value = clienteId;
            clienteSeleccionadoId = clienteId;
            document.getElementById('clienteSeleccionado').style.display = 'block';
            // Cargar nombre del cliente
            cargarNombreCliente(clienteId);
        }
    }
    
    function cargarNombreCliente(clienteId) {
        fetch(`/clientes/${clienteId}/edit`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const nombreCompleto = `${data.data.Nombre} ${data.data.apPaterno} ${data.data.apMaterno || ''}`.trim();
                document.getElementById('clienteNombre').innerHTML = nombreCompleto;
                document.getElementById('buscarCliente').value = nombreCompleto;
                clienteSeleccionadoNombre = nombreCompleto;
            }
        })
        .catch(error => console.error('Error:', error));
    }
    
    // Función para obtener fecha inicio/fin según el filtro
    function getFechasByFiltro(filtro) {
        const hoy = new Date();
        let inicio, fin;
        
        switch(filtro) {
            case 'hoy':
                inicio = formatearFechaLocal(hoy);
                fin = formatearFechaLocal(hoy);
                break;
            case 'esta_semana':
                const dia = hoy.getDay();
                const diff = dia === 0 ? 6 : dia - 1;
                const inicioSemana = new Date(hoy);
                inicioSemana.setDate(hoy.getDate() - diff);
                const finSemana = new Date(inicioSemana);
                finSemana.setDate(inicioSemana.getDate() + 6);
                inicio = formatearFechaLocal(inicioSemana);
                fin = formatearFechaLocal(finSemana);
                break;
            case 'este_mes':
                const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
                const finMes = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
                inicio = formatearFechaLocal(inicioMes);
                fin = formatearFechaLocal(finMes);
                break;
            case 'este_ano':
                const inicioAno = new Date(hoy.getFullYear(), 0, 1);
                const finAno = new Date(hoy.getFullYear(), 11, 31);
                inicio = formatearFechaLocal(inicioAno);
                fin = formatearFechaLocal(finAno);
                break;
            default:
                return null;
        }
        
        return { inicio, fin };
    }
    
    // Validar filtros obligatorios
    function validarFiltros() {
        const top = document.getElementById('topSelect').value;
        if (!top) {
            if (window.mostrarToast) window.mostrarToast('Debe seleccionar un Top', 'warning');
            document.getElementById('topSelect').focus();
            return false;
        }
        
        const filtroFecha = document.getElementById('filtroFecha').value;
        if (!filtroFecha) {
            if (window.mostrarToast) window.mostrarToast('Debe seleccionar un período de fecha', 'warning');
            document.getElementById('filtroFecha').focus();
            return false;
        }
        
        return true;
    }
    
    async function cargarDatos() {
        if (!validarFiltros()) return;
        
        const top = document.getElementById('topSelect').value;
        const filtroFecha = document.getElementById('filtroFecha').value;
        
        let fechaInicio, fechaFin;
        
        if (filtroFecha === 'personalizado') {
            fechaInicio = document.getElementById('fechaInicio').value;
            fechaFin = document.getElementById('fechaFin').value;
            if (!fechaInicio || !fechaFin) {
                if (window.mostrarToast) window.mostrarToast('Debe seleccionar ambas fechas para el filtro personalizado', 'warning');
                return;
            }
        } else {
            const fechas = getFechasByFiltro(filtroFecha);
            if (!fechas) return;
            fechaInicio = fechas.inicio;
            fechaFin = fechas.fin;
        }
        
        if (fechaInicio > fechaFin) {
            if (window.mostrarToast) window.mostrarToast('La fecha de inicio no puede ser mayor a la fecha de fin', 'danger');
            return;
        }
        
        // Actualizar URL sin recargar
        const url = new URL(window.location.href);
        url.searchParams.set('top', top);
        url.searchParams.set('filtro_fecha', filtroFecha);
        url.searchParams.set('fecha_inicio', fechaInicio);
        url.searchParams.set('fecha_fin', fechaFin);
        if (clienteSeleccionadoId) {
            url.searchParams.set('search_cliente', clienteSeleccionadoId);
        } else {
            url.searchParams.delete('search_cliente');
        }
        window.history.pushState({}, '', url);
        
        document.getElementById('loadingIndicator').style.display = 'block';
        document.getElementById('resultadosContainer').innerHTML = '';
        document.getElementById('botonesExportacion').style.display = 'none';
        
        try {
            const params = new URLSearchParams({
                top: top,
                filtro_fecha: filtroFecha,
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin
            });
            
            if (clienteSeleccionadoId) {
                params.append('search_cliente', clienteSeleccionadoId);
            }
            
            const response = await fetch(`{{ route("reportes.cotizaciones-cliente.data") }}?${params.toString()}`, {
                headers: { 'Accept': 'application/json' }
            });
            
            const data = await response.json();
            
            if (data.success && data.data && data.data.length > 0) {
                mostrarResultados(data);
                document.getElementById('botonesExportacion').style.display = 'inline-flex';
            } else {
                document.getElementById('resultadosContainer').innerHTML = `
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle"></i> 
                        No se encontraron cotizaciones en el período seleccionado.
                    </div>
                `;
                document.getElementById('botonesExportacion').style.display = 'none';
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('resultadosContainer').innerHTML = `
                <div class="alert alert-danger text-center">
                    <i class="bi bi-exclamation-triangle"></i> 
                    Error al cargar los datos: ${error.message}
                </div>
            `;
            document.getElementById('botonesExportacion').style.display = 'none';
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
                            <th>#</th>
                            <th>Cliente</th>
                            <th class="text-center">Total Cotizaciones</th>
                            <th class="text-center">En proceso</th>
                            <th class="text-center">Completadas</th>
                            <th class="text-center">Canceladas</th>
                            <th class="text-right">Importe Total</th>
                            <th class="text-right">Ticket Promedio</th>
                            <th class="text-center">Última Cotización</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        clientes.forEach((cliente, index) => {
            const nombreCompleto = `${cliente.Nombre} ${cliente.apPaterno} ${cliente.apMaterno || ''}`.trim();
            const urlDetalle = `/reportes/cotizaciones-cliente/cliente/${cliente.id_Cliente}/detalle?filtro_fecha=${document.getElementById('filtroFecha').value}&fecha_inicio=${data.filtros.fecha_inicio}&fecha_fin=${data.filtros.fecha_fin}&top=${document.getElementById('topSelect').value}`;
            
            const enProcesoBadge = cliente.en_proceso > 0 ? `<span class="badge bg-warning">${cliente.en_proceso}</span>` : '<span class="text-muted">0</span>';
            const completadasBadge = cliente.completadas > 0 ? `<span class="badge bg-success">${cliente.completadas}</span>` : '<span class="text-muted">0</span>';
            const canceladasBadge = cliente.canceladas > 0 ? `<span class="badge bg-danger">${cliente.canceladas}</span>` : '<span class="text-muted">0</span>';
            
            html += `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td><strong>${nombreCompleto}</strong></td>
                    <td class="text-center"><span class="badge bg-secondary">${cliente.total_cotizaciones}</span></td>
                    <td class="text-center">${enProcesoBadge}</td>
                    <td class="text-center">${completadasBadge}</td>
                    <td class="text-center">${canceladasBadge}</td>
                    <td class="text-right">$${Number(cliente.importe_total).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                    <td class="text-right">$${Number(cliente.ticket_promedio).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                    <td class="text-center">${cliente.ultima_cotizacion ? new Date(cliente.ultima_cotizacion).toLocaleDateString() : '-'}</td>
                    <td class="text-center">
                        <a href="${urlDetalle}" class="btn btn-info btn-sm">
                            <i class="bi bi-eye"></i> Ver Detalle
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
    
    // Buscar clientes
    function buscarClientes(termino) {
        if (timeoutBusqueda) clearTimeout(timeoutBusqueda);
        
        if (!termino || termino.length < 3) {
            document.getElementById('resultadosClientes').style.display = 'none';
            return;
        }
        
        timeoutBusqueda = setTimeout(() => {
            fetch(`{{ route("reportes.compras_cliente.buscar-clientes") }}?q=${encodeURIComponent(termino)}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                const resultadosDiv = document.getElementById('resultadosClientes');
                const listaResultados = document.getElementById('listaClientes');
                
                if (data.success && data.data && data.data.length > 0) {
                    listaResultados.innerHTML = data.data.map(cliente => `
                        <div class="list-group-item list-group-item-action" style="cursor: pointer;" onclick="seleccionarCliente(${cliente.id}, '${escapeHtml(cliente.nombre_completo).replace(/'/g, "\\'")}')">
                            <div>
                                <strong>${escapeHtml(cliente.nombre_completo)}</strong>
                                <br>
                                <small class="text-muted">ID: ${cliente.id}</small>
                            </div>
                        </div>
                    `).join('');
                    resultadosDiv.style.display = 'block';
                } else {
                    listaResultados.innerHTML = '<div class="list-group-item text-muted">No se encontraron clientes</div>';
                    resultadosDiv.style.display = 'block';
                }
            })
            .catch(error => console.error('Error:', error));
        }, 300);
    }
    
    window.seleccionarCliente = function(id, nombre) {
        clienteSeleccionadoId = id;
        clienteSeleccionadoNombre = nombre;
        document.getElementById('cliente_id').value = id;
        document.getElementById('clienteNombre').innerHTML = nombre;
        document.getElementById('clienteSeleccionado').style.display = 'block';
        document.getElementById('resultadosClientes').style.display = 'none';
        document.getElementById('buscarCliente').value = nombre;
        
        if (window.mostrarToast) {
            window.mostrarToast(`Cliente "${nombre}" seleccionado. Aplique filtros para ver sus datos.`, 'success');
        }
    };
    
    window.limpiarCliente = function() {
        clienteSeleccionadoId = null;
        clienteSeleccionadoNombre = null;
        document.getElementById('cliente_id').value = '';
        document.getElementById('clienteSeleccionado').style.display = 'none';
        document.getElementById('buscarCliente').value = '';
        
        // Limpiar también de la URL
        const url = new URL(window.location.href);
        url.searchParams.delete('search_cliente');
        window.history.pushState({}, '', url);
    };
    
    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }
    
    // Mostrar/ocultar fechas personalizadas
    document.getElementById('filtroFecha').addEventListener('change', function() {
        const fechaInicioDiv = document.getElementById('fechaInicioDiv');
        const fechaFinDiv = document.getElementById('fechaFinDiv');
        
        if (this.value === 'personalizado') {
            fechaInicioDiv.style.display = 'block';
            fechaFinDiv.style.display = 'block';
            const hoy = new Date();
            const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
            // Usar formatearFechaLocal en lugar de toISOString
            document.getElementById('fechaInicio').value = formatearFechaLocal(inicioMes);
            document.getElementById('fechaFin').value = formatearFechaLocal(hoy);
        } else {
            fechaInicioDiv.style.display = 'none';
            fechaFinDiv.style.display = 'none';
        }
    });
    
    // Eventos
    document.getElementById('btnAplicarFiltros').addEventListener('click', cargarDatos);
    document.getElementById('buscarCliente').addEventListener('keyup', function(e) {
        buscarClientes(this.value);
    });
    
    // Cerrar resultados al hacer clic fuera
    document.addEventListener('click', function(e) {
        const resultadosDiv = document.getElementById('resultadosClientes');
        const searchBox = document.getElementById('buscarCliente');
        if (resultadosDiv && searchBox && !searchBox.contains(e.target) && !resultadosDiv.contains(e.target)) {
            resultadosDiv.style.display = 'none';
        }
    });
    
    // Exportar
    window.exportarReporte = function(tipo) {
        const top = document.getElementById('topSelect').value;
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
            filtro_fecha: filtroFecha,
            fecha_inicio: fechaInicio,
            fecha_fin: fechaFin,
            top: top
        });
        
        if (clienteSeleccionadoId) {
            params.append('search_cliente', clienteSeleccionadoId);
        }
        
        let url;
        if (tipo === 'excel') {
            url = `{{ route("reportes.cotizaciones-cliente.exportar.excel") }}?${params.toString()}`;
        } else {
            url = `{{ route("reportes.cotizaciones-cliente.exportar.pdf") }}?${params.toString()}`;
        }
        
        if (window.mostrarToast) window.mostrarToast(`Generando ${tipo.toUpperCase()}...`, 'warning');
        window.open(url, '_blank');
    };
    
    // Inicialización
    document.addEventListener('DOMContentLoaded', function() {
        cargarFiltrosDesdeURL();
        
        // Si hay parámetros en la URL (regreso desde detalle), cargar datos automáticamente
        if (window.location.search.length > 0) {
            // Verificar que los selects tengan valores
            if (document.getElementById('topSelect').value && document.getElementById('filtroFecha').value) {
                cargarDatos();
            }
        }
    });
</script>
@endpush
@endsection