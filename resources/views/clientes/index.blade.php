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
            <div id="resultadosBusquedaClientes" class="mt-2" style="display: none; position: absolute; z-index: 1000; background: white; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 66%; max-height: 400px; overflow-y: auto;">
                <div class="list-group" id="listaResultadosClientes"></div>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoCliente" style="height: 50px; padding: 0 25px; font-size: 1rem;">
                <i class="bi bi-plus-circle"></i> Nuevo Cliente
            </button>
        </div>
    </div>

    <!-- Tabla de Clientes (paginada) -->
    <div class="card">
        <div class="card-body p-0" id="clientes-table-container">
            @include('clientes.partials.tabla', ['clientes' => $clientes])
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ============================================
// VARIABLES GLOBALES
// ============================================
let clienteActualId = null;
let timeoutIdBusqueda;

// ============================================
// BUSCADOR GLOBAL (busca en TODA la BD)
// ============================================
document.getElementById('buscarClienteGlobal')?.addEventListener('input', function() {
    clearTimeout(timeoutIdBusqueda);
    const termino = this.value.trim();
    
    const resultadosDiv = document.getElementById('resultadosBusquedaClientes');
    
    if (termino.length < 2) {
        resultadosDiv.style.display = 'none';
        return;
    }
    
    timeoutIdBusqueda = setTimeout(() => {
        fetch(`/clientes/buscar?q=${encodeURIComponent(termino)}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Datos recibidos:', data); // Para ver qué viene
            console.log('Primer cliente status:', data.data[0]?.status); // Ver status
            const listaResultados = document.getElementById('listaResultadosClientes');
            
            if (data.data.length === 0) {
                listaResultados.innerHTML = '<div class="list-group-item text-muted">No se encontraron clientes</div>';
            } else {
                listaResultados.innerHTML = data.data.map(cliente => {
                    // DETERMINAR COLOR SEGÚN STATUS
                    let statusLimpio = cliente.status ? cliente.status.trim() : '';
                    let badgeClass = '';
                    switch(statusLimpio) {
                        case 'CLIENTE':
                            badgeClass = 'bg-success';
                            break;
                        case 'PROSPECTO':
                            badgeClass = 'bg-warning text-dark';
                            break;
                        case 'BLOQUEADO':
                            badgeClass = 'bg-danger';
                            break;
                        default:
                            badgeClass = 'bg-secondary';
                    }
                    
                    return `
                        <a href="/clientes/${cliente.id_Cliente}" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${cliente.titulo ? cliente.titulo + ' ' : ''}${cliente.Nombre} ${cliente.apPaterno} ${cliente.apMaterno || ''}</strong>
                                    <br>
                                    <small class="text-muted">
                                        <i class="bi bi-envelope"></i> ${cliente.email1} 
                                        ${cliente.telefono1 ? `<i class="bi bi-telephone ms-2"></i> ${cliente.telefono1}` : ''}
                                    </small>
                                </div>
                                <span class="badge ${badgeClass}">${cliente.status}</span>
                            </div>
                        </a>
                    `;
                }).join('');
    }
    
    resultadosDiv.style.display = 'block';
})
        .catch(error => console.error('Error en búsqueda:', error));
    }, 300);
});

// Cerrar resultados al hacer clic fuera
document.addEventListener('click', function(event) {
    const resultados = document.getElementById('resultadosBusquedaClientes');
    const buscador = document.getElementById('buscarClienteGlobal');
    
    if (resultados && !resultados.contains(event.target) && event.target !== buscador) {
        resultados.style.display = 'none';
    }
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
            // Llenar el formulario con los datos actualizados de tu BD
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
            
            // Seleccionar patologías
            const select = document.getElementById('edit_enfermedades');
            if (select && data.data.enfermedades) {
                Array.from(select.options).forEach(option => {
                    option.selected = data.data.enfermedades.includes(parseInt(option.value));
                });
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
            if (data.html) {
                document.getElementById('clientes-table-container').innerHTML = data.html;
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
    // Obtener valores del formulario
    let fechaNac = document.getElementById('FechaNac')?.value || null;
    
    // Función auxiliar para convertir vacío a null
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
        // Campos numéricos: si están vacíos, enviar null
        pais_id: toNull(document.getElementById('pais_id')?.value),
        estado_id: toNull(document.getElementById('estado_id')?.value),
        municipio_id: toNull(document.getElementById('municipio_id')?.value),
        localidad_id: toNull(document.getElementById('localidad_id')?.value),
        enfermedades: [],
        _token: '{{ csrf_token() }}'
    };

    // Validaciones básicas
    if (!formData.Nombre || !formData.apPaterno) {
        if (window.mostrarToast) {
            window.mostrarToast('Completa los campos requeridos (Nombre y Apellido Paterno)', 'warning');
        }
        return;
    }

    // Validar email SOLO si tiene valor
    if (formData.email1 && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email1)) {
        if (window.mostrarToast) {
            window.mostrarToast('Correo electrónico no válido', 'warning');
        }
        return;
    }

    console.log('Enviando datos:', formData);

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
            
            if (data.html) {
                document.getElementById('clientes-table-container').innerHTML = data.html;
            }
            
            if (window.mostrarToast) {
                window.mostrarToast('Cliente creado correctamente', 'success');
            }
            
            document.getElementById('formNuevoCliente').reset();
            setTimeout(() => location.reload(), 1500);
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
        enfermedades: [], // Aquí irían las patologías seleccionadas
        _token: '{{ csrf_token() }}',
        _method: 'PUT'
    };
    
    // Validaciones básicas - SOLO nombre y apellido paterno
    if (!formData.Nombre || !formData.apPaterno) {
        if (window.mostrarToast) {
            window.mostrarToast('Completa los campos requeridos (Nombre y Apellido Paterno)', 'warning');
        }
        return;
    }

    // Validar email SOLO si tiene valor
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
            
            if (data.html) {
                document.getElementById('clientes-table-container').innerHTML = data.html;
            }
            
            if (window.mostrarToast) {
                window.mostrarToast('Cliente actualizado correctamente', 'success');
            }
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

// ============================================
// INICIALIZACIÓN
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Precargar patologías si es necesario
    fetch('/patologias/todas')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Catálogo de patologías cargado:', data.data.length);
            }
        })
        .catch(error => console.error('Error al cargar patologías:', error));
});
</script>
@endpush