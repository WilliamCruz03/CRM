@extends('layouts.app')

@section('title', 'Clientes - CRM')
@section('page-title', 'Gestión de Clientes')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <h3><i class="bi bi-people"></i> Gestión de Clientes</h3>
        <p class="text-muted">Gestión y alta de nuevos clientes</p>
    </div>

    <!-- Search and Actions -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="search-box" style="position: relative; width: 100%;">
                <i class="bi bi-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); z-index: 10; color: #6c757d;"></i>
                <input type="text" class="form-control" id="buscarClienteGlobal" 
                       placeholder="Buscar por ID, nombre, apellidos, correo o teléfono..." 
                       style="padding-left: 45px; height: 50px; font-size: 1rem; border-radius: 8px; border: 1px solid #ced4da; width: 100%;"
                       autocomplete="off">
            </div>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoCliente" style="height: 50px; padding: 0 25px; font-size: 1rem;">
                <i class="bi bi-plus-circle"></i> Nuevo Cliente
            </button>
        </div>
    </div>

    <!-- Tabla de Clientes -->
    <div class="card">
        <div class="card-body p-0" id="clientes-table-container">
            @include('clientes.partials.tabla', ['clientes' => $clientes])
        </div>
    </div>
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
    
    // Buscar con 1 o más caracteres
    timeoutBusqueda = setTimeout(() => {
        // Si el término está vacío, recargar la página normal
        if (termino.length === 0) {
            location.reload();
            return;
        }
        
        // Mostrar indicador de carga (opcional)
        document.getElementById('clientes-table-container').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Buscando...</span>
                </div>
                <p class="mt-2 text-muted">Buscando clientes...</p>
            </div>
        `;
        
        // Consultar al servidor
        fetch(`/clientes/buscar?q=${encodeURIComponent(termino)}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Construir la tabla con los resultados
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
                    data.data.forEach(cliente => {
                        let statusClass = '';
                        switch(cliente.status) {
                            case 'CLIENTE': statusClass = 'bg-success'; break;
                            case 'PROSPECTO': statusClass = 'bg-warning'; break;
                            case 'BLOQUEADO': statusClass = 'bg-danger'; break;
                            default: statusClass = 'bg-secondary';
                        }
                        
                        // Procesar patologías
                        let patologiasHtml = '<span class="text-muted small">-</span>';
                        if (cliente.patologias_asociadas && cliente.patologias_asociadas.length > 0) {
                            patologiasHtml = cliente.patologias_asociadas.slice(0, 2).map(p => 
                                `<span class="badge bg-info">${p.patologia}</span>`
                            ).join(' ');
                            if (cliente.patologias_asociadas.length > 2) {
                                patologiasHtml += ` <span class="badge bg-secondary">+${cliente.patologias_asociadas.length - 2}</span>`;
                            }
                        }
                        
                        html += `
                            <tr id="cliente-row-${cliente.id_Cliente}">
                                <td><span class="badge bg-secondary">${cliente.id_Cliente}</span></td>
                                <td>
                                    <strong>${cliente.titulo ? cliente.titulo + ' ' : ''}${cliente.Nombre} ${cliente.apPaterno} ${cliente.apMaterno || ''}</strong>
                                </td>
                                <td>
                                    <div class="small">
                                        <i class="bi bi-envelope text-muted"></i> ${cliente.email1}<br>
                                        ${cliente.telefono1 ? `<i class="bi bi-telephone text-muted"></i> ${cliente.telefono1}` : ''}
                                    </div>
                                </td>
                                <td>
                                    <small>${cliente.Domicilio || 'No especificado'}</small>
                                </td>
                                <td>
                                    ${patologiasHtml}
                                </td>
                                <td>
                                    <span class="badge ${statusClass}">${cliente.status}</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/clientes/${cliente.id_Cliente}" 
                                        class="btn btn-sm btn-outline-info btn-action" title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-action" 
                                                onclick="confirmarEliminar('cliente', ${cliente.id_Cliente}, '${cliente.titulo ? cliente.titulo + ' ' : ''}${cliente.Nombre} ${cliente.apPaterno}')" 
                                                title="Eliminar cliente">
                                            <i class="bi bi-trash"></i>
                                        </button>
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
    }, 300); // Debounce de 300ms
});

// ============================================
// FUNCIÓN PARA EDITAR CLIENTE
// ============================================
window.editarCliente = function(id) {
    clienteActualId = id;
    
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
            // Si la búsqueda está activa, recargar resultados
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
// FUNCIÓN PARA GUARDAR NUEVO CLIENTE
// ============================================
window.guardarNuevoCliente = function() {
    let fechaNac = document.getElementById('FechaNac')?.value || null;
    
    const toNull = (valor) => valor === '' ? null : valor;
    
    const formData = {
        Nombre: document.getElementById('Nombre')?.value || '',
        apPaterno: document.getElementById('apPaterno')?.value || '',
        apMaterno: document.getElementById('apMaterno')?.value || null,
        titulo: document.getElementById('titulo')?.value || null,
        email1: document.getElementById('email1')?.value || null,
        telefono1: document.getElementById('telefono1')?.value || null,
        telefono2: document.getElementById('telefono2')?.value || null,
        Domicilio: document.getElementById('Domicilio')?.value || null,
        Sexo: document.getElementById('Sexo')?.value || null,
        FechaNac: fechaNac,
        status: document.getElementById('status')?.value || 'PROSPECTO',
        pais_id: toNull(document.getElementById('pais_id')?.value),
        estado_id: toNull(document.getElementById('estado_id')?.value),
        municipio_id: toNull(document.getElementById('municipio_id')?.value),
        localidad_id: toNull(document.getElementById('localidad_id')?.value),
        enfermedades: [],
        _token: '{{ csrf_token() }}'
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

    fetch('{{ route("clientes.store") }}', {
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
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevoCliente'));
            modal.hide();
            
            // Recargar la página para ver el nuevo cliente
            location.reload();
        } else if (data.errors) {
            let mensajes = Object.values(data.errors).flat().join('\n');
            if (window.mostrarToast) {
                window.mostrarToast(mensajes, 'danger');
            }
        }
    })
    .catch(error => {
        console.error('Error completo:', error);
        if (window.mostrarToast) {
            window.mostrarToast('Error: ' + (error.message || 'Error de conexión'), 'danger');
        }
    });
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
            
            // Recargar para ver cambios
            location.reload();
        } else if (data.errors) {
            let mensajes = Object.values(data.errors).flat().join('\n');
            if (window.mostrarToast) {
                window.mostrarToast(mensajes, 'danger');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) {
            window.mostrarToast('Error de conexión', 'danger');
        }
    });
};
</script>
@endpush