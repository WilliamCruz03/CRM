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
                        <th>Severidad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cliente->enfermedades as $index => $enfermedad)
                    <tr id="enfermedad-row-{{ $enfermedad->id }}">
                        <!-- ... contenido ... -->
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
                                <button class="btn btn-sm btn-outline-danger btn-action"
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
// Variables globales
let todasEnfermedades = [];
let enfermedadesCliente = []; // Array de objetos {id, nombre, categoria}

// ============================================
// FUNCIONES PARA CARGAR DATOS
// ============================================

// Cargar todas las enfermedades disponibles
function cargarCatalogoEnfermedades() {
    return fetch('/enfermedades/todas', {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            todasEnfermedades = data.data;
            console.log('✅ Catálogo de enfermedades cargado:', todasEnfermedades.length);
        }
        return data;
    })
    .catch(error => {
        console.error('❌ Error al cargar catálogo:', error);
    });
}

// Cargar datos del cliente en el modal
async function cargarDatosCliente(clienteId) {
    try {
        const response = await fetch(`/clientes/${clienteId}/edit`, {
            headers: {
                'Accept': 'application/json'
            }
        });
        const data = await response.json();
        
        if (data.success) {
            // Datos básicos
            document.getElementById('edit_cliente_id').value = data.data.id;
            document.getElementById('edit_nombre').value = data.data.nombre;
            document.getElementById('edit_apellidos').value = data.data.apellidos;
            document.getElementById('edit_email').value = data.data.email;
            document.getElementById('edit_telefono').value = data.data.telefono || '';
            document.getElementById('edit_calle').value = data.data.calle || '';
            document.getElementById('edit_colonia').value = data.data.colonia || '';
            document.getElementById('edit_ciudad').value = data.data.ciudad || '';
            document.getElementById('edit_estado').value = data.data.estado;
            
            // Cargar catálogo si no está cargado
            if (todasEnfermedades.length === 0) {
                await cargarCatalogoEnfermedades();
            }
            
            // Cargar enfermedades del cliente
            enfermedadesCliente = [];
            if (data.data.enfermedades && todasEnfermedades.length > 0) {
                data.data.enfermedades.forEach(enfId => {
                    const enfermedad = todasEnfermedades.find(e => e.id === enfId);
                    if (enfermedad) {
                        enfermedadesCliente.push({
                            id: enfermedad.id,
                            nombre: enfermedad.nombre,
                            categoria: enfermedad.categoria?.nombre || 'Sin categoría'
                        });
                    }
                });
            }
            
            console.log('✅ Enfermedades del cliente:', enfermedadesCliente);
            renderizarTablaEnfermedades();
        }
    } catch (error) {
        console.error('❌ Error al cargar datos:', error);
    }
}

// ============================================
// FUNCIONES PARA LA TABLA DE ENFERMEDADES
// ============================================

// Renderizar la tabla de enfermedades del cliente
function renderizarTablaEnfermedades() {
    const tbody = document.getElementById('enfermedadesClienteBody');
    if (!tbody) return;
    
    if (enfermedadesCliente.length === 0) {
        tbody.innerHTML = `
            <tr id="sin-enfermedades-row">
                <td colspan="4" class="text-center py-4">
                    <i class="bi bi-heart-pulse text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2 mb-0">Este cliente no tiene enfermedades registradas</p>
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    enfermedadesCliente.forEach((enf, index) => {
        html += `
            <tr id="enfermedad-row-${enf.id}">
                <td class="fw-bold">${index + 1}</td>
                <td>${enf.nombre}</td>
                <td><span class="badge bg-info">${enf.categoria}</span></td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-action" 
                            onclick="eliminarEnfermedadDeTabla(${enf.id})"
                            title="Eliminar enfermedad">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

// Eliminar enfermedad de la tabla
function eliminarEnfermedadDeTabla(enfermedadId) {
    if (confirm('¿Estás seguro de eliminar esta enfermedad del cliente?')) {
        enfermedadesCliente = enfermedadesCliente.filter(e => e.id !== enfermedadId);
        renderizarTablaEnfermedades();
    }
}

// ============================================
// FUNCIONES DE BÚSQUEDA Y AGREGADO
// ============================================

// Buscar enfermedades
function buscarEnfermedades(termino) {
    if (!termino || termino.length < 2) {
        document.getElementById('resultadosBusqueda').style.display = 'none';
        return;
    }
    
    const resultados = todasEnfermedades.filter(enf => 
        enf.nombre.toLowerCase().includes(termino.toLowerCase()) ||
        (enf.categoria?.nombre || '').toLowerCase().includes(termino.toLowerCase())
    );
    
    const resultadosDiv = document.getElementById('resultadosBusqueda');
    const listaResultados = document.getElementById('listaResultados');
    
    if (resultados.length === 0) {
        listaResultados.innerHTML = `
            <div class="list-group-item text-muted">
                <i class="bi bi-exclamation-circle"></i> No se encontraron resultados
            </div>
        `;
    } else {
        listaResultados.innerHTML = resultados.map(enf => {
            const yaExiste = enfermedadesCliente.some(e => e.id === enf.id);
            return `
                <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${enf.nombre}</strong>
                        <br><small class="text-muted">${enf.categoria?.nombre || 'Sin categoría'}</small>
                    </div>
                    <button class="btn btn-sm ${yaExiste ? 'btn-secondary' : 'btn-success'}" 
                            onclick="agregarEnfermedadACliente(${enf.id})"
                            ${yaExiste ? 'disabled' : ''}>
                        ${yaExiste ? '<i class="bi bi-check"></i> Agregada' : '<i class="bi bi-plus"></i> Agregar'}
                    </button>
                </div>
            `;
        }).join('');
    }
    
    resultadosDiv.style.display = 'block';
}

// Agregar enfermedad al cliente
function agregarEnfermedadACliente(enfermedadId) {
    const enfermedad = todasEnfermedades.find(e => e.id === enfermedadId);
    if (!enfermedad) return;
    
    if (enfermedadesCliente.some(e => e.id === enfermedadId)) {
        alert('⚠️ Esta enfermedad ya está agregada al cliente');
        return;
    }
    
    enfermedadesCliente.push({
        id: enfermedad.id,
        nombre: enfermedad.nombre,
        categoria: enfermedad.categoria?.nombre || 'Sin categoría'
    });
    
    renderizarTablaEnfermedades();
    
    // Limpiar búsqueda
    document.getElementById('buscarEnfermedadModal').value = '';
    document.getElementById('resultadosBusqueda').style.display = 'none';
    
    console.log('✅ Enfermedad agregada:', enfermedad.nombre);
}

// ============================================
// FUNCIÓN PRINCIPAL PARA GUARDAR
// ============================================

window.guardarEdicionCliente = function() {
    const id = document.getElementById('edit_cliente_id')?.value;
    
    if (!id) {
        alert('Error: ID de cliente no encontrado');
        return;
    }
    
    const enfermedadesIds = enfermedadesCliente.map(e => e.id);
    
    const formData = {
        nombre: document.getElementById('edit_nombre')?.value || '',
        apellidos: document.getElementById('edit_apellidos')?.value || '',
        email: document.getElementById('edit_email')?.value || '',
        telefono: document.getElementById('edit_telefono')?.value || '',
        calle: document.getElementById('edit_calle')?.value || '',
        colonia: document.getElementById('edit_colonia')?.value || '',
        ciudad: document.getElementById('edit_ciudad')?.value || '',
        estado: document.getElementById('edit_estado')?.value || 'Activo',
        enfermedades: enfermedadesIds,
        _token: '{{ csrf_token() }}',
        _method: 'PUT'
    };
    
    // Validar campos requeridos
    if (!formData.nombre || !formData.apellidos || !formData.email) {
        alert('Por favor completa todos los campos requeridos');
        return;
    }
    
    console.log('📤 Enviando datos:', formData);
    
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
        console.log('📥 Respuesta:', data);
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarCliente'));
            if (modal) modal.hide();
            alert('✅ Cliente actualizado correctamente');
            location.reload();
        } else {
            alert('❌ Error: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('❌ Error completo:', error);
        alert('Error al actualizar: ' + (error.message || 'Error de conexión'));
    });
};

// ============================================
// FUNCIONES EXISTENTES PARA ELIMINAR
// ============================================

function eliminarEnfermedadCliente(clienteId, enfermedadId) {
    if (confirm('¿Estás seguro de eliminar esta enfermedad del cliente?')) {
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
                alert('✅ Enfermedad eliminada correctamente');
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

function eliminarPreferencia(id) {
    if (confirm('¿Estás seguro de eliminar esta preferencia?')) {
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
        })
        .catch(error => console.error('Error:', error));
    }
}

// ============================================
// EVENT LISTENERS
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    const modalEditar = document.getElementById('modalEditarCliente');
    
    if (modalEditar) {
        modalEditar.addEventListener('show.bs.modal', async function(event) {
            const button = event.relatedTarget;
            const clienteId = button.getAttribute('data-cliente-id');
            
            // Limpiar búsqueda
            document.getElementById('buscarEnfermedadModal').value = '';
            document.getElementById('resultadosBusqueda').style.display = 'none';
            
            // Cargar datos
            await cargarDatosCliente(clienteId);
        });
    }
    
    // Buscador en tiempo real
    const buscador = document.getElementById('buscarEnfermedadModal');
    if (buscador) {
        buscador.addEventListener('input', function() {
            buscarEnfermedades(this.value);
        });
    }
    
    // Botón agregar enfermedad
    document.getElementById('btnAgregarEnfermedad')?.addEventListener('click', function() {
        const termino = document.getElementById('buscarEnfermedadModal').value;
        if (termino.length >= 2) {
            buscarEnfermedades(termino);
        } else {
            alert('Ingresa al menos 2 caracteres para buscar');
        }
    });
    
    // Cerrar resultados al hacer clic fuera
    document.addEventListener('click', function(event) {
        const resultados = document.getElementById('resultadosBusqueda');
        const buscador = document.getElementById('buscarEnfermedadModal');
        const btnAgregar = document.getElementById('btnAgregarEnfermedad');
        
        if (resultados && 
            !resultados.contains(event.target) && 
            event.target !== buscador && 
            event.target !== btnAgregar) {
            resultados.style.display = 'none';
        }
    });
});
</script>
@endpush