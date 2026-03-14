@extends('layouts.app')

@section('title', 'Detalle del Cliente - CRM')
@section('page-title', 'Datos del Cliente')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <h3><i class="bi bi-person-vcard"></i> Datos del Cliente</h3>
        <p class="text-muted">Gestiona el historial médico y datos del cliente</p>
    </div>

    <!-- Indicador de status destacado (FUERA de la card) -->
    @if(trim($cliente->status) == 'BLOQUEADO')
    <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
        <div>
            <strong>Cliente Bloqueado</strong> - Este cliente tiene restricciones en el sistema.
        </div>
    </div>
    @elseif(trim($cliente->status) == 'PROSPECTO')
    <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-info-circle-fill me-2 fs-4"></i>
        <div>
            <strong>Cliente en Prospecto</strong> - En proceso de validación.
        </div>
    </div>
    @endif

    <!-- Información básica del cliente -->
    <div class="card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span><i class="bi bi-info-circle text-warning"></i> Información del Cliente</span>
            <button type="button" class="btn btn-warning" id="btnEditarCliente"
                    data-bs-toggle="modal"
                    data-bs-target="#modalEditarCliente"
                    data-cliente-id="{{ $cliente->id_Cliente }}"
                    title="Editar cliente">
                <i class="bi bi-pencil"></i> Editar datos generales
            </button>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="info-label">Nombre completo</div>
                    <div class="info-value h5 mb-3">{{ $cliente->nombre_completo }}</div>
                </div>
                <div class="col-md-4">
                    <div class="info-label">Título</div>
                    <div class="info-value">{{ $cliente->titulo ?? 'No especificado' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        @php
                            $statusLimpio = trim($cliente->status);
                            $statusClass = match($statusLimpio) {
                                'CLIENTE' => 'bg-success',
                                'PROSPECTO' => 'bg-warning',
                                'BLOQUEADO' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                        @endphp
                        <span class="badge {{ $statusClass }}">{{ $cliente->status }}</span>
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-4">
                    <div class="info-label">Correo principal</div>
                    <div class="info-value">
                        <i class="bi bi-envelope text-primary"></i> {{ $cliente->email1 }}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-label">Teléfono principal</div>
                    <div class="info-value">
                        <i class="bi bi-telephone text-primary"></i> {{ $cliente->telefono1 ?? 'No especificado' }}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-label">Teléfono secundario</div>
                    <div class="info-value">
                        <i class="bi bi-telephone text-secondary"></i> {{ $cliente->telefono2 ?? 'No especificado' }}
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-4">
                    <div class="info-label">Sexo</div>
                    <div class="info-value">
                        @switch($cliente->Sexo)
                            @case('M') Masculino @break
                            @case('F') Femenino @break
                            @case('OTRO') Otro @break
                            @default No especificado
                        @endswitch
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-label">Fecha de nacimiento</div>
                    <div class="info-value">
                        {{ $cliente->FechaNac ? $cliente->FechaNac->format('d/m/Y') : 'No especificada' }}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-label">Sucursal origen</div>
                    <div class="info-value">{{ $cliente->sucursal_origen == 0 ? 'CRM' : 'Sucursal ' . $cliente->sucursal_origen }}</div>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-12">
                    <div class="info-label">Domicilio</div>
                    <div class="info-value">
                        <i class="bi bi-geo-alt text-primary"></i> {{ $cliente->Domicilio ?? 'No especificado' }}
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="info-label">País ID</div>
                    <div class="info-value">{{ $cliente->pais_id ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Estado ID</div>
                    <div class="info-value">{{ $cliente->estado_id ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Municipio ID</div>
                    <div class="info-value">{{ $cliente->municipio_id ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Localidad ID</div>
                    <div class="info-value">{{ $cliente->localidad_id ?? '-' }}</div>
                </div>
            </div>
            
            <div class="row mt-2">
                <div class="col-12">
                    <div class="info-label">Fecha de registro</div>
                    <div class="info-value text-muted small">
                        {{ $cliente->fecha_creacion ? $cliente->fecha_creacion->format('d/m/Y H:i') : 'No especificada' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Tabla de patologías -->
<div class="card">
    <div class="card-header bg-white">
        <span><i class="bi bi-heart-pulse"></i> Patologías Asociadas</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="tablaPatologiasShow">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Patología</th>
                        <th>Status</th>
                        <th>Fecha de asociación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cliente->patologiasAsociadas as $index => $asociada)
                    <tr id="patologia-row-{{ $asociada->id_patologia_asociada }}">
                        <td>{{ $index + 1 }}</td>
                        <td>{{ trim($asociada->patologia) }}</td>
                        <td>
                            <span class="badge {{ $asociada->status ? 'bg-success' : 'bg-secondary' }}">
                                {{ $asociada->status ? 'ACTIVO' : 'INACTIVO' }}
                            </span>
                        </td>
                        <td>
                            {{ $asociada->fecha_creacion ? \Carbon\Carbon::parse($asociada->fecha_creacion)->format('d/m/Y H:i') : '-' }}
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                    onclick="eliminarPatologiaCliente({{ $cliente->id_Cliente }}, {{ $asociada->id_patologia_asociada }}, '{{ trim($asociada->patologia) }}')"
                                    title="Eliminar patología">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <i class="bi bi-heart-pulse text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2">No hay patologías asociadas a este cliente</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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

// Función para eliminar patología
window.eliminarPatologiaCliente = function(clienteId, patologiaAsociadaId, patologiaNombre) {
    const modalConfirmar = document.getElementById('modalConfirmarEliminar');
    if (!modalConfirmar) return;
    
    window.contextoEliminar = {
        clienteId: clienteId,
        patologiaDescripcion: patologiaDescripcion
    };
    
    document.getElementById('detalleConfirmacion').textContent = 
        `¿Eliminar la patología "${patologiaDescripcion}" de este cliente?`;
    
    const btnConfirmar = document.getElementById('btnConfirmarEliminar');
    const originalOnClick = btnConfirmar.onclick;
    
    btnConfirmar.onclick = function() {
        fetch(`/clientes/${clienteId}/patologias`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                patologia: patologiaDescripcion
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recargar la página o eliminar la fila
                location.reload();
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