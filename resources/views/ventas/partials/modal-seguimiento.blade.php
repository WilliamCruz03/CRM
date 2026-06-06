{{-- resources/views/ventas/cotizaciones/partials/modal-seguimiento.blade.php --}}

<div class="modal fade" id="modalSeguimiento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-chat-dots"></i> <span id="modalSeguimientoTitulo">Seguimiento</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formSeguimiento">
                <div class="modal-body">
                    <!-- Datos ocultos -->
                    <input type="hidden" name="tipo" id="seg_tipo">
                    <input type="hidden" name="folio_referencia" id="seg_folio_referencia">
                    <input type="hidden" name="id_cliente_maestro" id="seg_id_cliente_maestro">

                    <!-- Información del documento -->
                    <div class="card mb-3 bg-light">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">Folio</small>
                                    <p class="fw-bold mb-0" id="seg_folio">-</p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Estado</small>
                                    <p class="mb-0" id="seg_estado">-</p>
                                </div>
                                <div class="col-md-6 mt-2">
                                    <small class="text-muted">Fecha de creación</small>
                                    <p class="mb-0" id="seg_fecha_creacion">-</p>
                                </div>
                                <div class="col-md-6 mt-2">
                                    <small class="text-muted">Días transcurridos</small>
                                    <p class="mb-0" id="seg_dias">-</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Datos del cliente -->
                    <div class="card mb-3 bg-light">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">Cliente</small>
                                    <p class="fw-bold mb-0" id="seg_cliente_nombre">-</p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Teléfono</small>
                                    <p class="mb-0" id="seg_cliente_telefono">-</p>
                                </div>
                            </div>
                            <!-- Preferencia de contacto -->
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <small class="text-muted">Preferencia de contacto</small>
                                    <p class="mb-0" id="seg_preferencia_contacto">
                                        <span class="badge bg-secondary">No especificada</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resto del modal igual... -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-clock-history"></i> Hora de inicio
                                </label>
                                <input type="text" class="form-control" id="seg_hora_inicio" readonly>
                                <small class="text-muted">Capturada automáticamente</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-clock"></i> Hora de fin <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" class="form-control" id="seg_hora_fin" name="hora_fin" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-chat-text"></i> Mensaje al cliente
                        </label>
                        <div class="input-group">
                            <textarea class="form-control" id="seg_mensaje_cliente" name="mensaje_cliente" rows="3"
                                placeholder="Registra aquí el mensaje enviado al cliente..."></textarea>
                            <button type="button" class="btn btn-success" id="btnEnviarWhatsApp" style="display: none;" onclick="enviarMensajeWhatsApp()">
                                <i class="bi bi-whatsapp"></i> Enviar
                            </button>
                        </div>
                        <small class="text-muted">Escribe tu mensaje y usa el botón WhatsApp para enviarlo directamente</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-exclamation-triangle"></i> Motivo de no finalización
                        </label>
                        <textarea class="form-control" id="seg_motivo_no_finalizacion" name="motivo_no_finalizacion" rows="2"
                            placeholder="Si no se concretó, indica el motivo..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-chat-dots"></i> Conversación copiada
                        </label>
                        <textarea class="form-control" id="seg_conversacion" name="conversacion" rows="4"
                            placeholder="Pega aquí la conversación..."></textarea>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-emoji-frown"></i> Queja / Inconformidad
                                </label>
                                <textarea class="form-control" id="seg_queja" name="queja" rows="3"
                                    placeholder="Registra quejas del cliente..."></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-lightbulb"></i> Sugerencia
                                </label>
                                <textarea class="form-control" id="seg_sugerencia" name="sugerencia" rows="3"
                                    placeholder="Registra sugerencias del cliente..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" onclick="guardarSeguimiento()">
                        <i class="bi bi-save"></i> Guardar seguimiento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>