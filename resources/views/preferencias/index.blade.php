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
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevaPreferencia">
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
                                    <i class="bi bi-calendar3"></i> {{ $preferencia->fecha_registro_formateada }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-action"
                                            onclick="editarPreferencia({{ $preferencia->id }})"
                                            title="Editar preferencia">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                            onclick="confirmarEliminar('preferencia', {{ $preferencia->id }}, '{{ Str::limit($preferencia->descripcion, 30) }}')"
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
                Mostrando <span id="registrosMostrados">{{ count($preferencias) }}</span> registros
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item disabled">
                        <span class="page-link">Anterior</span>
                    </li>
                    <li class="page-item active"><span class="page-link">1</span></li>
                    <li class="page-item"><span class="page-link">2</span></li>
                    <li class="page-item"><span class="page-link">3</span></li>
                    <li class="page-item">
                        <span class="page-link">Siguiente</span>
                    </li>
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
// Variables globales
let preferenciaActualId = null;

// Función para filtrar la tabla
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
    
    document.getElementById('registrosMostrados').textContent = visibleCount;
});

// Función para editar preferencia
function editarPreferencia(id) {
    preferenciaActualId = id;
    
    fetch(`/preferencias/${id}/edit`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('edit_preferencia_id').value = data.data.id;
            document.getElementById('edit_descripcion').value = data.data.descripcion;
            document.getElementById('edit_categoria').value = data.data.categoria || '';
            
            const modal = new bootstrap.Modal(document.getElementById('modalEditarPreferencia'));
            modal.show();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Función para guardar nueva preferencia
function guardarNuevaPreferencia() {
    const formData = {
        cliente_id: document.getElementById('preferencia_cliente_id').value,
        descripcion: document.getElementById('nueva_descripcion').value.trim(),
        categoria: document.getElementById('nueva_categoria').value.trim(),
        _token: '{{ csrf_token() }}'
    };
    
    if (!formData.cliente_id) {
        alert('Por favor selecciona un cliente');
        return;
    }
    
    if (!formData.descripcion) {
        alert('Por favor ingresa el detalle de la preferencia');
        return;
    }
    
    fetch('/preferencias', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevaPreferencia'));
            modal.hide();
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Función para guardar edición de preferencia
function guardarEdicionPreferencia() {
    const formData = {
        descripcion: document.getElementById('edit_descripcion').value.trim(),
        categoria: document.getElementById('edit_categoria').value.trim(),
        _token: '{{ csrf_token() }}',
        _method: 'PUT'
    };
    
    if (!formData.descripcion) {
        alert('Por favor ingresa el detalle de la preferencia');
        return;
    }
    
    fetch(`/preferencias/${preferenciaActualId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarPreferencia'));
            modal.hide();
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Función para eliminar preferencia
function ejecutarEliminarPreferencia(id) {
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
            document.getElementById(`preferencia-row-${id}`).remove();
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
@endpush