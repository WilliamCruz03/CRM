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
                            <th>Acciones</th>
                        </thead>
                    <tbody id="cotizacionesTableBody">
                        @forelse($cotizaciones as $cotizacion)
                        <tr id="cotizacion-row-{{ $cotizacion->id_cotizacion }}">
                            <td>
                                <span class="badge bg-secondary">{{ $cotizacion->folio }}</span>
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
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-info btn-action"
                                            onclick="verCotizacion({{ $cotizacion->id_cotizacion }})"
                                            title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @if($puedeEditar)
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-action"
                                            onclick="editarCotizacion({{ $cotizacion->id_cotizacion }})"
                                            title="Editar cotización">
                                        <i class="bi bi-pencil"></i>
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
                            <td colspan="7" class="text-center py-4">
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
@endsection

@push('scripts')
<script>
// Buscador en tabla
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

// Función para ver cotización
window.verCotizacion = function(id) {
    fetch(`/ventas/cotizaciones/${id}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cargarDatosVerCotizacion(data.data);
            const modal = new bootstrap.Modal(document.getElementById('modalVerCotizacion'));
            modal.show();
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al cargar cotización', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
};

// Función para editar cotización
window.editarCotizacion = function(id) {
    fetch(`/ventas/cotizaciones/${id}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cargarDatosEditarCotizacion(data.data);
            const modal = new bootstrap.Modal(document.getElementById('modalEditarCotizacion'));
            modal.show();
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al cargar cotización', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
};

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
</script>
@endpush