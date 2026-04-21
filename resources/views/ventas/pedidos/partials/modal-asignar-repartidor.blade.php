<div class="modal fade" id="modalAsignarRepartidor" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-person-badge"></i> Asignar Repartidor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Pedido: <strong id="asignar_repartidor_folio"></strong></p>
                <div class="mb-3">
                    <label class="form-label">Seleccione un repartidor</label>
                    <select class="form-select" id="repartidor_select">
                        <option value="">Cargando...</option>
                    </select>
                </div>
                <input type="hidden" id="asignar_repartidor_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="asignarRepartidor()">
                    <i class="bi bi-check-lg"></i> Asignar
                </button>
            </div>
        </div>
    </div>
</div>