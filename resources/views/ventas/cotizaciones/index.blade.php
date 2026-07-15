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

<!-- Modal Confirmar Convertir a Pedido -->
<div class="modal fade" id="modalConfirmarPedido" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-cart-check"></i> Convertir a Pedido
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Confirma la conversión de la cotización <strong id="confirmar_pedido_folio"></strong> en un pedido.</p>
                <p class="text-muted small">
                    <i class="bi bi-info-circle"></i> 
                    Asigna las cantidades a las sucursales correspondientes. El total debe coincidir con la cantidad solicitada.
                </p>
                <input type="hidden" id="confirmar_pedido_id">
                
                <!-- Tabla de asignación de inventario -->
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
    @if(isset($cotizaciones) && method_exists($cotizaciones, 'links'))
    <div class="d-flex justify-content-end mt-3">
        {{ $cotizaciones->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
    @endif

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

// ============================================
// CREAR NUEVA VERSIÓN (precarga modal y cierra el de opciones)
// ============================================
window.crearNuevaVersion = function(id) {
    // Asegurar que id sea un número
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
    
    const modalNueva = new bootstrap.Modal(document.getElementById('modalNuevaCotizacion'));
    
    fetch(`/ventas/cotizaciones/${cotizacionId}/preparar-version`, {
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
            // Asegurar que data.data es un objeto
            if (typeof data.data === 'object' && data.data !== null) {
                precargarDatosCotizacion(data.data);
            } else {
                console.error('Datos de cotización inválidos:', data.data);
                if (window.mostrarToast) {
                    window.mostrarToast('Datos de cotización inválidos', 'danger');
                }
                return;
            }
            modalNueva.show();
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al preparar nueva versión', 'danger');
        }
    })
    .catch(error => {
        console.error('Error en crearNuevaVersion:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
};

// ============================================
// CREAR COTIZACIÓN INDEPENDIENTE (sin versionado)
// ============================================
// Variable para esperar bootstrap
window.crearNuevaIndependiente = function(id) {
    // Si id es un número o string, convertirlo
    const cotizacionId = typeof id === 'string' ? parseInt(id, 10) : Number(id);
    
    if (isNaN(cotizacionId) || cotizacionId <= 0) {
        console.error('ID inválido en crearNuevaIndependiente:', id);
        if (window.mostrarToast) {
            window.mostrarToast('ID de cotización inválido', 'danger');
        }
        return;
    }
    
    // Función interna que ejecuta la lógica
    function ejecutarCrearIndependiente() {
        try {
            const modalOpciones = bootstrap.Modal.getInstance(document.getElementById('modalOpcionesEdicion'));
            if (modalOpciones) modalOpciones.hide();
            
            const modalEditar = bootstrap.Modal.getInstance(document.getElementById('modalEditarCotizacion'));
            if (modalEditar) modalEditar.hide();
            
            const modalElement = document.getElementById('modalNuevaCotizacion');
            if (!modalElement) {
                console.error('Modal no encontrado');
                return;
            }
            
            const modalNueva = new bootstrap.Modal(modalElement);
            
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
                    
                    if (typeof window.setEsNuevaVersion === 'function') {
                        window.setEsNuevaVersion(false, null);
                    }
                    
                    window.esNuevaIndependiente = true;
                    // Asegurar que cotizacion es un objeto
                    if (typeof cotizacion === 'object' && cotizacion !== null) {
                        precargarDatosCotizacionIndependiente(cotizacion);
                    } else {
                        console.error('Datos de cotización inválidos:', cotizacion);
                        if (window.mostrarToast) {
                            window.mostrarToast('Datos de cotización inválidos', 'danger');
                        }
                        return;
                    }
                    modalNueva.show();
                } else {
                    if (window.mostrarToast) window.mostrarToast(data.message || 'Error al cargar cotización', 'danger');
                }
            })
            .catch(error => {
                console.error('Error en crearNuevaIndependiente:', error);
                if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
            });
        } catch (error) {
            console.error('Error en ejecutarCrearIndependiente:', error);
        }
    }
    
    // Verificar si bootstrap está disponible
    if (typeof bootstrap !== 'undefined') {
        ejecutarCrearIndependiente();
    } else {
        // Esperar a que Bootstrap se cargue (máximo 5 segundos)
        let intentos = 0;
        const maxIntentos = 50; // 5 segundos con intervalos de 100ms
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
// ENVIAR COTIZACIÓN (generar PDF - botón siempre visible)
// ============================================
window.enviarCotizacion = function(id, folio) {
    // Mostrar toast de confirmación
    if (window.mostrarToast) {
        window.mostrarToast('Generando ticket PDF...', 'warning');
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
// CONFIRMAR SOBREESCRIBIR (sin segundo modal)
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
            <div class="alert alert-warning">
                <i class="bi bi-info-circle"></i> 
                ${mensaje || 'Esta cotización contiene solo productos externos (sobre pedido). No requieren asignación de inventario.'}
                <br>
                <small>Los productos externos se asignarán automáticamente al crear el pedido.</small>
            </div>
        `;
        return;
    }
    
    if (!datos || datos.length === 0) {
        loading.innerHTML = `
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> 
                No hay artículos para asignar en esta cotización.
                <br>
                <small>Si la cotización solo tiene productos externos, no requieren asignación de inventario.</small>
            </div>
        `;
        return;
    }
    
    loading.style.display = 'none';
    container.style.display = 'block';
    
    let html = '';
    
    datos.forEach((articulo, index) => {
        const totalRequerido = articulo.cantidad;
        
        // Filtrar solo las sucursales con stock para la asignación automática
        const sucursalesConStock = (articulo.stock_por_sucursal || [])
            .filter(s => s.inventario > 0)
            .map(s => ({ ...s, inventario: Math.floor(s.inventario) }))
            .sort((a, b) => b.inventario - a.inventario);
        
        // Todas las sucursales (incluyendo las sin stock) para el select de "Sobre Pedido"
        const todasLasSucursales = articulo.stock_por_sucursal || [];
        const totalDisponible = sucursalesConStock.reduce((sum, s) => sum + s.inventario, 0);
        
        // Asignación automática solo con sucursales que tienen stock
        let restante = totalRequerido;
        let totalAsignado = 0;
        let asignaciones = [];
        
        for (let i = 0; i < sucursalesConStock.length && restante > 0; i++) {
            const sucursal = sucursalesConStock[i];
            const asignar = Math.min(sucursal.inventario, restante);
            if (asignar > 0) {
                asignaciones.push({
                    sucursal: sucursal,
                    asignado: asignar,
                    mostrado: true
                });
                restante -= asignar;
                totalAsignado += asignar;
            }
        }
        
        // Si aún falta stock, marcar como "Sobre Pedido"
        const necesitaSobrePedido = restante > 0;
        
        html += `
            <div class="card mb-3" data-articulo-index="${index}">
                <div class="card-header bg-light">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>${escapeHtml(articulo.nombre)}</strong>
                            <br><small class="text-muted">Código: ${escapeHtml(articulo.codbar)}</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <span class="badge bg-primary">Requerido: ${totalRequerido}</span>
                        </div>
                        <div class="col-md-3 text-center">
                            <span class="badge bg-success">Disponible: ${totalDisponible}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-bordered" id="asignacion-${index}">
                        <thead>
                            <tr>
                                <th>Sucursal</th>
                                <th class="text-center">Stock</th>
                                <th class="text-center">Cantidad a Asignar</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
        `;
        
        // Mostrar solo las sucursales con asignación
        const sucursalesMostradas = asignaciones.filter(a => a.asignado > 0);
        const hayMasSucursales = sucursalesConStock.length > sucursalesMostradas.length;
        
        // Sucursales con asignación
        sucursalesMostradas.forEach((item, idx) => {
            const sucursal = item.sucursal;
            const maxAsignar = Math.floor(sucursal.inventario);
            const valorAsignado = Math.floor(item.asignado);
            
            html += `
                <tr>
                    <td>${escapeHtml(sucursal.nombre)}</td>
                    <td class="text-center">${maxAsignar}</td>
                    <td class="text-center">
                        <input type="number" 
                               class="form-control form-control-sm text-center asignar-cantidad" 
                               data-articulo="${index}" 
                               data-sucursal="${sucursal.id_sucursal}"
                               data-max="${maxAsignar}"
                               data-total-requerido="${totalRequerido}"
                               value="${valorAsignado}" 
                               min="0" 
                               max="${maxAsignar}"
                               oninput="actualizarAsignacion(this, ${index})" 
                               style="width: 80px;">
                    </td>
                    <td class="text-center" id="estado-${index}-${sucursal.id_sucursal}">
                        <span class="badge bg-success">Asignado</span>
                    </td>
                </tr>
            `;
        });
        
        // Ver más sucursales
        if (hayMasSucursales) {
            const sucursalesOcultas = sucursalesConStock.filter(s => 
                !asignaciones.some(a => a.sucursal.id_sucursal === s.id_sucursal && a.asignado > 0)
            );
            
            html += `
                <tr id="ver-mas-${index}">
                    <td colspan="4" class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-info" 
                                onclick="mostrarMasSucursales(${index})">
                            <i class="bi bi-eye"></i> Ver más sucursales 
                            (${sucursalesOcultas.length} con inventario)
                        </button>
                    </td>
                </tr>
                <tr id="sucursales-ocultas-${index}" style="display: none;">
                    <td colspan="4">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <tbody>
                        `;
            
            sucursalesOcultas.forEach((sucursal, idxOculto) => {
                const maxAsignar = Math.floor(sucursal.inventario);
                html += `
                    <tr>
                        <td>${escapeHtml(sucursal.nombre)}</td>
                        <td class="text-center">${maxAsignar}</td>
                        <td class="text-center">
                            <input type="number" 
                                   class="form-control form-control-sm text-center asignar-cantidad" 
                                   data-articulo="${index}" 
                                   data-sucursal="${sucursal.id_sucursal}"
                                   data-max="${maxAsignar}"
                                   data-total-requerido="${totalRequerido}"
                                   value="0" 
                                   min="0" 
                                   max="${maxAsignar}"
                                   oninput="actualizarAsignacion(this, ${index})">
                        </td>
                        <td class="text-center" id="estado-${index}-${sucursal.id_sucursal}">
                            <span class="badge bg-secondary">Pendiente</span>
                        </td>
                    </tr>
                `;
            });
            
            html += `
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            `;
        }
        
        // Sobre Pedido - con todas las sucursales en el select
        if (necesitaSobrePedido) {
            // Usar todas las sucursales para el select, no solo las que tienen stock
            // Seleccionar la primera sucursal por defecto
            const primeraSucursal = todasLasSucursales.length > 0 ? todasLasSucursales[0] : null;
            const opcionesSucursales = todasLasSucursales.map((s, idx) => 
                `<option value="${s.id_sucursal}" ${idx === 0 ? 'selected' : ''}>${escapeHtml(s.nombre)}</option>`
            ).join('');
            
            html += `
                <tr class="table-warning" id="sobre-pedido-${index}">
                    <td>
                        <strong>Sobre Pedido</strong>
                        <br><small class="text-muted">(Asignar a sucursal)</small>
                    </td>
                    <td class="text-center">-</td>
                    <td class="text-center">
                        <div class="row g-1">
                            <div class="col-6">
                                <input type="number" 
                                       class="form-control form-control-sm text-center asignar-cantidad" 
                                       data-articulo="${index}" 
                                       data-sucursal="especial"
                                       data-max="${Math.floor(restante)}"
                                       data-total-requerido="${totalRequerido}"
                                       value="${Math.floor(restante)}" 
                                       min="0" 
                                       max="${Math.floor(restante)}"
                                       oninput="actualizarAsignacion(this, ${index})"
                                       style="width: 100%;">
                            </div>
                            <div class="col-6">
                                <select class="form-select form-select-sm sucursal-sobre-pedido" 
                                        data-articulo="${index}"
                                        style="width: 100%;">
                                    ${opcionesSucursales}
                                </select>
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-warning">Sobre Pedido</span>
                        <br><small class="text-muted" id="sucursal-seleccionada-${index}">${primeraSucursal ? 'Sucursal: ' + escapeHtml(primeraSucursal.nombre) : 'Selecciona una sucursal'}</small>
                    </td>
                </tr>
            `;
        }
        
        html += `
                        </tbody>
                        <tfoot>
                            <tr class="table-info">
                                <td colspan="3" class="text-end"><strong>Total Asignado:</strong></td>
                                <td class="text-center"><strong id="total-asignado-${index}">${Math.floor(totalAsignado)}</strong> / ${Math.floor(totalRequerido)}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    
    // Event listeners para los selects de "Sobre Pedido"
    document.querySelectorAll('.sucursal-sobre-pedido').forEach(select => {
        // Disparar el evento change para actualizar el label con el valor por defecto
        const event = new Event('change');
        select.dispatchEvent(event);
        
        select.addEventListener('change', function() {
            const articuloIndex = parseInt(this.dataset.articulo);
            const sucursalNombre = this.options[this.selectedIndex]?.text || '';
            const label = document.getElementById(`sucursal-seleccionada-${articuloIndex}`);
            if (label) {
                label.textContent = sucursalNombre ? `Sucursal: ${sucursalNombre}` : 'Selecciona una sucursal';
            }
        });
    });
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
function actualizarAsignacion(input, articuloIndex) {
    const valor = parseInt(input.value) || 0;
    const maxPermitido = parseInt(input.dataset.max) || 0;
    const totalRequerido = parseInt(input.dataset.totalRequerido) || 0;
    const esEspecial = input.dataset.sucursal === 'especial';
    
    // Validar que no exceda el stock de la sucursal
    if (!esEspecial && valor > maxPermitido) {
        input.value = maxPermitido;
        if (window.mostrarToast) {
            window.mostrarToast(`No puedes asignar más de ${maxPermitido} unidades en esta sucursal`, 'warning');
        }
        return;
    }
    
    const container = document.getElementById(`asignacion-${articuloIndex}`);
    if (!container) return;
    
    const inputs = container.querySelectorAll('.asignar-cantidad');
    let totalAsignado = 0;
    let totalSobrePedido = 0;
    
    inputs.forEach(inp => {
        const val = parseInt(inp.value) || 0;
        totalAsignado += val;
        if (inp.dataset.sucursal === 'especial') {
            totalSobrePedido += val;
        }
    });
    
    // Si el total asignado excede el requerido, ajustar
    if (totalAsignado > totalRequerido) {
        const excedente = totalAsignado - totalRequerido;
        const nuevoValor = Math.max(0, valor - excedente);
        input.value = nuevoValor;
        totalAsignado = 0;
        inputs.forEach(inp => {
            totalAsignado += parseInt(inp.value) || 0;
        });
        
        if (window.mostrarToast) {
            window.mostrarToast(`El total asignado (${totalAsignado}) no puede exceder el requerido (${totalRequerido})`, 'warning');
        }
    }
    
    // Validar que Sobre Pedido no supere el restante
    if (esEspecial && totalSobrePedido > 0) {
        const restante = totalRequerido - (totalAsignado - totalSobrePedido);
        if (totalSobrePedido > restante) {
            input.value = Math.max(0, restante);
            if (window.mostrarToast) {
                window.mostrarToast(`Solo faltan ${restante} unidades para completar el pedido`, 'warning');
            }
        }
    }
    
    // Actualizar estados
    inputs.forEach(inp => {
        const val = parseInt(inp.value) || 0;
        const estadoId = `estado-${articuloIndex}-${inp.dataset.sucursal}`;
        const estadoCell = document.getElementById(estadoId);
        if (estadoCell) {
            const badge = estadoCell.querySelector('.badge');
            if (badge) {
                badge.className = `badge ${val > 0 ? 'bg-success' : 'bg-secondary'}`;
                badge.textContent = val > 0 ? 'Asignado' : 'Pendiente';
            }
        }
    });
    
    // Actualizar total
    const totalSpan = document.getElementById(`total-asignado-${articuloIndex}`);
    if (totalSpan) {
        totalSpan.textContent = `${totalAsignado} / ${totalRequerido}`;
        
        // Cambiar color si no coincide
        totalSpan.style.color = totalAsignado !== totalRequerido ? 'red' : 'green';
        totalSpan.style.fontWeight = 'bold';
    }
}

// ============================================
// CONFIRMAR Y GENERAR PEDIDO CON ASIGNACIONES
// ============================================
window.confirmarGenerarPedidoConAsignacion = function() {
    const id = document.getElementById('confirmar_pedido_id').value;
    const folio = document.getElementById('confirmar_pedido_folio').textContent;
    
    if (!id) return;
    
    // Recolectar todas las asignaciones
    const asignaciones = [];
    const container = document.getElementById('asignacionTablaContainer');
    if (!container) {
        if (window.mostrarToast) {
            window.mostrarToast('Error: No hay datos de asignación', 'danger');
        }
        return;
    }
    
    const articulos = container.querySelectorAll('.card');
    let todoCompletado = true;
    let hayError = false;
    let mensajeError = '';
    
    articulos.forEach((articuloCard, index) => {
        const inputs = articuloCard.querySelectorAll('.asignar-cantidad');
        const nombreArticulo = articuloCard.querySelector('strong')?.textContent || `Artículo ${index + 1}`;
        const totalRequerido = parseInt(articuloCard.querySelector('.badge.bg-primary').textContent.replace('Requerido: ', ''));
        let totalAsignado = 0;
        let asignacionesPorArticulo = [];
        let cantidadSobrePedido = 0;
        
        // Obtener el select de "Sobre Pedido" - se leerá cuando sea necesario
        const selectSobrePedido = articuloCard.querySelector('.sucursal-sobre-pedido');
        
        inputs.forEach(input => {
            const valor = parseInt(input.value) || 0;
            const maxPermitido = parseInt(input.dataset.max) || 0;
            const esEspecial = input.dataset.sucursal === 'especial';
            
            // Validar que no exceda el stock de la sucursal
            if (!esEspecial && valor > maxPermitido) {
                hayError = true;
                const nombreSucursal = input.closest('tr').querySelector('td:first-child')?.textContent?.trim() || 'Sucursal';
                mensajeError = `No puedes asignar más de ${maxPermitido} unidades en ${nombreSucursal} para "${nombreArticulo}"`;
                return;
            }
            
            if (valor > 0) {
                let sucursalId = null;
                let sucursalNombre = '';
                let esAgregado = 0;
                
                if (esEspecial) {
                    // Para Sobre Pedido, leer el valor del select en este momento
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
                    sucursalNombre = input.closest('tr').querySelector('td:first-child strong')?.textContent || 'Sucursal';
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
        
        // Validar que no exceda el total requerido
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
    
    // Mostrar error con Toast
    if (hayError) {
        if (window.mostrarToast) {
            window.mostrarToast(mensajeError, 'danger');
        }
        return;
    }
    
    // Si no está completado, mostrar toast y NO permitir continuar
    if (!todoCompletado) {
        if (window.mostrarToast) {
            window.mostrarToast('Las cantidades asignadas no coinciden con las requeridas', 'warning');
        }
        return;
    }
    
    // Cerrar el modal
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

function refrescarTablaCotizaciones(mostrarNotificacion = false, desdePolling = false) {
    
    if (estaRefrescando) return;
    estaRefrescando = true;
    
    const btnRefrescar = document.getElementById('btnRefrescarCotizaciones');
    const iconoOriginal = btnRefrescar?.innerHTML;
    
    if (!desdePolling && btnRefrescar) {
        btnRefrescar.innerHTML = '<i class="bi bi-arrow-repeat fa-spin"></i> Refrescando...';
        btnRefrescar.disabled = true;
    }
    
    fetch('{{ route("ventas.cotizaciones.refrescar") }}?ultimo_id=' + ultimoIdCotizacion, {
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
        if (data.success && data.html) {
            // Extraer SOLO el tbody del HTML recibido
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = data.html;
            const nuevoTbody = tempDiv.querySelector('#cotizacionesTableBody');
            
            const tbodyActual = document.querySelector('#cotizacionesTableBody');
            if (nuevoTbody && tbodyActual) {
                tbodyActual.innerHTML = nuevoTbody.innerHTML;
            } else {
                // Fallback: reemplazar todo el contenedor
                document.getElementById('tabla-cotizaciones-container').innerHTML = data.html;
            }
            
            ultimoIdCotizacion = data.ultimo_id;
            
            if (!desdePolling && mostrarNotificacion && window.mostrarToast) {
                window.mostrarToast('Cotizaciones actualizadas', 'success');
            }
        }
    })
    .catch(error => {
        console.error('Error refrescando tabla:', error);
        if (!desdePolling && mostrarNotificacion && window.mostrarToast) {
            window.mostrarToast('Error al actualizar cotizaciones', 'danger');
        }
    })
    .finally(() => {
        estaRefrescando = false;
        if (!desdePolling && btnRefrescar) {
            btnRefrescar.innerHTML = iconoOriginal;
            btnRefrescar.disabled = false;
        }
    });
}

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