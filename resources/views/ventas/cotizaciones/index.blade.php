@extends('layouts.app')

@section('title', 'Cotizaciones - CRM')
@section('page-title', 'Gestión de Cotizaciones')

@section('content')
<style>
/* Estilos para alerta fuerte (próximo a vencer) */
.cotizacion-alerta-alta {
    background-color: #ffebee !important;
    border-left: 4px solid #dc3545 !important;
}
.cotizacion-alerta-media {
    background-color: #fff3e0 !important;
    border-left: 4px solid #ff9800 !important;
}
.cotizacion-alerta-baja {
    background-color: #e3f2fd !important;
    border-left: 4px solid #2196f3 !important;
}

/* Estilo para alerta suave (resaltado preliminar) */
.cotizacion-resaltado {
    background-color: #fff8e1 !important;
    border-left: 4px solid #ffc107 !important;
}
</style>
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
                <input type="text" class="form-control" id="buscarCotizacion" placeholder="Buscar por folio, cliente o fase..." autocomplete="off">
                <button type="button" class="bi bi-x-circle-fill" id="limpiarBuscarCotizacion" 
                style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); z-index: 10; color: #6c757d; cursor: pointer; display: none; font-size: 1.2rem; background: none; border: none; padding: 0; line-height: 1;"
                title="Limpiar búsqueda"></button>
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
                <!-- Usamos el contenedor de la vista parcial -->
                <div class="card">
                    <div class="card-body p-0">
                        <div id="tabla-cotizaciones-container">
                            @include('ventas.cotizaciones.partials.tabla-cotizaciones', [
                                'cotizaciones' => $cotizaciones, 
                                'permisos' => $permisos
                            ])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @elseif($puedeCrear)
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-file-earmark-text" style="font-size: 3rem; color: #ccc;"></i>
            <p class="text-muted mt-3">No tienes permiso para ver el listado de cotizaciones, pero puedes crear nuevas.</p>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaCotizacion">
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
@include('ventas.partials.modal-seguimiento')

<!-- Modal Confirmar Envío -->
<div class="modal fade" id="modalConfirmarEnvio" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle"></i> Completar Cotización
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">
                    ¿Marcar la cotización <strong id="confirmar_envio_folio"></strong> como <strong>completada</strong>?
                </p>
                <div class="alert alert-info py-2 mb-0 small">
                    <i class="bi bi-info-circle"></i>
                    La cotización finalizará su proceso. A partir de este momento podrá convertirse en pedido.
                </div>
                <input type="hidden" id="confirmar_envio_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="ejecutarEnvio()">
                    <i class="bi bi-check-circle"></i> Marcar como Completada
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmación de Cambios Significativos -->
<div class="modal fade" id="modalConfirmarCambios" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
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
                        <i class="bi bi-pencil-square"></i> Sobrescribir cotización actual
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

<!-- Modal Confirmar Convertir a Pedido -->
<div class="modal fade" id="modalConfirmarPedido" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-cart-check"></i> Convertir a Pedido
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Confirma la conversión de la cotización <strong id="confirmar_pedido_folio"></strong> en un pedido.</p>
                <input type="hidden" id="confirmar_pedido_id">

                <!-- Barra de controles -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small">
                        <i class="bi bi-info-circle"></i>
                        Asigna las cantidades a las sucursales. La suma debe coincidir con el requerido.
                    </span>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary" onclick="expandirTodosCotizacion()" title="Expandir todos">
                            <i class="bi bi-arrows-expand"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="colapsarTodosCotizacion()" title="Colapsar todos">
                            <i class="bi bi-arrows-collapse"></i>
                        </button>
                    </div>
                </div>

                <!-- Contenedor de asignación -->
                <div id="asignacionInventarioContainer">
                    <div class="alert alert-info text-center" id="cargandoAsignacion">
                        <i class="bi bi-hourglass-split"></i> Cargando disponibilidad de inventario...
                    </div>
                    <div id="asignacionTablaContainer" style="display: none;">
                        <!-- Se llena dinámicamente con JavaScript -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="confirmarGenerarPedidoConAsignacion()">
                    <i class="bi bi-check-lg"></i> Confirmar Pedido
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
    #modalConfirmarCambios.show {
        z-index: 1060;
    }
</style>
@endsection

@push('scripts')
<script>
// FUNCIÓN PARA LIMPIAR BACKDROPS
function limpiarBackdrops() {
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(backdrop => backdrop.remove());
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
}
window.limpiarBackdrops = limpiarBackdrops;

// ============================================
// FUNCIÓN VER COTIZACIÓN (global)
// ============================================
window.verCotizacion = function(id) {
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
    
    const cotizacionId = typeof id === 'string' ? parseInt(id, 10) : Number(id);
    
    if (isNaN(cotizacionId) || cotizacionId <= 0) {
        console.error('ID inválido:', id);
        if (window.mostrarToast) {
            window.mostrarToast('ID de cotización inválido', 'danger');
        }
        return;
    }
    
    fetch(`/ventas/cotizaciones/${cotizacionId}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const cotizacion = data.data;
            if (cotizacion.enviado) {
                crearNuevaVersion(cotizacionId);
            } else {
                const modal = new bootstrap.Modal(document.getElementById('modalOpcionesEdicion'));
                document.getElementById('opcion_editar_id').value = cotizacionId;
                document.getElementById('opcion_editar_folio').textContent = cotizacion.folio;
                modal.show();
            }
        } else {
            if (window.mostrarToast) window.mostrarToast('Error al cargar la cotización', 'danger');
        }
    })
    .catch(error => {
        console.error('Error en mostrarOpcionesEdicion:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
};

// ============================================
// EDITAR COTIZACIÓN ACTUAL
// ============================================
window.editarCotizacionActual = function(id) {
    // Si es un objeto, intentar extraer el ID
    if (typeof id === 'object' && id !== null) {
        if (id.target) {
            const btn = id.target.closest('.btn-action');
            if (btn && btn.dataset && btn.dataset.id) {
                id = btn.dataset.id;
            } else {
                console.error('No se pudo extraer el ID del evento');
                return;
            }
        } else if (id.id_cotizacion) {
            id = id.id_cotizacion;
        } else if (id.id) {
            id = id.id;
        } else {
            console.error('ID inválido - objeto sin id:', id);
            return;
        }
    }
    
    const cotizacionId = typeof id === 'string' ? parseInt(id, 10) : Number(id);
    
    if (isNaN(cotizacionId) || cotizacionId <= 0) {
        console.error('ID inválido en editarCotizacionActual:', id);
        if (window.mostrarToast) {
            window.mostrarToast('ID de cotización inválido', 'danger');
        }
        return;
    }
    
    // Convertir a número
    const idNumerico = typeof cotizacionId === 'string' ? parseInt(cotizacionId, 10) : Number(cotizacionId);
    
    if (isNaN(idNumerico) || idNumerico <= 0) {
        console.error('ID inválido en editarCotizacionActual:', idNumerico);
        if (window.mostrarToast) {
            window.mostrarToast('ID de cotización inválido', 'danger');
        }
        return;
    }
    
    // Usar idNumerico en lugar de cotizacionId
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
            return fetch(`/ventas/cotizaciones/${idNumerico}`, {
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
 

// CREAR NUEVA VERSIÓN
window.crearNuevaVersion = function(id) {
    const cotizacionId = parseInt(id);
    if (isNaN(cotizacionId) || cotizacionId <= 0) {
        console.error('ID inválido en crearNuevaVersion:', id);
        if (window.mostrarToast) {
            window.mostrarToast('ID de cotización inválido', 'danger');
        }
        return;
    }
    
    const modalOpciones = bootstrap.Modal.getInstance(document.getElementById('modalOpcionesEdicion'));
    if (modalOpciones) modalOpciones.hide();
    
    const modalEditar = bootstrap.Modal.getInstance(document.getElementById('modalEditarCotizacion'));
    if (modalEditar) modalEditar.hide();
    
    // Configurar bandera de nueva versión
    if (typeof window.setEsNuevaVersion === 'function') {
        window.setEsNuevaVersion(true, cotizacionId);
    } else {
        window.esNuevaVersionGlobal = true;
        window.cotizacionOrigenIdGlobal = cotizacionId;
    }
    
    cargarDatosParaNuevaCotizacion(cotizacionId, true);
    
    // Mostrar el modal
    const modalNueva = new bootstrap.Modal(document.getElementById('modalNuevaCotizacion'));
    modalNueva.show();
};


// CREAR COTIZACIÓN INDEPENDIENTE (sin versionado)
window.crearNuevaIndependiente = function(id) {
    const cotizacionId = typeof id === 'string' ? parseInt(id, 10) : Number(id);
    
    if (isNaN(cotizacionId) || cotizacionId <= 0) {
        console.error('ID inválido en crearNuevaIndependiente:', id);
        if (window.mostrarToast) {
            window.mostrarToast('ID de cotización inválido', 'danger');
        }
        return;
    }
    
    function ejecutarCrearIndependiente() {
        try {
            const modalOpciones = bootstrap.Modal.getInstance(document.getElementById('modalOpcionesEdicion'));
            if (modalOpciones) modalOpciones.hide();
            
            const modalEditar = bootstrap.Modal.getInstance(document.getElementById('modalEditarCotizacion'));
            if (modalEditar) modalEditar.hide();
            
            // Configurar bandera de independiente
            window.esNuevaIndependiente = true;
            
            cargarDatosParaNuevaCotizacion(cotizacionId, false);
            
            // Mostrar el modal
            const modalNueva = new bootstrap.Modal(document.getElementById('modalNuevaCotizacion'));
            modalNueva.show();
        } catch (error) {
            console.error('Error en ejecutarCrearIndependiente:', error);
        }
    }
    
    // Verificar si bootstrap está disponible
    if (typeof bootstrap !== 'undefined') {
        ejecutarCrearIndependiente();
    } else {
        let intentos = 0;
        const maxIntentos = 50;
        const intervalo = setInterval(function() {
            intentos++;
            if (typeof bootstrap !== 'undefined') {
                clearInterval(intervalo);
                ejecutarCrearIndependiente();
            } else if (intentos >= maxIntentos) {
                clearInterval(intervalo);
                console.error('Timeout: Bootstrap no se cargó');
                if (window.mostrarToast) window.mostrarToast('Error: No se pudo cargar el componente', 'danger');
            }
        }, 100);
    }
};

// ============================================
// LIMPIAR MODAL NUEVA COTIZACIÓN
// ============================================
function limpiarModalNuevaCotizacion(limpiarArticulos = true) {
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
    
    // Limpiar artículos SOLO si se solicita
    if (limpiarArticulos && typeof articulosSeleccionados !== 'undefined') {
        articulosSeleccionados = [];
        if (typeof renderizarTablaArticulos === 'function') {
            renderizarTablaArticulos();
        }
    }
}

// ============================================
// MOSTRAR MODAL CONFIRMAR ENVÍO
// ============================================
window.mostrarModalConfirmarEnvio = function(id, folio) {
    document.getElementById('confirmar_envio_id').value = id;
    document.getElementById('confirmar_envio_folio').textContent = folio;
    
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmarEnvio'));
    modal.show();
};

// ============================================
// EJECUTAR ENVÍO (desde el modal)
// ============================================
window.ejecutarEnvio = function() {
    const id = document.getElementById('confirmar_envio_id').value;
    const folio = document.getElementById('confirmar_envio_folio').textContent;
    
    if (!id) return;
    
    // Cerrar el modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarEnvio'));
    if (modal) modal.hide();
    
    // Ejecutar la función existente
    enviarCotizacion(id, folio);
};

// ============================================
// GENERAR PDF SOLO (sin cambiar estado)
// ============================================
window.generarPDF = function(id, folio) {
    if (window.mostrarToast) {
        window.mostrarToast('Generando PDF...', 'warning');
    }

    fetch(`/ventas/cotizaciones/${id}/ticket-solo`, {
        method: 'GET',
        headers: { 'Accept': 'application/pdf' }
    })
    .then(response => {
        if (!response.ok) throw new Error('Error al generar PDF');
        return response.blob();
    })
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        window.open(url, '_blank');
        window.URL.revokeObjectURL(url);

        if (window.mostrarToast) {
            window.mostrarToast('PDF generado correctamente', 'success');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) {
            window.mostrarToast('Error al generar el PDF', 'danger');
        }
    });
};

// ============================================
// ENVIAR COTIZACIÓN (marca como enviada + completada + PDF)
// ============================================
window.enviarCotizacion = function(id, folio) {
    if (window.mostrarToast) {
        window.mostrarToast('Marcando como completada...', 'warning');
    }
    
    // Primero marcar como enviada
    fetch(`/ventas/cotizaciones/${id}/marcar-enviada`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            if (window.mostrarToast) {
                window.mostrarToast(data.message || 'Error al marcar como enviada', 'danger');
            }
            return;
        }

        if (window.mostrarToast) {
            window.mostrarToast('Cotización marcada como completada', 'success');
        }

        setTimeout(() => location.reload(), 1000);
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) {
            window.mostrarToast('Error al marcar la cotización', 'danger');
        }
    });
};

// ============================================
// GUARDAR EDICIÓN COTIZACIÓN
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
        codbar: a.codbar || a.ean || '',
        cantidad: parseInt(a.cantidad),
        precio_unitario: parseFloat(a.precio),
        descuento: parseFloat(a.descuento || 0),
        id_convenio: a.id_convenio ? parseInt(a.id_convenio) : null,
        es_externo: a.es_externo ? 1 : 0 
    }));

    const formData = {
        id_cliente: parseInt(clienteId),
        id_fase: parseInt(faseId),
        id_clasificacion: document.getElementById('edit_clasificacion_id')?.value || null,
        id_sucursal_asignada: document.getElementById('edit_sucursal_asignada_id')?.value || null,
        id_convenio: document.getElementById('edit_convenio_general')?.value || null,
        certeza: parseInt(document.getElementById('edit_certeza')?.value || 0),
        comentarios: document.getElementById('edit_comentarios')?.value || '',
        articulos: articulos,
        _token: '{{ csrf_token() }}',
        _method: 'PUT',
        accion: 'editar'
    };

    datosPendientesConfirmacion = formData;
    cotizacionIdPendiente = cotizacionId;

    if (window.mostrarToast) window.mostrarToast('Validando cambios...', 'warning');

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
// CONFIRMAR SOBRESCRIBIR (sin segundo modal)
// ============================================
window.confirmarSobreescribir = function() {
    const modalConfirmacion = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarCambios'));
    if (modalConfirmacion) modalConfirmacion.hide();
    
    if (window.mostrarToast) window.mostrarToast('Guardando cambios...', 'info');
    
    // Asegurar que los artículos tengan es_externo
    if (datosPendientesConfirmacion.articulos) {
        datosPendientesConfirmacion.articulos = datosPendientesConfirmacion.articulos.map(a => ({
            ...a,
            es_externo: a.es_externo ? 1 : 0
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
    
    // Asegurar que los artículos tengan es_externo y que id_cliente esté presente
    if (datosPendientesConfirmacion.articulos) {
        datosPendientesConfirmacion.articulos = datosPendientesConfirmacion.articulos.map(a => ({
            ...a,
            es_externo: a.es_externo ? 1 : 0
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
// RECALCULAR FECHA DE ENTREGA SUGERIDA
// ============================================
function recalcularFechaEntrega() {
    // Obtener los artículos seleccionados
    const articulos = window.articulosSeleccionados || [];
    
    // Verificar si hay productos externos
    const hayExternos = articulos.some(a => a.es_externo == 1);
    
    // Verificar si hay stock insuficiente
    let stockInsuficiente = false;
    for (const articulo of articulos) {
        if (articulo.es_externo == 0) {
            const maxDisponible = articulo.inventario_global || 0;
            if (maxDisponible < articulo.cantidad) {
                stockInsuficiente = true;
                break;
            }
        }
    }
    
    // DEFINIR LA FECHA ACTUAL
    const ahora = new Date();
    const esAntesDe12 = ahora.getHours() < 12;
    let fechaEntrega = new Date(ahora);
    
    if (hayExternos) {
        // 2 días después
        fechaEntrega.setDate(fechaEntrega.getDate() + 2);
    } else if (stockInsuficiente) {
        // 1 día después
        fechaEntrega.setDate(fechaEntrega.getDate() + 1);
    } else {
        if (!esAntesDe12) {
            // Después de las 12, día siguiente
            fechaEntrega.setDate(fechaEntrega.getDate() + 1);
        }
        // Si es antes de las 12, mismo día
    }
    
    // Actualizar solo el campo de fecha
    const fechaInput = document.getElementById('fecha_entrega_sugerida') || 
                       document.getElementById('edit_fecha_entrega_sugerida');
    
    if (fechaInput) {
        const año = fechaEntrega.getFullYear();
        const mes = String(fechaEntrega.getMonth() + 1).padStart(2, '0');
        const dia = String(fechaEntrega.getDate()).padStart(2, '0');
        fechaInput.value = `${año}-${mes}-${dia}`;
    }
}

function sumarDias(fecha, dias) {
    const nuevaFecha = new Date(fecha);
    nuevaFecha.setDate(nuevaFecha.getDate() + dias);
    return nuevaFecha;
}

function formatDate(fecha) {
    const año = fecha.getFullYear();
    const mes = String(fecha.getMonth() + 1).padStart(2, '0');
    const dia = String(fecha.getDate()).padStart(2, '0');
    return `${año}-${mes}-${dia}`;
}


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
// MOSTRAR MODAL CON ASIGNACIÓN DE INVENTARIO
// ============================================
window.mostrarModalPedido = function(id, folio) {
    document.getElementById('confirmar_pedido_id').value = id;
    document.getElementById('confirmar_pedido_folio').textContent = folio;
    
    // Mostrar carga
    document.getElementById('cargandoAsignacion').style.display = 'block';
    document.getElementById('asignacionTablaContainer').style.display = 'none';
    
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmarPedido'));
    modal.show();
    
    // Cargar disponibilidad de inventario
    cargarDisponibilidadInventario(id);
};

// ============================================
// CARGAR DISPONIBILIDAD DE INVENTARIO POR SUCURSAL
// ============================================
async function cargarDisponibilidadInventario(cotizacionId) {
    try {
        const response = await fetch(`/ventas/cotizaciones/${cotizacionId}/disponibilidad-inventario`, {
            headers: { 'Accept': 'application/json' }
        });
        
        if (!response.ok) {
            throw new Error('Error al cargar disponibilidad');
        }
        
        const data = await response.json();
        
        if (data.success) {
            renderizarAsignacionInventario(data.data);
        } else {
            throw new Error(data.message || 'Error al cargar disponibilidad');
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('cargandoAsignacion').innerHTML = `
            <i class="bi bi-exclamation-triangle text-danger"></i> 
            Error al cargar disponibilidad: ${error.message}
        `;
    }
}

// ============================================
// RENDERIZAR TABLA DE ASIGNACIÓN DE INVENTARIO
// ============================================
function renderizarAsignacionInventario(datos, mensaje = null, todosExternos = false) {
    const container = document.getElementById('asignacionTablaContainer');
    const loading = document.getElementById('cargandoAsignacion');

    if (todosExternos) {
        loading.innerHTML = `
            <div class="reprogram-alert">
                <i class="bi bi-info-circle-fill"></i>
                <div>
                    ${mensaje || 'Esta cotización contiene solo productos externos (sobre pedido). No requieren asignación de inventario.'}
                    <br><small>Los productos externos se asignarán automáticamente al crear el pedido.</small>
                </div>
            </div>
        `;
        return;
    }

    if (!datos || datos.length === 0) {
        loading.innerHTML = `
            <div class="reprogram-alert">
                <i class="bi bi-info-circle-fill"></i>
                <div>No hay artículos para asignar en esta cotización.</div>
            </div>
        `;
        return;
    }

    loading.style.display = 'none';
    container.style.display = 'block';

    let html = '';
    let primerIncompletoAsignado = false;

    datos.forEach((articulo, index) => {
        const totalRequerido = articulo.cantidad;

        // Filtrar sucursales con stock
        const sucursalesConStock = (articulo.stock_por_sucursal || [])
            .filter(s => s.inventario > 0)
            .map(s => ({ ...s, inventario: Math.floor(s.inventario) }))
            .sort((a, b) => b.inventario - a.inventario);

        const sucursalesSinStock = (articulo.stock_por_sucursal || [])
            .filter(s => s.inventario <= 0);

        const totalDisponible = sucursalesConStock.reduce((sum, s) => sum + s.inventario, 0);

        // Asignación automática solo con sucursales con stock
        let restante = totalRequerido;
        let totalAsignado = 0;
        let asignacionesMap = {};

        sucursalesConStock.forEach(suc => {
            if (restante > 0) {
                const asignar = Math.min(suc.inventario, restante);
                if (asignar > 0) {
                    asignacionesMap[suc.id_sucursal] = asignar;
                    restante -= asignar;
                    totalAsignado += asignar;
                }
            } else {
                asignacionesMap[suc.id_sucursal] = 0;
            }
        });

        const necesitaSobrePedido = restante > 0;
        const cantidadSobrePedido = necesitaSobrePedido ? Math.floor(restante) : 0;

        // Generar fila de sucursal
        const generarFila = (suc) => {
            const asignado = asignacionesMap[suc.id_sucursal] || 0;
            const sinStock = suc.inventario <= 0;
            const estaAsignada = asignado > 0;

            const clases = [
                'sucursal-row',
                sinStock ? 'sin-stock' : '',
                estaAsignada ? 'asignada' : ''
            ].filter(Boolean).join(' ');

            return `
                <div class="${clases}">
                    <span class="status-icon">
                        <i class="bi ${estaAsignada ? 'bi-check-lg' : 'bi-dash'}"></i>
                    </span>
                    <span class="sucursal-name">${escapeHtml(suc.nombre)}</span>
                    <span class="stock-info">
                        ${sinStock ? 'Sin stock' : `Stock: <strong>${suc.inventario}</strong>`}
                    </span>
                    <input type="number"
                           class="form-control form-control-sm asignar-cantidad"
                           data-articulo="${index}"
                           data-sucursal="${suc.id_sucursal}"
                           data-max="${suc.inventario}"
                           data-total-requerido="${totalRequerido}"
                           value="${asignado}"
                           min="0"
                           max="${suc.inventario}">
                </div>
            `;
        };

        // Fila Sobre Pedido
        const opcionesSucursales = (articulo.stock_por_sucursal || [])
            .map((s, idx) => `<option value="${s.id_sucursal}" ${idx === 0 ? 'selected' : ''}>${escapeHtml(s.nombre)}</option>`)
            .join('');

        const sobrePedidoHtml = necesitaSobrePedido ? `
            <div class="sobre-pedido-section" data-sobre-pedido-index="${index}">
                <div class="sobre-pedido-header">
                    <i class="bi bi-truck"></i>
                    Sobre pedido (faltante)
                </div>
                <div class="sobre-pedido-row">
                    <span style="font-size: 0.82rem; font-weight: 600;">Asignar:</span>
                    <input type="number"
                           class="form-control form-control-sm asignar-cantidad"
                           data-articulo="${index}"
                           data-sucursal="especial"
                           data-max="${cantidadSobrePedido}"
                           data-total-requerido="${totalRequerido}"
                           value="${cantidadSobrePedido}"
                           min="0"
                           max="${cantidadSobrePedido}"
                           style="width: 80px; text-align: center; font-weight: 700;">
                    <span style="font-size: 0.82rem;">a sucursal:</span>
                    <select class="form-select form-select-sm sucursal-sobre-pedido"
                            data-articulo="${index}"
                            style="flex: 1;">
                        ${opcionesSucursales}
                    </select>
                </div>
            </div>
        ` : '';

        // Estado inicial de la barra
        const pct = totalRequerido > 0 ? Math.min(100, Math.round((totalAsignado / totalRequerido) * 100)) : 0;
        const completo = totalAsignado === totalRequerido && totalRequerido > 0;
        const progresoEstado = completo ? 'bg-success' : (totalAsignado > 0 ? 'bg-warning' : 'bg-danger');

        // El primer producto incompleto se expande automáticamente
        const debeExpandir = !primerIncompletoAsignado && !completo;
        if (debeExpandir) primerIncompletoAsignado = true;

        html += `
            <div class="reprogram-card ${completo ? 'completo' : 'incompleto'} ${debeExpandir ? 'expandido' : ''}"
                 data-articulo-card="${index}">

                <div class="reprogram-card-header" onclick="toggleCardCotizacion(${index})">
                    <div class="d-flex align-items-center gap-2" style="min-width: 0;">
                        <div class="icon-box"><i class="bi bi-box-seam"></i></div>
                        <div style="min-width: 0;">
                            <div class="title" title="${escapeHtml(articulo.nombre)}">
                                ${escapeHtml(articulo.nombre)}
                            </div>
                        </div>
                    </div>

                    <span class="badge bg-primary" data-requerido="${totalRequerido}">Requerido: ${totalRequerido}</span>

                    <div class="header-summary">
                        <div class="mini-progress">
                            <div class="progress-bar ${progresoEstado}" style="width: ${pct}%"></div>
                        </div>
                        <span class="mini-pct ${completo ? 'text-success' : (totalAsignado > 0 ? 'text-warning' : 'text-muted')}">${pct}%</span>
                        <span class="mini-check"><i class="bi bi-check-lg"></i></span>
                    </div>

                    <i class="bi bi-chevron-down toggle-chevron"></i>
                </div>

                <div class="reprogram-card-context">
                    <div class="item">
                        <i class="bi bi-upc"></i>
                        <span>Código: <strong>${escapeHtml(articulo.codbar)}</strong></span>
                    </div>
                    <div class="item">
                        <i class="bi bi-boxes"></i>
                        <span>Disponible total: <strong>${totalDisponible}</strong></span>
                    </div>
                    ${articulo.es_externo ? `
                        <div class="item">
                            <i class="bi bi-exclamation-circle"></i>
                            <span class="badge bg-warning text-dark">Sobre pedido</span>
                        </div>
                    ` : ''}
                </div>

                <div class="reprogram-card-body">
                    ${sucursalesConStock.length > 0 ? `
                        <div class="sucursales-section">
                            <div class="sucursales-section-title">
                                <span class="dot"></span>
                                Sucursales con stock
                            </div>
                            ${sucursalesConStock.map(generarFila).join('')}
                        </div>
                    ` : ''}

                    ${sucursalesSinStock.length > 0 ? `
                        <div class="sucursales-section">
                            <div class="sucursales-section-title sin-stock">
                                <span class="dot"></span>
                                Sucursales sin stock
                            </div>
                            ${sucursalesSinStock.map(generarFila).join('')}
                        </div>
                    ` : ''}

                    ${sobrePedidoHtml}

                    <div class="reprogram-progress ${completo ? 'completo' : ''}" data-progress-index="${index}">
                        <div class="progress-info">
                            Asignado: <strong class="progress-asignado">${totalAsignado} / ${totalRequerido}</strong>
                        </div>
                        <div class="progress">
                            <div class="progress-bar ${progresoEstado}" role="progressbar" style="width: ${pct}%"></div>
                        </div>
                        <div class="progress-pct ${completo ? 'text-success' : (totalAsignado > 0 ? 'text-warning' : 'text-muted')}">${pct}%</div>
                        <span class="progress-check"><i class="bi bi-check-lg"></i></span>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;

    // Listeners de inputs
    container.querySelectorAll('.asignar-cantidad').forEach(input => {
        input.addEventListener('input', function() {
            actualizarAsignacion(this);
        });
    });

    // Prevenir propagación del click en inputs y selects (evita colapso/expansión accidental)
    container.querySelectorAll('.asignar-cantidad, .sucursal-sobre-pedido').forEach(el => {
        el.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });

    // Listeners de selects "Sobre Pedido"
    container.querySelectorAll('.sucursal-sobre-pedido').forEach(select => {
        select.addEventListener('change', function() {
            const articuloIndex = parseInt(this.dataset.articulo);
            const sucursalNombre = this.options[this.selectedIndex]?.text || '';
            // Actualizar visualmente si quieres mostrar el nombre
            // (por ahora no mostramos, el payload lo lee al confirmar)
        });
    });

    // Recalcular todo una vez renderizado
    setTimeout(() => {
        container.querySelectorAll('.asignar-cantidad').forEach(input => {
            actualizarAsignacion(input);
        });
    }, 50);
}

// ============================================
// TOGGLE Y AUTO-COLAPSO - COTIZACIONES
// ============================================

function toggleCardCotizacion(index) {
    const card = document.querySelector(`.reprogram-card[data-articulo-card="${index}"]`);
    if (!card) return;
    card.classList.toggle('expandido');
}

function expandirTodosCotizacion() {
    document.querySelectorAll('#asignacionTablaContainer .reprogram-card').forEach(card => {
        card.classList.add('expandido');
    });
}

function colapsarTodosCotizacion() {
    document.querySelectorAll('#asignacionTablaContainer .reprogram-card').forEach(card => {
        card.classList.remove('expandido');
    });
}

/**
 * Auto-colapsa la card si está completa. Se llama desde actualizarAsignacion.
 */
function autoColapsarSiCompletoCotizacion(articuloIndex) {
    const card = document.querySelector(`.reprogram-card[data-articulo-card="${articuloIndex}"]`);
    if (!card) return;

    const completo = card.classList.contains('completo');
    if (!completo) return;

    // No colapsar el que tiene el foco (usuario sigue interactuando)
    const focusedElement = document.activeElement;
    if (focusedElement && card.contains(focusedElement)) return;

    // Colapsar con un pequeño delay para que el usuario vea el check
    setTimeout(() => {
        card.classList.remove('expandido');
    }, 600);
}

// ============================================
// TOGGLE Y AUTO-COLAPSO - REPROGRAMACIÓN
// ============================================

function toggleCardReprogramacion(detalleId) {
    const card = document.querySelector(`.reprogram-card[data-detalle="${detalleId}"]`);
    if (!card) return;
    card.classList.toggle('expandido');
}

function expandirTodosReprogramacion() {
    document.querySelectorAll('#reprogramar_productos_container .reprogram-card').forEach(card => {
        card.classList.add('expandido');
    });
}

function colapsarTodosReprogramacion() {
    document.querySelectorAll('#reprogramar_productos_container .reprogram-card').forEach(card => {
        card.classList.remove('expandido');
    });
}

function autoColapsarSiCompletoReprogramacion(detalleId) {
    const card = document.querySelector(`.reprogram-card[data-detalle="${detalleId}"]`);
    if (!card) return;

    const completo = card.classList.contains('completo');
    if (!completo) return;

    const focusedElement = document.activeElement;
    if (focusedElement && card.contains(focusedElement)) return;

    setTimeout(() => {
        card.classList.remove('expandido');
    }, 600);
}

// ============================================
// MOSTRAR MÁS SUCURSALES
// ============================================
function mostrarMasSucursales(index) {
    const filaOculta = document.getElementById(`sucursales-ocultas-${index}`);
    const botonFila = document.getElementById(`ver-mas-${index}`);
    
    if (filaOculta) {
        if (filaOculta.style.display === 'none') {
            filaOculta.style.display = 'table-row';
            if (botonFila) {
                botonFila.querySelector('button').innerHTML = '<i class="bi bi-eye-slash"></i> Ocultar sucursales';
            }
        } else {
            filaOculta.style.display = 'none';
            if (botonFila) {
                const sucursalesOcultas = document.querySelectorAll(`#sucursales-ocultas-${index} tbody tr`).length;
                botonFila.querySelector('button').innerHTML = `<i class="bi bi-eye"></i> Ver más sucursales (${sucursalesOcultas} con inventario)`;
            }
        }
    }
}

// ============================================
// ACTUALIZAR ASIGNACIÓN CON VALIDACIÓN
// ============================================
function actualizarAsignacion(input) {
    const articuloIndex = parseInt(input.dataset.articulo);
    const totalRequerido = parseInt(input.dataset.totalRequerido) || 0;
    const esEspecial = input.dataset.sucursal === 'especial';

    // Validar stock por sucursal (no aplica a "especial")
    if (!esEspecial) {
        const max = parseInt(input.dataset.max) || 0;
        let valor = parseInt(input.value) || 0;

        if (valor > max) {
            input.value = max;
            if (window.mostrarToast) {
                window.mostrarToast(`No puedes asignar más de ${max} unidades en esta sucursal`, 'warning');
            }
        }

        if (valor < 0) {
            input.value = 0;
        }
    }

    // Recalcular total del artículo
    const card = document.querySelector(`.reprogram-card[data-articulo-card="${articuloIndex}"]`);
    if (!card) return;

    const inputs = card.querySelectorAll('.asignar-cantidad');
    let totalAsignado = 0;

    inputs.forEach(inp => {
        totalAsignado += parseInt(inp.value) || 0;
    });

    // Validar que no exceda el requerido (auto-ajustar al input actual)
    if (totalAsignado > totalRequerido) {
        const excedente = totalAsignado - totalRequerido;
        const valorActual = parseInt(input.value) || 0;
        const nuevoValor = Math.max(0, valorActual - excedente);
        input.value = nuevoValor;

        // Recalcular
        totalAsignado = 0;
        inputs.forEach(inp => {
            totalAsignado += parseInt(inp.value) || 0;
        });

        if (window.mostrarToast) {
            window.mostrarToast(
                `El total asignado no puede exceder el requerido (${totalRequerido})`,
                'warning'
            );
        }
    }

    // Actualizar barra de progreso
    const pct = totalRequerido > 0
        ? Math.min(100, Math.round((totalAsignado / totalRequerido) * 100))
        : 0;
    const completo = totalAsignado === totalRequerido && totalRequerido > 0;

    const progressBox = card.querySelector('.reprogram-progress');
    if (progressBox) {
        const asignadoLabel = progressBox.querySelector('.progress-asignado');
        if (asignadoLabel) asignadoLabel.textContent = `${totalAsignado} / ${totalRequerido}`;

        const barra = progressBox.querySelector('.progress-bar');
        if (barra) {
            barra.className = `progress-bar ${completo ? 'bg-success' : (totalAsignado > 0 ? 'bg-warning' : 'bg-danger')}`;
            barra.style.width = `${pct}%`;
        }

        const pctLabel = progressBox.querySelector('.progress-pct');
        if (pctLabel) {
            pctLabel.textContent = `${pct}%`;
            pctLabel.className = `progress-pct ${completo ? 'text-success' : (totalAsignado > 0 ? 'text-warning' : 'text-muted')}`;
        }

        if (completo) {
            progressBox.classList.add('completo');
        } else {
            progressBox.classList.remove('completo');
        }
    }

    // Actualizar estado del card
    card.classList.remove('completo', 'incompleto');
    card.classList.add(completo ? 'completo' : 'incompleto');

    // Actualizar estado de cada fila de sucursal
    card.querySelectorAll('.sucursal-row').forEach(row => {
        const inp = row.querySelector('.asignar-cantidad');
        const asignado = parseInt(inp?.value) || 0;
        const icon = row.querySelector('.status-icon i');

        if (asignado > 0) {
            row.classList.add('asignada');
            if (icon) icon.className = 'bi bi-check-lg';
        } else {
            row.classList.remove('asignada');
            if (icon) icon.className = 'bi bi-dash';
        }
    });

    // Actualizar resumen mini del header
    const headerSummary = card.querySelector('.header-summary');
    if (headerSummary) {
        const miniProgress = headerSummary.querySelector('.mini-progress .progress-bar');
        const miniPct = headerSummary.querySelector('.mini-pct');
        const miniCheck = headerSummary.querySelector('.mini-check');

        if (miniProgress) {
            miniProgress.className = `progress-bar ${completo ? 'bg-success' : (totalAsignado > 0 ? 'bg-warning' : 'bg-danger')}`;
            miniProgress.style.width = `${pct}%`;
        }

        if (miniPct) {
            miniPct.textContent = `${pct}%`;
            miniPct.className = `mini-pct ${completo ? 'text-success' : (totalAsignado > 0 ? 'text-warning' : 'text-muted')}`;
        }
    }

    // Auto-colapsar si está completo
    if (completo) {
        autoColapsarSiCompletoCotizacion(articuloIndex);
    }
}

// ============================================
// CONFIRMAR Y GENERAR PEDIDO CON ASIGNACIONES
// ============================================
window.confirmarGenerarPedidoConAsignacion = function() {
    const id = document.getElementById('confirmar_pedido_id').value;
    const folio = document.getElementById('confirmar_pedido_folio').textContent;

    if (!id) return;

    const asignaciones = [];
    const container = document.getElementById('asignacionTablaContainer');
    if (!container) {
        if (window.mostrarToast) {
            window.mostrarToast('Error: No hay datos de asignación', 'danger');
        }
        return;
    }

    const articulos = container.querySelectorAll('.reprogram-card');
    let todoCompletado = true;
    let hayError = false;
    let mensajeError = '';

    articulos.forEach((articuloCard, index) => {
        const inputs = articuloCard.querySelectorAll('.asignar-cantidad');
        const nombreArticulo = articuloCard.querySelector('.title')?.textContent?.trim() || `Artículo ${index + 1}`;
        const totalRequerido = parseInt(articuloCard.querySelector('.badge.bg-primary')?.dataset.requerido) || 0;
        let totalAsignado = 0;
        let asignacionesPorArticulo = [];
        let cantidadSobrePedido = 0;

        const selectSobrePedido = articuloCard.querySelector('.sucursal-sobre-pedido');

        inputs.forEach(input => {
            const valor = parseInt(input.value) || 0;
            const maxPermitido = parseInt(input.dataset.max) || 0;
            const esEspecial = input.dataset.sucursal === 'especial';

            if (!esEspecial && valor > maxPermitido) {
                hayError = true;
                const nombreSucursal = input.closest('.sucursal-row')?.querySelector('.sucursal-name')?.textContent?.trim() || 'Sucursal';
                mensajeError = `No puedes asignar más de ${maxPermitido} unidades en ${nombreSucursal} para "${nombreArticulo}"`;
                return;
            }

            if (valor > 0) {
                let sucursalId = null;
                let sucursalNombre = '';
                let esAgregado = 0;

                if (esEspecial) {
                    if (selectSobrePedido) {
                        const valorSelect = selectSobrePedido.value;
                        sucursalId = parseInt(valorSelect) || null;
                        sucursalNombre = selectSobrePedido.options[selectSobrePedido.selectedIndex]?.text || 'Sobre Pedido';
                    } else {
                        sucursalNombre = 'Sobre Pedido';
                    }
                    esAgregado = 1;
                    cantidadSobrePedido += valor;
                } else {
                    sucursalId = parseInt(input.dataset.sucursal);
                    sucursalNombre = input.closest('.sucursal-row')?.querySelector('.sucursal-name')?.textContent?.trim() || 'Sucursal';
                    esAgregado = 0;
                }

                asignacionesPorArticulo.push({
                    sucursal: sucursalId,
                    sucursal_nombre: sucursalNombre,
                    cantidad: valor,
                    es_agregado: esAgregado
                });
                totalAsignado += valor;
            }
        });

        if (totalAsignado > totalRequerido) {
            hayError = true;
            mensajeError = `"${nombreArticulo}" tiene ${totalAsignado} unidades asignadas, pero solo requiere ${totalRequerido}.`;
            return;
        }
        
        // Validar que si hay Sobre Pedido, tenga sucursal seleccionada
        if (cantidadSobrePedido > 0) {
            // Revisar si hay alguna asignación de Sobre Pedido con sucursal null
            const tieneSobrePedidoSinSucursal = asignacionesPorArticulo.some(a => a.es_agregado === 1 && !a.sucursal);
            if (tieneSobrePedidoSinSucursal) {
                hayError = true;
                mensajeError = `Para "${nombreArticulo}" debes seleccionar una sucursal para las unidades "Sobre Pedido"`;
                return;
            }
        }

        if (totalAsignado !== totalRequerido) {
            todoCompletado = false;
        }

        asignaciones.push({
            articulo_index: index,
            total_requerido: totalRequerido,
            total_asignado: totalAsignado,
            detalles: asignacionesPorArticulo
        });
    });

    if (hayError) {
        if (window.mostrarToast) window.mostrarToast(mensajeError, 'danger');
        return;
    }

    if (!todoCompletado) {
        if (window.mostrarToast) {
            window.mostrarToast('Las cantidades asignadas no coinciden con las requeridas', 'warning');
        }
        return;
    }

    const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarPedido'));
    if (modal) modal.hide();

    if (window.mostrarToast) {
        window.mostrarToast('Convirtiendo a pedido...', 'warning');
    }

    fetch(`/ventas/cotizaciones/${id}/generar-pedido-con-asignacion`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ asignaciones: asignaciones })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (window.mostrarToast) {
                window.mostrarToast(`Cotización ${folio} convertida a pedido correctamente`, 'success');
            }
            setTimeout(() => location.reload(), 1000);
        } else {
            if (window.mostrarToast) {
                window.mostrarToast(data.message || 'Error al convertir a pedido', 'danger');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) {
            window.mostrarToast('Error de conexión', 'danger');
        }
    });
};

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
let timeoutBusquedaCotizacion = null;
let timeoutSpinnerCotizacion = null;

document.getElementById('buscarCotizacion')?.addEventListener('keyup', function() {
    const searchTerm = this.value.trim();

    clearTimeout(timeoutBusquedaCotizacion);
    clearTimeout(timeoutSpinnerCotizacion);

    if (searchTerm.length === 0) {
        refrescarTablaCotizaciones(false, false);
        return;
    }

    if (searchTerm.length >= 3) {
        // Spinner con delay
        timeoutSpinnerCotizacion = setTimeout(() => {
            window.mostrarSpinnerTabla('#tabla-cotizaciones-container tbody', 'Buscando cotizaciones...', 10);
        }, 300);

        timeoutBusquedaCotizacion = setTimeout(() => {
            refrescarTablaCotizaciones(false, false);
        }, 200);
    }
});

    // Establecer la sucursal del usuario logueado para el modal de nueva cotización
    window.sucursalUsuarioDefecto = {{ $sucursalAsignadaUsuario ?? 0 }};

// ============================================
// FUNCIÓN PARA ABRIR MODAL DE SEGUIMIENTO (DESDE COTIZACIONES)
// ============================================

window.abrirModalSeguimiento = function(id, folio) {
    if (window.mostrarToast) {
        window.mostrarToast('Cargando datos de la cotización para contacto...', 'warning');
    }
    
    fetch(`/ventas/seguimiento/cotizacion/${id}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Usar la función global del archivo JS
            if (typeof window.cargarDatosModalSeguimiento === 'function') {
                window.cargarDatosModalSeguimiento(data.data);
                const modal = new bootstrap.Modal(document.getElementById('modalSeguimiento'));
                modal.show();
                if (window.mostrarToast) window.mostrarToast('Datos cargados', 'success');
            } else {
                console.error('Error: window.cargarDatosModalSeguimiento no está definida');
                if (window.mostrarToast) window.mostrarToast('Error al cargar los datos', 'danger');
            }
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al cargar datos', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
};

// ============================================
// POLLING AJAX PARA ACTUALIZAR TABLA DE COTIZACIONES
// ============================================
let pollingCotizacionesInterval = null;
let ultimoIdCotizacion = {{ $cotizaciones->isNotEmpty() ? $cotizaciones->first()->id_cotizacion : 0 }};
let estaRefrescando = false;
let filtroBusquedaActual = ''; // Variable para guardar los terminos de busqueda

function refrescarTablaCotizaciones(mostrarNotificacion = false, desdePolling = false) {

    // Si es polling automático y hay un modal abierto, no ejecutar
    if (desdePolling) {
        const modalAbierto = document.querySelector('.modal.show');
        if (modalAbierto) {
            return;
        }
    }

    if (estaRefrescando) return;
    estaRefrescando = true;

    const btnRefrescar = document.getElementById('btnRefrescarCotizaciones');
    const iconoOriginal = btnRefrescar?.innerHTML;

    if (!desdePolling && btnRefrescar) {
        btnRefrescar.innerHTML = '<i class="bi bi-arrow-repeat fa-spin"></i> Refrescando...';
        btnRefrescar.disabled = true;
    }

    const buscarInput = document.getElementById('buscarCotizacion');
    const searchTerm = buscarInput ? buscarInput.value.trim() : '';

    // Leer per_page de la variable global
    const perPageActual = window.perPageActual || 15;

    // ============================================
    // Spinner condicional (sin delay, inmediato)
    // ============================================
    const hayBusquedaActiva = searchTerm.length >= 3;
    const esClickManualRefrescar = mostrarNotificacion === true;

    if (!desdePolling && (hayBusquedaActiva || esClickManualRefrescar)) {
        const mensaje = hayBusquedaActiva
            ? 'Buscando cotizaciones...'
            : 'Actualizando cotizaciones...';
        window.mostrarSpinnerTabla('#tabla-cotizaciones-container tbody', mensaje, 10);
    }

    let url = '{{ route("ventas.cotizaciones.refrescar") }}?ultimo_id=' + ultimoIdCotizacion;
    if (searchTerm.length > 0) {
        url += '&search_term=' + encodeURIComponent(searchTerm);
    }
    url += '&per_page=' + encodeURIComponent(perPageActual);

    fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Error en la petición');
        return response.json();
    })
    .then(data => {
        // Cancelar el timeout del spinner porque ya llegaron los datos
        clearTimeout(timeoutSpinnerCotizacion);
        if (data.success && data.html) {
            const container = document.getElementById('tabla-cotizaciones-container');
            if (container) {
                container.innerHTML = data.html;
                ultimoIdCotizacion = data.ultimo_id;

                // Reasignar event listeners a los nuevos links de paginación
                document.querySelectorAll('#tabla-cotizaciones-container .pagination a').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const pageUrl = this.getAttribute('href');
                        if (pageUrl) {
                            cargarPaginaCotizaciones(pageUrl);
                        }
                    });
                });

                // Re-inicializar el selector (porque el partial se re-renderizó y tiene un <select> nuevo)
                window.inicializarSelectorPerPage(
                    'perPageSelect',
                    'crm_per_page_cotizaciones',
                    () => refrescarTablaCotizaciones(false, false)
                );

                if (!desdePolling && mostrarNotificacion && window.mostrarToast) {
                    window.mostrarToast('Cotizaciones actualizadas', 'success');
                }
            }
        }
    })
    .catch(error => {
        clearTimeout(timeoutSpinnerCotizacion);
        console.error('Error refrescando tabla:', error);
        const container = document.getElementById('tabla-cotizaciones-container');
        if (container) {
            container.innerHTML = `
                <div class="text-center py-5 text-danger">
                    <i class="bi bi-exclamation-triangle fs-1"></i>
                    <p class="mt-2">Error al buscar cotizaciones</p>
                </div>
            `;
        }
        if (!desdePolling && mostrarNotificacion && window.mostrarToast) {
            window.mostrarToast('Error al actualizar cotizaciones', 'danger');
        }
    })
    .finally(() => {
        clearTimeout(timeoutSpinnerCotizacion);
        estaRefrescando = false;
        if (!desdePolling && btnRefrescar) {
            btnRefrescar.innerHTML = iconoOriginal;
            btnRefrescar.disabled = false;
        }
    });
}

function cargarPaginaCotizaciones(url) {
    // Extraer el número de página de la URL
    const urlParams = new URLSearchParams(url.split('?')[1]);
    const page = urlParams.get('page') || 1;
    
    // Obtener filtros actuales
    const buscarInput = document.getElementById('buscarCotizacion');
    const searchTerm = buscarInput ? buscarInput.value.trim() : '';
    
    // Construir URL con los mismos parámetros + página
    let fetchUrl = '{{ route("ventas.cotizaciones.refrescar") }}';
    fetchUrl += '?page=' + page;
    if (searchTerm.length > 0) {
        fetchUrl += '&search_term=' + encodeURIComponent(searchTerm);
    }
    fetchUrl += '&ultimo_id=' + ultimoIdCotizacion;
    
    fetch(fetchUrl, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.html) {
            const container = document.getElementById('tabla-cotizaciones-container');
            if (container) {
                container.innerHTML = data.html;
                ultimoIdCotizacion = data.ultimo_id;
                
                // Reasignar event listeners a los nuevos links
                document.querySelectorAll('#tabla-cotizaciones-container .pagination a').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const pageUrl = this.getAttribute('href');
                        if (pageUrl) {
                            cargarPaginaCotizaciones(pageUrl);
                        }
                    });
                });
            }
        }
    })
    .catch(error => console.error('Error cargando página:', error));
}

function iniciarPollingCotizaciones() {
    if (pollingCotizacionesInterval) clearInterval(pollingCotizacionesInterval);
    
    pollingCotizacionesInterval = setInterval(() => {
        if (!document.hidden) {
            refrescarTablaCotizaciones(false, true);
        }
    }, 30000);
}

// Al volver a la pestaña
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        refrescarTablaCotizaciones(false, true);
    }
});

window.guardarNuevaCotizacion = function() {
    const clienteId = document.getElementById('cliente_id').value;
    const faseId = document.getElementById('fase_id').value;
    
    if (!clienteId) {
        if (window.mostrarToast) window.mostrarToast('Selecciona un cliente', 'warning');
        return;
    }
    
    if (!faseId) {
        if (window.mostrarToast) window.mostrarToast('Selecciona una fase', 'warning');
        return;
    }
    
    if (articulosSeleccionados.length === 0) {
        if (window.mostrarToast) window.mostrarToast('Agrega al menos un artículo', 'warning');
        return;
    }
    
    const articulos = articulosSeleccionados.map((a) => ({
        codbar: a.codbar || a.ean || '',
        cantidad: a.cantidad,
        precio_unitario: a.precio,
        descuento: a.descuento,
        id_convenio: a.id_convenio,
        es_externo: a.es_externo ? 1 : 0
    }));
    
    let url = '{{ route("ventas.cotizaciones.store") }}';
    let method = 'POST';
    
    if (esNuevaVersion && cotizacionOrigenId) {
        url = `/ventas/cotizaciones/${cotizacionOrigenId}/guardar-version`;
        method = 'POST';
    }
    
    const formData = {
        id_cliente: parseInt(clienteId),
        id_fase: parseInt(faseId),
        id_clasificacion: document.getElementById('clasificacion_id').value || null,
        id_sucursal_asignada: document.getElementById('sucursal_asignada_id').value || null,
        certeza: parseInt(document.getElementById('certeza')?.value || 0),
        comentarios: document.getElementById('comentarios').value,
        articulos: articulos,
        _token: '{{ csrf_token() }}'
    };
    
    // Deshabilitar botón para evitar múltiples envíos
    const btn = document.querySelector('#modalNuevaCotizacion .btn-primary');
    const textoOriginal = btn?.innerHTML;
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';
    }
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw err; });
        }
        return response.json();
    })
    .then(data => {
        // Cerrar modal
        const modalElement = document.getElementById('modalNuevaCotizacion');
        if (modalElement) {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
        }
        
        // Limpiar backdrop
        limpiarBackdrops();
        
        if (data.success) {
            if (window.mostrarToast) window.mostrarToast(data.message, 'success');
            esNuevaVersion = false;
            cotizacionOrigenId = null;
            
            setTimeout(() => {
                refrescarTablaCotizaciones();
            }, 1000);
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al guardar', 'danger');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = textoOriginal;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Limpiar backdrop en caso de error
        limpiarBackdrops();
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = textoOriginal;
        }
    });
};

// Polling automático (marcado como desdePolling = true)
function iniciarPollingCotizaciones() {
    if (pollingCotizacionesInterval) clearInterval(pollingCotizacionesInterval);
    
    pollingCotizacionesInterval = setInterval(() => {
        if (!document.hidden) {
            refrescarTablaCotizaciones(false, true);
        }
    }, 30000);
}

// Al volver a la pestaña - SIN notificación (solo actualiza en silencio)
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        refrescarTablaCotizaciones(false, true);
    }
});

// Botón manual (NO es polling)
function agregarBotonRefrescar() {
    const headerRow = document.querySelector('.row.mb-4 .col-md-6.text-end');
    if (headerRow && !document.getElementById('btnRefrescarCotizaciones')) {
        const btnHtml = `
            <button type="button" class="btn btn-sm btn-outline-primary me-2" id="btnRefrescarCotizaciones">
                <i class="bi bi-arrow-repeat"></i> Refrescar
            </button>
        `;
        headerRow.insertAdjacentHTML('beforeend', btnHtml);
        
        document.getElementById('btnRefrescarCotizaciones')?.addEventListener('click', () => {
            refrescarTablaCotizaciones(true, false); // mostrar notificación, no es polling
        });
    }
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    agregarBotonRefrescar();
    iniciarPollingCotizaciones();
});

// ============================================
// LISTENER PARA BOTONES DE EDICIÓN
// ============================================
document.addEventListener('click', function(e) {
    // Si el clic es dentro del modal de edición, ignorar
    const modalEditar = document.getElementById('modalEditarCotizacion');
    if (modalEditar && modalEditar.contains(e.target)) {
        return;
    }
    
    // Botón de editar cotización
    const btnEditar = e.target.closest('.btn-editar-cotizacion');
    if (btnEditar) {
        const id = btnEditar.dataset.id;
        if (id) {
            e.preventDefault();
            mostrarOpcionesEdicion(id);
        }
        return;
    }
    
    // Botón de crear independiente
    const btnIndependiente = e.target.closest('.btn-crear-independiente');
    if (btnIndependiente) {
        const id = btnIndependiente.dataset.id;
        if (id) {
            e.preventDefault();
            crearNuevaIndependiente(id);
        }
        return;
    }
});

// Limpiar intervalo al salir
window.addEventListener('beforeunload', function() {
    if (pollingCotizacionesInterval) clearInterval(pollingCotizacionesInterval);
});
</script>
@endpush