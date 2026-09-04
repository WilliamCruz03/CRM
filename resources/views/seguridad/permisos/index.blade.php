@extends('layouts.app')

@section('title', 'Permisos de Usuarios - CRM')
@section('page-title', 'Gestión de Permisos')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h3><i class="bi bi-key"></i> Gestión de Permisos</h3>
        <p class="text-muted">Administra los permisos de acceso de los usuarios del sistema</p>
    </div>

    @php
        $puedeVer = $permisos['ver'] ?? false;
        $puedeEditar = $permisos['editar'] ?? false;
        $puedeEliminar = $permisos['eliminar'] ?? false;
    @endphp

    @if($puedeVer)
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" id="buscarUsuario" placeholder="Buscar usuario por nombre..." autocomplete="off">
            </div>
        </div>
        <div class="col-md-6 text-end">
            <span class="text-muted">
                <i class="bi bi-info-circle"></i> Los usuarios se crean desde el módulo de Usuarios
            </span>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Usuario</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="permisosTableBody">
                        @forelse($usuarios as $usuario)
                        <tr id="usuario-row-{{ $usuario->id_personal_empresa }}">
                            <td><span class="badge bg-secondary">{{ $usuario->id_personal_empresa }}</span></td>
                            <td><strong>{{ $usuario->nombre_completo }}</strong></td>
                            <td><span class="badge bg-info">{{ $usuario->usuario }}</span></td>
                            <td>
                                @if($usuario->Activo)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Inactivo</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @if($puedeEditar)
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-action"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditarPermisos"
                                            data-usuario-id="{{ $usuario->id_personal_empresa }}"
                                            title="Editar permisos">
                                        <i class="bi bi-key"></i>
                                    </button>
                                    @endif
                                    @if($puedeEliminar)
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                            onclick="confirmarEliminar('permisos', {{ $usuario->id_personal_empresa }}, '{{ addslashes($usuario->usuario) }}')"
                                            title="Eliminar usuario">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="bi bi-people" style="font-size: 2rem; color: #ccc;"></i>
                                <p class="text-muted mt-2">No hay usuarios registrados</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($usuarios->hasPages())
                <div class="d-flex justify-content-end mt-3">
                    {{ $usuarios->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
    @else
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> No tienes permiso para acceder a este módulo.
    </div>
    @endif
</div>

<!-- Modal Editar Permisos -->
@include('seguridad.permisos.partials.modal-editar-permisos')
@endsection

@push('scripts')
<script>
// Buscador de usuarios
let timeoutBusquedaPermisos = null;

document.getElementById('buscarUsuario')?.addEventListener('keyup', function() {
    const searchTerm = this.value.trim();
    
    clearTimeout(timeoutBusquedaPermisos);
    
    if (searchTerm.length === 0) {
        window.location.reload();
        return;
    }
    
    if (searchTerm.length >= 2) {
        timeoutBusquedaPermisos = setTimeout(() => {
            buscarUsuariosPermisos(searchTerm);
        }, 500);
    }
});

function buscarUsuariosPermisos(termino) {
    const tbody = document.getElementById('permisosTableBody');
    const searchTerm = encodeURIComponent(termino);
    
    fetch(`{{ route('seguridad.usuarios.buscar') }}?q=${searchTerm}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data.length > 0) {
            const puedeEditar = {{ $puedeEditar ? 'true' : 'false' }};
            const puedeEliminar = {{ $puedeEliminar ? 'true' : 'false' }};
            
            let html = '';
            data.data.forEach(usuario => {
                const nombreCompleto = `${usuario.Nombre || ''} ${usuario.ApPaterno || ''} ${usuario.ApMaterno || ''}`.trim();
                const estado = usuario.Activo ? 'Activo' : 'Inactivo';
                const estadoBadge = usuario.Activo ? 'bg-success' : 'bg-danger';
                
                html += `
                    <tr id="usuario-row-${usuario.id_personal_empresa}">
                        <td><span class="badge bg-secondary">${usuario.id_personal_empresa}</span></td>
                        <td><strong>${nombreCompleto}</strong></td>
                        <td><span class="badge bg-info">${usuario.usuario || '-'}</span></td>
                        <td>
                            <span class="badge ${estadoBadge}">${estado}</span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                ${puedeEditar ? `
                                <button type="button" class="btn btn-sm btn-outline-primary btn-action"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEditarPermisos"
                                        data-usuario-id="${usuario.id_personal_empresa}"
                                        title="Editar permisos">
                                    <i class="bi bi-key"></i>
                                </button>
                                ` : ''}
                                ${puedeEliminar ? `
                                <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                        onclick="confirmarEliminar('permisos', ${usuario.id_personal_empresa}, '${usuario.usuario}')"
                                        title="Eliminar usuario">
                                    <i class="bi bi-trash"></i>
                                </button>
                                ` : ''}
                            </div>
                        </td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = html;
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">
                        <i class="bi bi-search"></i> No se encontraron usuarios con "<strong>${termino}</strong>"
                    </td>
                </tr>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error al buscar usuarios', 'danger');
    });
}

// Delegación de eventos para botones de edición
document.addEventListener('click', function(e) {
    const btn = e.target.closest('[data-bs-toggle="modal"][data-bs-target="#modalEditarPermisos"]');
    if (btn) {
        const usuarioId = btn.getAttribute('data-usuario-id');
        if (usuarioId) {
            cargarDatosPermisos(usuarioId);
        }
    }
});
</script>
@endpush