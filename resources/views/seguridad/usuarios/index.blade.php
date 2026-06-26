@extends('layouts.app')

@section('title', 'Usuarios - CRM')
@section('page-title', 'Gestión de Usuarios')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h3><i class="bi bi-people"></i> Gestión de Usuarios</h3>
        <p class="text-muted">Administra los usuarios del sistema</p>
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
                <input type="text" class="form-control" id="buscarUsuario" placeholder="Buscar por usuario, nombre o correo...">
            </div>
            @endif
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-outline-info" id="btnVerRepartidores">
                <i class="bi bi-truck"></i> Ver repartidores
            </button>
            @if($puedeCrear)
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
                <i class="bi bi-plus-circle"></i> Registrar
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
                            <th>Usuario</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                    </thead>
                    <tbody id="usuariosTableBody">
                        <!-- Fila de carga (oculta por defecto) -->
                        <tr id="loadingUsuariosRow" style="display: none;">
                            <td colspan="5" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-2">Buscando usuarios...</p>
                            </td>
                        </tr>
                        
                        @forelse($usuarios as $usuario)
                        <tr id="usuario-row-{{ $usuario->id_personal_empresa }}">
                            <td><span class="badge bg-secondary">{{ $usuario->usuario }}</span></td>
                            <td><strong>{{ $usuario->nombre_completo }}</strong></td>
                            <td>{{ $usuario->contacto ?? 'N/A' }}</td>
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
                                            data-bs-target="#modalEditarUsuario"
                                            data-usuario-id="{{ $usuario->id_personal_empresa }}"
                                            title="Editar usuario">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @endif
                                    @if($puedeEliminar)
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                            onclick="confirmarEliminar('usuario', {{ $usuario->id_personal_empresa }}, '{{ addslashes($usuario->usuario) }}')"
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
    @elseif($puedeCrear)
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-people" style="font-size: 3rem; color: #ccc;"></i>
            <p class="text-muted mt-3">No tienes permiso para ver la lista de usuarios, pero puedes crear nuevos.</p>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
                <i class="bi bi-plus-circle"></i> Registrar usuario
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
@include('seguridad.usuarios.partials.modal-nuevo-usuario')
@include('seguridad.usuarios.partials.modal-editar-usuario')
@endsection

@push('scripts')
<script>
// ============================================
// FILTRO DE REPARTIDORES (agregar/remover filas)
// ============================================
let modoRepartidores = false;
let repartidoresCache = null;
let timeoutBusquedaUsuarios = null;

const btnVerRepartidores = document.getElementById('btnVerRepartidores');
if (btnVerRepartidores) {
    btnVerRepartidores.addEventListener('click', function() {
        modoRepartidores = !modoRepartidores;
        
        if (modoRepartidores) {
            // Cargar repartidores y agregarlos a la tabla
            if (repartidoresCache === null) {
                fetch('/seguridad/usuarios/repartidores')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            repartidoresCache = data.data;
                            agregarRepartidoresATabla(repartidoresCache);
                            btnVerRepartidores.innerHTML = '<i class="bi bi-people"></i> Ocultar repartidores';
                        }
                    })
                    .catch(error => console.error('Error:', error));
            } else {
                agregarRepartidoresATabla(repartidoresCache);
                btnVerRepartidores.innerHTML = '<i class="bi bi-people"></i> Ocultar repartidores';
            }
        } else {
            // Remover repartidores de la tabla
            removerRepartidoresDeTabla();
            btnVerRepartidores.innerHTML = '<i class="bi bi-truck"></i> Ver repartidores';
        }
    });
}

// Función para agregar repartidores a la tabla
function agregarRepartidoresATabla(repartidores) {
    const tbody = document.getElementById('usuariosTableBody');
    if (!tbody) return;
    
    // Obtener IDs de usuarios normales que ya están en la tabla
    const idsExistentes = [];
    document.querySelectorAll('#usuariosTableBody tr').forEach(row => {
        const idCell = row.querySelector('td:first-child');
        if (idCell && idCell.textContent) {
            idsExistentes.push(parseInt(idCell.textContent));
        }
    });
    
    // Agregar solo los repartidores que no están ya en la tabla
    repartidores.forEach(usuario => {
        if (!idsExistentes.includes(usuario.id_personal_empresa)) {
            agregarFilaUsuario(usuario);
        }
    });
}

// Función para remover repartidores de la tabla
function removerRepartidoresDeTabla() {
    const filasRepartidores = document.querySelectorAll('#usuariosTableBody tr[data-es-repartidor="true"]');
    filasRepartidores.forEach(fila => fila.remove());
}

// Función para agregar una fila de usuario a la tabla
function agregarFilaUsuario(usuario) {
    const tbody = document.getElementById('usuariosTableBody');
    if (!tbody) return;
    
    const puedeEditar = {{ $puedeEditar ? 'true' : 'false' }};
    const puedeEliminar = {{ $puedeEliminar ? 'true' : 'false' }};
    
    const html = `
        <tr id="usuario-row-${usuario.id_personal_empresa}" data-es-repartidor="true">
            <td><span class="badge bg-secondary">${usuario.usuario || '-'}</span></td>
            <td><strong>${usuario.Nombre || ''} ${usuario.ApPaterno || ''} ${usuario.ApMaterno || ''}</strong></td>
            <td>${usuario.contacto || 'N/A'}</td>
            <td>
                <span class="badge ${usuario.Activo ? 'bg-success' : 'bg-danger'}">
                    ${usuario.Activo ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>
                <div class="btn-group" role="group">
                    ${puedeEditar ? `
                    <button type="button" class="btn btn-sm btn-outline-primary btn-action"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEditarUsuario"
                            data-usuario-id="${usuario.id_personal_empresa}"
                            title="Editar usuario">
                        <i class="bi bi-pencil"></i>
                    </button>
                    ` : ''}
                    ${puedeEliminar ? `
                    <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                            onclick="confirmarEliminar('usuario', ${usuario.id_personal_empresa}, '${usuario.usuario}')"
                            title="Eliminar usuario">
                        <i class="bi bi-trash"></i>
                    </button>
                    ` : ''}
                </div>
            </td>
        </tr>
    `;
    
    tbody.insertAdjacentHTML('beforeend', html);
}

// ============================================
// BUSCADOR DE USUARIOS
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    const buscarInput = document.getElementById('buscarUsuario');
    if (buscarInput) {
        buscarInput.addEventListener('keyup', function() {
            const searchTerm = this.value.trim();
            
            clearTimeout(timeoutBusquedaUsuarios);
            
            if (searchTerm.length === 0) {
                // Restaurar paginación y recargar
                const paginationContainer = document.querySelector('.d-flex.justify-content-end.mt-3');
                if (paginationContainer) {
                    paginationContainer.style.display = 'block';
                }
                // Recargar la página para mostrar la tabla original con paginación
                window.location.reload();
                return;
            }
            
            if (searchTerm.length >= 2) {
                timeoutBusquedaUsuarios = setTimeout(() => {
                    buscarUsuarios(searchTerm);
                }, 500);
            }
        });
    }
});

function buscarUsuarios(termino) {
    const tbody = document.getElementById('usuariosTableBody');
    const loadingRow = document.getElementById('loadingUsuariosRow');
    const paginationContainer = document.querySelector('.d-flex.justify-content-end.mt-3');
    
    // Ocultar paginación mientras se busca
    if (paginationContainer) {
        paginationContainer.style.display = 'none';
    }
    
    if (loadingRow) {
        loadingRow.style.display = 'table-row';
    }
    
    fetch(`{{ route('seguridad.usuarios.buscar') }}?q=${encodeURIComponent(termino)}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (loadingRow) {
            loadingRow.style.display = 'none';
        }
        
        if (data.success && data.data.length > 0) {
            mostrarResultadosUsuarios(data.data);
        } else {
            tbody.innerHTML = `
                <tr id="usuariosSinResultados">
                    <td colspan="5" class="text-center py-4 text-muted">
                        <i class="bi bi-search"></i> No se encontraron usuarios con "${termino}"
                    </td>
                </tr>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (loadingRow) {
            loadingRow.style.display = 'none';
        }
        if (window.mostrarToast) {
            window.mostrarToast('Error al buscar usuarios', 'danger');
        }
    });
}

function mostrarResultadosUsuarios(usuarios) {
    const tbody = document.getElementById('usuariosTableBody');
    const puedeEditar = {{ $puedeEditar ? 'true' : 'false' }};
    const puedeEliminar = {{ $puedeEliminar ? 'true' : 'false' }};
    
    // Ocultar paginación
    const paginationContainer = document.querySelector('.d-flex.justify-content-end.mt-3');
    if (paginationContainer) {
        paginationContainer.style.display = 'none';
    }
    
    let html = '';
    usuarios.forEach((usuario) => {
        const nombreCompleto = `${usuario.Nombre || ''} ${usuario.ApPaterno || ''} ${usuario.ApMaterno || ''}`.trim();
        const estado = usuario.Activo ? 'Activo' : 'Inactivo';
        const estadoBadge = usuario.Activo ? 'bg-success' : 'bg-danger';
        
        // Escapar nombre para el onclick
        const nombreEscapado = nombreCompleto.replace(/'/g, "\\'");
        
        html += `
            <tr id="usuario-row-${usuario.id_personal_empresa}">
                <td><span class="badge bg-secondary">${usuario.usuario || '-'}</span></td>
                <td><strong>${nombreCompleto}</strong></td>
                <td>${usuario.contacto || 'N/A'}</td>
                <td>
                    <span class="badge ${estadoBadge}">${estado}</span>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        ${puedeEditar ? `
                        <button type="button" class="btn btn-sm btn-outline-primary btn-action"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditarUsuario"
                                data-usuario-id="${usuario.id_personal_empresa}"
                                title="Editar usuario">
                            <i class="bi bi-pencil"></i>
                        </button>
                        ` : ''}
                        ${puedeEliminar ? `
                        <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                onclick="confirmarEliminar('usuario', ${usuario.id_personal_empresa}, '${usuario.usuario}')"
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
}

// Delegación de eventos para botones de edición dinámicos
document.addEventListener('click', function(e) {
    const btn = e.target.closest('[data-bs-toggle="modal"][data-bs-target="#modalEditarUsuario"]');
    if (btn) {
        const usuarioId = btn.getAttribute('data-usuario-id');
        if (usuarioId) {
            cargarDatosUsuario(usuarioId);
        }
    }
});
</script>
@endpush