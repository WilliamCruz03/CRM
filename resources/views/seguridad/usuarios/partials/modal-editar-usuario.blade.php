<!-- Modal Editar Usuario -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-labelledby="modalEditarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarUsuarioLabel">
                    <i class="bi bi-pencil-square"></i> Editar Usuario
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarUsuario">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_usuario_id" name="usuario_id">
                    
                    <div class="row">
                        <!-- Columna Izquierda: Datos del Usuario -->
                        <div class="col-md-4">
                            <h6 class="mb-3">Datos del Usuario</h6>
                            
                            <div class="mb-3">
                                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_Nombre" name="Nombre" 
                                       onkeydown="return soloLetras(event)"
                                       oninput="aMayusculas(event)"
                                       required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Apellido Paterno <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_ApPaterno" name="ApPaterno" 
                                       onkeydown="return soloLetras(event)"
                                       oninput="aMayusculas(event)"
                                       required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Apellido Materno</label>
                                <input type="text" class="form-control" id="edit_ApMaterno" name="ApMaterno" 
                                       onkeydown="return soloLetras(event)"
                                       oninput="aMayusculas(event)">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Usuario <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_usuario" name="usuario" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email / Contacto</label>
                                <input type="email" class="form-control" id="edit_contacto" name="contacto">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Teléfono Móvil</label>
                                <input type="text" class="form-control" id="edit_TelefonoMovil" name="TelefonoMovil"
                                       onkeydown="return soloNumeros(event)">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Dirección</label>
                                <input type="text" class="form-control" id="edit_Direccion" name="Direccion">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Localidad</label>
                                    <input type="text" class="form-control" id="edit_Localidad" name="Localidad">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Municipio</label>
                                    <input type="text" class="form-control" id="edit_Municipio" name="Municipio">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">CURP</label>
                                    <input type="text" class="form-control" id="edit_curp" name="curp" maxlength="18"
                                           oninput="aMayusculas(event)">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fecha Nacimiento</label>
                                    <input type="date" class="form-control" id="edit_fecha_nacimiento" name="fecha_nacimiento">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Estado</label>
                                    <select class="form-select" id="edit_Activo" name="Activo">
                                        <option value="1">Activo</option>
                                        <option value="0">Inactivo</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Sucursal Asignada</label>
                                    <select class="form-select" id="edit_sucursal_asignada" name="sucursal_asignada">
                                        <option value="">Seleccionar</option>
                                        <option value="1">Sucursal Mercado</option>
                                        <option value="2">Sucursal Jardin</option>
                                        <option value="3">Sucursal Zacatipan</option>
                                        <option value="4">Sucursal Boulevard</option>
                                        <option value="5">Sucursal smg</option>
                                        <option value="6">Sucursal sfo</option>
                                        <option value="7">Sucursal hug</option>
                                        <option value="8">Sucursal huc</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Nueva Contraseña</label>
                                <input type="password" class="form-control" id="edit_passw" name="passw" placeholder="Dejar vacío para no cambiar">
                                <small class="text-muted">********</small>
                            </div>
                        </div>
                        
                        <!-- Columna Derecha: Permisos con Collapse -->
                        <div class="col-md-8">
                            <h6 class="mb-3">Permisos por Módulo</h6>
                            
                            <!-- ============================================ -->
                            <!-- CLIENTES -->
                            <!-- ============================================ -->
                            <div class="card mb-3">
                                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center" 
                                     data-bs-toggle="collapse" data-bs-target="#collapseClientes" style="cursor: pointer;">
                                    <span><strong>📁 Clientes</strong></span>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="form-check form-switch" onclick="event.stopPropagation()">
                                            <input class="form-check-input" type="checkbox" id="permiso_clientes_mostrar">
                                            <label class="form-check-label">Mostrar/Ocultar</label>
                                        </div>
                                        <i class="bi bi-chevron-down collapse-icon"></i>
                                    </div>
                                </div>
                                <div class="collapse show" id="collapseClientes">
                                    <div class="card-body py-2">
                                        <!-- Directorio Clientes -->
                                        <div class="border-bottom pb-2 mb-2">
                                            <strong class="text-primary">Directorio Clientes</strong>
                                            <div class="row mt-2">
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_clientes_ver">
                                                        <label class="form-check-label">Ver</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_clientes_altas">
                                                        <label class="form-check-label">Crear</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_clientes_edicion">
                                                        <label class="form-check-label">Editar</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_clientes_eliminar">
                                                        <label class="form-check-label">Eliminar</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Enfermedades -->
                                        <div class="border-bottom pb-2 mb-2">
                                            <strong class="text-primary">Enfermedades</strong>
                                            <div class="row mt-2">
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_enfermedades_ver">
                                                        <label class="form-check-label">Ver</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_enfermedades_altas">
                                                        <label class="form-check-label">Crear</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_enfermedades_edicion">
                                                        <label class="form-check-label">Editar</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_enfermedades_eliminar">
                                                        <label class="form-check-label">Eliminar</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Intereses/Preferencias -->
                                        <div>
                                            <strong class="text-primary">Intereses / Preferencias</strong>
                                            <div class="row mt-2">
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_intereses_ver">
                                                        <label class="form-check-label">Ver</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_intereses_altas">
                                                        <label class="form-check-label">Crear</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_intereses_edicion">
                                                        <label class="form-check-label">Editar</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_intereses_eliminar">
                                                        <label class="form-check-label">Eliminar</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ============================================ -->
                            <!-- VENTAS -->
                            <!-- ============================================ -->
                            <div class="card mb-3">
                                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center"
                                     data-bs-toggle="collapse" data-bs-target="#collapseVentas" style="cursor: pointer;">
                                    <span><strong>📈 Ventas</strong></span>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="form-check form-switch" onclick="event.stopPropagation()">
                                            <input class="form-check-input" type="checkbox" id="permiso_ventas_mostrar">
                                            <label class="form-check-label">Mostrar/Ocultar</label>
                                        </div>
                                        <i class="bi bi-chevron-down collapse-icon"></i>
                                    </div>
                                </div>
                                <div class="collapse show" id="collapseVentas">
                                    <div class="card-body py-2">
                                        <!-- Cotizaciones (CRUD completo) -->
                                        <div class="border-bottom pb-2 mb-2">
                                            <strong class="text-primary">Cotizaciones</strong>
                                            <div class="row mt-2">
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_cotizaciones_ver">
                                                        <label class="form-check-label">Ver</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_cotizaciones_altas">
                                                        <label class="form-check-label">Crear</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_cotizaciones_edicion">
                                                        <label class="form-check-label">Editar</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_cotizaciones_eliminar">
                                                        <label class="form-check-label">Eliminar</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Pedidos Anticipo (CRUD completo) -->
                                        <div class="border-bottom pb-2 mb-2">
                                            <strong class="text-primary">Pedidos Anticipo</strong>
                                            <div class="row mt-2">
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_pedidos_anticipo_ver">
                                                        <label class="form-check-label">Ver</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_pedidos_anticipo_altas">
                                                        <label class="form-check-label">Crear</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_pedidos_anticipo_edicion">
                                                        <label class="form-check-label">Editar</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_pedidos_anticipo_eliminar">
                                                        <label class="form-check-label">Eliminar</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Seguimiento Ventas (Solo Ver y Editar) -->
                                        <div class="border-bottom pb-2 mb-2">
                                            <strong class="text-primary">Seguimiento Ventas</strong>
                                            <div class="row mt-2">
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_seguimiento_ventas_ver">
                                                        <label class="form-check-label">Ver</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_seguimiento_ventas_edicion">
                                                        <label class="form-check-label">Editar</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Seguimiento Cotizaciones (Solo Ver y Editar) -->
                                        <div class="border-bottom pb-2 mb-2">
                                            <strong class="text-primary">Seguimiento Cotizaciones</strong>
                                            <div class="row mt-2">
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_seguimiento_cotizaciones_ver">
                                                        <label class="form-check-label">Ver</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_seguimiento_cotizaciones_edicion">
                                                        <label class="form-check-label">Editar</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Agenda Contactos (CRUD completo) -->
                                        <div>
                                            <strong class="text-primary">Agenda Contactos</strong>
                                            <div class="row mt-2">
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_agenda_contactos_ver">
                                                        <label class="form-check-label">Ver</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_agenda_contactos_altas">
                                                        <label class="form-check-label">Crear</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_agenda_contactos_edicion">
                                                        <label class="form-check-label">Editar</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_agenda_contactos_eliminar">
                                                        <label class="form-check-label">Eliminar</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ============================================ -->
                            <!-- SEGURIDAD -->
                            <!-- ============================================ -->
                            <div class="card mb-3">
                                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center"
                                     data-bs-toggle="collapse" data-bs-target="#collapseSeguridad" style="cursor: pointer;">
                                    <span><strong>🔒 Seguridad</strong></span>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="form-check form-switch" onclick="event.stopPropagation()">
                                            <input class="form-check-input" type="checkbox" id="permiso_seguridad_mostrar">
                                            <label class="form-check-label">Mostrar/Ocultar</label>
                                        </div>
                                        <i class="bi bi-chevron-down collapse-icon"></i>
                                    </div>
                                </div>
                                <div class="collapse show" id="collapseSeguridad">
                                    <div class="card-body py-2">
                                        <!-- Usuarios (CRUD completo) -->
                                        <div class="border-bottom pb-2 mb-2">
                                            <strong class="text-primary">Usuarios</strong>
                                            <div class="row mt-2">
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_usuarios_ver">
                                                        <label class="form-check-label">Ver</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_usuarios_altas">
                                                        <label class="form-check-label">Crear</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_usuarios_edicion">
                                                        <label class="form-check-label">Editar</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_usuarios_eliminar">
                                                        <label class="form-check-label">Eliminar</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Permisos (Solo Ver) -->
                                        <div class="border-bottom pb-2 mb-2">
                                            <strong class="text-primary">Permisos</strong>
                                            <div class="row mt-2">
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_permisos_ver">
                                                        <label class="form-check-label">Ver</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Respaldos (Solo Ver) -->
                                        <div>
                                            <strong class="text-primary">Respaldos</strong>
                                            <div class="row mt-2">
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_respaldos_ver">
                                                        <label class="form-check-label">Ver</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ============================================ -->
                            <!-- REPORTES (Solo Ver) -->
                            <!-- ============================================ -->
                            <div class="card mb-3">
                                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center"
                                     data-bs-toggle="collapse" data-bs-target="#collapseReportes" style="cursor: pointer;">
                                    <span><strong>📊 Reportes</strong></span>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="form-check form-switch" onclick="event.stopPropagation()">
                                            <input class="form-check-input" type="checkbox" id="permiso_reportes_mostrar">
                                            <label class="form-check-label">Mostrar/Ocultar</label>
                                        </div>
                                        <i class="bi bi-chevron-down collapse-icon"></i>
                                    </div>
                                </div>
                                <div class="collapse show" id="collapseReportes">
                                    <div class="card-body py-2">
                                        <div class="row">
                                            <div class="col-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="permiso_reportes_compras_cliente">
                                                    <label class="form-check-label">Compras por Cliente</label>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="permiso_reportes_frecuencia_compra">
                                                    <label class="form-check-label">Frecuencia de Compra</label>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="permiso_reportes_montos_promedio">
                                                    <label class="form-check-label">Montos Promedio</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="permiso_reportes_sucursales_preferidas">
                                                    <label class="form-check-label">Sucursales Preferidas</label>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="permiso_reportes_cotizaciones_cliente">
                                                    <label class="form-check-label">Cotizaciones por Cliente</label>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="permiso_reportes_cotizaciones_concretadas">
                                                    <label class="form-check-label">Cotizaciones Concretadas</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarEdicionUsuario()">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Función para formatear fecha correctamente
function formatearFecha(fechaISO) {
    if (!fechaISO) return '';
    const fecha = new Date(fechaISO);
    if (isNaN(fecha.getTime())) return '';
    
    const año = fecha.getFullYear();
    const mes = String(fecha.getMonth() + 1).padStart(2, '0');
    const dia = String(fecha.getDate()).padStart(2, '0');
    return `${año}-${mes}-${dia}`;
}

// Función para cargar datos del usuario en el modal
function cargarDatosUsuario(id) {
    fetch(`/seguridad/usuarios/${id}/edit`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Datos básicos
            document.getElementById('edit_usuario_id').value = data.data.id_personal_empresa;
            document.getElementById('edit_Nombre').value = data.data.Nombre || '';
            document.getElementById('edit_ApPaterno').value = data.data.ApPaterno || '';
            document.getElementById('edit_ApMaterno').value = data.data.ApMaterno || '';
            document.getElementById('edit_usuario').value = data.data.usuario || '';
            document.getElementById('edit_contacto').value = data.data.contacto || '';
            document.getElementById('edit_TelefonoMovil').value = data.data.TelefonoMovil || '';
            document.getElementById('edit_Direccion').value = data.data.Direccion || '';
            document.getElementById('edit_Localidad').value = data.data.Localidad || '';
            document.getElementById('edit_Municipio').value = data.data.Municipio || '';
            document.getElementById('edit_curp').value = data.data.curp || '';
            document.getElementById('edit_fecha_nacimiento').value = formatearFecha(data.data.fecha_nacimiento);
            document.getElementById('edit_Activo').value = data.data.Activo ? '1' : '0';
            document.getElementById('edit_sucursal_asignada').value = data.data.sucursal_asignada || '';
            
            // Cargar permisos
            const permisos = data.permisos || {};
            
            // Función auxiliar
            const setCheckbox = (id, valor) => {
                const checkbox = document.getElementById(id);
                if (checkbox) checkbox.checked = valor === true;
            };
            
            // CLIENTES
            setCheckbox('permiso_clientes_mostrar', permisos.clientes?.mostrar);
            setCheckbox('permiso_clientes_ver', permisos.clientes?.ver);
            setCheckbox('permiso_clientes_altas', permisos.clientes?.altas);
            setCheckbox('permiso_clientes_edicion', permisos.clientes?.edicion);
            setCheckbox('permiso_clientes_eliminar', permisos.clientes?.eliminar);
            setCheckbox('permiso_enfermedades_ver', permisos.clientes?.enfermedades);
            setCheckbox('permiso_intereses_ver', permisos.clientes?.intereses);
            
            // VENTAS
            setCheckbox('permiso_ventas_mostrar', permisos.ventas?.mostrar);
            setCheckbox('permiso_cotizaciones_ver', permisos.ventas?.cotizaciones);
            setCheckbox('permiso_cotizaciones_altas', permisos.ventas?.cotizaciones_altas);
            setCheckbox('permiso_cotizaciones_edicion', permisos.ventas?.cotizaciones_edicion);
            setCheckbox('permiso_cotizaciones_eliminar', permisos.ventas?.cotizaciones_eliminar);
            setCheckbox('permiso_pedidos_anticipo_ver', permisos.ventas?.pedidos_anticipo);
            setCheckbox('permiso_pedidos_anticipo_altas', permisos.ventas?.pedidos_anticipo_altas);
            setCheckbox('permiso_pedidos_anticipo_edicion', permisos.ventas?.pedidos_anticipo_edicion);
            setCheckbox('permiso_pedidos_anticipo_eliminar', permisos.ventas?.pedidos_anticipo_eliminar);
            setCheckbox('permiso_seguimiento_ventas_ver', permisos.ventas?.seguimiento_ventas);
            setCheckbox('permiso_seguimiento_ventas_edicion', permisos.ventas?.seguimiento_ventas_edicion);
            setCheckbox('permiso_seguimiento_cotizaciones_ver', permisos.ventas?.seguimiento_cotizaciones);
            setCheckbox('permiso_seguimiento_cotizaciones_edicion', permisos.ventas?.seguimiento_cotizaciones_edicion);
            setCheckbox('permiso_agenda_contactos_ver', permisos.ventas?.agenda_contactos);
            setCheckbox('permiso_agenda_contactos_altas', permisos.ventas?.agenda_contactos_altas);
            setCheckbox('permiso_agenda_contactos_edicion', permisos.ventas?.agenda_contactos_edicion);
            setCheckbox('permiso_agenda_contactos_eliminar', permisos.ventas?.agenda_contactos_eliminar);
            
            // SEGURIDAD
            setCheckbox('permiso_seguridad_mostrar', permisos.seguridad?.mostrar);
            setCheckbox('permiso_usuarios_ver', permisos.seguridad?.usuarios);
            setCheckbox('permiso_usuarios_altas', permisos.seguridad?.altas);
            setCheckbox('permiso_usuarios_edicion', permisos.seguridad?.edicion);
            setCheckbox('permiso_usuarios_eliminar', permisos.seguridad?.eliminar);
            setCheckbox('permiso_permisos_ver', permisos.seguridad?.permisos);
            setCheckbox('permiso_respaldos_ver', permisos.seguridad?.respaldos);
            
            // REPORTES
            setCheckbox('permiso_reportes_mostrar', permisos.reportes?.mostrar);
            setCheckbox('permiso_reportes_compras_cliente', permisos.reportes?.compras_cliente);
            setCheckbox('permiso_reportes_frecuencia_compra', permisos.reportes?.frecuencia_compra);
            setCheckbox('permiso_reportes_montos_promedio', permisos.reportes?.montos_promedio);
            setCheckbox('permiso_reportes_sucursales_preferidas', permisos.reportes?.sucursales_preferidas);
            setCheckbox('permiso_reportes_cotizaciones_cliente', permisos.reportes?.cotizaciones_cliente);
            setCheckbox('permiso_reportes_cotizaciones_concretadas', permisos.reportes?.cotizaciones_concretadas);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Función para controlar la visibilidad del módulo
function toggleModuloVisibility(modulo, mostrar) {
    let collapseId = '';
    switch(modulo) {
        case 'clientes': collapseId = 'collapseClientes'; break;
        case 'ventas': collapseId = 'collapseVentas'; break;
        case 'seguridad': collapseId = 'collapseSeguridad'; break;
        case 'reportes': collapseId = 'collapseReportes'; break;
    }
    
    const collapseElement = document.getElementById(collapseId);
    if (collapseElement) {
        if (!mostrar) {
            collapseElement.classList.remove('show');
            // Opcional: deshabilitar todos los checkboxes dentro
            collapseElement.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                cb.disabled = true;
                cb.checked = false;
            });
        } else {
            collapseElement.classList.add('show');
            collapseElement.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                cb.disabled = false;
            });
        }
    }
}

// Función para guardar edición de usuario
window.guardarEdicionUsuario = function() {
    const id = document.getElementById('edit_usuario_id').value;
    
    const permisos = {
        clientes: {
            mostrar: document.getElementById('permiso_clientes_mostrar')?.checked || false,
            ver: document.getElementById('permiso_clientes_ver')?.checked || false,
            enfermedades: document.getElementById('permiso_enfermedades_ver')?.checked || false,
            intereses: document.getElementById('permiso_intereses_ver')?.checked || false,
            altas: document.getElementById('permiso_clientes_altas')?.checked || false,
            edicion: document.getElementById('permiso_clientes_edicion')?.checked || false,
            eliminar: document.getElementById('permiso_clientes_eliminar')?.checked || false
        },
        ventas: {
            mostrar: document.getElementById('permiso_ventas_mostrar')?.checked || false,
            cotizaciones: document.getElementById('permiso_cotizaciones_ver')?.checked || false,
            cotizaciones_altas: document.getElementById('permiso_cotizaciones_altas')?.checked || false,
            cotizaciones_edicion: document.getElementById('permiso_cotizaciones_edicion')?.checked || false,
            cotizaciones_eliminar: document.getElementById('permiso_cotizaciones_eliminar')?.checked || false,
            pedidos_anticipo: document.getElementById('permiso_pedidos_anticipo_ver')?.checked || false,
            pedidos_anticipo_altas: document.getElementById('permiso_pedidos_anticipo_altas')?.checked || false,
            pedidos_anticipo_edicion: document.getElementById('permiso_pedidos_anticipo_edicion')?.checked || false,
            pedidos_anticipo_eliminar: document.getElementById('permiso_pedidos_anticipo_eliminar')?.checked || false,
            seguimiento_ventas: document.getElementById('permiso_seguimiento_ventas_ver')?.checked || false,
            seguimiento_ventas_edicion: document.getElementById('permiso_seguimiento_ventas_edicion')?.checked || false,
            seguimiento_cotizaciones: document.getElementById('permiso_seguimiento_cotizaciones_ver')?.checked || false,
            seguimiento_cotizaciones_edicion: document.getElementById('permiso_seguimiento_cotizaciones_edicion')?.checked || false,
            agenda_contactos: document.getElementById('permiso_agenda_contactos_ver')?.checked || false,
            agenda_contactos_altas: document.getElementById('permiso_agenda_contactos_altas')?.checked || false,
            agenda_contactos_edicion: document.getElementById('permiso_agenda_contactos_edicion')?.checked || false,
            agenda_contactos_eliminar: document.getElementById('permiso_agenda_contactos_eliminar')?.checked || false
        },
        seguridad: {
            mostrar: document.getElementById('permiso_seguridad_mostrar')?.checked || false,
            usuarios: document.getElementById('permiso_usuarios_ver')?.checked || false,
            permisos: document.getElementById('permiso_permisos_ver')?.checked || false,
            respaldos: document.getElementById('permiso_respaldos_ver')?.checked || false,
            altas: document.getElementById('permiso_usuarios_altas')?.checked || false,
            edicion: document.getElementById('permiso_usuarios_edicion')?.checked || false,
            eliminar: document.getElementById('permiso_usuarios_eliminar')?.checked || false
        },
        reportes: {
            mostrar: document.getElementById('permiso_reportes_mostrar')?.checked || false,
            compras_cliente: document.getElementById('permiso_reportes_compras_cliente')?.checked || false,
            frecuencia_compra: document.getElementById('permiso_reportes_frecuencia_compra')?.checked || false,
            montos_promedio: document.getElementById('permiso_reportes_montos_promedio')?.checked || false,
            sucursales_preferidas: document.getElementById('permiso_reportes_sucursales_preferidas')?.checked || false,
            cotizaciones_cliente: document.getElementById('permiso_reportes_cotizaciones_cliente')?.checked || false,
            cotizaciones_concretadas: document.getElementById('permiso_reportes_cotizaciones_concretadas')?.checked || false
        }
    };
    
    // Construir datos del formulario
    const formData = {
        Nombre: document.getElementById('edit_Nombre').value,
        ApPaterno: document.getElementById('edit_ApPaterno').value,
        ApMaterno: document.getElementById('edit_ApMaterno').value || null,
        usuario: document.getElementById('edit_usuario').value,
        contacto: document.getElementById('edit_contacto').value || null,
        TelefonoMovil: document.getElementById('edit_TelefonoMovil').value || null,
        Direccion: document.getElementById('edit_Direccion').value || null,
        Localidad: document.getElementById('edit_Localidad').value || null,
        Municipio: document.getElementById('edit_Municipio').value || null,
        curp: document.getElementById('edit_curp').value || null,
        fecha_nacimiento: document.getElementById('edit_fecha_nacimiento').value || null,
        Activo: document.getElementById('edit_Activo').value,
        sucursal_asignada: document.getElementById('edit_sucursal_asignada').value || null,
        passw: document.getElementById('edit_passw').value || null,
        permisos_modulos: permisos,
        _token: '{{ csrf_token() }}',
        _method: 'PUT'
    };
    
    // Validar campos requeridos
    if (!formData.Nombre || !formData.ApPaterno || !formData.usuario) {
        if (window.mostrarToast) window.mostrarToast('Completa los campos requeridos', 'warning');
        return;
    }
    
    fetch(`/seguridad/usuarios/${id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarUsuario'));
            modal.hide();
            if (window.mostrarToast) window.mostrarToast('Usuario actualizado correctamente', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al actualizar', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
};

// Event listener para cargar datos al abrir el modal
document.addEventListener('DOMContentLoaded', function() {
    const modalEditar = document.getElementById('modalEditarUsuario');
    if (modalEditar) {
        modalEditar.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const usuarioId = button.getAttribute('data-usuario-id');
            cargarDatosUsuario(usuarioId);
        });
    }
});
</script>
@endpush