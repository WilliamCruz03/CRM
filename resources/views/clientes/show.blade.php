@extends('layouts.app')

@section('title', 'Detalle del Cliente - CRM')
@section('page-title', 'Datos del Cliente')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <h3><i class="bi bi-person-vcard"></i> Datos del Cliente</h3>
        <p class="text-muted">Gestiona el historial médico, alergias, y condiciones especiales del cliente</p>
    </div>

    <!-- Información básica del cliente -->
    <div class="card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span><i class="bi bi-info-circle text-warning"></i> Información del Cliente</span>
            <button type="button" class="btn btn-warning" id="btnEditarCliente"
                    data-bs-toggle="modal"
                    data-bs-target="#modalEditarCliente"
                    data-cliente-id="{{ $cliente->id }}"
                    title="Editar cliente">
                <i class="bi bi-pencil"></i> Editar datos generales
            </button>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="info-label">Nombre</div>
                    <div class="info-value h5 mb-3">{{ $cliente->nombre_completo }}</div>
                </div>
                <div class="col-md-4">
                    <div class="info-label">Correo electrónico</div>
                    <div class="info-value">
                        <i class="bi bi-envelope text-primary"></i> {{ $cliente->email }}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-label">Teléfono</div>
                    <div class="info-value">
                        <i class="bi bi-telephone text-primary"></i> {{ $cliente->telefono ?? 'No especificado' }}
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <div class="info-label">Dirección</div>
                    <div class="info-value">
                        <i class="bi bi-geo-alt text-primary"></i> {{ $cliente->direccion_completa }}
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <div class="info-label">Estado</div>
                    <div class="info-value">
                        <span class="badge-status {{ $cliente->estado == 'Activo' ? 'badge-active' : 'badge-inactive' }}">
                            {{ $cliente->estado }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de padecimientos -->
    <div class="card">
        <div class="card-header bg-white">
            <span><i class="bi bi-heart-pulse"></i> Historial Médico</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Padecimiento o Enfermedad</th>
                            <th>Categoría</th>
                            <th>Severidad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cliente->enfermedades as $index => $enfermedad)
                        <tr id="enfermedad-row-{{ $enfermedad->id }}">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <span class="fw-medium">{{ $enfermedad->nombre }}</span>
                                @if($enfermedad->pivot->notas)
                                    <br><small class="text-muted">{{ $enfermedad->pivot->notas }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $enfermedad->categoria->nombre ?? 'Sin categoría' }}</span>
                            </td>
                            <td>
                                @if($enfermedad->pivot->severidad)
                                    @php
                                        $severidadClass = match($enfermedad->pivot->severidad) {
                                            'Leve' => 'bg-success',
                                            'Moderada' => 'bg-warning',
                                            'Grave' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $severidadClass }}">{{ $enfermedad->pivot->severidad }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                        onclick="eliminarEnfermedadCliente({{ $cliente->id }}, {{ $enfermedad->id }})"
                                        title="Eliminar enfermedad">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="bi bi-heart-pulse" style="font-size: 2rem; color: #ccc;"></i>
                                <p class="text-muted mt-2">No hay enfermedades registradas para este cliente</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarEnfermedad">
                <i class="bi bi-plus"></i> Agregar Enfermedad
            </button>
        </div>
    </div>

    <!-- Preferencias del Cliente -->
    <div class="card mt-4">
        <div class="card-header bg-white">
            <span><i class="bi bi-heart"></i> Preferencias del Cliente</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Categoría</th>
                            <th>Preferencia</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cliente->preferencias as $preferencia)
                        <tr>
                            <td>{{ $preferencia->fecha_registro->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-info">{{ $preferencia->categoria ?? 'General' }}</span>
                            </td>
                            <td>{{ $preferencia->descripcion }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-danger btn-action"
                                        onclick="eliminarPreferencia({{ $preferencia->id }})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <i class="bi bi-heart" style="font-size: 2rem; color: #ccc;"></i>
                                <p class="text-muted mt-2">No hay preferencias registradas</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalNuevaPreferencia">
                <i class="bi bi-plus"></i> Agregar Preferencia
            </button>
        </div>
    </div>

    <!-- Botones de navegación -->
    <div class="mt-4">
        <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver al listado
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Variable global para el ID del cliente
let clienteActualId = null;

// Función para guardar edición de cliente (GLOBAL)
window.guardarEdicionCliente = function() {
    const id = document.getElementById('edit_cliente_id')?.value;
    clienteActualId = id;
    
    // Obtener enfermedades seleccionadas
    const selectEnfermedades = document.getElementById('edit_enfermedades');
    const enfermedadesSeleccionadas = [];
    
    if (selectEnfermedades) {
        Array.from(selectEnfermedades.selectedOptions).forEach(option => {
            enfermedadesSeleccionadas.push(parseInt(option.value));
        });
    }
    
    console.log('Enfermedades seleccionadas:', enfermedadesSeleccionadas);
    
    const formData = {
        nombre: document.getElementById('edit_nombre')?.value || '',
        apellidos: document.getElementById('edit_apellidos')?.value || '',
        email: document.getElementById('edit_email')?.value || '',
        telefono: document.getElementById('edit_telefono')?.value || '',
        calle: document.getElementById('edit_calle')?.value || '',
        colonia: document.getElementById('edit_colonia')?.value || '',
        ciudad: document.getElementById('edit_ciudad')?.value || '',
        estado: document.getElementById('edit_estado')?.value || 'Activo',
        enfermedades: enfermedadesSeleccionadas,
        _token: '{{ csrf_token() }}',
        _method: 'PUT'
    };
    
    // Validar campos requeridos
    if (!formData.nombre || !formData.apellidos || !formData.email) {
        alert('Por favor completa todos los campos requeridos');
        return;
    }
    
    console.log('Enviando datos:', formData);
    
    fetch(`/clientes/${id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw err; });
        }
        return response.json();
    })
    .then(data => {
        console.log('Respuesta:', data);
        if (data.success) {
            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarCliente'));
            if (modal) {
                modal.hide();
            }
            
            // Recargar la página para ver los cambios
            alert('Cliente actualizado correctamente');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error completo:', error);
        alert('Error al actualizar: ' + (error.message || 'Error de conexión'));
    });
};

// Función para cargar enfermedades en el modal
function cargarEnfermedadesParaEdicion(enfermedadesSeleccionadas = []) {
    fetch('/enfermedades/todas', {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const select = document.getElementById('edit_enfermedades');
            if (!select) return;
            
            select.innerHTML = '';
            
            data.data.forEach(enfermedad => {
                const option = document.createElement('option');
                option.value = enfermedad.id;
                option.textContent = `${enfermedad.nombre} (${enfermedad.categoria?.nombre || 'Sin categoría'})`;
                
                // Seleccionar si el cliente ya tiene esta enfermedad
                if (enfermedadesSeleccionadas && enfermedadesSeleccionadas.includes(enfermedad.id)) {
                    option.selected = true;
                }
                
                select.appendChild(option);
            });
            
            // Ocultar loading y mostrar select
            const loading = document.getElementById('enfermedades-loading');
            if (loading) loading.style.display = 'none';
            select.style.display = 'block';
        }
    })
    .catch(error => console.error('Error al cargar enfermedades:', error));
}

// Función para cargar datos del cliente en el modal
function cargarDatosCliente(clienteId) {
    fetch(`/clientes/${clienteId}/edit`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('edit_cliente_id').value = data.data.id;
            document.getElementById('edit_nombre').value = data.data.nombre;
            document.getElementById('edit_apellidos').value = data.data.apellidos;
            document.getElementById('edit_email').value = data.data.email;
            document.getElementById('edit_telefono').value = data.data.telefono || '';
            document.getElementById('edit_calle').value = data.data.calle || '';
            document.getElementById('edit_colonia').value = data.data.colonia || '';
            document.getElementById('edit_ciudad').value = data.data.ciudad || '';
            document.getElementById('edit_estado').value = data.data.estado;
            
            // Cargar enfermedades después de los datos básicos
            cargarEnfermedadesParaEdicion(data.data.enfermedades);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Evento cuando se abre el modal
document.addEventListener('DOMContentLoaded', function() {
    const modalEditar = document.getElementById('modalEditarCliente');
    
    if (modalEditar) {
        modalEditar.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const clienteId = button.getAttribute('data-cliente-id');
            
            // Mostrar loading
            const loading = document.getElementById('enfermedades-loading');
            const select = document.getElementById('edit_enfermedades');
            if (loading) loading.style.display = 'block';
            if (select) select.style.display = 'none';
            
            // Cargar datos del cliente
            cargarDatosCliente(clienteId);
        });
    }
});

// Función para eliminar enfermedad de un cliente
function eliminarEnfermedadCliente(clienteId, enfermedadId) {
    if (confirm('¿Estás seguro de eliminar esta enfermedad del cliente?')) {
        fetch(`/clientes/${clienteId}/enfermedades/${enfermedadId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`enfermedad-row-${enfermedadId}`).remove();
                alert('Enfermedad eliminada correctamente');
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

// Función para eliminar preferencia
function eliminarPreferencia(id) {
    if (confirm('¿Estás seguro de eliminar esta preferencia?')) {
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
                location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
    }
}
</script>
@endpush