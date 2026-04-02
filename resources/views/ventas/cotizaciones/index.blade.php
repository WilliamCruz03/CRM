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
                                    @if($puedeEditar && !$cotizacion->enviado)
                                    <button type="button" class="btn btn-sm btn-outline-success btn-action"
                                            onclick="enviarCotizacion({{ $cotizacion->id_cotizacion }}, '{{ addslashes($cotizacion->folio) }}')"
                                            title="Enviar cotización">
                                        <i class="bi bi-send"></i>
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

<!-- Modal Confirmar Envío -->
<div class="modal fade" id="modalConfirmarEnvio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-send"></i> Enviar Cotización</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Enviar la cotización <strong id="confirmar_envio_folio"></strong> al cliente?</p>
                <p class="text-muted small">
                    <i class="bi bi-info-circle"></i> Se generará un archivo PDF con los detalles de la cotización.
                </p>
                <input type="hidden" id="confirmar_envio_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="ejecutarEnvio()">
                    <i class="bi bi-send"></i> Enviar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('ventas.cotizaciones.partials.modal-nueva-cotizacion')
@include('ventas.cotizaciones.partials.modal-editar-cotizacion')
@include('ventas.cotizaciones.partials.modal-ver-cotizacion')
@include('ventas.cotizaciones.partials.modal-opciones-edicion')
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
// LIMPIAR MODAL NUEVA COTIZACIÓN
// ============================================
function limpiarModalNuevaCotizacion() {
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
    
    if (typeof window.articulosSeleccionados !== 'undefined') {
        window.articulosSeleccionados = [];
        if (typeof renderizarTablaArticulos === 'function') {
            renderizarTablaArticulos();
        }
    }
}

// ============================================
// PRECARGAR MODAL NUEVA COTIZACIÓN
// ============================================
function precargarModalNuevaCotizacion(data) {
    console.log('Precargando datos para nueva versión:', data);
    
    setTimeout(() => {
        if (data.id_cliente && typeof window.seleccionarCliente === 'function') {
            window.seleccionarCliente(data.id_cliente, data.cliente_nombre, data.cliente_email);
        }
    }, 100);
    
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
    
    if (data.articulos && data.articulos.length > 0) {
        if (typeof window.articulosSeleccionados !== 'undefined') {
            window.articulosSeleccionados = data.articulos.map(articulo => ({
                ...articulo,
                id_producto: parseInt(articulo.id_producto),
                cantidad: parseInt(articulo.cantidad),
                precio: parseFloat(articulo.precio),
                descuento: parseFloat(articulo.descuento || 0),
                id_sucursal_surtido: articulo.id_sucursal_surtido ? parseInt(articulo.id_sucursal_surtido) : null
            }));
            if (typeof renderizarTablaArticulos === 'function') {
                renderizarTablaArticulos();
            }
        }
    }
}

// ============================================
// ENVIAR COTIZACIÓN
// ============================================
window.enviarCotizacion = function(id, folio) {
    document.getElementById('confirmar_envio_id').value = id;
    document.getElementById('confirmar_envio_folio').textContent = folio;
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmarEnvio'));
    modal.show();
};

window.ejecutarEnvio = function() {
    const id = document.getElementById('confirmar_envio_id').value;
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarEnvio'));
    
    fetch(`/ventas/cotizaciones/${id}/enviar`, {
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
            if (window.mostrarToast) window.mostrarToast(data.message, 'success');
            modal.hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al enviar', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
};

// ============================================
// GUARDAR EDICIÓN COTIZACIÓN
// ============================================
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
            return response.json().then(data => {
                if (confirm(data.message + ' ¿Deseas crear una nueva versión?')) {
                    crearNuevaVersion(cotizacionId);
                    const modalEditar = bootstrap.Modal.getInstance(document.getElementById('modalEditarCotizacion'));
                    if (modalEditar) modalEditar.hide();
                }
            });
        }
        return response.json();
    })
    .then(data => {
        if (data && data.success) {
            if (window.mostrarToast) window.mostrarToast(data.message, 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarCotizacion'));
            if (modal) modal.hide();
            setTimeout(() => location.reload(), 1000);
        } else if (data && !data.success) {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al guardar', 'danger');
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