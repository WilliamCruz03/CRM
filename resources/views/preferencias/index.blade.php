@extends('layouts.app')

@section('title', 'Preferencias de Clientes - CRM')
@section('page-title', 'Preferencias de Clientes')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <h3><i class="bi bi-heart"></i> Preferencias de Clientes</h3>
        <p class="text-muted">Gestiona gustos, hábitos y necesidades especiales para personalizar la atención</p>
    </div>

    <!-- Search and Actions -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" id="buscarPreferencia" placeholder="Buscar por cliente, categoría o preferencia...">
            </div>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaPreferencia">
                <i class="bi bi-plus-circle"></i> Registrar preferencia
            </button>
        </div>
    </div>

    <!-- Tabla de Preferencias -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Detalle de preferencia</th>
                            <th>Fecha de registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="preferenciasTableBody">
                        @forelse($preferencias as $preferencia)
                        <tr id="preferencia-row-{{ $preferencia->id }}">
                            <td><span class="badge bg-secondary">{{ $preferencia->id }}</span></td>
                            <td>
                                <strong>{{ $preferencia->cliente->nombre_completo }}</strong>
                                <br>
                                <small class="text-muted">{{ $preferencia->cliente->email }}</small>
                            </td>
                            <td>
                                <div class="preferencia-detalle">
                                    <p class="mb-1">{{ $preferencia->descripcion }}</p>
                                    @if($preferencia->categoria)
                                        <span class="badge bg-info">{{ $preferencia->categoria }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-calendar3"></i> {{ $preferencia->fecha_registro->format('d/m/Y') }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-action"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditarPreferencia"
                                            data-preferencia-id="{{ $preferencia->id }}"
                                            title="Editar preferencia">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                            onclick="confirmarEliminarPreferencia({{ $preferencia->id }}, '{{ addslashes($preferencia->descripcion) }}')"
                                            title="Eliminar preferencia">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="bi bi-heart" style="font-size: 2rem; color: #ccc;"></i>
                                <p class="text-muted mt-2">No hay preferencias registradas</p>
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevaPreferencia">
                                    <i class="bi bi-plus"></i> Registrar primera preferencia
                                </button>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-3">
            <div class="pagination-info">
                Mostrando {{ $preferencias->firstItem() }} - {{ $preferencias->lastItem() }} de {{ $preferencias->total() }} registros
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    {{ $preferencias->links() }}
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Modals -->
@include('preferencias.partials.modal-nueva-preferencia')
@include('preferencias.partials.modal-editar-preferencia')
@endsection

@push('scripts')
<script>
// Función para filtrar la tabla en tiempo real (solo frontend)
document.getElementById('buscarPreferencia')?.addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#preferenciasTableBody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        if (row.id === 'no-results-row') return;
        
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Mostrar mensaje si no hay resultados (opcional)
    const noResultsRow = document.getElementById('no-results-row');
    if (visibleCount === 0 && !noResultsRow) {
        const tbody = document.getElementById('preferenciasTableBody');
        const tr = document.createElement('tr');
        tr.id = 'no-results-row';
        tr.innerHTML = '<td colspan="5" class="text-center py-4 text-muted">No se encontraron resultados</td>';
        tbody.appendChild(tr);
    } else if (visibleCount > 0 && noResultsRow) {
        noResultsRow.remove();
    }
});

// Función para confirmar eliminación usando el modal global
window.confirmarEliminarPreferencia = function(id, descripcion) {
    const modalConfirmar = document.getElementById('modalConfirmarEliminar');
    if (!modalConfirmar) return;
    
    window.contextoEliminarPreferencia = { id: id, descripcion: descripcion };
    
    document.getElementById('detalleConfirmacion').textContent = 
        `¿Eliminar la preferencia: "${descripcion.substring(0, 50)}..." ?`;
    
    const btnConfirmar = document.getElementById('btnConfirmarEliminar');
    const originalOnClick = btnConfirmar.onclick;
    
    btnConfirmar.onclick = function() {
        fetch(`/preferencias/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`preferencia-row-${id}`)?.remove();
                if (window.mostrarToast) {
                    window.mostrarToast('Preferencia eliminada correctamente', 'success');
                }
            }
        });
        
        btnConfirmar.onclick = originalOnClick;
        bootstrap.Modal.getInstance(modalConfirmar).hide();
    };
    
    new bootstrap.Modal(modalConfirmar).show();
};
</script>
@endpush