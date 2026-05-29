<!-- Modal para seleccionar bases de datos -->
<div class="modal fade" id="modalSeleccionBD" tabindex="-1" aria-labelledby="modalSeleccionBDLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalSeleccionBDLabel">
                    <i class="bi bi-database"></i> Seleccionar Bases de Datos
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">
                    <i class="bi bi-info-circle"></i> Seleccione las bases de datos que desea respaldar:
                </p>
                
                <div class="list-group" id="listaBasesDatos">
                    @foreach($databases as $db)
                    <div class="list-group-item">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="{{ $db }}" id="db_{{ $loop->index }}" 
                                   {{ in_array($db, ['fp_central_matriz', 'fp_central_ventas']) ? 'checked' : '' }}>
                            <label class="form-check-label" for="db_{{ $loop->index }}">
                                <i class="bi bi-database-fill text-primary me-2"></i>
                                <strong>{{ $db }}</strong>
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="alert alert-info mt-3 mb-0">
                    <i class="bi bi-shield-check"></i>
                    <small>Los respaldos se generarán en formato .bak y se guardarán en la carpeta de respaldos.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="btnConfirmarRespaldo">
                    <i class="bi bi-check-circle"></i> Generar Respaldos
                </button>
            </div>
        </div>
    </div>
</div>