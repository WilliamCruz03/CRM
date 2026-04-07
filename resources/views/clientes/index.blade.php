@extends('layouts.app')

@section('title', 'Clientes - CRM')
@section('page-title', 'Gestión de Clientes')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h3><i class="bi bi-people"></i> Gestión de Clientes</h3>
        <p class="text-muted">Gestión y alta de nuevos clientes</p>
    </div>

    @php
        $puedeVer = $permisos['ver'] ?? false;
        $puedeCrear = $permisos['crear'] ?? false;
        $puedeEditar = $permisos['editar'] ?? false;
        $puedeEliminar = $permisos['eliminar'] ?? false;
    @endphp

    <!-- Search and Actions - visible si tiene ver O crear -->
    @if($puedeVer || $puedeCrear)
    <div class="row mb-4">
        <div class="col-md-8">
            @if($puedeVer)
            <div class="search-box" style="position: relative; width: 100%;">
                <i class="bi bi-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); z-index: 10; color: #6c757d;"></i>
                <input type="text" class="form-control" id="buscarClienteGlobal" 
                       placeholder="Buscar por ID, nombre, apellidos, correo o teléfono..." 
                       style="padding-left: 45px; height: 50px; font-size: 1rem; border-radius: 8px; border: 1px solid #ced4da; width: 100%;"
                       autocomplete="off">
            </div>
            @endif
        </div>
        <div class="col-md-4 text-end">
            @if($puedeCrear)
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoCliente" style="height: 50px; padding: 0 25px; font-size: 1rem;">
                <i class="bi bi-plus-circle"></i> Nuevo Cliente
            </button>
            @endif
        </div>
    </div>
    @endif

    <!-- Contenido principal -->
    @if($puedeVer)
    <div class="card">
        <div class="card-body p-0" id="clientes-table-container">
            @include('clientes.partials.tabla', ['clientes' => $clientes, 'permisos' => ['editar' => $puedeEditar, 'eliminar' => $puedeEliminar]])
        </div>
    </div>
    @elseif($puedeCrear)
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-people" style="font-size: 3rem; color: #ccc;"></i>
            <p class="text-muted mt-3">No tienes permiso para ver el listado de clientes, pero puedes crear nuevos.</p>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoCliente">
                <i class="bi bi-plus-circle"></i> Crear nuevo cliente
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
@include('clientes.partials.modal-nuevo-cliente')
@include('clientes.partials.modal-editar-cliente')
@endsection

@push('scripts')
<script>
// ============================================
// VARIABLES GLOBALES
// ============================================
let clienteActualId = null;
let timeoutBusqueda;

// ============================================
// BUSCADOR GLOBAL (consulta al servidor)
// ============================================
document.getElementById('buscarClienteGlobal')?.addEventListener('input', function() {
    clearTimeout(timeoutBusqueda);
    const termino = this.value.trim();
    
    timeoutBusqueda = setTimeout(() => {
        if (termino.length === 0) {
            location.reload();
            return;
        }
        
        document.getElementById('clientes-table-container').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Buscando...</span>
                </div>
                <p class="mt-2 text-muted">Buscando clientes...</p>
            </div>
        `;
        
        fetch(`/clientes/buscar?q=${encodeURIComponent(termino)}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = `
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Contacto</th>
                                    <th>Dirección</th>
                                    <th>Patologías</th>
                                    <th>Status</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                if (data.data.length === 0) {
                    html += `
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-people" style="font-size: 3rem; color: #ccc;"></i>
                                <p class="text-muted mt-3">No se encontraron clientes con "${termino}"</p>
                                <button class="btn btn-sm btn-primary" onclick="location.reload()">
                                    <i class="bi bi-arrow-left"></i> Volver al listado
                                </button>
                            </td>
                        </tr>
                    `;
                } else {
                    const puedeEditar = {{ $permisos['editar'] ? 'true' : 'false' }};
                    const puedeEliminar = {{ $permisos['eliminar'] ? 'true' : 'false' }};
                    
                    data.data.forEach(cliente => {
                    let statusClass = '';
                    switch(cliente.status) {
                        case 'CLIENTE': statusClass = 'bg-success'; break;
                        case 'PROSPECTO': statusClass = 'bg-warning'; break;
                        case 'BLOQUEADO': statusClass = 'bg-danger'; break;
                        default: statusClass = 'bg-secondary';
                    }
                    
                    let patologiasHtml = '<span class="text-muted small">-</span>';
                    if (cliente.patologias_asociadas && cliente.patologias_asociadas.length > 0) {
                        patologiasHtml = cliente.patologias_asociadas.slice(0, 2).map(p => 
                            `<span class="badge bg-info">${p.patologia}</span>`
                        ).join(' ');
                        if (cliente.patologias_asociadas.length > 2) {
                            patologiasHtml += ` <span class="badge bg-secondary">+${cliente.patologias_asociadas.length - 2}</span>`;
                        }
                    }
                    
                    // CONTACTO: orden prioridad: telefono1, telefono2, email1
                    let contactoHtml = '';
                    if (cliente.telefono1) {
                        contactoHtml += `<i class="bi bi-telephone text-muted"></i> ${cliente.telefono1}<br>`;
                    }
                    if (cliente.telefono2) {
                        contactoHtml += `<i class="bi bi-telephone text-muted"></i> ${cliente.telefono2} (sec)<br>`;
                    }
                    if (cliente.email1) {
                        contactoHtml += `<i class="bi bi-envelope text-muted"></i> ${cliente.email1}`;
                    }
                    if (!contactoHtml) {
                        contactoHtml = '<span class="text-muted">Sin contacto</span>';
                    }
                    
                    // NOMBRE: con título debajo (en small)
                    let nombreHtml = `<strong>${cliente.titulo ? cliente.titulo + ' ' : ''}${cliente.Nombre} ${cliente.apPaterno} ${cliente.apMaterno || ''}</strong>`;
                    if (cliente.titulo) {
                        nombreHtml += `<br><small class="text-muted">${cliente.titulo}</small>`;
                    }
                    
                    html += `
                        <tr id="cliente-row-${cliente.id_Cliente}">
                            <td><span class="badge bg-secondary">${cliente.id_Cliente}</span></td>
                            <td>${nombreHtml}</td>
                            <td><div class="small">${contactoHtml}</div></td>
                            <td><small>${cliente.Domicilio || 'No especificado'}</small></td>
                            <td>${patologiasHtml}</td>
                            <td><span class="badge ${statusClass}">${cliente.status}</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="/clientes/${cliente.id_Cliente}" 
                                    class="btn btn-sm btn-outline-info btn-action" title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    ${puedeEditar ? `
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-action" 
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditarCliente"
                                            data-cliente-id="${cliente.id_Cliente}"
                                            title="Editar cliente">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    ` : ''}
                                    ${puedeEliminar ? `
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-action" 
                                            onclick="confirmarEliminar('cliente', ${cliente.id_Cliente}, '${cliente.titulo ? cliente.titulo + ' ' : ''}${cliente.Nombre} ${cliente.apPaterno}')" 
                                            title="Eliminar cliente">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    ` : ''}
                                </div>
                            </td>
                        </tr>
                    `;
                });
                }
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
                
                document.getElementById('clientes-table-container').innerHTML = html;
            }
        })
        .catch(error => {
            console.error('Error en búsqueda:', error);
            document.getElementById('clientes-table-container').innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">Error al buscar clientes</p>
                    <button class="btn btn-sm btn-primary" onclick="location.reload()">
                        <i class="bi bi-arrow-left"></i> Volver al listado
                    </button>
                </div>
            `;
        });
    }, 300);
});

// ============================================
// FUNCIÓN PARA EDITAR CLIENTE
// ============================================
window.editarCliente = function(id) {
    window.clienteActualId = id;  // ← Guardar en variable global
    
    fetch(`/clientes/${id}/edit`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('edit_id_Cliente').value = data.data.id_Cliente;
            document.getElementById('edit_Nombre').value = data.data.Nombre;
            document.getElementById('edit_apPaterno').value = data.data.apPaterno;
            document.getElementById('edit_apMaterno').value = data.data.apMaterno || '';
            document.getElementById('edit_titulo').value = data.data.titulo || '';
            document.getElementById('edit_email1').value = data.data.email1;
            document.getElementById('edit_telefono1').value = data.data.telefono1 || '';
            document.getElementById('edit_telefono2').value = data.data.telefono2 || '';
            document.getElementById('edit_Domicilio').value = data.data.Domicilio || '';
            document.getElementById('edit_Sexo').value = data.data.Sexo || '';
            document.getElementById('edit_FechaNac').value = data.data.FechaNac || '';
            document.getElementById('edit_status').value = data.data.status || 'PROSPECTO';
            document.getElementById('edit_pais_id').value = data.data.pais_id || '';
            document.getElementById('edit_estado_id').value = data.data.estado_id || '';
            document.getElementById('edit_municipio_id').value = data.data.municipio_id || '';
            document.getElementById('edit_localidad_id').value = data.data.localidad_id || '';
            
            // También guardar las patologías si vienen
            if (data.data.enfermedades && window.cargarPatologiasCliente) {
                window.cargarPatologiasCliente(data.data.enfermedades);
            }
            
            const modal = new bootstrap.Modal(document.getElementById('modalEditarCliente'));
            modal.show();
        }
    })
    .catch(error => console.error('Error al editar:', error));
};

// ============================================
// FUNCIÓN PARA ELIMINAR CLIENTE
// ============================================
window.ejecutarEliminarCliente = function(id, nombre) {
    fetch(`/clientes/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const termino = document.getElementById('buscarClienteGlobal').value.trim();
            if (termino.length > 0) {
                document.getElementById('buscarClienteGlobal').dispatchEvent(new Event('input'));
            } else {
                location.reload();
            }
            
            if (window.mostrarToast) {
                window.mostrarToast(`Cliente "${nombre}" eliminado`, 'success');
            }
        }
    })
    .catch(error => console.error('Error al eliminar:', error));
};

// ============================================
// FUNCIÓN PARA GUARDAR EDICIÓN DE CLIENTE
// ============================================
window.guardarEdicionCliente = function() {
    const id = document.getElementById('edit_id_Cliente')?.value;
    
    const formData = {
        Nombre: document.getElementById('edit_Nombre')?.value || '',
        apPaterno: document.getElementById('edit_apPaterno')?.value || '',
        apMaterno: document.getElementById('edit_apMaterno')?.value || '',
        titulo: document.getElementById('edit_titulo')?.value || '',
        email1: document.getElementById('edit_email1')?.value || '',
        telefono1: document.getElementById('edit_telefono1')?.value || '',
        telefono2: document.getElementById('edit_telefono2')?.value || '',
        Domicilio: document.getElementById('edit_Domicilio')?.value || '',
        Sexo: document.getElementById('edit_Sexo')?.value || '',
        FechaNac: document.getElementById('edit_FechaNac')?.value || '',
        status: document.getElementById('edit_status')?.value || 'PROSPECTO',
        pais_id: document.getElementById('edit_pais_id')?.value || '',
        estado_id: document.getElementById('edit_estado_id')?.value || '',
        municipio_id: document.getElementById('edit_municipio_id')?.value || '',
        localidad_id: document.getElementById('edit_localidad_id')?.value || '',
        enfermedades: [],
        _token: '{{ csrf_token() }}',
        _method: 'PUT'
    };
    
    if (!formData.Nombre || !formData.apPaterno) {
        if (window.mostrarToast) {
            window.mostrarToast('Completa los campos requeridos (Nombre y Apellido Paterno)', 'warning');
        }
        return;
    }

    if (formData.email1 && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email1)) {
        if (window.mostrarToast) {
            window.mostrarToast('Correo electrónico no válido', 'warning');
        }
        return;
    }
    
    fetch(`/clientes/${id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarCliente'));
            modal.hide();
            location.reload();
            return;
        }
        
        if (data.errors) {
            let mensajes = Object.values(data.errors).flat().join('\n');
            if (window.mostrarToast) window.mostrarToast(mensajes, 'danger');
            return;
        }
        
        if (window.mostrarToast) window.mostrarToast('Error al actualizar cliente', 'danger');
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
};

// ============================================
// FUNCIÓN PARA BLOQUEAR/DESBLOQUEAR CLIENTE
// ============================================
window.toggleClienteBlock = function(id, nombre, accion) {
    const textoConfirmacion = accion === 'bloquear' 
        ? `¿Bloquear al cliente "${nombre}"? Un cliente bloqueado no podrá realizar acciones.`
        : `¿Desbloquear al cliente "${nombre}"?`;
    
    if (!confirm(textoConfirmacion)) return;
    
    fetch(`/clientes/${id}/toggle-block`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar toast de éxito
            if (window.mostrarToast) {
                window.mostrarToast(data.message, 'success');
            }
            
            // Actualizar la tabla según el contexto actual
            const termino = document.getElementById('buscarClienteGlobal')?.value.trim() || '';
            if (termino.length > 0) {
                // Si hay búsqueda activa, refrescar la búsqueda
                document.getElementById('buscarClienteGlobal').dispatchEvent(new Event('input'));
            } else {
                // Si no hay búsqueda, recargar la página
                location.reload();
            }
        } else {
            if (window.mostrarToast) {
                window.mostrarToast(data.message || 'Error al cambiar estado', 'danger');
            } else {
                alert(data.message || 'Error al cambiar estado');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) {
            window.mostrarToast('Error de conexión', 'danger');
        } else {
            alert('Error de conexión');
        }
    });
};
</script>
@endpush