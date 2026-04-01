<!-- Modal Opciones de Edición -->
<div class="modal fade" id="modalOpcionesEdicion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Editar Cotización</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Selecciona una opción para la cotización <strong id="opcion_editar_folio"></strong>:</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-primary" id="btnEditarActual" onclick="editarCotizacionActual(document.getElementById('opcion_editar_id').value)">
                        <i class="bi bi-pencil"></i> Editar cotización actual
                    </button>
                    <button class="btn btn-secondary" id="btnNuevaVersion" onclick="crearNuevaVersion(document.getElementById('opcion_editar_id').value)">
                        <i class="bi bi-files"></i> Crear nueva versión
                    </button>
                </div>
                <input type="hidden" id="opcion_editar_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>