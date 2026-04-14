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
                            @php
                                $contactos = [];
                                if ($cotizacion->cliente && $cotizacion->cliente->telefono1) {
                                    $contactos[] = '<i class="bi bi-telephone"></i> ' . e($cotizacion->cliente->telefono1);
                                }
                                if ($cotizacion->cliente && $cotizacion->cliente->telefono2) {
                                    $contactos[] = '<i class="bi bi-telephone"></i> ' . e($cotizacion->cliente->telefono2) . ' <span class="text-muted">(secundario)</span>';
                                }
                                if ($cotizacion->cliente && $cotizacion->cliente->email1) {
                                    $contactos[] = '<i class="bi bi-envelope"></i> ' . e($cotizacion->cliente->email1);
                                }
                                $contactoMostrar = !empty($contactos) ? implode('<br>', $contactos) : '<span class="text-muted">Sin contacto</span>';
                            @endphp
                            <br><small class="text-muted">{!! $contactoMostrar !!}</small>
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
                            <td>
                                @if($cotizacion->enviado && $cotizacion->fase_nombre === 'Completada' && !$cotizacion->es_pedido)
                                <button type="button" class="btn btn-sm btn-success btn-action"
                                        onclick="generarPedido({{ $cotizacion->id_cotizacion }})"
                                        title="Convertir en pedido">
                                    <i class="bi bi-cart-check"></i> Pedido
                                </button>
                                @endif
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
<div class="d-flex justify-content-center mt-3">
    {{ $cotizaciones->links() }}
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
    #modalConfirmarCambios.show {
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
    
    if (window.mostrarToast) {
        window.mostrarToast('Cargando datos de la cotización...', 'warning');
    }
    
    // Primero cargar los catálogos si es necesario, luego obtener la cotización
    const cargarCatalogoPromise = (typeof cargarCatalogosEdit === 'function') 
        ? cargarCatalogosEdit() 
        : Promise.resolve();
    
    cargarCatalogoPromise
        .then(() => {
            return fetch(`/ventas/cotizaciones/${id}`, {
                headers: { 
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                if (typeof cargarDatosEditarCotizacion === 'function') {
                    // Pasar el objeto completo de la cotización
                    cargarDatosEditarCotizacion(data.data);
                    const modalEditar = new bootstrap.Modal(document.getElementById('modalEditarCotizacion'));
                    modalEditar.show();
                    if (window.mostrarToast) {
                        window.mostrarToast('Datos cargados correctamente', 'success');
                    }
                } else {
                    console.error('cargarDatosEditarCotizacion no está definida');
                    if (window.mostrarToast) {
                        window.mostrarToast('Error al cargar datos para edición', 'danger');
                    }
                }
            } else {
                if (window.mostrarToast) {
                    window.mostrarToast(data.message || 'Error al cargar cotización', 'danger');
                }
            }
        })
        .catch(error => {
            console.error('Error en editarCotizacionActual:', error);
            if (window.mostrarToast) {
                window.mostrarToast('Error de conexión al cargar la cotización', 'danger');
            }
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
            precargarDatosCotizacion(data.data);
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
    // Mostrar toast de confirmación
    if (window.mostrarToast) {
        window.mostrarToast('Generando ticket PDF...', 'info');
    }
    
    // Primero recargar la página para actualizar el estado (fase, enviado)
    // El PDF se abrirá en una nueva pestaña antes de recargar
    fetch(`/ventas/cotizaciones/${id}/ticket`, {
        method: 'GET',
        headers: {
            'Accept': 'application/pdf',
        }
    })
    .then(response => {
        if (response.ok) {
            // Abrir el PDF en nueva pestaña
            return response.blob();
        }
        throw new Error('Error al generar PDF');
    })
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        window.open(url, '_blank');
        window.URL.revokeObjectURL(url);
        
        // Recargar la página después de un momento para mostrar cambios
        setTimeout(() => {
            location.reload();
        }, 1500);
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) {
            window.mostrarToast('Error al generar el PDF', 'danger');
        }
    });
};

// ============================================
// GUARDAR EDICIÓN COTIZACIÓN (CORREGIDO)
// ============================================
let datosPendientesConfirmacion = null;
let cotizacionIdPendiente = null;

window.guardarEdicionCotizacion = function() {
    const cotizacionId = document.getElementById('edit_cotizacion_id')?.value;
    const faseId = document.getElementById('edit_fase_id')?.value;
    const clienteId = document.getElementById('edit_cliente_id')?.value;

    if (!faseId) {
        if (window.mostrarToast) window.mostrarToast('Selecciona una fase', 'warning');
        return;
    }

    if (!clienteId) {
        if (window.mostrarToast) window.mostrarToast('Cliente no encontrado', 'warning');
        return;
    }

    if (typeof editArticulosSeleccionados === 'undefined' || editArticulosSeleccionados.length === 0) {
        if (window.mostrarToast) window.mostrarToast('Agrega al menos un artículo', 'warning');
        return;
    }

    const articulos = editArticulosSeleccionados.map((a) => ({
        id_producto: parseInt(a.id_producto),
        cantidad: parseInt(a.cantidad),
        precio_unitario: parseFloat(a.precio),
        descuento: parseFloat(a.descuento || 0),
        id_convenio: a.id_convenio ? parseInt(a.id_convenio) : null,
        id_sucursal_surtido: a.id_sucursal_surtido ? parseInt(a.id_sucursal_surtido) : null,
        tipo_producto: a.es_externo ? 'externo' : 'normal'
    }));

    const formData = {
        id_cliente: parseInt(clienteId), // ← AGREGAR
        id_fase: parseInt(faseId),
        id_clasificacion: document.getElementById('edit_clasificacion_id')?.value || null,
        id_sucursal_asignada: document.getElementById('edit_sucursal_asignada_id')?.value || null,
        certeza: parseInt(document.getElementById('edit_certeza')?.value || 0),
        comentarios: document.getElementById('edit_comentarios')?.value || '',
        articulos: articulos,
        _token: '{{ csrf_token() }}',
        _method: 'PUT',
        accion: 'editar'
    };

    datosPendientesConfirmacion = formData;
    cotizacionIdPendiente = cotizacionId;

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
            const modalEditar = bootstrap.Modal.getInstance(document.getElementById('modalEditarCotizacion'));
            if (modalEditar) modalEditar.hide();
            
            setTimeout(() => {
                response.json().then(data => {
                    window.similitudData = data;
                    const modalConfirmacion = new bootstrap.Modal(document.getElementById('modalConfirmarCambios'));
                    modalConfirmacion.show();
                });
            }, 300);
            return null;
        }
        return response.json();
    })
    .then(data => {
        if (data && data.success) {
            if (window.mostrarToast) window.mostrarToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else if (data && !data.success && data.message) {
            if (window.mostrarToast) window.mostrarToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
};

// ============================================
// CONFIRMAR SOBREESCRIBIR (sin segundo modal)
// ============================================
window.confirmarSobreescribir = function() {
    const modalConfirmacion = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarCambios'));
    if (modalConfirmacion) modalConfirmacion.hide();
    
    if (window.mostrarToast) window.mostrarToast('Guardando cambios...', 'info');
    
    // Asegurar que los artículos tengan tipo_producto
    if (datosPendientesConfirmacion.articulos) {
        datosPendientesConfirmacion.articulos = datosPendientesConfirmacion.articulos.map(a => ({
            ...a,
            tipo_producto: a.tipo_producto || (a.es_externo ? 'externo' : 'normal')
        }));
    }
    
    // Asegurar que id_cliente esté presente
    if (!datosPendientesConfirmacion.id_cliente) {
        const clienteId = document.getElementById('edit_cliente_id')?.value;
        if (clienteId) {
            datosPendientesConfirmacion.id_cliente = parseInt(clienteId);
        }
    }
    
    datosPendientesConfirmacion.accion = 'sobrescribir';
    
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
// CREAR COTIZACIÓN NUEVA SIN VERSIÓN (usa mismo endpoint)
// ============================================
window.confirmarCrearNueva = function() {
    const modalConfirmacion = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarCambios'));
    if (modalConfirmacion) modalConfirmacion.hide();
    
    if (window.mostrarToast) window.mostrarToast('Creando nueva cotización...', 'info');
    
    // Asegurar que los artículos tengan tipo_producto y que id_cliente esté presente
    if (datosPendientesConfirmacion.articulos) {
        datosPendientesConfirmacion.articulos = datosPendientesConfirmacion.articulos.map(a => ({
            ...a,
            tipo_producto: a.tipo_producto || (a.es_externo ? 'externo' : 'normal')
        }));
    }
    
    // Asegurar que id_cliente esté presente
    if (!datosPendientesConfirmacion.id_cliente) {
        const clienteId = document.getElementById('edit_cliente_id')?.value;
        if (clienteId) {
            datosPendientesConfirmacion.id_cliente = parseInt(clienteId);
        } else {
            if (window.mostrarToast) window.mostrarToast('Error: Cliente no encontrado', 'danger');
            return;
        }
    }
    
    datosPendientesConfirmacion.accion = 'nueva_sin_version';
    
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
            if (window.mostrarToast) window.mostrarToast(data.message, 'success');
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
// COTIZACIÓN A PEDIDO, CAMBIA COLUMNA es_pedido 0 -> 1 (solo si está en fase completada y enviada)
// ============================================
window.generarPedido = function(id) {
    if (!confirm('¿Convertir esta cotización en pedido?')) return;
    
    fetch(`/ventas/cotizaciones/${id}/generar-pedido`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (window.mostrarToast) window.mostrarToast('Pedido generado correctamente', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al generar pedido', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
};

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

    // Establecer la sucursal del usuario logueado para el modal de nueva cotización
    window.sucursalUsuarioDefecto = {{ $sucursalAsignadaUsuario ?? 0 }};
    console.log('Sucursal usuario establecida:', window.sucursalUsuarioDefecto);
</script>
@endpush