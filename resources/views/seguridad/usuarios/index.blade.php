@extends('layouts.app')

@section('title', 'Usuarios - CRM')
@section('page-title', 'Gestión de Usuarios')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <h3><i class="bi bi-people"></i> Gestión de Usuarios</h3>
        <p class="text-muted">Administra los usuarios del sistema</p>
    </div>

    <!-- Search and Actions -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" id="buscarUsuario" placeholder="Buscar por nombre, usuario o rol...">
            </div>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
                <i class="bi bi-plus-circle"></i> Nuevo Usuario
            </button>
        </div>
    </div>

    <!-- Tabla de Usuarios -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover" id="tablaUsuarios">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="usuariosTableBody">
                        @forelse($usuarios as $usuario)
                        <tr id="usuario-row-{{ $usuario->id }}">
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
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-action"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditarUsuario"
                                            data-usuario-id="{{ $usuario->id }}"
                                            title="Editar usuario">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                            onclick="confirmarEliminar('usuario', {{ $usuario->id }}, '{{ $usuario->nombre_completo }}')"
                                            title="Eliminar usuario">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="bi bi-people" style="font-size: 2rem; color: #ccc;"></i>
                                <p class="text-muted mt-2">No hay usuarios registrados</p>
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
                                    <i class="bi bi-plus"></i> Agregar primer usuario
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
                    Mostrando {{ count($usuarios) }} registros
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('seguridad.usuarios.partials.modal-nuevo-usuario')
@include('seguridad.usuarios.partials.modal-editar-usuario')
@endsection

@push('scripts')
<script>
// ============================================
// BUSCADOR EN TIEMPO REAL
// ============================================
document.getElementById('buscarUsuario')?.addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase().trim();
    const rows = document.querySelectorAll('#usuariosTableBody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (searchTerm.length === 0 || text.includes(searchTerm)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
});

// ============================================
// FUNCIÓN PARA EDITAR USUARIO
// ============================================
window.editarUsuario = function(id) {
    fetch(`/seguridad/usuarios/${id}/edit`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Llenar formulario con datos
            document.getElementById('edit_usuario_id').value = data.data.id;
            document.getElementById('edit_Nombre').value = data.data.Nombre || '';
            document.getElementById('edit_ApPaterno').value = data.data.ApPaterno || '';
            document.getElementById('edit_ApMaterno').value = data.data.ApMaterno || '';
            document.getElementById('edit_usuario').value = data.data.usuario || '';
            document.getElementById('edit_contacto').value = data.data.contacto || '';
            document.getElementById('edit_TelefonoMovil').value = data.data.TelefonoMovil || '';
            document.getElementById('edit_Direccion').value = data.data.Direccion || '';
            document.getElementById('edit_Localidad').value = data.data.Localidad || '';
            document.getElementById('edit_Municipio').value = data.data.Municipio || '';
            document.getElementById('edit_curp').value = data.data.curp || '';
            document.getElementById('edit_fecha_nacimiento').value = data.data.fecha_nacimiento || '';
            document.getElementById('edit_Activo').value = data.data.Activo ? '1' : '0';
            
            const modal = new bootstrap.Modal(document.getElementById('modalEditarUsuario'));
            modal.show();
        }
    })
    .catch(error => console.error('Error al editar:', error));
};
</script>
@endpush