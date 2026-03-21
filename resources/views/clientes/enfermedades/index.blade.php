@extends('layouts.app')

@section('title', 'Patologías - CRM')
@section('page-title', 'Registro de Patologías')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h3><i class="bi bi-heart-pulse"></i> Registro de Patologías</h3>
        <p class="text-muted">Gestiona el catálogo de patologías registradas</p>
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
                <input type="text" class="form-control" id="buscarPatologia" placeholder="Buscar patología...">
            </div>
            @endif
        </div>
        <div class="col-md-6 text-end">
            @if($puedeCrear)
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaPatologia">
                <i class="bi bi-plus-circle"></i> Nueva Patología
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
                    <thead>
                            <th>ID</th>
                            <th>Patología</th>
                            <th>Fecha de registro</th>
                            <th>Acciones</th>
                        </thead>
                    <tbody id="patologiasTableBody">
                        @forelse($patologias as $patologia)
                        <tr id="patologia-row-{{ $patologia->id_patologia }}">
                            <td><span class="badge bg-secondary">{{ $patologia->id_patologia }}</span></td>
                            <td>{{ $patologia->descripcion }}</td>
                            <td>
                                <small class="text-muted">
                                    <i class="bi bi-calendar3"></i> 
                                    {{ $patologia->fecha_creacion ? \Carbon\Carbon::parse($patologia->fecha_creacion)->format('d/m/Y H:i') : 'No especificada' }}
                                </small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @if($puedeEditar)
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-action"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditarPatologia"
                                            data-patologia-id="{{ $patologia->id_patologia }}"
                                            title="Editar patología">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @endif
                                    @if($puedeEliminar)
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                            onclick="confirmarEliminarPatologia({{ $patologia->id_patologia }}, '{{ addslashes($patologia->descripcion) }}')"
                                            title="Eliminar patología">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <i class="bi bi-heart-pulse" style="font-size: 2rem; color: #ccc;"></i>
                                <p class="text-muted mt-2">No hay patologías registradas</p>
                                @if($puedeCrear)
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevaPatologia">
                                    <i class="bi bi-plus"></i> Agregar primera patología
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
            <i class="bi bi-heart-pulse" style="font-size: 3rem; color: #ccc;"></i>
            <p class="text-muted mt-3">No tienes permiso para ver el listado de patologías, pero puedes crear nuevas.</p>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaPatologia">
                <i class="bi bi-plus-circle"></i> Crear nueva patología
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
@include('clientes.enfermedades.partials.modal-nueva-patologia')
@include('clientes.enfermedades.partials.modal-editar-patologia')
@endsection

@push('scripts')
<script>
// ============================================
// FUNCIONES PARA LA VISTA DE PATOLOGÍAS
// ============================================

let patologiaActualId = null;
let timeoutIdPatologia;

function editarPatologia(id) {
    patologiaActualId = id;
    
    fetch(`/enfermedades/${id}/edit`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('edit_patologia_id').value = data.data.id_patologia;
            document.getElementById('edit_patologia_descripcion').value = data.data.descripcion;
            
            const modal = new bootstrap.Modal(document.getElementById('modalEditarPatologia'));
            modal.show();
        } else {
            if (window.mostrarToast) window.mostrarToast('Error al cargar los datos', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
}

window.confirmarEliminarPatologia = function(id, descripcion) {
    const modalConfirmar = document.getElementById('modalConfirmarEliminar');
    if (!modalConfirmar) return;
    
    window.patologiaAEliminar = { id: id, descripcion: descripcion };
    
    document.getElementById('detalleConfirmacion').textContent = 
        `¿Eliminar la patología "${descripcion}"? Esta acción no se puede deshacer.`;
    
    const btnConfirmar = document.getElementById('btnConfirmarEliminar');
    const originalOnClick = btnConfirmar.onclick;
    
    btnConfirmar.onclick = function() {
        fetch(`/enfermedades/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`patologia-row-${id}`).remove();
                if (window.mostrarToast) {
                    window.mostrarToast(`Patología "${descripcion}" eliminada`, 'success');
                }
            } else {
                if (window.mostrarToast) {
                    window.mostrarToast(data.message || 'Error al eliminar', 'danger');
                }
            }
        });
        
        btnConfirmar.onclick = originalOnClick;
        bootstrap.Modal.getInstance(modalConfirmar).hide();
    };
    
    new bootstrap.Modal(modalConfirmar).show();
};

// Buscador
document.getElementById('buscarPatologia')?.addEventListener('input', function() {
    clearTimeout(timeoutIdPatologia);
    const termino = this.value.toLowerCase().trim();
    
    timeoutIdPatologia = setTimeout(() => {
        const rows = document.querySelectorAll('#patologiasTableBody tr');
        let visibleCount = 0;
        
        rows.forEach(row => {
            if (row.id === 'no-results-row') return;
            const text = row.textContent.toLowerCase();
            
            if (termino.length === 0 || text.includes(termino)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        const tbody = document.getElementById('patologiasTableBody');
        let noResultsRow = document.getElementById('no-results-row');
        
        if (visibleCount === 0 && termino.length > 0) {
            if (!noResultsRow) {
                noResultsRow = document.createElement('tr');
                noResultsRow.id = 'no-results-row';
                noResultsRow.innerHTML = '<td colspan="4" class="text-center py-4 text-muted">No se encontraron patologías</td>';
                tbody.appendChild(noResultsRow);
            }
        } else if (noResultsRow) {
            noResultsRow.remove();
        }
    }, 150);
});
</script>
@endpush