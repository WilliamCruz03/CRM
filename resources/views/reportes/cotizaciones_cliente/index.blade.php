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
                                        <option value="este_mes" selected>Este mes</option>
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
                    <label>Estado <span class="text-danger">*</span></label>
                    <select class="form-control" id="statusFilter">
                        <option value="todos">Todos</option>
                        <option value="proceso">En proceso</option>
                        <option value="completadas">Completadas</option>
                        <option value="canceladas">Canceladas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Top <span class="text-danger">*</span></label>
                    <select class="form-control" id="topSelect">
                        <option value="10">Top 10</option>
                        <option value="25">Top 25</option>
                        <option value="50">Top 50</option>
                        <option value="100">Top 100</option>
                        <option value="todos" selected>Todos</option>
                    </select>
                </div>
                <div class="col-md-4">
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

    // Mostrar/ocultar fechas personalizadas
    document.getElementById('filtroFecha').addEventListener('change', function() {
        const fechaInicioDiv = document.getElementById('fechaInicioDiv');
        const fechaFinDiv = document.getElementById('fechaFinDiv');
        
        if (this.value === 'personalizado') {
            fechaInicioDiv.style.display = 'block';
            fechaFinDiv.style.display = 'block';
            const hoy = new Date();
            const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
            document.getElementById('fechaInicio').value = inicioMes.toISOString().split('T')[0];
            document.getElementById('fechaFin').value = hoy.toISOString().split('T')[0];
        } else {
            fechaInicioDiv.style.display = 'none';
            fechaFinDiv.style.display = 'none';
        }
    });

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
        const filtroFecha = document.getElementById('filtroFecha').value;
        const statusFilter = document.getElementById('statusFilter').value;
        const top = document.getElementById('topSelect').value;
        
        if (!filtroFecha) {
            if (window.mostrarToast) window.mostrarToast('Seleccione un período de fecha', 'warning');
            return;
        }
        
        let fechaInicio, fechaFin;
        
        if (filtroFecha === 'personalizado') {
            fechaInicio = document.getElementById('fechaInicio').value;
            fechaFin = document.getElementById('fechaFin').value;
            if (!fechaInicio || !fechaFin) {
                if (window.mostrarToast) window.mostrarToast('Seleccione ambas fechas', 'warning');
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
                filtro_fecha: filtroFecha,
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin,
                status_filter: statusFilter,
                top: top
            });
            
            if (clienteSeleccionadoId) {
                params.append('search_cliente', clienteSeleccionadoId);
            }
            
            const response = await fetch(`{{ route("reportes.cotizaciones-cliente.data") }}?${params.toString()}`);
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
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Total Cotizaciones</th>
                            <th>Importe Total</th>
                            <th>Ticket Promedio</th>
                            <th>Última Cotización</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        clientes.forEach((cliente, index) => {
            const nombreCompleto = `${cliente.Nombre} ${cliente.apPaterno} ${cliente.apMaterno || ''}`.trim();
            const urlDetalle = `{{ url("reportes/cotizaciones-cliente/cliente") }}/${cliente.id_Cliente}/detalle?filtro_fecha=${document.getElementById('filtroFecha').value}&status_filter=${document.getElementById('statusFilter').value}`;
            
            html += `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td><strong>${nombreCompleto}</strong></td>
                    <td class="text-center">${Number(cliente.total_cotizaciones).toLocaleString()}</td>
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
        document.getElementById('cliente_id').value = '';
        document.getElementById('clienteSeleccionado').style.display = 'none';
        document.getElementById('buscarCliente').value = '';
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
        const filtroFecha = document.getElementById('filtroFecha').value;
        const statusFilter = document.getElementById('statusFilter').value;
        const top = document.getElementById('topSelect').value;
        
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
            status_filter: statusFilter,
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
</script>
@endpush
@endsection