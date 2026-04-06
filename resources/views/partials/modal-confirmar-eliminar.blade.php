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
// ============================================
// VARIABLES GLOBALES PARA EL MODAL
// ============================================
var tipoEliminar = null;
var idEliminar = null;
var nombreEliminar = null;

// ============================================
// FUNCIÓN PARA EJECUTAR LA ELIMINACIÓN SEGÚN EL TIPO
// ============================================
function ejecutarEliminacion(tipo, id, nombre) {
    console.log('Ejecutando eliminación:', {tipo, id, nombre});
    
    if (tipo === 'cliente' && window.ejecutarEliminarCliente) {
        window.ejecutarEliminarCliente(id, nombre);
    } else if (tipo === 'enfermedad' && window.ejecutarEliminarEnfermedad) {
        window.ejecutarEliminarEnfermedad(id, nombre);
    } else if (tipo === 'interes' && window.ejecutarEliminarInteres) {
        window.ejecutarEliminarInteres(id, nombre);
    } else if (tipo === 'preferencia' && window.ejecutarEliminarPreferencia) {
        window.ejecutarEliminarPreferencia(id, nombre);
    } else if (tipo === 'usuario' && window.ejecutarEliminarUsuario) {
        window.ejecutarEliminarUsuario(id, nombre);
    } else if (tipo === 'cotizacion' && window.ejecutarEliminarCotizacion) {
        window.ejecutarEliminarCotizacion(id, nombre);
    } else {
        if (window.mostrarToast) {
            window.mostrarToast('No se ha implementado la función para eliminar este tipo de elemento', 'warning');
        } else {
            alert('No se ha implementado la función para eliminar este tipo de elemento');
        }
    }
}

// ============================================
// FUNCIÓN PARA ABRIR EL MODAL (CON FALLBACK)
// ============================================
window.confirmarEliminar = function(tipo, id, nombre) {
    console.log('confirmarEliminar llamado:', {tipo, id, nombre});
    
    tipoEliminar = tipo;
    idEliminar = id;
    nombreEliminar = nombre;
    
    let mensaje = '';
    
    if (tipo === 'cliente') {
        mensaje = `¿Eliminar el cliente "${nombre}"? Esta acción no se puede deshacer.`;
    } else if (tipo === 'enfermedad') {
        mensaje = `¿Eliminar la enfermedad "${nombre}"? Esta acción no se puede deshacer.`;
    } else if (tipo === 'interes') {
        mensaje = `¿Eliminar el interés "${nombre}"? Esta acción no se puede deshacer.`;
    } else if (tipo === 'preferencia') {
        mensaje = `¿Eliminar esta preferencia? Esta acción no se puede deshacer.`;
    } else if (tipo === 'usuario') {
        mensaje = `¿Eliminar el usuario "${nombre}"? Esta acción no se puede deshacer.`;
    } else if (tipo === 'cotizacion') {
        mensaje = `¿Eliminar la cotización "${nombre}"? Esta acción no se puede deshacer.`;
    } else {
        mensaje = `¿Eliminar "${nombre}"? Esta acción no se puede deshacer.`;
    }
    
    // Actualizar el mensaje en el modal
    const detalleElement = document.getElementById('detalleConfirmacion');
    if (detalleElement) {
        detalleElement.textContent = mensaje;
    } else {
        console.error('No se encontró el elemento detalleConfirmacion');
    }
    
    // Intentar mostrar el modal de Bootstrap
    const modalElement = document.getElementById('modalConfirmarEliminar');
    if (!modalElement) {
        console.error('No se encontró el elemento modalConfirmarEliminar');
        // Fallback: usar confirm nativo
        if (confirm(mensaje)) {
            ejecutarEliminacion(tipo, id, nombre);
        }
        return;
    }
    
    try {
        // Asegurarse de que el modal no tenga el backdrop activo previamente
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
        
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: true
        });
        modal.show();
        console.log('Modal mostrado correctamente');
    } catch (error) {
        console.error('Error al mostrar el modal:', error);
        // Fallback: usar confirm nativo
        if (confirm(mensaje)) {
            ejecutarEliminacion(tipo, id, nombre);
        }
    }
};

// ============================================
// FUNCIONES DE ELIMINACIÓN POR TIPO
// ============================================

// Eliminar enfermedad (patología)
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

// Eliminar interés
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

// Eliminar usuario
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

// Eliminar cotización
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

// Eliminar cliente
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

// ============================================
// BOTÓN CONFIRMAR DEL MODAL
// ============================================
document.getElementById('btnConfirmarEliminar')?.addEventListener('click', function() {
    const modalElement = document.getElementById('modalConfirmarEliminar');
    const modal = bootstrap.Modal.getInstance(modalElement);
    if (modal) {
        modal.hide();
    }
    
    // Eliminar el backdrop manualmente si queda
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) {
        backdrop.remove();
    }
    
    // Ejecutar la eliminación según el tipo guardado
    ejecutarEliminacion(tipoEliminar, idEliminar, nombreEliminar);
    
    // Limpiar variables
    tipoEliminar = null;
    idEliminar = null;
    nombreEliminar = null;
});

// Limpiar backdrop cuando el modal se cierre
document.getElementById('modalConfirmarEliminar')?.addEventListener('hidden.bs.modal', function() {
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) {
        backdrop.remove();
    }
});
</script>