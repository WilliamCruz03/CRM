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
                        构建
                            <th>Folio</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Importe</th>
                            <th>Fase</th>
                            <th>Clasificación</th>
                            <th>Certeza</th>
                            <th>Entrega sugerida</th>
                            <th>Acciones</th>
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

<!-- Modals -->
@include('ventas.cotizaciones.partials.modal-nueva-cotizacion')
@include('ventas.cotizaciones.partials.modal-editar-cotizacion')
@include('ventas.cotizaciones.partials.modal-ver-cotizacion')
@include('ventas.cotizaciones.partials.modal-opciones-edicion')
@endsection

@push('scripts')
<script>
// ... (funciones existentes) ...

// Función para mostrar modal de opciones de edición
window.mostrarOpcionesEdicion = function(id) {
    // Cargar datos de la cotización para saber si está enviada o no
    fetch(`/ventas/cotizaciones/${id}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const cotizacion = data.data;
            if (cotizacion.enviado) {
                // Si está enviada, solo se permite nueva versión
                crearNuevaVersion(id);
            } else {
                // Mostrar modal con opciones
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

window.editarCotizacionActual = function(id) {
    fetch(`/ventas/cotizaciones/${id}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cargarDatosEditarCotizacion(data.data);
            const modalEditar = new bootstrap.Modal(document.getElementById('modalEditarCotizacion'));
            modalEditar.show();
            // Cerrar modal de opciones
            const modalOpciones = bootstrap.Modal.getInstance(document.getElementById('modalOpcionesEdicion'));
            if (modalOpciones) modalOpciones.hide();
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al cargar cotización', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
};

window.crearNuevaVersion = function(id) {
    // Mostrar confirmación
    if (confirm('¿Deseas crear una nueva versión de esta cotización? La versión actual se archivará.')) {
        fetch(`/ventas/cotizaciones/${id}/version`, {
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
                location.reload();
            } else {
                if (window.mostrarToast) window.mostrarToast(data.message || 'Error al crear nueva versión', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
        });
    }
};

window.enviarCotizacion = function(id, folio) {
    if (confirm(`¿Enviar la cotización ${folio} al cliente?`)) {
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
                location.reload();
            } else {
                if (window.mostrarToast) window.mostrarToast(data.message || 'Error al enviar', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
        });
    }
};

// Modificar la función guardarEdicionCotizacion para manejar la respuesta de similitud
window.guardarEdicionCotizacion = function() {
    const cotizacionId = document.getElementById('edit_cotizacion_id')?.value;
    const faseId = document.getElementById('edit_fase_id')?.value;

    if (!faseId) {
        if (window.mostrarToast) window.mostrarToast('Selecciona una fase', 'warning');
        return;
    }

    if (editArticulosSeleccionados.length === 0) {
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
        opcion: 'editar' // Indica que se quiere editar la misma cotización
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
            // Similitud baja
            return response.json().then(data => {
                // Mostrar modal de confirmación
                if (confirm(data.message + ' ¿Deseas crear una nueva versión?')) {
                    // Llamar a crear nueva versión con los mismos datos
                    fetch(`/ventas/cotizaciones/${cotizacionId}/version`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ articulos: articulos, ...formData })
                    })
                    .then(res => res.json())
                    .then(dataVersion => {
                        if (dataVersion.success) {
                            if (window.mostrarToast) window.mostrarToast('Nueva versión creada', 'success');
                            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarCotizacion'));
                            modal.hide();
                            location.reload();
                        } else {
                            if (window.mostrarToast) window.mostrarToast(dataVersion.message || 'Error al crear nueva versión', 'danger');
                        }
                    });
                } else {
                    // Forzar guardado en la misma cotización
                    formData.forzar = true;
                    fetch(`/ventas/cotizaciones/${cotizacionId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(formData)
                    })
                    .then(res => res.json())
                    .then(dataForzada => {
                        if (dataForzada.success) {
                            if (window.mostrarToast) window.mostrarToast('Cotización actualizada', 'success');
                            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarCotizacion'));
                            modal.hide();
                            location.reload();
                        } else {
                            if (window.mostrarToast) window.mostrarToast(dataForzada.message, 'danger');
                        }
                    });
                }
            });
        }
        return response.json();
    })
    .then(data => {
        if (data && data.success) {
            if (window.mostrarToast) window.mostrarToast(data.message, 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarCotizacion'));
            modal.hide();
            location.reload();
        } else if (data && !data.success) {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al guardar', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
};
</script>
@endpush