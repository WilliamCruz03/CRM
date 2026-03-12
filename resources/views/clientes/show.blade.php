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
                <table class="table table-hover mb-0" id="tablaEnfermedadesShow">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Padecimiento o Enfermedad</th>
                            <th>Categoría</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cliente->enfermedades as $index => $enfermedad)
                        <tr id="enfermedad-row-{{ $enfermedad->id }}">
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $enfermedad->nombre }}</td>
                            <td><span class="badge bg-info">{{ $enfermedad->categoria->nombre ?? 'Sin categoría' }}</span></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                        onclick="eliminarEnfermedadCliente({{ $cliente->id }}, {{ $enfermedad->id }}, '{{ $enfermedad->nombre }}')"
                                        title="Eliminar enfermedad">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="bi bi-heart-pulse text-muted" style="font-size: 2rem;"></i>
                                <p class="text-muted mt-2">No hay enfermedades registradas</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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
                                <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                        onclick="eliminarPreferencia({{ $preferencia->id }})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <i class="bi bi-heart" style="font-size: 2rem; color: #ccc;"></i>
                                <p class="text-muted mt-2">No hay preferencias registradas para este cliente</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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
// ============================================
// FUNCIONES PARA LA VISTA SHOW
// ============================================

// Función para eliminar enfermedad (usando modal de confirmación)
window.eliminarEnfermedadCliente = function(clienteId, enfermedadId, enfermedadNombre) {
    const modalConfirmar = document.getElementById('modalConfirmarEliminar');
    if (!modalConfirmar) return;
    
    window.contextoEliminar = {
        clienteId: clienteId,
        enfermedadId: enfermedadId,
        nombre: enfermedadNombre
    };
    
    document.getElementById('detalleConfirmacion').textContent = 
        `¿Eliminar la enfermedad "${enfermedadNombre}" de este cliente?`;
    
    const btnConfirmar = document.getElementById('btnConfirmarEliminar');
    const originalOnClick = btnConfirmar.onclick;
    
    btnConfirmar.onclick = function() {
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
                if (window.mostrarToast) {
                    window.mostrarToast(`"${enfermedadNombre}" eliminada`, 'success');
                }
            }
        });
        
        btnConfirmar.onclick = originalOnClick;
        bootstrap.Modal.getInstance(modalConfirmar).hide();
    };
    
    new bootstrap.Modal(modalConfirmar).show();
};

// Función para eliminar preferencia
window.eliminarPreferencia = function(id) {
    const modalConfirmar = document.getElementById('modalConfirmarEliminar');
    if (!modalConfirmar) return;
    
    document.getElementById('detalleConfirmacion').textContent = '¿Eliminar esta preferencia?';
    
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
                location.reload();
            }
        });
        
        btnConfirmar.onclick = originalOnClick;
        bootstrap.Modal.getInstance(modalConfirmar).hide();
    };
    
    new bootstrap.Modal(modalConfirmar).show();
};
</script>
@endpush