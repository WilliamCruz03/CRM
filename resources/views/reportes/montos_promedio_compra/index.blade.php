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
                        <option value="monto_promedio">Mayor Promedio</option>
                        <option value="monto_promedio_asc">Menor Promedio</option>
                        <option value="total_compras">Más Compras</option>
                        <option value="total_compras_asc">Menos Compras</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Buscar cliente (opcional)</label>
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" class="form-control" id="buscarCliente" 
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
    
    // ============================================
    // Cargar filtros desde la URL al iniciar la página
    // ============================================
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
        
        // Cargar cliente desde URL
        if (urlParams.has('search_cliente')) {
            const clienteId = urlParams.get('search_cliente');
            document.getElementById('cliente_id').value = clienteId;
            
            // Cargar el nombre del cliente y mostrarlo
            fetch(`/clientes/${clienteId}/edit`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    console.warn('Respuesta no es JSON, redirigiendo al login...');
                    if (response.status === 401 || response.status === 403) {
                        window.location.href = '/login';
                    }
                    throw new Error('La respuesta no es JSON');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const nombreCompleto = `${data.data.Nombre} ${data.data.apPaterno} ${data.data.apMaterno || ''}`.trim();
                    document.getElementById('clienteNombre').innerHTML = nombreCompleto;
                    document.getElementById('buscarCliente').value = nombreCompleto;
                    document.getElementById('clienteSeleccionado').style.display = 'block';
                    clienteSeleccionadoId = clienteId;
                    clienteSeleccionadoNombre = nombreCompleto;
                }
            })
            .catch(error => {
                console.error('Error al cargar cliente:', error);
                document.getElementById('cliente_id').value = '';
                document.getElementById('clienteSeleccionado').style.display = 'none';
                document.getElementById('buscarCliente').value = '';
            });
        }
    }

    // Llamar la función al cargar la página
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
            case 'hoy':  // Para los filtros que lo usen.
                inicio = formatearFechaLocal(hoy);
                fin = formatearFechaLocal(hoy);
                break;
            case 'esta_semana':
                const dia = hoy.getDay(); // 0=Domingo, 1=Lunes...
                const diff = dia === 0 ? 6 : dia - 1; // Lunes como inicio
                const inicioSemana = new Date(hoy);
                inicioSemana.setDate(hoy.getDate() - diff);
                // FIN = HOY
                inicio = formatearFechaLocal(inicioSemana);
                fin = formatearFechaLocal(hoy);
                break;
            case 'este_mes':
                const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
                // FIN = HOY
                inicio = formatearFechaLocal(inicioMes);
                fin = formatearFechaLocal(hoy);
                break;
            case 'este_ano':
                const inicioAno = new Date(hoy.getFullYear(), 0, 1);
                // FIN = HOY
                inicio = formatearFechaLocal(inicioAno);
                fin = formatearFechaLocal(hoy);
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
    
    // ============================================
    // CARGAR DATOS VÍA AJAX
    // ============================================
    async function cargarDatos() {
        if (!validarFiltros()) return;
        
        const top = document.getElementById('topSelect').value;
        const sortBy = document.getElementById('sortBySelect').value;
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
        
        // Leer cliente_id del input oculto
        const clienteIdInput = document.getElementById('cliente_id');
        const clienteId = clienteIdInput ? clienteIdInput.value : null;
        
        // Actualizar URL
        const url = new URL(window.location.href);
        url.searchParams.set('top', top);
        url.searchParams.set('sort_by', sortBy);
        url.searchParams.set('filtro_fecha', filtroFecha);
        url.searchParams.set('fecha_inicio', fechaInicio);
        url.searchParams.set('fecha_fin', fechaFin);
        
        if (clienteId && clienteId !== '' && clienteId !== 'null' && clienteId !== 'undefined') {
            url.searchParams.set('search_cliente', clienteId);
        } else {
            url.searchParams.delete('search_cliente');
        }
        window.history.pushState({}, '', url);
        
        // Mostrar loading
        const loadingIndicator = document.getElementById('loadingIndicator');
        const resultadosContainer = document.getElementById('resultadosContainer');
        const botonesExportacion = document.getElementById('botonesExportacion');
        
        if (loadingIndicator) loadingIndicator.style.display = 'block';
        if (resultadosContainer) resultadosContainer.innerHTML = '';
        if (botonesExportacion) botonesExportacion.style.display = 'none';
        
        try {
            const params = new URLSearchParams({
                top: top,
                sort_by: sortBy,
                filtro_fecha: filtroFecha,
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin
            });
            
            // Usar clienteId del input
            if (clienteId && clienteId !== '' && clienteId !== 'null' && clienteId !== 'undefined') {
                params.append('search_cliente', clienteId);
            }
            
            const response = await fetch(`{{ route("reportes.compras_cliente.montos-promedio-compra.data") }}?${params.toString()}`, {
                headers: { 'Accept': 'application/json' }
            });
            
            // ============================================
            // MANEJAR 401 - SESIÓN EXPIRADA
            // ============================================
            if (response.status === 401) {
                if (window.mostrarToast) {
                    window.mostrarToast('Tu sesión ha expirado. Redirigiendo al login...', 'warning');
                }
                // Redirigir al login después de 1.5 segundos
                setTimeout(() => {
                    window.location.href = '/login';
                }, 1500);
                if (loadingIndicator) loadingIndicator.style.display = 'none';
                return;
            }
            
            // ============================================
            // MANEJAR 403 - USUARIO DESACTIVADO
            // ============================================
            if (response.status === 403) {
                if (window.mostrarToast) {
                    window.mostrarToast('Tu cuenta ha sido desactivada. Contacta al administrador.', 'danger');
                }
                setTimeout(() => {
                    window.location.href = '/login';
                }, 1500);
                if (loadingIndicator) loadingIndicator.style.display = 'none';
                return;
            }
            
            // ============================================
            // VERIFICAR CONTENT-TYPE
            // ============================================
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Respuesta no es JSON:', text.substring(0, 500));
                
                // Si la respuesta es HTML (redirección al login)
                if (text.includes('<!DOCTYPE html>') || text.includes('<html')) {
                    if (window.mostrarToast) {
                        window.mostrarToast('Tu sesión ha expirado. Redirigiendo al login...', 'warning');
                    }
                    setTimeout(() => {
                        window.location.href = '/login';
                    }, 1500);
                    if (loadingIndicator) loadingIndicator.style.display = 'none';
                    return;
                }
                
                if (window.mostrarToast) {
                    if (text.includes('Invalid object name')) {
                        window.mostrarToast('Error: Tabla no encontrada en la base de datos', 'danger');
                    } else if (text.includes('Invalid column name')) {
                        window.mostrarToast('Error: Columna no encontrada', 'danger');
                    } else {
                        window.mostrarToast('Error del servidor', 'danger');
                    }
                }
                
                if (resultadosContainer) {
                    resultadosContainer.innerHTML = `
                        <div class="alert alert-danger text-center">
                            <i class="bi bi-exclamation-triangle"></i> 
                            Error al cargar los datos. Verifique la conexión a la base de datos.
                        </div>
                    `;
                }
                if (botonesExportacion) botonesExportacion.style.display = 'none';
                if (loadingIndicator) loadingIndicator.style.display = 'none';
                return;
            }
            
            // ============================================
            // PROCESAR RESPUESTA JSON
            // ============================================
            const data = await response.json();
            
            if (loadingIndicator) loadingIndicator.style.display = 'none';
            
            if (data.success && data.data && data.data.length > 0) {
                mostrarResultados(data);
                if (botonesExportacion) botonesExportacion.style.display = 'inline-flex';
            } else if (data.success && (!data.data || data.data.length === 0)) {
                if (resultadosContainer) {
                    resultadosContainer.innerHTML = `
                        <div class="alert alert-info text-center">
                            <i class="bi bi-info-circle"></i> 
                            No se encontraron clientes en el período seleccionado.
                        </div>
                    `;
                }
                if (botonesExportacion) botonesExportacion.style.display = 'none';
            } else {
                let mensajeError = data.message || 'Error desconocido';
                if (window.mostrarToast) window.mostrarToast(`Error: ${mensajeError}`, 'danger');
                if (resultadosContainer) {
                    resultadosContainer.innerHTML = `
                        <div class="alert alert-danger text-center">
                            <i class="bi bi-exclamation-triangle"></i> 
                            ${mensajeError}
                        </div>
                    `;
                }
                if (botonesExportacion) botonesExportacion.style.display = 'none';
            }
        } catch (error) {
            if (loadingIndicator) loadingIndicator.style.display = 'none';
            console.error('Error:', error);
            
            // Verificar si el error es por redirección o sesión expirada
            if (error.message.includes('Unexpected token') || error.message.includes('JSON')) {
                if (window.mostrarToast) {
                    window.mostrarToast('Tu sesión ha expirado. Redirigiendo al login...', 'warning');
                }
                setTimeout(() => {
                    window.location.href = '/login';
                }, 1500);
                return;
            }
            
            let mensajeUsuario = 'Error de conexión';
            if (error.message.includes('Failed to fetch')) {
                mensajeUsuario = 'No se pudo conectar al servidor. Verifique que el servidor esté funcionando.';
            } else if (error.message.includes('NetworkError')) {
                mensajeUsuario = 'Error de red. Verifique su conexión a internet.';
            } else {
                mensajeUsuario = error.message;
            }
            
            if (window.mostrarToast) window.mostrarToast(mensajeUsuario, 'danger');
            
            if (resultadosContainer) {
                resultadosContainer.innerHTML = `
                    <div class="alert alert-danger text-center">
                        <i class="bi bi-exclamation-triangle"></i> 
                        <strong>Error de conexión</strong><br>
                        <small>${mensajeUsuario}</small>
                    </div>
                `;
            }
            if (botonesExportacion) botonesExportacion.style.display = 'none';
        } finally {
            if (loadingIndicator) loadingIndicator.style.display = 'none';
        }
    }

    // Mostrar resultados en la tabla
    function mostrarResultados(data) {
        const clientes = data.data;
        const top = document.getElementById('topSelect').value;
        const sortBy = document.getElementById('sortBySelect').value;
        const filtroFecha = document.getElementById('filtroFecha').value;
        const fechaInicio = data.filtros.fecha_inicio;
        const fechaFin = data.filtros.fecha_fin;
        const clienteIdInput = document.getElementById('cliente_id');
        const clienteSeleccionadoId = clienteIdInput ? clienteIdInput.value : '';
        
        // Guardar el estado actual en sessionStorage para restaurar al volver del detalle
        const estado = {
            filtros: {
                top: top,
                sortBy: sortBy,
                filtroFecha: filtroFecha,
                fechaInicio: fechaInicio,
                fechaFin: fechaFin,
                clienteId: clienteSeleccionadoId
            },
            datos: data,
            desdeDetalle: true
        };
        // Clave específica para montos promedio
        sessionStorage.setItem('reporte_montos_promedio_estado', JSON.stringify(estado));
        sessionStorage.getItem('reporte_montos_promedio_estado');
        sessionStorage.removeItem('reporte_montos_promedio_estado');
        
        let html = `
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i> 
                Mostrando <strong>${clientes.length}</strong> clientes
                <br><small>Período: ${fechaInicio} al ${fechaFin}</small>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Total Compras</th>
                            <th>Monto Total</th>
                            <th>Monto Promedio</th>
                            <th>Primera Compra</th>
                            <th>Última Compra</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        clientes.forEach((cliente, index) => {
            const nombreCompleto = `${cliente.Nombre} ${cliente.apPaterno} ${cliente.apMaterno || ''}`.trim();
            
            let url = `/reportes/ventas/montos-promedio-compra/detalle/${cliente.id_Cliente}?top=${top}&sort_by=${sortBy}&filtro_fecha=${filtroFecha}&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
            if (clienteSeleccionadoId) {
                url += `&search_cliente=${clienteSeleccionadoId}`;
            }
            
            html += `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td>${nombreCompleto}</td>
                    <td class="text-center">${Number(cliente.total_compras).toLocaleString('es-MX')}</td>
                    <td class="text-right">$${Number(cliente.monto_total).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                    <td class="text-right">$${Number(cliente.monto_promedio).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                    <td class="text-center">${cliente.fecha_primera_compra ? new Date(cliente.fecha_primera_compra).toLocaleDateString() : '-'}</td>
                    <td class="text-center">${cliente.fecha_ultima_compra ? new Date(cliente.fecha_ultima_compra).toLocaleDateString() : '-'}</td>
                    <td class="text-center">
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
    function buscarClientes(termino) {
        if (timeoutBusqueda) clearTimeout(timeoutBusqueda);
        
        if (!termino || termino.length < 3) {
            const resultadosDiv = document.getElementById('resultadosClientes');
            if (resultadosDiv) resultadosDiv.style.display = 'none';
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
    
    // Seleccionar cliente
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
    
    // Limpiar cliente
    window.limpiarCliente = function() {
        clienteSeleccionadoId = null;
        clienteSeleccionadoNombre = null;
        document.getElementById('cliente_id').value = '';
        document.getElementById('clienteSeleccionado').style.display = 'none';
        document.getElementById('buscarCliente').value = '';
    };
    
    // Limpiar todos los filtros
    function limpiarFiltros() {
        document.getElementById('topSelect').value = '';
        document.getElementById('sortBySelect').value = '';
        document.getElementById('filtroFecha').value = '';
        
        document.getElementById('fechaInicio').value = '';
        document.getElementById('fechaFin').value = '';
        
        document.getElementById('fechaInicioDiv').style.display = 'none';
        document.getElementById('fechaFinDiv').style.display = 'none';
        
        if (typeof limpiarCliente === 'function') {
            limpiarCliente();
        } else {
            clienteSeleccionadoId = null;
            clienteSeleccionadoNombre = null;
            document.getElementById('cliente_id').value = '';
            document.getElementById('clienteSeleccionado').style.display = 'none';
            document.getElementById('buscarCliente').value = '';
        }
        
        // Limpiar sessionStorage
        sessionStorage.removeItem('reporte_montos_promedio_estado');
        
        const url = new URL(window.location.href);
        url.search = '';
        window.history.pushState({}, '', url);
        
        document.getElementById('resultadosContainer').innerHTML = `
            <div class="alert alert-secondary text-center">
                <i class="bi bi-funnel"></i> 
                Seleccione los filtros (Top, Ordenar y Fecha) y presione <strong>"Aplicar Filtros"</strong> para ver los resultados.
            </div>
        `;
        
        document.getElementById('botonesExportacion').style.display = 'none';
        
        if (window.mostrarToast) {
            window.mostrarToast('Filtros limpiados correctamente', 'success');
        }
    }

    // Exportar reporte
    window.exportarReporte = function(tipo) {
        const top = document.getElementById('topSelect').value;
        const sortBy = document.getElementById('sortBySelect').value;
        const filtroFecha = document.getElementById('filtroFecha').value;
        const searchCliente = document.getElementById('buscarCliente')?.value || '';
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
            top: top,
            sort_by: sortBy,
            filtro_fecha: filtroFecha,
            fecha_inicio: fechaInicio,
            fecha_fin: fechaFin
        });
        
        // Agregar search_cliente si existe
        if (clienteId) {
            params.append('search_cliente', clienteId);
        } else if (searchCliente) {
            params.append('search_cliente', searchCliente);
        }
        
        let url;
        if (tipo === 'excel') {
            url = `{{ route("reportes.compras_cliente.montos-promedio-compra.exportar.excel") }}?${params.toString()}`;
        } else {
            url = `{{ route("reportes.compras_cliente.montos-promedio-compra.exportar.pdf") }}?${params.toString()}`;
        }
        
        // Mostrar toast de preparación
        if (window.mostrarToast) {
            window.mostrarToast('Generando archivo... Esto puede tomar varios segundos.', 'warning');
        }
        
        // Abrir en nueva ventana
        const win = window.open(url, '_blank');
        
        if (win) {
            setTimeout(() => {
                if (window.mostrarToast) {
                    window.mostrarToast('El archivo se está generando. Por favor espere...', 'success');
                }
            }, 2000);
        } else {
            if (window.mostrarToast) {
                window.mostrarToast('Descarga iniciada. Por favor espere...', 'info');
            }
            window.location.href = url;
        }
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
    document.getElementById('buscarCliente').addEventListener('keyup', function(e) {
        buscarClientes(this.value);
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
    
    document.addEventListener('DOMContentLoaded', function() {
        // Intentar recuperar estado guardado
        const estadoGuardado = sessionStorage.getItem('reporte_montos_promedio_estado');
        
        if (estadoGuardado) {
            try {
                const estado = JSON.parse(estadoGuardado);
                
                // SOLO restaurar si viene del detalle
                if (estado.desdeDetalle === true) {
                    if (estado.filtros) {
                        const f = estado.filtros;
                        if (f.top) document.getElementById('topSelect').value = f.top;
                        if (f.sortBy) document.getElementById('sortBySelect').value = f.sortBy;
                        if (f.filtroFecha) document.getElementById('filtroFecha').value = f.filtroFecha;
                        if (f.fechaInicio) document.getElementById('fechaInicio').value = f.fechaInicio;
                        if (f.fechaFin) document.getElementById('fechaFin').value = f.fechaFin;
                        if (f.clienteId) {
                            document.getElementById('cliente_id').value = f.clienteId;
                            cargarNombreCliente(f.clienteId);
                        }
                        
                        if (f.filtroFecha === 'personalizado') {
                            document.getElementById('fechaInicioDiv').style.display = 'block';
                            document.getElementById('fechaFinDiv').style.display = 'block';
                        }
                    }
                    
                    if (estado.datos) {
                        mostrarResultados(estado.datos);
                        sessionStorage.removeItem('reporte_montos_promedio_estado');
                        return;
                    }
                } else {
                    sessionStorage.removeItem('reporte_montos_promedio_estado');
                }
            } catch (e) {
                console.error('Error al restaurar estado:', e);
                sessionStorage.removeItem('reporte_montos_promedio_estado');
            }
        }
        
        // Si no hay estado guardado, cargar desde URL
        cargarFiltrosDesdeURL();
        if (window.location.search.length > 0) {
            setTimeout(() => {
                cargarDatos();
            }, 300);
        }
    });

    function cargarNombreCliente(clienteId) {
        if (!clienteId) return;
        
        fetch(`/clientes/${clienteId}/edit`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                console.warn('Respuesta no es JSON al cargar cliente');
                if (response.status === 401 || response.status === 403) {
                    window.location.href = '/login';
                }
                throw new Error('La respuesta no es JSON');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const nombreCompleto = `${data.data.Nombre} ${data.data.apPaterno} ${data.data.apMaterno || ''}`.trim();
                document.getElementById('clienteNombre').innerHTML = nombreCompleto;
                document.getElementById('buscarCliente').value = nombreCompleto;
                document.getElementById('clienteSeleccionado').style.display = 'block';
                clienteSeleccionadoId = clienteId;
                clienteSeleccionadoNombre = nombreCompleto;
            }
        })
        .catch(error => {
            console.error('Error al cargar cliente:', error);
            document.getElementById('cliente_id').value = '';
            document.getElementById('clienteSeleccionado').style.display = 'none';
            document.getElementById('buscarCliente').value = '';
        });
    }

</script>
@endpush
@endsection