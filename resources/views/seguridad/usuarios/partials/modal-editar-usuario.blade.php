<!-- Modal Editar Usuario -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-labelledby="modalEditarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning">
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
                                        <option value="0">CRM (Sistema)</option>
                                        <option value="1">Sucursal Jardin</option>
                                        <option value="2">Sucursal Mercado</option>
                                        <option value="3">Sucursal Zacatipan</option>
                                        <option value="4">Sucursal Boulevard</option>
                                        {{-- 
                                        <option value="5">Sucursal smg</option>
                                        <option value="6">Sucursal sfo</option>
                                        <option value="7">Sucursal hug</option>
                                        <option value="8">Sucursal huc</option>
                                        --}}
                                    </select>
                                    <small class="text-muted">"CRM" indica que el usuario opera desde el sistema central</small>
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
                                    <span><strong><i class="bi bi-card-checklist"></i> Clientes</strong></span>
                                    <i class="bi bi-chevron-down collapse-icon"></i>
                                </div>
                                <div class="collapse show" id="collapseClientes">
                                    <div class="card-body py-2">
                                        <!-- Directorio Clientes -->
                                        <div class="border-bottom pb-2 mb-2">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <strong class="text-primary">Directorio Clientes</strong>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="permiso_clientes_directorio_mostrar">
                                                    <label class="form-check-label small">Mostrar/Ocultar</label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_clientes_directorio_ver">
                                                        <label class="form-check-label">Ver</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_clientes_directorio_crear">
                                                        <label class="form-check-label">Altas</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_clientes_directorio_editar">
                                                        <label class="form-check-label">Editar</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_clientes_directorio_eliminar">
                                                        <label class="form-check-label">Eliminar</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Enfermedades -->
                                        <div class="border-bottom pb-2 mb-2">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <strong class="text-primary">Enfermedades</strong>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="permiso_clientes_enfermedades_mostrar">
                                                    <label class="form-check-label small">Mostrar/Ocultar</label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_clientes_enfermedades_ver">
                                                        <label class="form-check-label">Ver</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_clientes_enfermedades_crear">
                                                        <label class="form-check-label">Altas</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_clientes_enfermedades_editar">
                                                        <label class="form-check-label">Editar</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_clientes_enfermedades_eliminar">
                                                        <label class="form-check-label">Eliminar</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Intereses/Preferencias -->
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <strong class="text-primary">Intereses / Preferencias</strong>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="permiso_clientes_intereses_mostrar">
                                                    <label class="form-check-label small">Mostrar/Ocultar</label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_clientes_intereses_ver">
                                                        <label class="form-check-label">Ver</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_clientes_intereses_crear">
                                                        <label class="form-check-label">Altas</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_clientes_intereses_editar">
                                                        <label class="form-check-label">Editar</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_clientes_intereses_eliminar">
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
                                    <span><strong><i class="bi bi-graph-up"></i> Ventas</strong></span>
                                    <i class="bi bi-chevron-down collapse-icon"></i>
                                </div>
                                <div class="collapse show" id="collapseVentas">
                                    <div class="card-body py-2">
                                        <!-- Cotizaciones -->
                                        <div class="border-bottom pb-2 mb-2">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <strong class="text-primary">Cotizaciones</strong>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="permiso_ventas_cotizaciones_mostrar">
                                                    <label class="form-check-label small">Mostrar/Ocultar</label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_ventas_cotizaciones_ver">
                                                        <label class="form-check-label">Ver</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_ventas_cotizaciones_crear">
                                                        <label class="form-check-label">Altas</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_ventas_cotizaciones_editar">
                                                        <label class="form-check-label">Editar</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_ventas_cotizaciones_eliminar">
                                                        <label class="form-check-label">Eliminar</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Pedidos Anticipo -->
                                        <div class="border-bottom pb-2 mb-2">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <strong class="text-primary">Pedidos Anticipo</strong>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="permiso_ventas_pedidos_anticipo_mostrar">
                                                    <label class="form-check-label small">Mostrar/Ocultar</label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_ventas_pedidos_anticipo_ver">
                                                        <label class="form-check-label">Ver</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_ventas_pedidos_anticipo_crear">
                                                        <label class="form-check-label">Altas</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_ventas_pedidos_anticipo_editar">
                                                        <label class="form-check-label">Editar</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_ventas_pedidos_anticipo_eliminar">
                                                        <label class="form-check-label">Eliminar</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Seguimiento Ventas (solo Ver y Editar) -->
                                        <div class="border-bottom pb-2 mb-2">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <strong class="text-primary">Seguimiento Ventas</strong>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="permiso_ventas_seguimiento_ventas_mostrar">
                                                    <label class="form-check-label small">Mostrar/Ocultar</label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_ventas_seguimiento_ventas_ver">
                                                        <label class="form-check-label">Ver</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_ventas_seguimiento_ventas_editar">
                                                        <label class="form-check-label">Editar</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Seguimiento Cotizaciones (solo Ver y Editar) -->
                                        <div class="border-bottom pb-2 mb-2">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <strong class="text-primary">Seguimiento Cotizaciones</strong>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="permiso_ventas_seguimiento_cotizaciones_mostrar">
                                                    <label class="form-check-label small">Mostrar/Ocultar</label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_ventas_seguimiento_cotizaciones_ver">
                                                        <label class="form-check-label">Ver</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_ventas_seguimiento_cotizaciones_editar">
                                                        <label class="form-check-label">Editar</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Agenda Contactos -->
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <strong class="text-primary">Agenda Contactos</strong>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="permiso_ventas_agenda_contactos_mostrar">
                                                    <label class="form-check-label small">Mostrar/Ocultar</label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_ventas_agenda_contactos_ver">
                                                        <label class="form-check-label">Ver</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_ventas_agenda_contactos_crear">
                                                        <label class="form-check-label">Altas</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_ventas_agenda_contactos_editar">
                                                        <label class="form-check-label">Editar</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_ventas_agenda_contactos_eliminar">
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
                                    <span><strong><i class="bi bi-lock"></i> Seguridad</strong></span>
                                    <i class="bi bi-chevron-down collapse-icon"></i>
                                </div>
                                <div class="collapse show" id="collapseSeguridad">
                                    <div class="card-body py-2">
                                        <!-- Usuarios -->
                                        <div class="border-bottom pb-2 mb-2">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <strong class="text-primary">Usuarios</strong>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="permiso_seguridad_usuarios_mostrar">
                                                    <label class="form-check-label small">Mostrar/Ocultar</label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_seguridad_usuarios_ver">
                                                        <label class="form-check-label">Ver</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_seguridad_usuarios_crear">
                                                        <label class="form-check-label">Altas</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_seguridad_usuarios_editar">
                                                        <label class="form-check-label">Editar</label>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_seguridad_usuarios_eliminar">
                                                        <label class="form-check-label">Eliminar</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Permisos (Solo Ver) -->
                                        <div class="border-bottom pb-2 mb-2">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <strong class="text-primary">Permisos</strong>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="permiso_seguridad_permisos_mostrar">
                                                    <label class="form-check-label small">Mostrar/Ocultar</label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_seguridad_permisos_ver">
                                                        <label class="form-check-label">Ver</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Respaldos (Solo Ver) -->
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <strong class="text-primary">Respaldos</strong>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="permiso_seguridad_respaldos_mostrar">
                                                    <label class="form-check-label small">Mostrar/Ocultar</label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="permiso_seguridad_respaldos_ver">
                                                        <label class="form-check-label">Ver</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ============================================ -->
                            <!-- REPORTES (Solo Mostrar/Ocultar) -->
                            <!-- ============================================ -->
                            <div class="card mb-3">
                                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center"
                                    data-bs-toggle="collapse" data-bs-target="#collapseReportes" style="cursor: pointer;">
                                    <span><strong><i class="bi bi-clipboard2-data"></i> Reportes</strong></span>
                                    <i class="bi bi-chevron-down collapse-icon"></i>
                                </div>
                                <div class="collapse show" id="collapseReportes">
                                    <div class="card-body py-2">
                                        <div class="row">
                                            <div class="col-4 mb-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span>Compras por Cliente</span>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="permiso_reportes_compras_cliente_mostrar">
                                                        <label class="form-check-label small">Mostrar</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-4 mb-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span>Frecuencia de Compra</span>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="permiso_reportes_frecuencia_compra_mostrar">
                                                        <label class="form-check-label small">Mostrar</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-4 mb-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span>Montos Promedio</span>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="permiso_reportes_montos_promedio_mostrar">
                                                        <label class="form-check-label small">Mostrar</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-4 mb-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span>Sucursales Preferidas</span>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="permiso_reportes_sucursales_preferidas_mostrar">
                                                        <label class="form-check-label small">Mostrar</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-4 mb-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span>Cotizaciones por Cliente</span>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="permiso_reportes_cotizaciones_cliente_mostrar">
                                                        <label class="form-check-label small">Mostrar</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-4 mb-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span>Cotizaciones Concretadas</span>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="permiso_reportes_cotizaciones_concretadas_mostrar">
                                                        <label class="form-check-label small">Mostrar</label>
                                                    </div>
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
                <button type="button" class="btn btn-warning" onclick="guardarEdicionUsuario()">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ============================================
// FUNCIONES DE DEPENDENCIA ENTRE PERMISOS
// ============================================

/**
 * Configura la dependencia donde al activar "Editar" o "Eliminar" se activa automáticamente "Ver"
 */
function setupPermisoDependencia(modulo, submodulo) {
    const checkboxVer = document.getElementById(`permiso_${modulo}_${submodulo}_ver`);
    const checkboxEditar = document.getElementById(`permiso_${modulo}_${submodulo}_editar`);
    const checkboxEliminar = document.getElementById(`permiso_${modulo}_${submodulo}_eliminar`);
    
    if (checkboxEditar) {
        checkboxEditar.addEventListener('change', function() {
            if (this.checked && checkboxVer && !checkboxVer.checked) {
                checkboxVer.checked = true;
                // Disparar evento change para actualizar cualquier otra lógica
                checkboxVer.dispatchEvent(new Event('change'));
            }
        });
    }
    
    if (checkboxEliminar) {
        checkboxEliminar.addEventListener('change', function() {
            if (this.checked && checkboxVer && !checkboxVer.checked) {
                checkboxVer.checked = true;
                checkboxVer.dispatchEvent(new Event('change'));
            }
        });
    }
}

/**
 * Configura la dependencia inversa: al desactivar "Ver", se desactivan "Editar" y "Eliminar"
 */
function setupDependenciaInversa(modulo, submodulo) {
    const checkboxVer = document.getElementById(`permiso_${modulo}_${submodulo}_ver`);
    const checkboxEditar = document.getElementById(`permiso_${modulo}_${submodulo}_editar`);
    const checkboxEliminar = document.getElementById(`permiso_${modulo}_${submodulo}_eliminar`);
    
    if (checkboxVer) {
        checkboxVer.addEventListener('change', function() {
            if (!this.checked) {
                if (checkboxEditar) checkboxEditar.checked = false;
                if (checkboxEliminar) checkboxEliminar.checked = false;
            }
        });
    }
}

// ============================================
// FUNCIÓN PARA FORMATEAR FECHA
// ============================================
function formatearFecha(fechaISO) {
    if (!fechaISO) return '';
    const fecha = new Date(fechaISO);
    if (isNaN(fecha.getTime())) return '';
    
    const año = fecha.getFullYear();
    const mes = String(fecha.getMonth() + 1).padStart(2, '0');
    const dia = String(fecha.getDate()).padStart(2, '0');
    return `${año}-${mes}-${dia}`;
}

// ============================================
// FUNCIÓN PARA CARGAR DATOS DEL USUARIO
// ============================================
function cargarDatosUsuario(id) {
    // Asegurarse de que id sea un número
    id = parseInt(id);
    if (isNaN(id)) {
        console.error('ID inválido:', id);
        return;
    }
    
    console.log('Cargando datos del usuario ID:', id);
    
    fetch(`/seguridad/usuarios/${id}/edit`, {
        headers: { 
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Respuesta del servidor:', data);
        
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
            // Sucursal asignada - si es null, undefined o vacío, asignar 0 (CRM)
            let sucursalAsignada = data.data.sucursal_asignada;
            if (sucursalAsignada === null || sucursalAsignada === undefined || sucursalAsignada === '') {
                sucursalAsignada = 0;
            }
            document.getElementById('edit_sucursal_asignada').value = sucursalAsignada;
            
            // Cargar permisos desde data.permisos
            const permisos = data.permisos || {};
            console.log('Permisos recibidos:', permisos);
            
            // Función auxiliar para establecer checkbox
            const setCheckbox = (id, valor) => {
                const checkbox = document.getElementById(id);
                if (checkbox) {
                    checkbox.checked = valor === true;
                } else {
                    console.warn(`Checkbox no encontrado: ${id}`);
                }
            };
            
            // ============================================
            // CLIENTES
            // ============================================
            // Directorio Clientes
            setCheckbox('permiso_clientes_directorio_mostrar', permisos.clientes?.directorio?.mostrar);
            setCheckbox('permiso_clientes_directorio_ver', permisos.clientes?.directorio?.ver);
            setCheckbox('permiso_clientes_directorio_crear', permisos.clientes?.directorio?.crear);
            setCheckbox('permiso_clientes_directorio_editar', permisos.clientes?.directorio?.editar);
            setCheckbox('permiso_clientes_directorio_eliminar', permisos.clientes?.directorio?.eliminar);
            
            // Enfermedades
            setCheckbox('permiso_clientes_enfermedades_mostrar', permisos.clientes?.enfermedades?.mostrar);
            setCheckbox('permiso_clientes_enfermedades_ver', permisos.clientes?.enfermedades?.ver);
            setCheckbox('permiso_clientes_enfermedades_crear', permisos.clientes?.enfermedades?.crear);
            setCheckbox('permiso_clientes_enfermedades_editar', permisos.clientes?.enfermedades?.editar);
            setCheckbox('permiso_clientes_enfermedades_eliminar', permisos.clientes?.enfermedades?.eliminar);
            
            // Intereses
            setCheckbox('permiso_clientes_intereses_mostrar', permisos.clientes?.intereses?.mostrar);
            setCheckbox('permiso_clientes_intereses_ver', permisos.clientes?.intereses?.ver);
            setCheckbox('permiso_clientes_intereses_crear', permisos.clientes?.intereses?.crear);
            setCheckbox('permiso_clientes_intereses_editar', permisos.clientes?.intereses?.editar);
            setCheckbox('permiso_clientes_intereses_eliminar', permisos.clientes?.intereses?.eliminar);
            
            // ============================================
            // VENTAS
            // ============================================
            // Cotizaciones
            setCheckbox('permiso_ventas_cotizaciones_mostrar', permisos.ventas?.cotizaciones?.mostrar);
            setCheckbox('permiso_ventas_cotizaciones_ver', permisos.ventas?.cotizaciones?.ver);
            setCheckbox('permiso_ventas_cotizaciones_crear', permisos.ventas?.cotizaciones?.crear);
            setCheckbox('permiso_ventas_cotizaciones_editar', permisos.ventas?.cotizaciones?.editar);
            setCheckbox('permiso_ventas_cotizaciones_eliminar', permisos.ventas?.cotizaciones?.eliminar);
            
            // Pedidos Anticipo
            setCheckbox('permiso_ventas_pedidos_anticipo_mostrar', permisos.ventas?.pedidos_anticipo?.mostrar);
            setCheckbox('permiso_ventas_pedidos_anticipo_ver', permisos.ventas?.pedidos_anticipo?.ver);
            setCheckbox('permiso_ventas_pedidos_anticipo_crear', permisos.ventas?.pedidos_anticipo?.crear);
            setCheckbox('permiso_ventas_pedidos_anticipo_editar', permisos.ventas?.pedidos_anticipo?.editar);
            setCheckbox('permiso_ventas_pedidos_anticipo_eliminar', permisos.ventas?.pedidos_anticipo?.eliminar);
            
            // Seguimiento Ventas
            setCheckbox('permiso_ventas_seguimiento_ventas_mostrar', permisos.ventas?.seguimiento_ventas?.mostrar);
            setCheckbox('permiso_ventas_seguimiento_ventas_ver', permisos.ventas?.seguimiento_ventas?.ver);
            setCheckbox('permiso_ventas_seguimiento_ventas_editar', permisos.ventas?.seguimiento_ventas?.editar);
            
            // Seguimiento Cotizaciones
            setCheckbox('permiso_ventas_seguimiento_cotizaciones_mostrar', permisos.ventas?.seguimiento_cotizaciones?.mostrar);
            setCheckbox('permiso_ventas_seguimiento_cotizaciones_ver', permisos.ventas?.seguimiento_cotizaciones?.ver);
            setCheckbox('permiso_ventas_seguimiento_cotizaciones_editar', permisos.ventas?.seguimiento_cotizaciones?.editar);
            
            // Agenda Contactos
            setCheckbox('permiso_ventas_agenda_contactos_mostrar', permisos.ventas?.agenda_contactos?.mostrar);
            setCheckbox('permiso_ventas_agenda_contactos_ver', permisos.ventas?.agenda_contactos?.ver);
            setCheckbox('permiso_ventas_agenda_contactos_crear', permisos.ventas?.agenda_contactos?.crear);
            setCheckbox('permiso_ventas_agenda_contactos_editar', permisos.ventas?.agenda_contactos?.editar);
            setCheckbox('permiso_ventas_agenda_contactos_eliminar', permisos.ventas?.agenda_contactos?.eliminar);
            
            // ============================================
            // SEGURIDAD
            // ============================================
            // Usuarios
            setCheckbox('permiso_seguridad_usuarios_mostrar', permisos.seguridad?.usuarios?.mostrar);
            setCheckbox('permiso_seguridad_usuarios_ver', permisos.seguridad?.usuarios?.ver);
            setCheckbox('permiso_seguridad_usuarios_crear', permisos.seguridad?.usuarios?.crear);
            setCheckbox('permiso_seguridad_usuarios_editar', permisos.seguridad?.usuarios?.editar);
            setCheckbox('permiso_seguridad_usuarios_eliminar', permisos.seguridad?.usuarios?.eliminar);
            
            // Permisos
            setCheckbox('permiso_seguridad_permisos_mostrar', permisos.seguridad?.permisos?.mostrar);
            setCheckbox('permiso_seguridad_permisos_ver', permisos.seguridad?.permisos?.ver);
            
            // Respaldos
            setCheckbox('permiso_seguridad_respaldos_mostrar', permisos.seguridad?.respaldos?.mostrar);
            setCheckbox('permiso_seguridad_respaldos_ver', permisos.seguridad?.respaldos?.ver);
            
            // ============================================
            // REPORTES - solo mostrar
            // ============================================
            setCheckbox('permiso_reportes_compras_cliente_mostrar', permisos.reportes?.compras_cliente?.mostrar);
            setCheckbox('permiso_reportes_frecuencia_compra_mostrar', permisos.reportes?.frecuencia_compra?.mostrar);
            setCheckbox('permiso_reportes_montos_promedio_mostrar', permisos.reportes?.montos_promedio?.mostrar);
            setCheckbox('permiso_reportes_sucursales_preferidas_mostrar', permisos.reportes?.sucursales_preferidas?.mostrar);
            setCheckbox('permiso_reportes_cotizaciones_cliente_mostrar', permisos.reportes?.cotizaciones_cliente?.mostrar);
            setCheckbox('permiso_reportes_cotizaciones_concretadas_mostrar', permisos.reportes?.cotizaciones_concretadas?.mostrar);
            
        } else {
            console.error('Error en la respuesta:', data);
            if (window.mostrarToast) window.mostrarToast('Error al cargar datos del usuario', 'danger');
        }
    })
    .catch(error => {
        console.error('Error en fetch:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
}

// ============================================
// FUNCIÓN PARA GUARDAR EDICIÓN DE USUARIO
// ============================================
window.guardarEdicionUsuario = function() {
    const id = document.getElementById('edit_usuario_id').value;
    
    const permisos = {
        clientes: {
            directorio: {
                mostrar: document.getElementById('permiso_clientes_directorio_mostrar')?.checked || false,
                ver: document.getElementById('permiso_clientes_directorio_ver')?.checked || false,
                crear: document.getElementById('permiso_clientes_directorio_crear')?.checked || false,
                editar: document.getElementById('permiso_clientes_directorio_editar')?.checked || false,
                eliminar: document.getElementById('permiso_clientes_directorio_eliminar')?.checked || false
            },
            enfermedades: {
                mostrar: document.getElementById('permiso_clientes_enfermedades_mostrar')?.checked || false,
                ver: document.getElementById('permiso_clientes_enfermedades_ver')?.checked || false,
                crear: document.getElementById('permiso_clientes_enfermedades_crear')?.checked || false,
                editar: document.getElementById('permiso_clientes_enfermedades_editar')?.checked || false,
                eliminar: document.getElementById('permiso_clientes_enfermedades_eliminar')?.checked || false
            },
            intereses: {
                mostrar: document.getElementById('permiso_clientes_intereses_mostrar')?.checked || false,
                ver: document.getElementById('permiso_clientes_intereses_ver')?.checked || false,
                crear: document.getElementById('permiso_clientes_intereses_crear')?.checked || false,
                editar: document.getElementById('permiso_clientes_intereses_editar')?.checked || false,
                eliminar: document.getElementById('permiso_clientes_intereses_eliminar')?.checked || false
            }
        },
        ventas: {
            cotizaciones: {
                mostrar: document.getElementById('permiso_ventas_cotizaciones_mostrar')?.checked || false,
                ver: document.getElementById('permiso_ventas_cotizaciones_ver')?.checked || false,
                crear: document.getElementById('permiso_ventas_cotizaciones_crear')?.checked || false,
                editar: document.getElementById('permiso_ventas_cotizaciones_editar')?.checked || false,
                eliminar: document.getElementById('permiso_ventas_cotizaciones_eliminar')?.checked || false
            },
            pedidos_anticipo: {
                mostrar: document.getElementById('permiso_ventas_pedidos_anticipo_mostrar')?.checked || false,
                ver: document.getElementById('permiso_ventas_pedidos_anticipo_ver')?.checked || false,
                crear: document.getElementById('permiso_ventas_pedidos_anticipo_crear')?.checked || false,
                editar: document.getElementById('permiso_ventas_pedidos_anticipo_editar')?.checked || false,
                eliminar: document.getElementById('permiso_ventas_pedidos_anticipo_eliminar')?.checked || false
            },
            seguimiento_ventas: {
                mostrar: document.getElementById('permiso_ventas_seguimiento_ventas_mostrar')?.checked || false,
                ver: document.getElementById('permiso_ventas_seguimiento_ventas_ver')?.checked || false,
                editar: document.getElementById('permiso_ventas_seguimiento_ventas_editar')?.checked || false
            },
            seguimiento_cotizaciones: {
                mostrar: document.getElementById('permiso_ventas_seguimiento_cotizaciones_mostrar')?.checked || false,
                ver: document.getElementById('permiso_ventas_seguimiento_cotizaciones_ver')?.checked || false,
                editar: document.getElementById('permiso_ventas_seguimiento_cotizaciones_editar')?.checked || false
            },
            agenda_contactos: {
                mostrar: document.getElementById('permiso_ventas_agenda_contactos_mostrar')?.checked || false,
                ver: document.getElementById('permiso_ventas_agenda_contactos_ver')?.checked || false,
                crear: document.getElementById('permiso_ventas_agenda_contactos_crear')?.checked || false,
                editar: document.getElementById('permiso_ventas_agenda_contactos_editar')?.checked || false,
                eliminar: document.getElementById('permiso_ventas_agenda_contactos_eliminar')?.checked || false
            }
        },
        seguridad: {
            usuarios: {
                mostrar: document.getElementById('permiso_seguridad_usuarios_mostrar')?.checked || false,
                ver: document.getElementById('permiso_seguridad_usuarios_ver')?.checked || false,
                crear: document.getElementById('permiso_seguridad_usuarios_crear')?.checked || false,
                editar: document.getElementById('permiso_seguridad_usuarios_editar')?.checked || false,
                eliminar: document.getElementById('permiso_seguridad_usuarios_eliminar')?.checked || false
            },
            permisos: {
                mostrar: document.getElementById('permiso_seguridad_permisos_mostrar')?.checked || false,
                ver: document.getElementById('permiso_seguridad_permisos_ver')?.checked || false
            },
            respaldos: {
                mostrar: document.getElementById('permiso_seguridad_respaldos_mostrar')?.checked || false,
                ver: document.getElementById('permiso_seguridad_respaldos_ver')?.checked || false
            }
        },
        reportes: {
            compras_cliente: {
                mostrar: document.getElementById('permiso_reportes_compras_cliente_mostrar')?.checked || false
            },
            frecuencia_compra: {
                mostrar: document.getElementById('permiso_reportes_frecuencia_compra_mostrar')?.checked || false
            },
            montos_promedio: {
                mostrar: document.getElementById('permiso_reportes_montos_promedio_mostrar')?.checked || false
            },
            sucursales_preferidas: {
                mostrar: document.getElementById('permiso_reportes_sucursales_preferidas_mostrar')?.checked || false
            },
            cotizaciones_cliente: {
                mostrar: document.getElementById('permiso_reportes_cotizaciones_cliente_mostrar')?.checked || false
            },
            cotizaciones_concretadas: {
                mostrar: document.getElementById('permiso_reportes_cotizaciones_concretadas_mostrar')?.checked || false
            }
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
        sucursal_asignada: document.getElementById('edit_sucursal_asignada')?.value || 0,
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

// ============================================
// CONFIGURACIÓN DE DEPENDENCIAS AL CARGAR LA PÁGINA
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Configurar dependencias para CLIENTES
    setupPermisoDependencia('clientes', 'directorio');
    setupPermisoDependencia('clientes', 'enfermedades');
    setupPermisoDependencia('clientes', 'intereses');
    setupDependenciaInversa('clientes', 'directorio');
    setupDependenciaInversa('clientes', 'enfermedades');
    setupDependenciaInversa('clientes', 'intereses');
    
    // Configurar dependencias para VENTAS
    setupPermisoDependencia('ventas', 'cotizaciones');
    setupPermisoDependencia('ventas', 'pedidos_anticipo');
    setupPermisoDependencia('ventas', 'seguimiento_ventas');
    setupPermisoDependencia('ventas', 'seguimiento_cotizaciones');
    setupPermisoDependencia('ventas', 'agenda_contactos');
    setupDependenciaInversa('ventas', 'cotizaciones');
    setupDependenciaInversa('ventas', 'pedidos_anticipo');
    setupDependenciaInversa('ventas', 'seguimiento_ventas');
    setupDependenciaInversa('ventas', 'seguimiento_cotizaciones');
    setupDependenciaInversa('ventas', 'agenda_contactos');
    
    // Configurar dependencias para SEGURIDAD
    setupPermisoDependencia('seguridad', 'usuarios');
    setupDependenciaInversa('seguridad', 'usuarios');
    
    // Configurar el evento del modal
    const modalEditar = document.getElementById('modalEditarUsuario');
    if (modalEditar) {
        modalEditar.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const usuarioId = button.getAttribute('data-usuario-id');
            console.log('ID obtenido:', usuarioId);
            if (usuarioId) {
                cargarDatosUsuario(usuarioId);
            } else {
                console.error('No se encontró data-usuario-id en el botón');
            }
        });
    }
});
</script>
@endpush