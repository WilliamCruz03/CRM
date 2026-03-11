<!-- Modal Editar Preferencia -->
<div class="modal fade" id="modalEditarPreferencia" tabindex="-1" aria-labelledby="modalEditarPreferenciaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarPreferenciaLabel">
                    <i class="bi bi-pencil-square"></i> Editar preferencia
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarPreferencia">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_preferencia_id" name="preferencia_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <input type="text" class="form-control" id="edit_categoria" name="categoria" 
                               placeholder="Ej: Contacto, Notificaciones, Entregas">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Detalle de preferencia <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit_descripcion" name="descripcion" 
                                  rows="4" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="guardarEdicionPreferencia()">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>