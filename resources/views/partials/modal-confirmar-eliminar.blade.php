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
let tipoEliminar = null;
let idEliminar = null;
let nombreEliminar = null;

// Función para abrir el modal
window.confirmarEliminar = function(tipo, id, nombre) {
    tipoEliminar = tipo;
    idEliminar = id;
    nombreEliminar = nombre;
    
    let mensaje = '';
    if (tipo === 'cliente') mensaje = `¿Eliminar el cliente "${nombre}"?`;
    else if (tipo === 'enfermedad') mensaje = `¿Eliminar la enfermedad "${nombre}"?`;
    else if (tipo === 'preferencia') mensaje = `¿Eliminar esta preferencia?`;
    else if (tipo === 'usuario') mensaje = `¿Eliminar el usuario "${nombre}"? Esta acción no se puede deshacer.`;
    
    document.getElementById('detalleConfirmacion').textContent = mensaje;
    new bootstrap.Modal(document.getElementById('modalConfirmarEliminar')).show();
};

// Botón confirmar del modal
document.getElementById('btnConfirmarEliminar')?.addEventListener('click', function() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarEliminar'));
    modal.hide();
    
    // Llamar a la función correcta según el tipo
    if (tipoEliminar === 'cliente' && window.ejecutarEliminarCliente) {
        window.ejecutarEliminarCliente(idEliminar, nombreEliminar);
    } else if (tipoEliminar === 'enfermedad' && window.ejecutarEliminarEnfermedad) {
        window.ejecutarEliminarEnfermedad(idEliminar, nombreEliminar);
    } else if (tipoEliminar === 'preferencia' && window.ejecutarEliminarPreferencia) {
        window.ejecutarEliminarPreferencia(idEliminar, nombreEliminar);
    } else if (tipoEliminar === 'usuario' && window.ejecutarEliminarUsuario) {
        window.ejecutarEliminarUsuario(idEliminar, nombreEliminar);
    }
});
</script>