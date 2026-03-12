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
        <div class="col-md-6">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" id="buscarCliente" placeholder="Buscar por nombre, correo o teléfono...">
            </div>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoCliente">
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
@endsection

@push('scripts')
<script>
let clienteActualId = null;

// Función para buscar clientes (filtrado local)
document.getElementById('buscarCliente')?.addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#clientes-table-container tbody tr');
    
    rows.forEach(row => {
        if (row.id === 'no-results-row') return;
        
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Función para editar cliente
function editarCliente(id) {
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
            document.getElementById('edit_cliente_id').value = data.data.id;
            document.getElementById('edit_nombre').value = data.data.nombre;
            document.getElementById('edit_apellidos').value = data.data.apellidos;
            document.getElementById('edit_email').value = data.data.email;
            document.getElementById('edit_telefono').value = data.data.telefono || '';
            document.getElementById('edit_calle').value = data.data.calle || '';
            document.getElementById('edit_colonia').value = data.data.colonia || '';
            document.getElementById('edit_ciudad').value = data.data.ciudad || '';
            document.getElementById('edit_estado').value = data.data.estado;
            
            // Seleccionar enfermedades
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
    .catch(error => console.error('Error:', error));
}

// Función para guardar nuevo cliente
function guardarNuevoCliente() {
    const formData = {
        nombre: document.getElementById('nombre')?.value || '',
        apellidos: document.getElementById('apellidos')?.value || '',
        email: document.getElementById('email')?.value || '',
        telefono: document.getElementById('telefono')?.value || '',
        calle: document.getElementById('calle')?.value || '',
        colonia: document.getElementById('colonia')?.value || '',
        ciudad: document.getElementById('ciudad')?.value || '',
        _token: '{{ csrf_token() }}'
    };
    
    // Validar campos requeridos
    if (!formData.nombre || !formData.apellidos || !formData.email) {
        alert('Por favor completa todos los campos requeridos');
        return;
    }
    
    fetch('{{ route("clientes.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevoCliente'));
            modal.hide();
            
            // Actualizar la tabla con los nuevos datos
            document.getElementById('clientes-table-container').innerHTML = data.html;
            
            // Actualizar la paginación si existe
            if (data.pagination) {
                // Actualizar paginación
            }
            
            // Limpiar formulario
            document.getElementById('formNuevoCliente').reset();
        } else {
            alert('Error: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al guardar');
    });
}

// Función para guardar edición de cliente
function guardarEdicionCliente() {
    const id = document.getElementById('edit_cliente_id')?.value;
    
    // Obtener enfermedades seleccionadas
    const selectEnfermedades = document.getElementById('edit_enfermedades');
    const enfermedadesSeleccionadas = [];
    
    if (selectEnfermedades) {
        Array.from(selectEnfermedades.selectedOptions).forEach(option => {
            enfermedadesSeleccionadas.push(parseInt(option.value));
        });
    }
    
    console.log('Enfermedades seleccionadas:', enfermedadesSeleccionadas); // Para debug
    
    const formData = {
        nombre: document.getElementById('edit_nombre')?.value || '',
        apellidos: document.getElementById('edit_apellidos')?.value || '',
        email: document.getElementById('edit_email')?.value || '',
        telefono: document.getElementById('edit_telefono')?.value || '',
        calle: document.getElementById('edit_calle')?.value || '',
        colonia: document.getElementById('edit_colonia')?.value || '',
        ciudad: document.getElementById('edit_ciudad')?.value || '',
        estado: document.getElementById('edit_estado')?.value || 'Activo',
        enfermedades: enfermedadesSeleccionadas, // Esto es crucial
        _token: '{{ csrf_token() }}',
        _method: 'PUT'
    };
    
    // Validar campos requeridos
    if (!formData.nombre || !formData.apellidos || !formData.email) {
        alert('Por favor completa todos los campos requeridos');
        return;
    }
    
    console.log('Enviando datos:', formData); // Para debug
    
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
        console.log('Respuesta:', data); // Para debug
        if (data.success) {
            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarCliente'));
            if (modal) {
                modal.hide();
            }
            
            // Actualizar la tabla si estamos en index
            const tableContainer = document.getElementById('clientes-table-container');
            if (tableContainer && data.html) {
                tableContainer.innerHTML = data.html;
            }
            
            // Si estamos en la vista show, recargar para ver cambios
            if (window.location.pathname.includes('/clientes/') && !window.location.pathname.includes('/edit')) {
                location.reload();
            } else {
                // Mostrar mensaje de éxito
                alert('Cliente actualizado correctamente');
            }
        } else {
            alert('Error: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error completo:', error);
        alert('Error al actualizar: ' + (error.message || 'Error de conexión'));
    });
}

// Función para eliminar cliente
function ejecutarEliminarCliente(id) {
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
            document.getElementById('clientes-table-container').innerHTML = data.html;
        }
    })
    .catch(error => console.error('Error:', error));
}

// Cargar enfermedades en los modales
document.addEventListener('DOMContentLoaded', function() {
    // Precargar enfermedades para los modales
    fetch('/enfermedades')
        .then(response => response.text())
        .then(html => {
            // Opcional: precargar datos
        });
});
</script>
@endpush

@push('scripts')
<script>
// Función para ejecutar la eliminación después de confirmar
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
            // Usar la función global mostrarToast
            if (window.mostrarToast) {
                window.mostrarToast(`Cliente "${nombre}" eliminado`, 'success');
            }
        }
    })
    .catch(error => console.error('Error:', error));
};
</script>
@endpush