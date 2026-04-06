<!-- Modal Confirmar Eliminación -->
<div class="modal fade" id="modalConfirmarEliminar" tabindex="-1" aria-labelledby="modalConfirmarEliminarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill"></i> Confirmar Eliminación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-trash3-fill text-danger" style="font-size: 3rem;"></i>
                <h4 class="mt-3">¿Estás seguro?</h4>
                <p class="text-muted" id="detalleConfirmacion"></p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">
                    <i class="bi bi-trash"></i> Sí, eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Variables globales para el modal
var tipoEliminar = null;
var idEliminar = null;
var nombreEliminar = null;

// Función para abrir el modal
window.confirmarEliminar = function(tipo, id, nombre) {
    tipoEliminar = tipo;
    idEliminar = id;
    nombreEliminar = nombre;
    
    let mensaje = '';
    
    if (tipo === 'cliente') {
        mensaje = `¿Eliminar el cliente "${nombre}"? Esta acción no se puede deshacer.`;
    } else if (tipo === 'enfermedad') {
        mensaje = `¿Eliminar la enfermedad "${nombre}"? Esta acción no se puede deshacer.`;
    } else if (tipo === 'preferencia') {
        mensaje = `¿Eliminar esta preferencia? Esta acción no se puede deshacer.`;
    } else if (tipo === 'usuario') {
        mensaje = `¿Eliminar el usuario "${nombre}"? Esta acción no se puede deshacer.`;
    } else if (tipo === 'cotizacion') {
        mensaje = `¿Eliminar la cotización "${nombre}"? Esta acción no se puede deshacer.`;
    } else {
        mensaje = `¿Eliminar "${nombre}"? Esta acción no se puede deshacer.`;
    }
    
    document.getElementById('detalleConfirmacion').textContent = mensaje;
    
    new bootstrap.Modal(document.getElementById('modalConfirmarEliminar')).show();
};

window.ejecutarEliminarEnfermedad = function(id, nombre) {
    fetch(`/enfermedades/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const fila = document.getElementById(`patologia-row-${id}`);
            if (fila) fila.remove();
            if (window.mostrarToast) window.mostrarToast(`Patología "${nombre}" eliminada`, 'success');
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al eliminar', 'danger');
        }
    })
    .catch(error => {
        console.error('Error al eliminar:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
};

window.ejecutarEliminarInteres = function(id, nombre) {
    fetch(`/intereses/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const fila = document.getElementById(`interes-row-${id}`);
            if (fila) fila.remove();
            if (window.mostrarToast) window.mostrarToast(`Interés "${nombre}" eliminado`, 'success');
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al eliminar', 'danger');
        }
    })
    .catch(error => {
        console.error('Error al eliminar:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
};

// Función para eliminar usuario
window.ejecutarEliminarUsuario = function(id, nombre) {
    fetch(`/seguridad/usuarios/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const fila = document.getElementById(`usuario-row-${id}`);
            if (fila) fila.remove();
            if (window.mostrarToast) window.mostrarToast(`Usuario "${nombre}" eliminado correctamente`, 'success');
        } else {
            const errorMsg = data.message || 'Error al eliminar el usuario';
            if (window.mostrarToast) window.mostrarToast(errorMsg, 'danger');
        }
    })
    .catch(error => {
        console.error('Error al eliminar:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión al eliminar el usuario', 'danger');
    });
};

// Función para eliminar cotización
window.ejecutarEliminarCotizacion = function(id, folio) {
    fetch(`/ventas/cotizaciones/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const fila = document.getElementById(`cotizacion-row-${id}`);
            if (fila) fila.remove();
            if (window.mostrarToast) window.mostrarToast(`Cotización "${folio}" eliminada correctamente`, 'success');
        } else {
            const errorMsg = data.message || 'Error al eliminar la cotización';
            if (window.mostrarToast) window.mostrarToast(errorMsg, 'danger');
        }
    })
    .catch(error => {
        console.error('Error al eliminar:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión al eliminar la cotización', 'danger');
    });
};

// Función para eliminar cliente
window.ejecutarEliminarCliente = function(id, nombre) {
    fetch(`/clientes/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const fila = document.getElementById(`cliente-row-${id}`);
            if (fila) fila.remove();
            if (window.mostrarToast) window.mostrarToast(`Cliente "${nombre}" eliminado correctamente`, 'success');
        } else {
            const errorMsg = data.message || 'Error al eliminar el cliente';
            if (window.mostrarToast) window.mostrarToast(errorMsg, 'danger');
        }
    })
    .catch(error => {
        console.error('Error al eliminar:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión al eliminar el cliente', 'danger');
    });
};

// Botón confirmar del modal
document.getElementById('btnConfirmarEliminar')?.addEventListener('click', function() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarEliminar'));
    modal.hide();
    
    if (tipoEliminar === 'cliente' && window.ejecutarEliminarCliente) {
        window.ejecutarEliminarCliente(idEliminar, nombreEliminar);
    } else if (tipoEliminar === 'enfermedad' && window.ejecutarEliminarEnfermedad) {
        window.ejecutarEliminarEnfermedad(idEliminar, nombreEliminar);
    } else if (tipoEliminar === 'preferencia' && window.ejecutarEliminarPreferencia) {
        window.ejecutarEliminarPreferencia(idEliminar, nombreEliminar);
    } else if (tipoEliminar === 'usuario' && window.ejecutarEliminarUsuario) {
        window.ejecutarEliminarUsuario(idEliminar, nombreEliminar);
    } else if (tipoEliminar === 'cotizacion' && window.ejecutarEliminarCotizacion) {
        window.ejecutarEliminarCotizacion(idEliminar, nombreEliminar);
    } else {
        if (window.mostrarToast) {
            window.mostrarToast('No se ha implementado la función para eliminar este tipo de elemento', 'warning');
        }
    }
    
    // Limpiar variables
    tipoEliminar = null;
    idEliminar = null;
    nombreEliminar = null;
});
</script>