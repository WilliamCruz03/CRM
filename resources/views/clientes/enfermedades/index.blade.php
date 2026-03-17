@extends('layouts.app')

@section('title', 'Patologías - CRM')
@section('page-title', 'Registro de Patologías')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <h3><i class="bi bi-heart-pulse"></i> Registro de Patologías</h3>
        <p class="text-muted">Gestiona el catálogo de patologías registradas</p>
    </div>

    <!-- Search and Actions -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" id="buscarPatologia" placeholder="Buscar patología...">
            </div>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevaPatologia">
                <i class="bi bi-plus-circle"></i> Nueva Patología
            </button>
        </div>
    </div>

    <!-- Tabla de Patologías -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover" id="tablaPatologias">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Patología</th>
                            <th>Fecha de registro</th>
                            <th>Acciones</th>
                        </tr>
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
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-action"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEditarPatologia"
                                                data-patologia-id="{{ $patologia->id_patologia }}"
                                                title="Editar patología">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                                onclick="confirmarEliminarPatologia({{ $patologia->id_patologia }}, '{{ $patologia->descripcion }}')"
                                                title="Eliminar patología">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <i class="bi bi-heart-pulse" style="font-size: 2rem; color: #ccc;"></i>
                                    <p class="text-muted mt-2">No hay patologías registradas</p>
                                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevaPatologia">
                                        <i class="bi bi-plus"></i> Agregar primera patología
                                    </button>
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
                    Mostrando {{ count($patologias) }} registros
                </div>
                <!-- Paginación simple si decides usarla después -->
            </div>
        </div>
    </div>
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

// Variable global para el ID de la patología a editar
let patologiaActualId = null;

// Función para editar patología
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

// Función para confirmar eliminación
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

// ============================================
// BUSCADOR MEJORADO DE PATOLOGÍAS
// ============================================
let timeoutIdPatologia;

document.getElementById('buscarPatologia')?.addEventListener('input', function() {
    clearTimeout(timeoutIdPatologia);
    const termino = this.value.toLowerCase().trim();
    
    // Buscar con 1 carácter
    timeoutIdPatologia = setTimeout(() => {
        const rows = document.querySelectorAll('#patologiasTableBody tr');
        let visibleCount = 0;
        
        rows.forEach(row => {
            if (row.id === 'no-results-row') return;
            
            const text = row.textContent.toLowerCase();
            // Coincidencia parcial sin importar posición
            if (termino.length === 0 || text.includes(termino)) {
                row.style.display = '';
                visibleCount++;
            } else {
                // Búsqueda aproximada: al menos 70% de las letras coinciden
                const palabras = termino.split(' ');
                let coincide = false;
                
                for (let palabra of palabras) {
                    if (palabra.length < 2) continue;
                    
                    // Buscar si la palabra aparece en cualquier parte
                    if (text.includes(palabra)) {
                        coincide = true;
                        break;
                    }
                    
                    // Búsqueda aproximada: distancia de Levenshtein simple
                    const palabrasTexto = text.split(' ');
                    for (let palabraTexto of palabrasTexto) {
                        if (distanciaLevenshtein(palabra, palabraTexto) <= 2) {
                            coincide = true;
                            break;
                        }
                    }
                    if (coincide) break;
                }
                
                row.style.display = coincide ? '' : 'none';
                if (coincide) visibleCount++;
            }
        });
        
        // Mostrar mensaje si no hay resultados
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

// Función de distancia de Levenshtein para búsqueda aproximada
function distanciaLevenshtein(a, b) {
    if (a.length === 0) return b.length;
    if (b.length === 0) return a.length;
    
    const matrix = [];
    for (let i = 0; i <= b.length; i++) matrix[i] = [i];
    for (let j = 0; j <= a.length; j++) matrix[0][j] = j;
    
    for (let i = 1; i <= b.length; i++) {
        for (let j = 1; j <= a.length; j++) {
            if (b.charAt(i-1) === a.charAt(j-1)) {
                matrix[i][j] = matrix[i-1][j-1];
            } else {
                matrix[i][j] = Math.min(
                    matrix[i-1][j-1] + 1,
                    matrix[i][j-1] + 1,
                    matrix[i-1][j] + 1
                );
            }
        }
    }
    return matrix[b.length][a.length];
}
</script>
@endpush