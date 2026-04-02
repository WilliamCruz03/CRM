@extends('layouts.app')

@section('title', 'Cotizaciones - CRM')
@section('page-title', 'Gestión de Cotizaciones')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h3><i class="bi bi-file-earmark-text"></i> Gestión de Cotizaciones</h3>
        <p class="text-muted">Monitorea el estado e interacciones de las cotizaciones</p>
    </div>

    @php
        $puedeVer = $permisos['ver'] ?? false;
        $puedeCrear = $permisos['crear'] ?? false;
        $puedeEditar = $permisos['editar'] ?? false;
        $puedeEliminar = $permisos['eliminar'] ?? false;
    @endphp

    @if($puedeVer || $puedeCrear)
    <div class="row mb-4">
        <div class="col-md-6">
            @if($puedeVer)
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" id="buscarCotizacion" placeholder="Buscar por folio, cliente o fase...">
            </div>
            @endif
        </div>
        <div class="col-md-6 text-end">
            @if($puedeCrear)
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaCotizacion">
                <i class="bi bi-plus-circle"></i> Nueva Cotización
            </button>
            @endif
        </div>
    </div>
    @endif

    @if($puedeVer)
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Folio</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Importe</th>
                            <th>Fase</th>
                            <th>Clasificación</th>
                            <th>Certeza</th>
                            <th>Entrega sugerida</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="cotizacionesTableBody">
                        @forelse($cotizaciones as $cotizacion)
                        <tr id="cotizacion-row-{{ $cotizacion->id_cotizacion }}">
                            <td>
                                <span class="badge bg-secondary">{{ $cotizacion->folio }}</span>
                                @if($cotizacion->enviado)
                                    <i class="bi bi-envelope-check text-primary" title="Enviada"></i>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $cotizacion->nombre_cliente }}</strong>
                                <br><small class="text-muted">{{ $cotizacion->cliente->email1 ?? '' }}</small>
                            </td>
                            <td>{{ $cotizacion->fecha_creacion ? $cotizacion->fecha_creacion->format('d/m/Y H:i') : '-' }}</td>
                            <td>${{ number_format($cotizacion->importe_total, 2) }}</td>
                            <td>
                                @php
                                    $faseClass = match($cotizacion->fase_nombre) {
                                        'En proceso' => 'bg-warning',
                                        'Completada' => 'bg-success',
                                        'Cancelada' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $faseClass }}">{{ $cotizacion->fase_nombre }}</span>
                            </td>
                            <td>{{ $cotizacion->clasificacion->clasificacion ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $cotizacion->certeza_color }}">{{ $cotizacion->certeza_nombre }}</span>
                            </td>
                            <td>{{ $cotizacion->fecha_entrega_sugerida ? \Carbon\Carbon::parse($cotizacion->fecha_entrega_sugerida)->format('d/m/Y') : '-' }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-info btn-action"
                                            onclick="verCotizacion({{ $cotizacion->id_cotizacion }})"
                                            title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    
                                    @if($puedeEditar && !$cotizacion->enviado)
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-action"
                                            onclick="mostrarOpcionesEdicion({{ $cotizacion->id_cotizacion }})"
                                            title="Editar cotización">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @elseif($puedeEditar && $cotizacion->enviado)
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-action"
                                            onclick="crearNuevaVersion({{ $cotizacion->id_cotizacion }})"
                                            title="Crear nueva versión">
                                        <i class="bi bi-files"></i>
                                    </button>
                                    @endif
                                    
                                    <!-- Botón PDF - SIEMPRE visible si tiene permiso de edición -->
                                    @if($puedeEditar)
                                    <button type="button" class="btn btn-sm {{ $cotizacion->enviado ? 'btn-outline-secondary' : 'btn-outline-success' }} btn-action"
                                            onclick="enviarCotizacion({{ $cotizacion->id_cotizacion }}, '{{ addslashes($cotizacion->folio) }}')"
                                            title="{{ $cotizacion->enviado ? 'Descargar ticket PDF' : 'Generar y descargar ticket PDF' }}">
                                        <i class="bi {{ $cotizacion->enviado ? 'bi-file-pdf' : 'bi-send' }}"></i>
                                        {{ $cotizacion->enviado ? 'PDF' : 'Enviar' }}
                                    </button>
                                    @endif
                                    
                                    @if($puedeEliminar)
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                            onclick="confirmarEliminar('cotizacion', {{ $cotizacion->id_cotizacion }}, '{{ addslashes($cotizacion->folio) }}')"
                                            title="Eliminar cotización">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="bi bi-file-earmark-text" style="font-size: 2rem; color: #ccc;"></i>
                                <p class="text-muted mt-2">No hay cotizaciones registradas</p>
                                @if($puedeCrear)
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaCotizacion">
                                    <i class="bi bi-plus"></i> Crear primera cotización
                                </button>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @elseif($puedeCrear)
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-file-earmark-text" style="font-size: 3rem; color: #ccc;"></i>
            <p class="text-muted mt-3">No tienes permiso para ver el listado de cotizaciones, pero puedes crear nuevas.</p>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevaCotizacion">
                <i class="bi bi-plus-circle"></i> Crear cotización
            </button>
        </div>
    </div>
    @else
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> No tienes permiso para acceder a este módulo.
    </div>
    @endif
</div>

<!-- Modals -->
@include('ventas.cotizaciones.partials.modal-nueva-cotizacion')
@include('ventas.cotizaciones.partials.modal-editar-cotizacion')
@include('ventas.cotizaciones.partials.modal-ver-cotizacion')
@include('ventas.cotizaciones.partials.modal-opciones-edicion')

<!-- Modal Confirmar Envío -->
<div class="modal fade" id="modalConfirmarEnvio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-file-pdf"></i> Generar Ticket</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Generar ticket PDF de la cotización <strong id="confirmar_envio_folio"></strong>?</p>
                <p class="text-muted small">
                    <i class="bi bi-info-circle"></i> El PDF se descargará automáticamente. 
                    La cotización quedará marcada como "enviada" la primera vez.
                </p>
                <input type="hidden" id="confirmar_envio_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="ejecutarEnvio()">
                    <i class="bi bi-file-pdf"></i> Generar PDF
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmación de Cambios Significativos -->
<div class="modal fade" id="modalConfirmarCambios" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle"></i> Productos modificados significativamente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Los productos han cambiado significativamente respecto a la cotización original.</p>
                <p class="text-muted">¿Qué deseas hacer?</p>
                
                <div class="alert alert-info mt-2 mb-3">
                    <i class="bi bi-info-circle"></i> 
                    <small>La similitud entre los productos es menor al 50%.</small>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary" id="btnSobreescribir" onclick="confirmarSobreescribir()">
                        <i class="bi bi-pencil-square"></i> Sobreescribir cotización actual
                    </button>
                    <small class="text-muted mb-2 ms-2">Reemplaza los productos de la cotización actual. Los productos originales se perderán.</small>
                    
                    <button type="button" class="btn btn-success" id="btnCrearNueva" onclick="confirmarCrearNueva()">
                        <i class="bi bi-file-earmark-plus"></i> Crear cotización nueva (sin versiones)
                    </button>
                    <small class="text-muted mb-2 ms-2">Crea una cotización completamente nueva. La original permanece intacta.</small>
                    
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <small class="text-muted ms-2">No se guarda ningún cambio.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmación Sobreescribir (advertencia adicional) -->
<div class="modal fade" id="modalConfirmarSobreescribir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-octagon"></i> ¿Sobreescribir cotización?
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Esta acción <strong>reemplazará permanentemente</strong> los productos de la cotización actual.</p>
                <p class="text-muted">Los productos originales se perderán y no podrán recuperarse.</p>
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle"></i> Si no estás seguro, puedes crear una nueva cotización en su lugar.
                </div>
                <input type="hidden" id="sobreescribir_cotizacion_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="ejecutarSobreescribir()">
                    <i class="bi bi-check-lg"></i> Sí, sobrescribir
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Asegurar que los modales de confirmación estén por encima del modal de edición*/
    .modal.fade.show {
        z-index: 1050 !important;
    }

    .modal-backdrop.fade.show {
        z-index: 1040;
    }

    /* Para el modal de confirmacion especificamente*/
    #modalConfirmarCambios.show,
    #modalConfirmarSobreescribir.show {
        z-index: 1060;
    }
</style>
@endsection

@push('scripts')
<script>
// ============================================
// FUNCIÓN VER COTIZACIÓN (global)
// ============================================
window.verCotizacion = function(id) {
    console.log('Ver cotización ID:', id);
    fetch(`/ventas/cotizaciones/${id}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error HTTP: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            if (typeof cargarDatosVerCotizacion === 'function') {
                cargarDatosVerCotizacion(data.data);
                const modal = new bootstrap.Modal(document.getElementById('modalVerCotizacion'));
                modal.show();
            } else {
                console.error('cargarDatosVerCotizacion no está definida');
                if (window.mostrarToast) window.mostrarToast('Error al cargar los datos de la cotización', 'danger');
            }
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al cargar cotización', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión al cargar la cotización', 'danger');
    });
};

// ============================================
// FUNCIÓN MOSTRAR OPCIONES EDICIÓN
// ============================================
window.mostrarOpcionesEdicion = function(id) {
    fetch(`/ventas/cotizaciones/${id}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const cotizacion = data.data;
            if (cotizacion.enviado) {
                crearNuevaVersion(id);
            } else {
                const modal = new bootstrap.Modal(document.getElementById('modalOpcionesEdicion'));
                document.getElementById('opcion_editar_id').value = id;
                document.getElementById('opcion_editar_folio').textContent = cotizacion.folio;
                modal.show();
            }
        } else {
            if (window.mostrarToast) window.mostrarToast('Error al cargar la cotización', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
};

// ============================================
// EDITAR COTIZACIÓN ACTUAL
// ============================================
window.editarCotizacionActual = function(id) {
    const modalOpciones = bootstrap.Modal.getInstance(document.getElementById('modalOpcionesEdicion'));
    if (modalOpciones) modalOpciones.hide();
    
    fetch(`/ventas/cotizaciones/${id}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof cargarDatosEditarCotizacion === 'function') {
                cargarDatosEditarCotizacion(data.data);
                const modalEditar = new bootstrap.Modal(document.getElementById('modalEditarCotizacion'));
                modalEditar.show();
            } else {
                console.error('cargarDatosEditarCotizacion no está definida');
                if (window.mostrarToast) window.mostrarToast('Error al cargar datos para edición', 'danger');
            }
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al cargar cotización', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
};

// ============================================
// CREAR NUEVA VERSIÓN (precarga modal y cierra el de opciones)
// ============================================
window.crearNuevaVersion = function(id) {
    const modalOpciones = bootstrap.Modal.getInstance(document.getElementById('modalOpcionesEdicion'));
    if (modalOpciones) modalOpciones.hide();
    
    const modalEditar = bootstrap.Modal.getInstance(document.getElementById('modalEditarCotizacion'));
    if (modalEditar) modalEditar.hide();
    
    const modalNueva = new bootstrap.Modal(document.getElementById('modalNuevaCotizacion'));
    
    fetch(`/ventas/cotizaciones/${id}/preparar-version`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Configurar banderas en el modal de nueva cotización
            if (typeof window.setEsNuevaVersion === 'function') {
                window.setEsNuevaVersion(true, data.data.id_cotizacion_origen);
            } else {
                // Fallback: acceder directamente a las variables del modal
                if (typeof esNuevaVersion !== 'undefined') {
                    window.esNuevaVersionGlobal = true;
                    window.cotizacionOrigenIdGlobal = data.data.id_cotizacion_origen;
                }
            }
            
            limpiarModalNuevaCotizacion();
            precargarModalNuevaCotizacion(data.data);
            modalNueva.show();
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al preparar nueva versión', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
};

// ============================================
// PRECARGAR MODAL NUEVA COTIZACIÓN (corregido)
// ============================================
function precargarModalNuevaCotizacion(data) {
    console.log('Precargando datos para nueva versión:', data);
    
    // Seleccionar cliente (inmediatamente, sin setTimeout)
    if (data.id_cliente && typeof window.seleccionarCliente === 'function') {
        window.seleccionarCliente(data.id_cliente, data.cliente_nombre, data.cliente_email);
    }
    
    // Asignar valores a los selects
    if (data.id_fase) {
        const faseSelect = document.getElementById('fase_id');
        if (faseSelect) faseSelect.value = data.id_fase;
    }
    
    if (data.id_clasificacion) {
        const clasificacionSelect = document.getElementById('clasificacion_id');
        if (clasificacionSelect) clasificacionSelect.value = data.id_clasificacion;
    }
    
    if (data.id_sucursal_asignada) {
        const sucursalSelect = document.getElementById('sucursal_asignada_id');
        if (sucursalSelect) sucursalSelect.value = data.id_sucursal_asignada;
    }
    
    if (data.certeza) {
        const certezaSelect = document.getElementById('certeza');
        if (certezaSelect) certezaSelect.value = data.certeza;
    }
    
    if (data.comentarios) {
        const comentariosTextarea = document.getElementById('comentarios');
        if (comentariosTextarea) comentariosTextarea.value = data.comentarios;
    }
    
    // Asignar artículos
    if (data.articulos && data.articulos.length > 0) {
        if (typeof articulosSeleccionados !== 'undefined') {
            // Limpiar array existente
            articulosSeleccionados.length = 0;
            
            // Agregar los nuevos artículos
            data.articulos.forEach(articulo => {
                articulosSeleccionados.push({
                    id_producto: parseInt(articulo.id_producto),
                    nombre: articulo.nombre,
                    codbar: articulo.codbar || '',
                    precio: parseFloat(articulo.precio),
                    cantidad: parseInt(articulo.cantidad),
                    descuento: parseFloat(articulo.descuento || 0),
                    id_convenio: articulo.id_convenio,
                    id_sucursal_surtido: articulo.id_sucursal_surtido ? parseInt(articulo.id_sucursal_surtido) : null,
                    num_familia: articulo.num_familia || '',
                    inventario_disponible: parseInt(articulo.inventario_disponible || 999),
                    nombre_sucursal_surtido: articulo.nombre_sucursal_surtido || ''
                });
            });
            
            console.log('Artículos precargados en articulosSeleccionados:', articulosSeleccionados);
            
            // Renderizar la tabla
            if (typeof renderizarTablaArticulos === 'function') {
                renderizarTablaArticulos();
            }
        } else {
            console.error('articulosSeleccionados no está definida');
        }
    }
}

// ============================================
// LIMPIAR MODAL NUEVA COTIZACIÓN (corregido)
// ============================================
function limpiarModalNuevaCotizacion() {
    // Limpiar cliente
    if (typeof window.limpiarCliente === 'function') {
        window.limpiarCliente();
    } else {
        const clienteId = document.getElementById('cliente_id');
        if (clienteId) clienteId.value = '';
        const clienteSeleccionado = document.getElementById('clienteSeleccionado');
        if (clienteSeleccionado) clienteSeleccionado.style.display = 'none';
        const buscadorCliente = document.getElementById('buscarClienteCotizacion');
        if (buscadorCliente) buscadorCliente.value = '';
    }
    
    // Limpiar selects
    const faseSelect = document.getElementById('fase_id');
    if (faseSelect) faseSelect.value = '';
    
    const clasificacionSelect = document.getElementById('clasificacion_id');
    if (clasificacionSelect) clasificacionSelect.value = '';
    
    const sucursalSelect = document.getElementById('sucursal_asignada_id');
    if (sucursalSelect) sucursalSelect.value = '';
    
    const certezaSelect = document.getElementById('certeza');
    if (certezaSelect) certezaSelect.value = '1';
    
    const convenioSelect = document.getElementById('convenio_general');
    if (convenioSelect) convenioSelect.value = '';
    
    const comentariosTextarea = document.getElementById('comentarios');
    if (comentariosTextarea) comentariosTextarea.value = '';
    
    // Limpiar artículos - usar la variable global 'articulosSeleccionados'
    if (typeof articulosSeleccionados !== 'undefined') {
        articulosSeleccionados.length = 0;
        if (typeof renderizarTablaArticulos === 'function') {
            renderizarTablaArticulos();
        }
    }
}

// ============================================
// ENVIAR COTIZACIÓN (generar PDF - botón siempre visible)
// ============================================
window.enviarCotizacion = function(id, folio) {
    // Abrir el PDF en nueva pestaña y descargar
    window.open(`/ventas/cotizaciones/${id}/ticket`, '_blank');
    
    // Mostrar toast de confirmación
    if (window.mostrarToast) {
        window.mostrarToast('Generando ticket PDF...', 'info');
    }
    
    // Actualizar la apariencia del botón después de generar el PDF
    setTimeout(() => {
        const boton = document.querySelector(`#cotizacion-row-${id} .btn-outline-success`);
        if (boton) {
            // Cambiar clases y texto del botón
            boton.classList.remove('btn-outline-success');
            boton.classList.add('btn-outline-secondary');
            boton.title = 'Descargar ticket PDF';
            // Cambiar ícono y texto
            const icono = boton.querySelector('i');
            if (icono) {
                icono.classList.remove('bi-send');
                icono.classList.add('bi-file-pdf');
            }
            boton.innerHTML = '<i class="bi bi-file-pdf"></i> PDF';
        }
        
        // También actualizar el ícono de "enviado" en el folio si no existe
        const folioCell = document.querySelector(`#cotizacion-row-${id} td:first-child`);
        if (folioCell && !folioCell.querySelector('.bi-envelope-check')) {
            const badge = folioCell.querySelector('.badge');
            if (badge) {
                badge.insertAdjacentHTML('afterend', ' <i class="bi bi-envelope-check text-primary" title="Enviada"></i>');
            }
        }
    }, 2000);
};

// ============================================
// GUARDAR EDICIÓN COTIZACIÓN (con modal de confirmación)
// ============================================
let datosPendientesConfirmacion = null;
let cotizacionIdPendiente = null;

window.guardarEdicionCotizacion = function() {
    const cotizacionId = document.getElementById('edit_cotizacion_id')?.value;
    const faseId = document.getElementById('edit_fase_id')?.value;

    if (!faseId) {
        if (window.mostrarToast) window.mostrarToast('Selecciona una fase', 'warning');
        return;
    }

    if (typeof editArticulosSeleccionados === 'undefined' || editArticulosSeleccionados.length === 0) {
        if (window.mostrarToast) window.mostrarToast('Agrega al menos un artículo', 'warning');
        return;
    }

    const articulos = editArticulosSeleccionados.map((a) => ({
        id_producto: a.id_producto,
        cantidad: a.cantidad,
        precio_unitario: a.precio,
        descuento: a.descuento,
        id_convenio: a.id_convenio,
        id_sucursal_surtido: a.id_sucursal_surtido
    }));

    const formData = {
        id_fase: parseInt(faseId),
        id_clasificacion: document.getElementById('edit_clasificacion_id')?.value || null,
        id_sucursal_asignada: document.getElementById('edit_sucursal_asignada_id')?.value || null,
        certeza: parseInt(document.getElementById('edit_certeza')?.value || 0),
        comentarios: document.getElementById('edit_comentarios')?.value || '',
        articulos: articulos,
        _token: '{{ csrf_token() }}',
        _method: 'PUT',
        opcion: 'editar'
    };

    // Guardar datos para usar en caso de confirmación
    datosPendientesConfirmacion = formData;
    cotizacionIdPendiente = cotizacionId;

    // Mostrar loading
    if (window.mostrarToast) window.mostrarToast('Validando cambios...', 'info');

    fetch(`/ventas/cotizaciones/${cotizacionId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (response.status === 409) {
            // Similitud baja - primero cerrar el modal de edición, luego mostrar confirmación
            const modalEditar = bootstrap.Modal.getInstance(document.getElementById('modalEditarCotizacion'));
            if (modalEditar) modalEditar.hide();
            
            // Esperar un momento para que se cierre el modal
            setTimeout(() => {
                return response.json().then(data => {
                    window.similitudData = data;
                    const modalConfirmacion = new bootstrap.Modal(document.getElementById('modalConfirmarCambios'));
                    modalConfirmacion.show();
                });
            }, 300);
            return null; // No procesar más
        }
        return response.json();
    })
    .then(data => {
        if (data && data.success) {
            if (window.mostrarToast) window.mostrarToast(data.message, 'success');
            const modalEditar = bootstrap.Modal.getInstance(document.getElementById('modalEditarCotizacion'));
            if (modalEditar) modalEditar.hide();
            setTimeout(() => location.reload(), 1000);
        } else if (data && !data.success && data.message !== undefined) {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al guardar', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
};

// ============================================
// CONFIRMAR SOBREESCRIBIR (advertencia adicional)
// ============================================
window.confirmarSobreescribir = function() {
    // Cerrar el primer modal
    const modalConfirmacion = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarCambios'));
    if (modalConfirmacion) modalConfirmacion.hide();
    
    // Mostrar modal de advertencia
    document.getElementById('sobreescribir_cotizacion_id').value = cotizacionIdPendiente;
    const modalSobreescribir = new bootstrap.Modal(document.getElementById('modalConfirmarSobreescribir'));
    modalSobreescribir.show();
};

// ============================================
// EJECUTAR SOBREESCRIBIR
// ============================================
window.ejecutarSobreescribir = function() {
    const modalSobreescribir = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarSobreescribir'));
    if (modalSobreescribir) modalSobreescribir.hide();
    
    // Mostrar loading
    if (window.mostrarToast) window.mostrarToast('Guardando cambios...', 'info');
    
    // Enviar petición para sobrescribir (forzar guardado)
    datosPendientesConfirmacion.forzar = true;
    
    fetch(`/ventas/cotizaciones/${cotizacionIdPendiente}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(datosPendientesConfirmacion)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (window.mostrarToast) window.mostrarToast('Cotización sobrescrita correctamente', 'success');
            const modalEditar = bootstrap.Modal.getInstance(document.getElementById('modalEditarCotizacion'));
            if (modalEditar) modalEditar.hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al sobrescribir', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
};

// ============================================
// CREAR COTIZACIÓN NUEVA (sin versiones)
// ============================================
window.confirmarCrearNueva = function() {
    // Cerrar modal de confirmación
    const modalConfirmacion = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarCambios'));
    if (modalConfirmacion) modalConfirmacion.hide();
    
    // Mostrar loading
    if (window.mostrarToast) window.mostrarToast('Creando nueva cotización...', 'info');
    
    // Enviar petición para crear nueva cotización (sin relación de versión)
    fetch(`/ventas/cotizaciones/crear-nueva-desde-edicion`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            cotizacion_origen_id: cotizacionIdPendiente,
            datos: datosPendientesConfirmacion
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (window.mostrarToast) window.mostrarToast('Nueva cotización creada correctamente', 'success');
            const modalEditar = bootstrap.Modal.getInstance(document.getElementById('modalEditarCotizacion'));
            if (modalEditar) modalEditar.hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al crear nueva cotización', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
};

// ============================================
// ELIMINAR COTIZACIÓN
// ============================================
if (typeof window.confirmarEliminar !== 'function') {
    window.confirmarEliminar = function(tipo, id, nombre) {
        if (confirm(`¿Eliminar ${tipo} "${nombre}"?`)) {
            if (tipo === 'cotizacion' && typeof window.ejecutarEliminarCotizacion === 'function') {
                window.ejecutarEliminarCotizacion(id, nombre);
            }
        }
    };
}

if (typeof window.ejecutarEliminarCotizacion !== 'function') {
    window.ejecutarEliminarCotizacion = function(id, folio) {
        fetch(`/ventas/cotizaciones/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const fila = document.getElementById(`cotizacion-row-${id}`);
                if (fila) fila.remove();
                if (window.mostrarToast) window.mostrarToast(`Cotización ${folio} eliminada`, 'success');
            } else {
                if (window.mostrarToast) window.mostrarToast(data.message || 'Error al eliminar', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
        });
    };
}

// ============================================
// BUSCADOR EN TABLA
// ============================================
document.getElementById('buscarCotizacion')?.addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase().trim();
    const rows = document.querySelectorAll('#cotizacionesTableBody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        if (row.querySelector('td[colspan]')) return;
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
        if (text.includes(searchTerm)) visibleCount++;
    });
});
</script>
@endpush