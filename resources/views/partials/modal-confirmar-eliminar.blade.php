<!-- Modal Confirmar Eliminación -->
<div class="modal fade" id="modalConfirmarEliminar" tabindex="-1" aria-labelledby="modalConfirmarEliminarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white" id="modalConfirmarHeader">
                <h5 class="modal-title" id="modalConfirmarTitulo"><i class="bi bi-exclamation-triangle-fill"></i> Confirmar Eliminación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-trash3-fill text-danger" id="modalConfirmarIcono" style="font-size: 3rem;"></i>
                <h4 class="mt-3" id="modalConfirmarPregunta">¿Estás seguro?</h4>
                <p class="text-muted" id="detalleConfirmacion"></p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">
                    <i class="bi bi-trash" id="btnConfirmarIcono"></i> <span id="btnConfirmarTexto">Sí, eliminar</span>
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
var callbackEliminar = null;

// Función para abrir el modal (ahora con estilo dinámico)
window.confirmarEliminar = function(tipo, id, nombre, callback = null) {
    tipoEliminar = tipo;
    idEliminar = id;
    nombreEliminar = nombre;
    callbackEliminar = callback;
    
    // Configuración según el tipo
    const config = {
        'cliente': { color: 'danger', icono: 'bi-trash3-fill', titulo: 'Confirmar Eliminación', pregunta: '¿Estás seguro?', btnTexto: 'Sí, eliminar', btnIcono: 'bi-trash' },
        'enfermedad': { color: 'danger', icono: 'bi-trash3-fill', titulo: 'Confirmar Eliminación', pregunta: '¿Estás seguro?', btnTexto: 'Sí, eliminar', btnIcono: 'bi-trash' },
        'preferencia': { color: 'danger', icono: 'bi-trash3-fill', titulo: 'Confirmar Eliminación', pregunta: '¿Estás seguro?', btnTexto: 'Sí, eliminar', btnIcono: 'bi-trash' },
        'usuario': { color: 'danger', icono: 'bi-trash3-fill', titulo: 'Confirmar Eliminación', pregunta: '¿Estás seguro?', btnTexto: 'Sí, eliminar', btnIcono: 'bi-trash' },
        'cotizacion': { color: 'danger', icono: 'bi-trash3-fill', titulo: 'Confirmar Eliminación', pregunta: '¿Estás seguro?', btnTexto: 'Sí, eliminar', btnIcono: 'bi-trash' },
        'producto_pedido': { color: 'danger', icono: 'bi-trash3-fill', titulo: 'Confirmar Eliminación', pregunta: '¿Estás seguro?', btnTexto: 'Sí, eliminar', btnIcono: 'bi-trash' },
        'cancelar_pedido': { color: 'warning', icono: 'bi-x-circle-fill', titulo: 'Confirmar Cancelación', pregunta: '¿Cancelar pedido?', btnTexto: 'Sí, cancelar', btnIcono: 'bi-x-circle' },
        'marcar_listo': { color: 'success', icono: 'bi-check-circle-fill', titulo: 'Confirmar acción', pregunta: '¿Marcar como lista?', btnTexto: 'Sí, marcar', btnIcono: 'bi-check-lg' }
    };
    
    const c = config[tipo] || { color: 'danger', icono: 'bi-question-circle-fill', titulo: 'Confirmar acción', pregunta: '¿Estás seguro?', btnTexto: 'Aceptar', btnIcono: 'bi-check-lg' };
    
    // Definir mensaje
    const mensajesPorTipo = {
        'cliente': `¿Eliminar el cliente "${nombre}"? Esta acción no se puede deshacer.`,
        'enfermedad': `¿Eliminar la enfermedad "${nombre}"? Esta acción no se puede deshacer.`,
        'preferencia': `¿Eliminar esta preferencia? Esta acción no se puede deshacer.`,
        'usuario': `¿Eliminar el usuario "${nombre}"? Esta acción no se puede deshacer.`,
        'cotizacion': `¿Eliminar la cotización "${nombre}"? Esta acción no se puede deshacer.`,
        'producto_pedido': `¿Eliminar "${nombre}" del pedido? Esta acción no se puede deshacer.`,
        'cancelar_pedido': `¿Cancelar el pedido "${nombre}"? Esta acción no se puede deshacer.`,
        'marcar_listo': `¿Marcar "${nombre}" como lista? Una vez marcada, no se puede deshacer.`
    };
    
    const mensaje = mensajesPorTipo[tipo] || `¿${nombre}?`;
    
    // Aplicar estilos dinámicos
    const header = document.getElementById('modalConfirmarHeader');
    const titulo = document.getElementById('modalConfirmarTitulo');
    const icono = document.getElementById('modalConfirmarIcono');
    const pregunta = document.getElementById('modalConfirmarPregunta');
    const btn = document.getElementById('btnConfirmarEliminar');
    const btnIcono = document.getElementById('btnConfirmarIcono');
    const btnTexto = document.getElementById('btnConfirmarTexto');
    
    header.className = `modal-header bg-${c.color} text-white`;
    titulo.innerHTML = `<i class="bi ${c.icono}"></i> ${c.titulo}`;
    icono.className = `bi ${c.icono} text-${c.color}`;
    pregunta.textContent = c.pregunta;
    btn.className = `btn btn-${c.color}`;
    btnIcono.className = `bi ${c.btnIcono}`;
    btnTexto.textContent = c.btnTexto;
    
    document.getElementById('detalleConfirmacion').textContent = mensaje;
    
    new bootstrap.Modal(document.getElementById('modalConfirmarEliminar')).show();
};

// Función para eliminar producto del pedido (sin mensaje adicional)
window.ejecutarEliminarProductoPedido = function(index, nombre) {
    if (typeof window.eliminarProductoPorIndice === 'function') {
        window.eliminarProductoPorIndice(index);
        if (window.mostrarToast) window.mostrarToast(`"${nombre}" eliminado del pedido`, 'success');
    }
};

// Función para cancelar pedido
window.ejecutarCancelarPedido = function(id, folio) {
    fetch(`/ventas/pedidos/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (window.mostrarToast) window.mostrarToast(`Pedido "${folio}" cancelado correctamente`, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            const errorMsg = data.message || 'Error al cancelar el pedido';
            if (window.mostrarToast) window.mostrarToast(errorMsg, 'danger');
        }
    })
    .catch(error => {
        console.error('Error al cancelar:', error);
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
    
    // Si hay callback personalizado, usarlo
    if (callbackEliminar && typeof callbackEliminar === 'function') {
        callbackEliminar();
    }
    // Si no, usar la lógica original por tipo
    else if (tipoEliminar === 'cliente' && window.ejecutarEliminarCliente) {
        window.ejecutarEliminarCliente(idEliminar, nombreEliminar);
    } else if (tipoEliminar === 'enfermedad' && window.ejecutarEliminarEnfermedad) {
        window.ejecutarEliminarEnfermedad(idEliminar, nombreEliminar);
    } else if (tipoEliminar === 'preferencia' && window.ejecutarEliminarPreferencia) {
        window.ejecutarEliminarPreferencia(idEliminar, nombreEliminar);
    } else if (tipoEliminar === 'usuario' && window.ejecutarEliminarUsuario) {
        window.ejecutarEliminarUsuario(idEliminar, nombreEliminar);
    } else if (tipoEliminar === 'cotizacion' && window.ejecutarEliminarCotizacion) {
        window.ejecutarEliminarCotizacion(idEliminar, nombreEliminar);
    } else if (tipoEliminar === 'producto_pedido' && window.ejecutarEliminarProductoPedido) {
        window.ejecutarEliminarProductoPedido(idEliminar, nombreEliminar);
    } else if (tipoEliminar === 'cancelar_pedido' && window.ejecutarCancelarPedido) {
        window.ejecutarCancelarPedido(idEliminar, nombreEliminar);
    } else {
        if (window.mostrarToast) {
            window.mostrarToast('No se ha implementado la función para eliminar este tipo de elemento', 'warning');
        }
    }
    
    // Limpiar variables
    tipoEliminar = null;
    idEliminar = null;
    nombreEliminar = null;
    callbackEliminar = null;
});
</script>