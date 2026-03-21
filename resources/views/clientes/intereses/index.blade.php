@extends('layouts.app')

@section('title', 'Intereses - CRM')
@section('page-title', 'Registro de Intereses')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <h3><i class="bi bi-star"></i> Registro de Intereses</h3>
        <p class="text-muted">Gestiona el catálogo de intereses de clientes</p>
    </div>

    @can('clientes.intereses.ver')
    <!-- Search and Actions -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" id="buscarInteres" placeholder="Buscar interés...">
            </div>
        </div>
        <div class="col-md-6 text-end">
            @can('clientes.intereses.crear')
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoInteres">
                <i class="bi bi-plus-circle"></i> Nuevo Interés
            </button>
            @endcan
        </div>
    </div>

    <!-- Tabla de Intereses -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover" id="tablaIntereses">
                    <thead>
                        32
                            <th>ID</th>
                            <th>Interés</th>
                            <th>Fecha de registro</th>
                            <th>Acciones</th>
                        </thead>
                    <tbody id="interesesTableBody">
                        @forelse($intereses as $interes)
                            <tr id="interes-row-{{ $interes->id_interes }}">
                                <td><span class="badge bg-secondary">{{ $interes->id_interes }}</span></td>
                                <td>{{ $interes->Descripcion }}</td>
                                <td>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar3"></i> 
                                        {{ $interes->fecha_creacion ? \Carbon\Carbon::parse($interes->fecha_creacion)->format('d/m/Y H:i') : 'No especificada' }}
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        @can('clientes.intereses.editar')
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-action"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEditarInteres"
                                                data-interes-id="{{ $interes->id_interes }}"
                                                title="Editar interés">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        @endcan
                                        @can('clientes.intereses.eliminar')
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                                onclick="confirmarEliminarInteres({{ $interes->id_interes }}, '{{ addslashes($interes->Descripcion) }}')"
                                                title="Eliminar interés">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <i class="bi bi-star" style="font-size: 2rem; color: #ccc;"></i>
                                    <p class="text-muted mt-2">No hay intereses registrados</p>
                                    @can('clientes.intereses.crear')
                                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevoInteres">
                                        <i class="bi bi-plus"></i> Agregar primer interés
                                    </button>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Mostrando {{ count($intereses) }} registros
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> No tienes permiso para ver el catálogo de intereses.
    </div>
    @endcan
</div>

<!-- Modals -->
@include('clientes.intereses.partials.modal-nuevo-interes')
@include('clientes.intereses.partials.modal-editar-interes')
@endsection

@push('scripts')
<script>
let interesActualId = null;
let timeoutIdInteres;

function editarInteres(id) {
    interesActualId = id;
    
    fetch(`/intereses/${id}/edit`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('edit_interes_id').value = data.data.id_interes;
            document.getElementById('edit_interes_descripcion').value = data.data.Descripcion;
            
            const modal = new bootstrap.Modal(document.getElementById('modalEditarInteres'));
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

window.confirmarEliminarInteres = function(id, descripcion) {
    const modalConfirmar = document.getElementById('modalConfirmarEliminar');
    if (!modalConfirmar) return;
    
    window.interesAEliminar = { id: id, descripcion: descripcion };
    
    document.getElementById('detalleConfirmacion').textContent = 
        `¿Eliminar el interés "${descripcion}"? Esta acción no se puede deshacer.`;
    
    const btnConfirmar = document.getElementById('btnConfirmarEliminar');
    const originalOnClick = btnConfirmar.onclick;
    
    btnConfirmar.onclick = function() {
        fetch(`/intereses/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`interes-row-${id}`).remove();
                if (window.mostrarToast) {
                    window.mostrarToast(`Interés "${descripcion}" eliminado`, 'success');
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
document.getElementById('buscarInteres')?.addEventListener('input', function() {
    clearTimeout(timeoutIdInteres);
    const termino = this.value.toLowerCase().trim();
    
    timeoutIdInteres = setTimeout(() => {
        const rows = document.querySelectorAll('#interesesTableBody tr');
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
        
        const tbody = document.getElementById('interesesTableBody');
        let noResultsRow = document.getElementById('no-results-row');
        
        if (visibleCount === 0 && termino.length > 0) {
            if (!noResultsRow) {
                noResultsRow = document.createElement('tr');
                noResultsRow.id = 'no-results-row';
                noResultsRow.innerHTML = '<td colspan="4" class="text-center py-4 text-muted">No se encontraron intereses</td>';
                tbody.appendChild(noResultsRow);
            }
        } else if (noResultsRow) {
            noResultsRow.remove();
        }
    }, 150);
});
</script>
@endpush