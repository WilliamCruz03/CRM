@extends('layouts.app')

@section('title', 'Enfermedades - CRM')
@section('page-title', 'Registro de Enfermedades')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <h3><i class="bi bi-heart-pulse"></i> Registro de Enfermedades</h3>
        <p class="text-muted">Gestiona el catálogo de enfermedades registradas</p>
    </div>

    <!-- Search and Actions -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" id="buscarEnfermedad" placeholder="Buscar padecimiento o categoría...">
            </div>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaEnfermedad">
                <i class="bi bi-plus-circle"></i> Nueva Enfermedad
            </button>
        </div>
    </div>

    <!-- Tabla de Enfermedades -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover" id="tablaEnfermedades">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Padecimiento</th>
                            <th>Categoría</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="enfermedadesTableBody">
                        @forelse($patologias as $patologia)
                            <tr id="patologia-row-{{ $patologia->id_patologia }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $patologia->descripcion }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-action"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEditarEnfermedad"
                                                data-patologia-id="{{ $patologia->id_patologia }}"
                                                title="Editar patología">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                                onclick="confirmarEliminar('patologia', {{ $patologia->id_patologia }}, '{{ $patologia->descripcion }}')"
                                                title="Eliminar patología">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4">
                                    <i class="bi bi-heart-pulse" style="font-size: 2rem; color: #ccc;"></i>
                                    <p class="text-muted mt-2">No hay patologías registradas</p>
                                </td>
                            </tr>
                            @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-3">
            <div class="pagination-info">
                Mostrando <span id="registrosMostrados">{{ count($enfermedades) }}</span> registros
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
@include('enfermedades.partials.modal-nueva-enfermedad')
@include('enfermedades.partials.modal-editar-enfermedad')

@endsection

@push('scripts')
<script>
// Variable global para almacenar el ID de la enfermedad a editar
let enfermedadActualId = null;

// Función para editar enfermedad
function editarEnfermedad(id) {
    enfermedadActualId = id;
    
    // Mostrar indicador de carga (opcional)
    console.log('Cargando enfermedad ID:', id);
    
    fetch(`/enfermedades/${id}/edit`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        return response.json();
    })
    .then(data => {
        console.log('Datos recibidos:', data);
        
        if (data.success) {
            // Llenar el formulario con los datos
            document.getElementById('edit_enfermedad_id').value = data.data.id;
            document.getElementById('edit_enfermedad_nombre').value = data.data.nombre;
            document.getElementById('edit_enfermedad_categoria').value = data.data.categoria_id;
            
            // Abrir el modal
            const modal = new bootstrap.Modal(document.getElementById('modalEditarEnfermedad'));
            modal.show();
        } else {
            alert('Error al cargar los datos de la enfermedad');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cargar los datos. Por favor, intenta de nuevo.');
    });
}

// Función para eliminar enfermedad con confirmación
function eliminarEnfermedad(id) {
    if (confirm('¿Estás seguro de que deseas eliminar esta enfermedad? Esta acción no se puede deshacer.')) {
        
        fetch(`/enfermedades/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Eliminar la fila de la tabla
                const row = document.getElementById(`enfermedad-row-${id}`);
                if (row) {
                    row.remove();
                }
                
                // Mostrar mensaje de éxito
                alert('Enfermedad eliminada correctamente');
                
                // Actualizar contador de registros
                const registrosMostrados = document.getElementById('registrosMostrados');
                if (registrosMostrados) {
                    const visibleRows = document.querySelectorAll('#enfermedadesTableBody tr:not([style*="display: none"])').length;
                    registrosMostrados.textContent = visibleRows;
                }
            } else {
                alert('Error al eliminar la enfermedad: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar. Por favor, intenta de nuevo.');
        });
    }
}

// Función para guardar edición
function guardarEdicionEnfermedad() {
    const nombre = document.getElementById('edit_enfermedad_nombre').value.trim();
    const categoriaId = document.getElementById('edit_enfermedad_categoria').value;
    
    // Validaciones
    if (!nombre) {
        alert('Por favor ingresa el nombre de la enfermedad');
        return;
    }
    
    if (!categoriaId) {
        alert('Por favor selecciona una categoría');
        return;
    }
    
    // Mostrar indicador de carga (opcional)
    console.log('Guardando cambios para enfermedad ID:', enfermedadActualId);
    
    fetch(`/enfermedades/${enfermedadActualId}`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            nombre: nombre,
            categoria_id: categoriaId
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        return response.json();
    })
    .then(data => {
        console.log('Respuesta:', data);
        
        if (data.success) {
            // Actualizar la fila en la tabla
            const row = document.getElementById(`enfermedad-row-${enfermedadActualId}`);
            if (row) {
                // Actualizar nombre
                const nombreCell = row.cells[1];
                if (nombreCell) {
                    nombreCell.textContent = data.data.nombre;
                }
                
                // Actualizar categoría
                const categoriaCell = row.cells[2];
                if (categoriaCell) {
                    const categoriaSpan = categoriaCell.querySelector('span');
                    if (categoriaSpan) {
                        categoriaSpan.textContent = data.data.categoria.nombre;
                    }
                }
            }
            
            // Cerrar el modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarEnfermedad'));
            if (modal) {
                modal.hide();
            }
            
            // Mostrar mensaje de éxito
            alert('Enfermedad actualizada correctamente');
        } else {
            alert('Error al actualizar: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al guardar los cambios. Por favor, intenta de nuevo.');
    });
}

// Función para filtrar la tabla
document.getElementById('buscarEnfermedad')?.addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#enfermedadesTableBody tr');
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
    
    const registrosMostrados = document.getElementById('registrosMostrados');
    if (registrosMostrados) {
        registrosMostrados.textContent = visibleCount;
    }
    
    // Mostrar fila de "sin resultados" si es necesario
    const noResultsRow = document.getElementById('no-results-row');
    if (visibleCount === 0) {
        if (!noResultsRow) {
            const tbody = document.getElementById('enfermedadesTableBody');
            const tr = document.createElement('tr');
            tr.id = 'no-results-row';
            tr.innerHTML = '<td colspan="4" class="text-center py-4 text-muted">No se encontraron resultados</td>';
            tbody.appendChild(tr);
        }
    } else if (noResultsRow) {
        noResultsRow.remove();
    }
});
</script>
@endpush

<script>
function ejecutarEliminarEnfermedad(id) {
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
            document.getElementById(`enfermedad-row-${id}`).remove();
            
            // Mostrar notificación de éxito (opcional)
            alert('Enfermedad eliminada correctamente');
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>

@push('scripts')
<script>
window.ejecutarEliminarEnfermedad = function(id, nombre) {
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
            document.getElementById(`enfermedad-row-${id}`).remove();
            if (window.mostrarToast) {
                window.mostrarToast(`Enfermedad "${nombre}" eliminada`, 'success');
            }
        }
    })
    .catch(error => console.error('Error:', error));
};
</script>
@endpush