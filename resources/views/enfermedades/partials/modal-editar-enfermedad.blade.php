<!-- Modal Editar Enfermedad -->
<div class="modal fade" id="modalEditarEnfermedad" tabindex="-1" aria-labelledby="modalEditarEnfermedadLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarEnfermedadLabel">
                    <i class="bi bi-pencil-square"></i> Editar Enfermedad
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarEnfermedad">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_enfermedad_id" name="enfermedad_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre de la enfermedad</label>
                        <input type="text" class="form-control" id="edit_enfermedad_nombre" name="nombre" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <div class="input-group">
                            <select class="form-select" id="edit_enfermedad_categoria" name="categoria_id" required>
                                <option value="">Seleccionar categoría</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-outline-primary" type="button" onclick="toggleEditNuevaCategoria()">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Campo oculto para nueva categoría en edición -->
                    <div id="editNuevaCategoriaContainer" style="display: none;" class="mb-3 p-3 border rounded bg-light">
                        <label class="form-label">Nombre de la nueva categoría</label>
                        <input type="text" class="form-control mb-2" id="edit_nueva_categoria_nombre" placeholder="Ej: Respiratoria">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="toggleEditNuevaCategoria()">Cancelar</button>
                            <button type="button" class="btn btn-sm btn-success" onclick="guardarEditNuevaCategoria()">Guardar y seleccionar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="guardarEdicionEnfermedad()">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>