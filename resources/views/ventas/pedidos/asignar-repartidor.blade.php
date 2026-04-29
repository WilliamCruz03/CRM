@extends('layouts.app')

@section('title', 'Asignar Repartidor')
@section('page-title', 'Asignar Repartidor - Pedido ' . $pedido->folio_pedido)

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-person-badge"></i> Asignar Repartidor
                <a href="{{ route('ventas.pedidos.index') }}" class="btn btn-light btn-sm float-end">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </h5>
        </div>
        <div class="card-body">
            <!-- Información del pedido -->
            <div class="alert alert-info">
                <strong>Pedido:</strong> {{ $pedido->folio_pedido }} |
                <strong>Cliente:</strong> {{ $pedido->cotizacion->nombre_cliente ?? 'N/A' }} |
                <strong>Total:</strong> ${{ number_format($pedido->importe_total ?? 0, 2) }}
            </div>

            <!-- Repartidores -->
            <h6 class="mt-3"><i class="bi bi-truck"></i> Repartidores disponibles</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="tablaRepartidores">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%">Seleccionar</th>
                            <th>Sucursal</th>
                            <th>Repartidor</th>
                            <th>Horario</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="repartidoresBody">
                        <tr><td colspan="5" class="text-center">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Entregas en curso -->
            <h6 class="mt-4"><i class="bi bi-clock-history"></i> Entregas en curso</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Repartidor</th>
                            <th>Cliente</th>
                            <th>Dirección</th>
                            <th>Hora salida</th>
                            <th>Tiempo fuera</th>
                        </tr>
                    </thead>
                    <tbody id="entregasBody">
                        <tr><td colspan="5" class="text-center">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-end">
                <button type="button" class="btn btn-primary" id="btnAsignar" disabled>
                    <i class="bi bi-person-badge"></i> Asignar repartidor
                </button>
                <a href="{{ route('ventas.pedidos.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </div>
    </div>
</div>

<script>
let repartidorSeleccionadoId = null;
let intervaloActualizacion = null;

// Cargar datos iniciales y cada 60 segundos
function cargarDatos() {
    fetch('{{ route("ventas.pedidos.repartidores.status", $pedido->id_pedido) }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                actualizarTablaRepartidores(data.repartidores);
                actualizarTablaEntregas(data.entregas_curso);
            }
        })
        .catch(error => console.error('Error:', error));
}

function actualizarTablaRepartidores(repartidores) {
    const tbody = document.getElementById('repartidoresBody');
    if (!repartidores || repartidores.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">No hay repartidores disponibles</td></tr>';
        return;
    }
    
    // Verificar si el usuario puede asignar (CRM = sucursal 0)
    const puedeAsignar = {{ $sucursalAsignada == 0 ? 'true' : 'false' }};
    
    let html = '';
    repartidores.forEach(rep => {
        let statusColor = '';
        let statusIcon = '';
        
        switch(rep.status) {
            case 'Disponible':
                statusColor = 'success';
                statusIcon = 'bi-check-circle';
                break;
            case 'En recorrido':
                statusColor = 'warning';
                statusIcon = 'bi-truck';
                break;
            case 'Fuera de horario':
                statusColor = 'secondary';
                statusIcon = 'bi-clock';
                break;
            default:
                statusColor = 'danger';
                statusIcon = 'bi-exclamation-circle';
        }
        
        // Determinar si se puede seleccionar (solo si está disponible)
        const puedeSeleccionar = puedeAsignar && rep.status === 'Disponible';
        
        html += `
            <tr>
                <td class="text-center">
                    ${puedeSeleccionar ? 
                        `<input type="radio" name="repartidor" value="${rep.id}" data-nombre="${rep.nombre}">` : 
                        '<span class="text-muted">---</span>'}
                </td>
                <td>Sucursal ${rep.sucursal}</td>
                <td><strong>${rep.nombre}</strong></td>
                <td>${rep.horario_entrada || '--'} - ${rep.horario_salida || '--'}</td>
                <td><span class="badge bg-${statusColor}"><i class="bi ${statusIcon}"></i> ${rep.status}</span></td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    
    // Agregar event listeners a los radios (solo si no están deshabilitados)
    document.querySelectorAll('input[name="repartidor"]').forEach(radio => {
        radio.addEventListener('change', function() {
            repartidorSeleccionadoId = this.value;
            const btnAsignar = document.getElementById('btnAsignar');
            if (btnAsignar) btnAsignar.disabled = false;
        });
    });
}

function actualizarTablaEntregas(entregas) {
    const tbody = document.getElementById('entregasBody');
    if (!entregas || entregas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">No hay entregas en curso</td></tr>';
        return;
    }
    
    let html = '';
    entregas.forEach(entrega => {
        // Calcular tiempo fuera en el cliente
        let tiempoFuera = '00:00:00';
        if (entrega.hora_salida) {
            const horaInicio = new Date(`2000-01-01T${entrega.hora_salida}`);
            const ahora = new Date();
            const inicio = new Date(ahora);
            inicio.setHours(horaInicio.getHours(), horaInicio.getMinutes(), horaInicio.getSeconds());
            
            let diffMs = ahora - inicio;
            if (diffMs < 0) diffMs = 0;
            
            const diffHoras = Math.floor(diffMs / 3600000);
            const diffMinutos = Math.floor((diffMs % 3600000) / 60000);
            const diffSegundos = Math.floor((diffMs % 60000) / 1000);
            tiempoFuera = `${String(diffHoras).padStart(2, '0')}:${String(diffMinutos).padStart(2, '0')}:${String(diffSegundos).padStart(2, '0')}`;
        }
        
        html += `
            <tr>
                <td><strong>${entrega.repartidor_nombre} ${entrega.repartidor_apaterno || ''}</strong></td>
                <td>${entrega.nombrecliente || 'N/A'}</td>
                <td>${entrega.Domicilio || 'N/A'}</td>
                <td>${entrega.hora_salida ? entrega.hora_salida.substring(0,5) : 'N/A'}</td>
                <td><span class="badge bg-info tiempo-fuera" data-inicio="${entrega.hora_salida}">${tiempoFuera}</span></td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
    
    // Iniciar actualización en tiempo real cada segundo
    actualizarTiemposFuera();
}

function actualizarTiemposFuera() {
    const elementos = document.querySelectorAll('.tiempo-fuera');
    elementos.forEach(el => {
        const horaInicioStr = el.getAttribute('data-inicio');
        if (horaInicioStr) {
            const horaInicio = new Date(`2000-01-01T${horaInicioStr}`);
            const ahora = new Date();
            const inicio = new Date(ahora);
            inicio.setHours(horaInicio.getHours(), horaInicio.getMinutes(), horaInicio.getSeconds());
            
            let diffMs = ahora - inicio;
            if (diffMs < 0) diffMs = 0;
            
            const diffHoras = Math.floor(diffMs / 3600000);
            const diffMinutos = Math.floor((diffMs % 3600000) / 60000);
            const diffSegundos = Math.floor((diffMs % 60000) / 1000);
            el.textContent = `${String(diffHoras).padStart(2, '0')}:${String(diffMinutos).padStart(2, '0')}:${String(diffSegundos).padStart(2, '0')}`;
        }
    });
}

// Actualizar tiempos fuera cada segundo
setInterval(actualizarTiemposFuera, 1000);

// Asignar repartidor
const btnAsignar = document.getElementById('btnAsignar');
if (btnAsignar) {
    btnAsignar.addEventListener('click', function() {
        if (!repartidorSeleccionadoId) return;
        
        fetch('{{ route("ventas.pedidos.asignarRepartidor", $pedido->id_pedido) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ id_repartidor: parseInt(repartidorSeleccionadoId) })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '{{ route("ventas.pedidos.index") }}';
            } else {
                alert(data.message || 'Error al asignar repartidor');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión');
        });
    });
}

// Iniciar polling cada 60 segundos
cargarDatos();
intervaloActualizacion = setInterval(cargarDatos, 60000);

// Limpiar intervalo al salir
window.addEventListener('beforeunload', function() {
    if (intervaloActualizacion) clearInterval(intervaloActualizacion);
});
</script>
@endsection