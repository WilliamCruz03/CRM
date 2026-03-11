<!-- Modal Nueva Preferencia -->
<div class="modal fade" id="modalNuevaPreferencia" tabindex="-1" aria-labelledby="modalNuevaPreferenciaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNuevaPreferenciaLabel">
                    <i class="bi bi-plus-circle"></i> Registrar nueva preferencia
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formNuevaPreferencia">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Cliente <span class="text-danger">*</span></label>
                        <select class="form-select" id="preferencia_cliente_id" name="cliente_id" required>
                            <option value="">Buscar y seleccionar cliente</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}">{{ $cliente->nombre_completo }} ({{ $cliente->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <input type="text" class="form-control" id="nueva_categoria" name="categoria" 
                               placeholder="Ej: Contacto, Notificaciones, Entregas">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Detalle de preferencia <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="nueva_descripcion" name="descripcion" 
                                  rows="4" placeholder="Describe la preferencia del cliente..." required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="guardarNuevaPreferencia()">Guardar</button>
            </div>
        </div>
    </div>
</div>