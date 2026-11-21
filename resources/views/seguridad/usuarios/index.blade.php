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
                <input type="text" class="form-control" id="buscarUsuario" placeholder="Buscar por usuario o nombre" autocomplete="off">
                <button type="button" class="bi bi-x-circle-fill" id="limpiarBuscarUsuario" 
                style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); z-index: 10; color: #6c757d; cursor: pointer; display: none; font-size: 1.2rem; background: none; border: none; padding: 0; line-height: 1;"
                title="Limpiar búsqueda"></button>
            </div>
            @endif
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-outline-info" id="btnVerRepartidores">
                <i class="bi bi-truck"></i> Ver repartidores
            </button>
            @if($puedeCrear)
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
                <i class="bi bi-plus-circle"></i> Nuevo Usuario
            </button>
            @endif
        </div>
    </div>
    @endif

    @if($puedeVer)
    <div id="seguridad-table-container">@include('seguridad.usuarios.partials.tabla')</div>
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
window.refrescarListadoSeguridad = async function() {
    const contenedor = document.getElementById('seguridad-table-container');
    if (!contenedor) return;
    contenedor.setAttribute('aria-busy', 'true');
    try {
        const respuesta = await fetch(window.location.href, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, cache: 'no-store' });
        if (!respuesta?.ok) throw new Error('Consulta fallida');
        const datos = await respuesta.json();
        if (typeof datos.html !== 'string') throw new Error('Respuesta inesperada');
        contenedor.innerHTML = datos.html;
        const campo = document.getElementById('buscarUsuario');
        if (campo?.value.trim()) campo.dispatchEvent(new Event('input'));
        else window.inicializarSelectorPerPage?.('perPageSelect');
    } catch {
        window.mostrarToast?.('No se pudo actualizar el listado. Intenta de nuevo.', 'danger');
    } finally {
        contenedor.removeAttribute('aria-busy');
    }
};

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
            <td><span class="badge bg-secondary">${usuario.id_personal_empresa}</span></td>
            <td><strong>${usuario.Nombre || ''} ${usuario.ApPaterno || ''} ${usuario.ApMaterno || ''}</strong></td>
            <td><span class="badge bg-info">${usuario.usuario || '-'}</span></td>
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
        buscarInput.addEventListener('input', function() {
            const searchTerm = this.value.trim();

            clearTimeout(timeoutBusquedaUsuarios);

            if (searchTerm.length === 0) {
                const paginationContainer = document.querySelector('.d-flex.justify-content-end.mt-3');
                if (paginationContainer) {
                    paginationContainer.style.display = 'block';
                }
                window.refrescarListadoSeguridad();
                return;
            }

            if (searchTerm.length >= 3) {
                // Spinner inmediato
                const tbody = document.getElementById('usuariosTableBody');
                if (tbody) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Buscando usuarios...</span>
                                </div>
                                <p class="mt-2 text-muted">Buscando usuarios...</p>
                            </td>
                        </tr>
                    `;
                }

                timeoutBusquedaUsuarios = setTimeout(() => {
                    buscarUsuarios(searchTerm);
                }, 200);
            }
        });
    }
});

function buscarUsuarios(termino) {
    // Ocultar el bloque de paginación mientras se busca
    document.querySelectorAll('.paginacion-bloque').forEach(el => el.style.display = 'none');
    
    const tbody = document.getElementById('usuariosTableBody');
    const paginationContainer = document.querySelector('.d-flex.justify-content-end.mt-3');

    // Ocultar paginación mientras se busca
    if (paginationContainer) {
        paginationContainer.style.display = 'none';
    }

    const searchTerm = encodeURIComponent(termino);

    fetch(`{{ route('seguridad.usuarios.buscar') }}?q=${searchTerm}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (document.getElementById('buscarUsuario').value.trim() !== termino) return;
        if (data.success && data.data.length > 0) {
            mostrarResultadosUsuarios(data.data);
        } else {
            tbody.innerHTML = `
                <tr id="usuariosSinResultados">
                    <td colspan="5" class="text-center py-4 text-muted">
                        <i class="bi bi-search"></i> No se encontraron usuarios con "<strong>${escapeHtml(termino)}</strong>"
                    </td>
                </tr>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-5 text-danger">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                        <p class="mt-2">Error al buscar usuarios</p>
                    </td>
                </tr>
            `;
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

document.addEventListener('DOMContentLoaded', function() {
    window.inicializarSelectorPerPage('perPageSelect');
});
document.getElementById('limpiarBuscarUsuario')?.addEventListener('click', () => {
    const campo = document.getElementById('buscarUsuario');
    campo.value = '';
    campo.dispatchEvent(new Event('input'));
    campo.focus();
});
</script>
@endpush