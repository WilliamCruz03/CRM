<!-- Modal Cancelar Pedido -->
<div class="modal fade" id="modalCancelarPedido" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-x-circle"></i> Cancelar Pedido
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de cancelar el pedido <strong id="cancelar_pedido_folio"></strong>?</p>
                <p class="text-muted small">Esta acción no se puede deshacer.</p>
                
                <div class="mb-3">
                    <label class="form-label">Motivo de cancelación <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="cancelar_pedido_motivo" rows="3" 
                              placeholder="Describa el motivo de la cancelación..." required></textarea>
                    <div class="invalid-feedback">El motivo es obligatorio</div>
                </div>
                
                <input type="hidden" id="cancelar_pedido_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarCancelar">
                    <i class="bi bi-check-circle"></i> Sí, cancelar pedido
                </button>
            </div>
        </div>
    </div>
</div>