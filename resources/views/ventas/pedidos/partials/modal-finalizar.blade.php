<div class="modal fade" id="modalFinalizarPedido" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle"></i> Finalizar Pedido
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Marcar como entregado el pedido <strong id="finalizar_pedido_folio"></strong>?</p>
                <p class="text-muted small">
                    <i class="bi bi-info-circle"></i> Esta acción cambiará el estado del pedido a "Finalizado".
                </p>
                <input type="hidden" id="finalizar_pedido_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="confirmarFinalizarPedido()">
                    <i class="bi bi-check-lg"></i> Sí, entregar
                </button>
            </div>
        </div>
    </div>
</div>