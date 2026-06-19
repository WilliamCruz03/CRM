@extends('layouts.app')

@section('title', 'Reporte de Clientes')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Reporte de Compras por Clientes</h3>
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
            <!-- Filtros -->
            <div class="row">
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
                        <option value="monto_total">Mayor Monto</option>
                        <option value="monto_total_asc">Menor Monto</option>
                        <option value="total_transacciones">Más Compras</option>
                        <option value="total_transacciones_asc">Menos Compras</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Buscar cliente (opcional)</label>
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" class="form-control" id="buscarClienteReporte" 
                            placeholder="Escriba al menos 3 caracteres..."
                            value="{{ $searchCliente ?? '' }}"
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
                <div class="col-md-3">
                    <label>Indicación Terapéutica (opcional)</label>
                    <select class="form-control" id="indicacionSelect">
                        <option value="">-- Todas --</option>
                        @foreach($indicaciones as $indicacion)
                            <option value="{{ $indicacion->id }}">{{ $indicacion->IndicacionTerapeutica }}</option>
                        @endforeach
                    </select>
                </div>

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
                                    <div class="col-md-3">
                                        <label>&nbsp;</label>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-primary flex-grow-1" id="btnAplicarFiltros">
                                                <i class="bi bi-funnel"></i> Aplicar
                                            </button>
                                            <button type="button" class="btn btn-secondary" id="btnLimpiarFiltros">
                                                <i class="bi bi-eraser"></i> Limpiar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
            <div id="resultadosContainer">
                <div class="alert alert-secondary text-center">
                    <i class="bi bi-funnel"></i> 
                    Seleccione los filtros (Top, Ordenar y Fecha) y presione <strong>"Aplicar Filtros"</strong> para ver los resultados.
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
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
    
    // Cargar filtros desde la URL al iniciar la página
    function cargarFiltrosDesdeURL() {
        const urlParams = new URLSearchParams(window.location.search);
        
        if (urlParams.has('top')) {
            document.getElementById('topSelect').value = urlParams.get('top');
        }
        if (urlParams.has('sort_by')) {
            document.getElementById('sortBySelect').value = urlParams.get('sort_by');
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
            
            // Cargar el nombre del cliente y mostrarlo
            fetch(`/clientes/${clienteId}/edit`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const nombreCompleto = `${data.data.Nombre} ${data.data.apPaterno} ${data.data.apMaterno || ''}`.trim();
                    document.getElementById('clienteNombre').innerHTML = nombreCompleto;
                    document.getElementById('buscarClienteReporte').value = nombreCompleto;
                    document.getElementById('clienteSeleccionado').style.display = 'block';
                    clienteSeleccionadoId = clienteId;
                    clienteSeleccionadoNombre = nombreCompleto;
                }
            })
            .catch(error => console.error('Error al cargar cliente:', error));
        }
        if (urlParams.has('indicacion_id')) {
            document.getElementById('indicacionSelect').value = urlParams.get('indicacion_id');
        }
    }

// Ejecutar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    cargarFiltrosDesdeURL();
    
    // Si hay parámetros en la URL, cargar datos automáticamente
    if (window.location.search.length > 0) {
        cargarDatos();
    }
});

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
        
        const sortBy = document.getElementById('sortBySelect').value;
        if (!sortBy) {
            if (window.mostrarToast) window.mostrarToast('Debe seleccionar un ordenamiento', 'warning');
            document.getElementById('sortBySelect').focus();
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
    
    // Cargar datos vía AJAX
    async function cargarDatos() {
        if (!validarFiltros()) return;
        
        const top = document.getElementById('topSelect').value;
        const sortBy = document.getElementById('sortBySelect').value;
        const filtroFecha = document.getElementById('filtroFecha').value;
        const indicacionId = document.getElementById('indicacionSelect').value;
        const clienteId = document.getElementById('cliente_id').value;
        
        let fechaInicio, fechaFin;
        
        // Obtener fechas según el filtro
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
        
        // Mostrar loading
        document.getElementById('loadingIndicator').style.display = 'block';
        document.getElementById('resultadosContainer').innerHTML = '';
        document.getElementById('botonesExportacion').style.display = 'none';
        
        try {
            const params = new URLSearchParams({
                top: top,
                sort_by: sortBy,
                filtro_fecha: filtroFecha,
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin
            });

            if (indicacionId) {
                params.append('indicacion_id', indicacionId);
            }
            
            if (clienteId && clienteId !== '' && clienteId !== 'null' && clienteId !== 'undefined') {
                params.append('search_cliente', clienteId);
            }
            
            const response = await fetch(`{{ route("reportes.compras_cliente.clientes.data") }}?${params.toString()}`, {
                headers: { 'Accept': 'application/json' }
            });
            
            // Verificar si la respuesta es JSON o HTML (error)
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Respuesta no es JSON:', text.substring(0, 500));
                
                // Detectar errores comunes
                if (text.includes('SQLSTATE') && text.includes('Invalid object name')) {
                    const match = text.match(/Invalid object name '([^']+)'/);
                    const tablaFaltante = match ? match[1] : 'desconocida';
                    window.mostrarToast(`Error de base de datos: Tabla '${tablaFaltante}' no existe`, 'danger');
                } else if (text.includes('SQLSTATE') && text.includes('Invalid column name')) {
                    const match = text.match(/Invalid column name '([^']+)'/);
                    const columnaFaltante = match ? match[1] : 'desconocida';
                    window.mostrarToast(`Error de base de datos: Columna '${columnaFaltante}' no existe`, 'danger');
                } else if (text.includes('Connection refused') || text.includes('could not find driver')) {
                    window.mostrarToast('Error de conexión a la base de datos', 'danger');
                } else if (response.status === 500) {
                    window.mostrarToast('Error interno del servidor (500). Verifique los logs.', 'danger');
                } else {
                    window.mostrarToast(`Error del servidor (${response.status})`, 'danger');
                }
                
                document.getElementById('resultadosContainer').innerHTML = `
                    <div class="alert alert-danger text-center">
                        <i class="bi bi-exclamation-triangle"></i> 
                        <strong>Error al cargar los datos</strong><br>
                        <small class="text-muted">El servidor respondió con un error. Verifique la conexión a la base de datos.</small>
                        <br><br>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="location.reload()">
                            <i class="bi bi-arrow-repeat"></i> Reintentar
                        </button>
                    </div>
                `;
                document.getElementById('botonesExportacion').style.display = 'none';
                return;
            }
            
            const data = await response.json();
            
            if (!data.success) {
                // Si el servidor devolvió success: false
                let mensajeError = data.message || 'Error desconocido';
                
                if (mensajeError.includes('SQLSTATE') && mensajeError.includes('Invalid object name')) {
                    const match = mensajeError.match(/Invalid object name '([^']+)'/);
                    const tabla = match ? match[1] : 'desconocida';
                    window.mostrarToast(`Tabla '${tabla}' no encontrada en la base de datos`, 'danger');
                } else if (mensajeError.includes('SQLSTATE') && mensajeError.includes('Invalid column name')) {
                    const match = mensajeError.match(/Invalid column name '([^']+)'/);
                    const columna = match ? match[1] : 'desconocida';
                    window.mostrarToast(`Columna '${columna}' no existe`, 'danger');
                } else {
                    window.mostrarToast(`Error: ${mensajeError}`, 'danger');
                }
                
                document.getElementById('resultadosContainer').innerHTML = `
                    <div class="alert alert-danger text-center">
                        <i class="bi bi-exclamation-triangle"></i> 
                        Error: ${mensajeError}
                    </div>
                `;
                document.getElementById('botonesExportacion').style.display = 'none';
                return;
            }
            
            if (data.data && data.data.length > 0) {
                mostrarResultados(data);
                document.getElementById('botonesExportacion').style.display = 'inline-flex';
            } else {
                document.getElementById('resultadosContainer').innerHTML = `
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle"></i> 
                        No se encontraron ventas en el período seleccionado.
                    </div>
                `;
                document.getElementById('botonesExportacion').style.display = 'none';
            }
        } catch (error) {
            console.error('Error:', error);
            
            let mensajeUsuario = 'Error de conexión';
            if (error.message.includes('Failed to fetch')) {
                mensajeUsuario = 'No se pudo conectar al servidor. Verifique que el servidor esté funcionando.';
            } else if (error.message.includes('NetworkError')) {
                mensajeUsuario = 'Error de red. Verifique su conexión a internet.';
            } else if (error.message.includes('Unexpected token')) {
                mensajeUsuario = 'El servidor respondió con un error. Posible problema en la base de datos.';
            } else {
                mensajeUsuario = error.message;
            }
            
            window.mostrarToast(`${mensajeUsuario}`, 'danger');
            
            document.getElementById('resultadosContainer').innerHTML = `
                <div class="alert alert-danger text-center">
                    <i class="bi bi-exclamation-triangle"></i> 
                    <strong>Error de conexión</strong><br>
                    <small>${mensajeUsuario}</small>
                    <br><br>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="location.reload()">
                        <i class="bi bi-arrow-repeat"></i> Reintentar
                    </button>
                </div>
            `;
            document.getElementById('botonesExportacion').style.display = 'none';
        } finally {
            document.getElementById('loadingIndicator').style.display = 'none';
        }
    }
    
    // Mostrar resultados en la tabla
    function mostrarResultados(data) {
        const clientes = data.data;
        
        // Obtener los filtros actuales
        const top = document.getElementById('topSelect').value;
        const sortBy = document.getElementById('sortBySelect').value;
        const filtroFecha = document.getElementById('filtroFecha').value;
        const indicacionId = document.getElementById('indicacionSelect').value;
        const clienteSeleccionadoId = document.getElementById('cliente_id')?.value || '';
        
        // Usar las fechas que vienen del backend (ya son strings)
        const fechaInicio = data.filtros.fecha_inicio;
        const fechaFin = data.filtros.fecha_fin;
        
        // Si no vienen del backend, usar las de los inputs
        let fechaInicioStr = fechaInicio || document.getElementById('fechaInicio').value || '';
        let fechaFinStr = fechaFin || document.getElementById('fechaFin').value || '';
        
        // Si es personalizado y no hay fechas en data.filtros, usar las de los inputs
        if (filtroFecha === 'personalizado') {
            fechaInicioStr = document.getElementById('fechaInicio').value || '';
            fechaFinStr = document.getElementById('fechaFin').value || '';
        }
        
        // SI las fechas vienen como objetos Date, convertirlas a string Y-m-d
        if (fechaInicioStr instanceof Date) {
            fechaInicioStr = fechaInicioStr.toISOString().split('T')[0];
        }
        if (fechaFinStr instanceof Date) {
            fechaFinStr = fechaFinStr.toISOString().split('T')[0];
        }
        
        let html = `
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i> 
                Mostrando <strong>${clientes.length}</strong> clientes
                <br><small>Período: ${fechaInicioStr} al ${fechaFinStr}</small>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Ventas Totales</th>
                            <th>Monto Total</th>
                            <th>Ticket Promedio</th>
                            <th>Última Compra</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        clientes.forEach(cliente => {
            // Construir URL con todos los parámetros
            let url = `/reportes/ventas/cliente/${cliente.id_Cliente}?top=${top}&sort_by=${sortBy}&filtro_fecha=${filtroFecha}&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
            
            // Agregar search_cliente para mantener el filtro al regresar
            if (clienteSeleccionadoId) {
                url += `&search_cliente=${clienteSeleccionadoId}`;
            }
            
            if (indicacionId) {
                url += `&indicacion_id=${indicacionId}`;
            }
            
            html += `
                <tr>
                    <td>${cliente.id_Cliente}</td>
                    <td>${cliente.Nombre} ${cliente.apPaterno} ${cliente.apMaterno || ''}</td>
                    <td style="text-align: center">${Number(cliente.total_transacciones).toLocaleString()}</td>
                    <td style="text-align: right">$${Number(cliente.monto_total).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                    <td style="text-align: right">$${Number(cliente.ticket_promedio).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                    <td style="text-align: center">${cliente.ultima_compra || 'N/A'}</td>
                    <td style="text-align: center">
                        <a href="${url}" class="btn btn-info btn-sm">
                            <i class="bi bi-pie-chart"></i> Ver Detalle
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
    
    // Buscar clientes (AJAX)
    function buscarClientesReporte(termino) {
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
                        <div class="list-group-item list-group-item-action" style="cursor: pointer;" onclick="seleccionarClienteReporte(${cliente.id}, '${escapeHtml(cliente.nombre_completo).replace(/'/g, "\\'")}')">
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
    
    // Seleccionar cliente
    window.seleccionarClienteReporte = function(id, nombre) {
        clienteSeleccionadoId = id;
        clienteSeleccionadoNombre = nombre;
        document.getElementById('cliente_id').value = id;
        document.getElementById('clienteNombre').innerHTML = nombre;
        document.getElementById('clienteSeleccionado').style.display = 'block';
        document.getElementById('resultadosClientes').style.display = 'none';
        document.getElementById('buscarClienteReporte').value = nombre;
        
        if (window.mostrarToast) {
            window.mostrarToast(`Cliente "${nombre}" seleccionado. Aplique filtros para ver sus datos.`, 'success');
        }
    };
    
    // Limpiar cliente
    window.limpiarCliente = function() {
        clienteSeleccionadoId = null;
        clienteSeleccionadoNombre = null;
        document.getElementById('cliente_id').value = '';
        document.getElementById('clienteSeleccionado').style.display = 'none';
        document.getElementById('buscarClienteReporte').value = '';
    };
    
    // Limpiar todos los filtros
    function limpiarFiltros() {
        // Limpiar selects
        document.getElementById('topSelect').value = '';
        document.getElementById('sortBySelect').value = '';
        document.getElementById('filtroFecha').value = '';
        document.getElementById('indicacionSelect').value = '';
        
        // Limpiar fechas personalizadas
        document.getElementById('fechaInicio').value = '';
        document.getElementById('fechaFin').value = '';
        
        // Ocultar campos de fechas personalizadas
        document.getElementById('fechaInicioDiv').style.display = 'none';
        document.getElementById('fechaFinDiv').style.display = 'none';
        
        // Limpiar cliente seleccionado
        if (typeof limpiarCliente === 'function') {
            limpiarCliente();
        } else {
            clienteSeleccionadoId = null;
            clienteSeleccionadoNombre = null;
            document.getElementById('cliente_id').value = '';
            document.getElementById('clienteSeleccionado').style.display = 'none';
            document.getElementById('buscarClienteReporte').value = '';
        }
        
        // Limpiar URL (remover parámetros)
        const url = new URL(window.location.href);
        url.search = '';
        window.history.pushState({}, '', url);
        
        // Mostrar mensaje inicial
        document.getElementById('resultadosContainer').innerHTML = `
            <div class="alert alert-secondary text-center">
                <i class="bi bi-funnel"></i> 
                Seleccione los filtros (Top, Ordenar y Fecha) y presione <strong>"Aplicar Filtros"</strong> para ver los resultados.
            </div>
        `;
        
        // Ocultar botones de exportación
        document.getElementById('botonesExportacion').style.display = 'none';
        
        // Mostrar toast de confirmación
        if (window.mostrarToast) {
            window.mostrarToast('Filtros limpiados correctamente', 'success');
        }
    }

    // Exportar reporte
    window.exportarReporte = function(tipo) {
        // Obtener todos los filtros actuales
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
        
        // AGREGAR indicacionId si existe
        if (indicacionId) {
            params.append('indicacion_id', indicacionId);
        }
        
        // AGREGAR clienteId si existe
        if (clienteId) {
            params.append('search_cliente', clienteId);
        }
        
        let url;
        if (tipo === 'excel') {
            url = `{{ route("reportes.compras_cliente.exportar.excel") }}?${params.toString()}`;
        } else {
            url = `{{ route("reportes.compras_cliente.exportar.pdf") }}?${params.toString()}`;
        }
        
        if (window.mostrarToast) window.mostrarToast(`Generando ${tipo.toUpperCase()}...`, 'warning');
        window.open(url, '_blank');
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
    document.getElementById('btnLimpiarFiltros').addEventListener('click', limpiarFiltros);
    document.getElementById('buscarClienteReporte').addEventListener('keyup', function(e) {
        buscarClientesReporte(this.value);
    });
    
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
    
    // Cerrar resultados al hacer clic fuera
    document.addEventListener('click', function(e) {
        const resultadosDiv = document.getElementById('resultadosClientes');
        const searchBox = document.getElementById('buscarClienteReporte');
        if (resultadosDiv && searchBox && !searchBox.contains(e.target) && !resultadosDiv.contains(e.target)) {
            resultadosDiv.style.display = 'none';
        }
    });
</script>
@endpush
@endsection