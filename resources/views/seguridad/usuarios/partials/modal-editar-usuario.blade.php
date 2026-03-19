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
                            
                            <!-- Clientes (con collapse) -->
                            <div class="card mb-3">
                                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center" 
                                     data-bs-toggle="collapse" data-bs-target="#collapseClientes" style="cursor: pointer;">
                                    <span><strong>Clientes</strong></span>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="form-check form-switch" onclick="event.stopPropagation()">
                                            <input class="form-check-input" type="checkbox" id="permiso_clientes_mostrar" data-modulo="clientes" data-accion="mostrar">
                                            <label class="form-check-label">Mostrar/Ocultar</label>
                                        </div>
                                        <i class="bi bi-chevron-down collapse-icon"></i>
                                    </div>
                                </div>
                                <div class="collapse show" id="collapseClientes">
                                    <div class="card-body py-2">
                                        <div class="row">
                                            <div class="col-4">
                                                <div class="form-check">
                                                    <input class="form-check-input permiso-checkbox" type="checkbox" id="permiso_clientes_ver" data-modulo="clientes" data-accion="ver">
                                                    <label class="form-check-label">Directorio Clientes</label>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="form-check">
                                                    <input class="form-check-input permiso-checkbox" type="checkbox" id="permiso_clientes_enfermedades" data-modulo="clientes" data-accion="enfermedades">
                                                    <label class="form-check-label">Enfermedades</label>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="form-check">
                                                    <input class="form-check-input permiso-checkbox" type="checkbox" id="permiso_clientes_intereses" data-modulo="clientes" data-accion="intereses">
                                                    <label class="form-check-label">Intereses</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-3">
                                                <div class="form-check">
                                                    <input class="form-check-input permiso-checkbox" type="checkbox" id="permiso_clientes_altas" data-modulo="clientes" data-accion="altas">
                                                    <label class="form-check-label">Altas</label>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-check">
                                                    <input class="form-check-input permiso-checkbox" type="checkbox" id="permiso_clientes_edicion" data-modulo="clientes" data-accion="edicion">
                                                    <label class="form-check-label">Edición</label>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-check">
                                                    <input class="form-check-input permiso-checkbox" type="checkbox" id="permiso_clientes_eliminar" data-modulo="clientes" data-accion="eliminar">
                                                    <label class="form-check-label">Eliminar</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Ventas (con collapse) -->
                            <div class="card mb-3">
                                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center"
                                     data-bs-toggle="collapse" data-bs-target="#collapseVentas" style="cursor: pointer;">
                                    <span><strong>Ventas</strong></span>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="form-check form-switch" onclick="event.stopPropagation()">
                                            <input class="form-check-input" type="checkbox" id="permiso_ventas_mostrar" data-modulo="ventas" data-accion="mostrar">
                                            <label class="form-check-label">Mostrar/Ocultar</label>
                                        </div>
                                        <i class="bi bi-chevron-down collapse-icon"></i>
                                    </div>
                                </div>
                                <div class="collapse show" id="collapseVentas">
                                    <div class="card-body py-2">
                                        <div class="row">
                                            <div class="col-4">
                                                <div class="form-check">
                                                    <input class="form-check-input permiso-checkbox" type="checkbox" id="permiso_cotizaciones_ver" data-modulo="cotizaciones" data-accion="ver">
                                                    <label class="form-check-label">Cotizaciones</label>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="form-check">
                                                    <input class="form-check-input permiso-checkbox" type="checkbox" id="permiso_pedidos_anticipo_ver" data-modulo="pedidos_anticipo" data-accion="ver">
                                                    <label class="form-check-label">Pedidos Anticipo</label>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="form-check">
                                                    <input class="form-check-input permiso-checkbox" type="checkbox" id="permiso_seguimiento_ventas_ver" data-modulo="seguimiento_ventas" data-accion="ver">
                                                    <label class="form-check-label">Seguimiento Ventas</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-4">
                                                <div class="form-check">
                                                    <input class="form-check-input permiso-checkbox" type="checkbox" id="permiso_seguimiento_cotizaciones_ver" data-modulo="seguimiento_cotizaciones" data-accion="ver">
                                                    <label class="form-check-label">Seguimiento Cotizaciones</label>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="form-check">
                                                    <input class="form-check-input permiso-checkbox" type="checkbox" id="permiso_agenda_contactos_ver" data-modulo="agenda_contactos" data-accion="ver">
                                                    <label class="form-check-label">Agenda Contactos</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Seguridad (con collapse) -->
                            <div class="card mb-3">
                                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center"
                                     data-bs-toggle="collapse" data-bs-target="#collapseSeguridad" style="cursor: pointer;">
                                    <span><strong>Seguridad</strong></span>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="form-check form-switch" onclick="event.stopPropagation()">
                                            <input class="form-check-input" type="checkbox" id="permiso_seguridad_mostrar" data-modulo="seguridad" data-accion="mostrar">
                                            <label class="form-check-label">Mostrar/Ocultar</label>
                                        </div>
                                        <i class="bi bi-chevron-down collapse-icon"></i>
                                    </div>
                                </div>
                                <div class="collapse show" id="collapseSeguridad">
                                    <div class="card-body py-2">
                                        <div class="row">
                                            <div class="col-4">
                                                <div class="form-check">
                                                    <input class="form-check-input permiso-checkbox" type="checkbox" id="permiso_seguridad_ver" data-modulo="seguridad" data-accion="ver">
                                                    <label class="form-check-label">Usuarios</label>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="form-check">
                                                    <input class="form-check-input permiso-checkbox" type="checkbox" id="permiso_seguridad_permisos" data-modulo="seguridad" data-accion="permisos">
                                                    <label class="form-check-label">Permisos</label>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="form-check">
                                                    <input class="form-check-input permiso-checkbox" type="checkbox" id="permiso_seguridad_respaldos" data-modulo="seguridad" data-accion="respaldos">
                                                    <label class="form-check-label">Respaldos</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-3">
                                                <div class="form-check">
                                                    <input class="form-check-input permiso-checkbox" type="checkbox" id="permiso_seguridad_altas" data-modulo="seguridad" data-accion="altas">
                                                    <label class="form-check-label">Altas</label>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-check">
                                                    <input class="form-check-input permiso-checkbox" type="checkbox" id="permiso_seguridad_edicion" data-modulo="seguridad" data-accion="edicion">
                                                    <label class="form-check-label">Edición</label>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-check">
                                                    <input class="form-check-input permiso-checkbox" type="checkbox" id="permiso_seguridad_eliminar" data-modulo="seguridad" data-accion="eliminar">
                                                    <label class="form-check-label">Eliminar</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Reportes (con collapse) -->
                            <div class="card mb-3">
                                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center"
                                     data-bs-toggle="collapse" data-bs-target="#collapseReportes" style="cursor: pointer;">
                                    <span><strong>Reportes</strong></span>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="form-check form-switch" onclick="event.stopPropagation()">
                                            <input class="form-check-input" type="checkbox" id="permiso_reportes_mostrar" data-modulo="reportes" data-accion="mostrar">
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
                                                    <input class="form-check-input permiso-checkbox" type="checkbox" id="permiso_reportes_compras_cliente" data-modulo="reportes" data-accion="compras_cliente">
                                                    <label class="form-check-label">Compras por Cliente</label>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="form-check">
                                                    <input class="form-check-input permiso-checkbox" type="checkbox" id="permiso_reportes_frecuencia_compra" data-modulo="reportes" data-accion="frecuencia_compra">
                                                    <label class="form-check-label">Frecuencia de Compra</label>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="form-check">
                                                    <input class="form-check-input permiso-checkbox" type="checkbox" id="permiso_reportes_montos_promedio" data-modulo="reportes" data-accion="montos_promedio">
                                                    <label class="form-check-label">Montos Promedio</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-4">
                                                <div class="form-check">
                                                    <input class="form-check-input permiso-checkbox" type="checkbox" id="permiso_reportes_sucursales_preferidas" data-modulo="reportes" data-accion="sucursales_preferidas">
                                                    <label class="form-check-label">Sucursales Preferidas</label>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="form-check">
                                                    <input class="form-check-input permiso-checkbox" type="checkbox" id="permiso_reportes_cotizaciones_cliente" data-modulo="reportes" data-accion="cotizaciones_cliente">
                                                    <label class="form-check-label">Cotizaciones por Cliente</label>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="form-check">
                                                    <input class="form-check-input permiso-checkbox" type="checkbox" id="permiso_reportes_cotizaciones_concretadas" data-modulo="reportes" data-accion="cotizaciones_concretadas">
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
            
            // Formatear fecha de nacimiento
            document.getElementById('edit_fecha_nacimiento').value = formatearFecha(data.data.fecha_nacimiento);
            
            document.getElementById('edit_Activo').value = data.data.Activo ? '1' : '0';
            document.getElementById('edit_sucursal_asignada').value = data.data.sucursal_asignada || '';
            
            // Cargar permisos
            const permisos = data.data.permisos_modulos || {};
            
            // Función auxiliar para establecer valor de checkbox
            const setCheckbox = (id, valor) => {
                const checkbox = document.getElementById(id);
                if (checkbox) checkbox.checked = valor === true;
            };
            
            // Clientes
            setCheckbox('permiso_clientes_mostrar', permisos.clientes?.mostrar);
            setCheckbox('permiso_clientes_ver', permisos.clientes?.ver);
            setCheckbox('permiso_clientes_enfermedades', permisos.clientes?.enfermedades);
            setCheckbox('permiso_clientes_intereses', permisos.clientes?.intereses);
            setCheckbox('permiso_clientes_altas', permisos.clientes?.altas);
            setCheckbox('permiso_clientes_edicion', permisos.clientes?.edicion);
            setCheckbox('permiso_clientes_eliminar', permisos.clientes?.eliminar);
            
            // Ventas
            setCheckbox('permiso_ventas_mostrar', permisos.ventas?.mostrar);
            setCheckbox('permiso_cotizaciones_ver', permisos.cotizaciones?.ver);
            setCheckbox('permiso_pedidos_anticipo_ver', permisos.pedidos_anticipo?.ver);
            setCheckbox('permiso_seguimiento_ventas_ver', permisos.seguimiento_ventas?.ver);
            setCheckbox('permiso_seguimiento_cotizaciones_ver', permisos.seguimiento_cotizaciones?.ver);
            setCheckbox('permiso_agenda_contactos_ver', permisos.agenda_contactos?.ver);
            
            // Seguridad
            setCheckbox('permiso_seguridad_mostrar', permisos.seguridad?.mostrar);
            setCheckbox('permiso_seguridad_ver', permisos.seguridad?.ver);
            setCheckbox('permiso_seguridad_permisos', permisos.seguridad?.permisos);
            setCheckbox('permiso_seguridad_respaldos', permisos.seguridad?.respaldos);
            setCheckbox('permiso_seguridad_altas', permisos.seguridad?.altas);
            setCheckbox('permiso_seguridad_edicion', permisos.seguridad?.edicion);
            setCheckbox('permiso_seguridad_eliminar', permisos.seguridad?.eliminar);
            
            // Reportes
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

// Función para guardar edición de usuario
window.guardarEdicionUsuario = function() {
    const id = document.getElementById('edit_usuario_id').value;
    
    // Construir objeto de permisos
    const permisos = {
        clientes: {
            mostrar: document.getElementById('permiso_clientes_mostrar')?.checked || false,
            ver: document.getElementById('permiso_clientes_ver')?.checked || false,
            enfermedades: document.getElementById('permiso_clientes_enfermedades')?.checked || false,
            intereses: document.getElementById('permiso_clientes_intereses')?.checked || false,
            altas: document.getElementById('permiso_clientes_altas')?.checked || false,
            edicion: document.getElementById('permiso_clientes_edicion')?.checked || false,
            eliminar: document.getElementById('permiso_clientes_eliminar')?.checked || false
        },
        ventas: {
            mostrar: document.getElementById('permiso_ventas_mostrar')?.checked || false
        },
        cotizaciones: {
            ver: document.getElementById('permiso_cotizaciones_ver')?.checked || false
        },
        pedidos_anticipo: {
            ver: document.getElementById('permiso_pedidos_anticipo_ver')?.checked || false
        },
        seguimiento_ventas: {
            ver: document.getElementById('permiso_seguimiento_ventas_ver')?.checked || false
        },
        seguimiento_cotizaciones: {
            ver: document.getElementById('permiso_seguimiento_cotizaciones_ver')?.checked || false
        },
        agenda_contactos: {
            ver: document.getElementById('permiso_agenda_contactos_ver')?.checked || false
        },
        seguridad: {
            mostrar: document.getElementById('permiso_seguridad_mostrar')?.checked || false,
            ver: document.getElementById('permiso_seguridad_ver')?.checked || false,
            permisos: document.getElementById('permiso_seguridad_permisos')?.checked || false,
            respaldos: document.getElementById('permiso_seguridad_respaldos')?.checked || false,
            altas: document.getElementById('permiso_seguridad_altas')?.checked || false,
            edicion: document.getElementById('permiso_seguridad_edicion')?.checked || false,
            eliminar: document.getElementById('permiso_seguridad_eliminar')?.checked || false
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