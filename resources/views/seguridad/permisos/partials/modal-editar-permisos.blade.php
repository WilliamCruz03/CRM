<!-- Modal Editar Permisos -->
<div class="modal fade" id="modalEditarPermisos" tabindex="-1" aria-labelledby="modalEditarPermisosLabel">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalEditarPermisosLabel">
                    <i class="bi bi-shield-lock"></i> Editar Permisos: <span id="modalPermisosUsuarioNombre">Cargando...</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarPermisos">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_permisos_usuario_id" name="usuario_id">
                    
                    <!-- Información del usuario -->
                    <div class="alert alert-info mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-person-circle me-2"></i>
                                <strong id="modalPermisosUsuarioInfo">Cargando...</strong>
                            </div>
                            <div>
                                <span class="badge bg-secondary" id="modalPermisosEstadoRepartidor">
                                    <i class="bi bi-truck"></i> Cargando...
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- SECCIÓN DASHBOARD - Cards visibles -->
                    <!-- ============================================ -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center" style="cursor: pointer;">
                            <h5 class="mb-0">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard - Cards Visibles
                            </h5>
                            <i class="bi bi-chevron-down collapse-icon" style="transition: transform 0.3s ease;"></i>
                        </div>
                        <div class="collapse" id="collapseDashboardPermisos">
                            <div class="card-body">
                                <p class="text-muted small mb-3">
                                    <i class="bi bi-info-circle"></i> Selecciona qué cards aparecerán en el dashboard del usuario.
                                    Cada card es independiente y no afecta los permisos de acceso a los módulos.
                                </p>
                                
                                <div class="row">
                                    <!-- Cards KPI Clientes -->
                                    <div class="col-12 mb-3">
                                        <h6 class="border-bottom pb-2"><i class="bi bi-bar-chart"></i> KPI - Clientes</h6>
                                        <div class="row">
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" name="dashboard_cards[]" value="kpi_total_clientes" id="card_kpi_total_clientes">
                                                    <label class="form-check-label" for="card_kpi_total_clientes">
                                                        <i class="bi bi-people-fill text-primary me-1"></i>
                                                        <strong>Total Clientes</strong>
                                                        <br><small class="text-muted">Muestra el total de clientes activos</small>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" name="dashboard_cards[]" value="kpi_contactos_proximos" id="card_kpi_contactos_proximos">
                                                    <label class="form-check-label" for="card_kpi_contactos_proximos">
                                                        <i class="bi bi-calendar-check-fill text-info me-1"></i>
                                                        <strong>Contactos Próximos</strong>
                                                        <br><small class="text-muted">Muestra contactos programados en los próximos 7 días</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Cards KPI Cotizaciones -->
                                    <div class="col-12 mb-3">
                                        <h6 class="border-bottom pb-2"><i class="bi bi-graph-up"></i> KPI - Cotizaciones</h6>
                                        <div class="row">
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" name="dashboard_cards[]" value="kpi_total_cotizaciones" id="card_kpi_total_cotizaciones">
                                                    <label class="form-check-label" for="card_kpi_total_cotizaciones">
                                                        <i class="bi bi-file-earmark-text-fill text-success me-1"></i>
                                                        <strong>Total Cotizaciones</strong>
                                                        <br><small class="text-muted">Muestra el total de cotizaciones</small>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" name="dashboard_cards[]" value="kpi_cotizaciones_pendientes" id="card_kpi_cotizaciones_pendientes">
                                                    <label class="form-check-label" for="card_kpi_cotizaciones_pendientes">
                                                        <i class="bi bi-hourglass-split text-warning me-1"></i>
                                                        <strong>Cotizaciones Pendientes</strong>
                                                        <br><small class="text-muted">Muestra cotizaciones en proceso</small>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" name="dashboard_cards[]" value="kpi_monto_total_mes" id="card_kpi_monto_total_mes">
                                                    <label class="form-check-label" for="card_kpi_monto_total_mes">
                                                        <i class="bi bi-currency-dollar text-success me-1"></i>
                                                        <strong>Monto Total del Mes</strong>
                                                        <br><small class="text-muted">Muestra el monto total del mes (cotizaciones)</small>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" name="dashboard_cards[]" value="resumen_ventas_mensual" id="card_resumen_ventas_mensual">
                                                    <label class="form-check-label" for="card_resumen_ventas_mensual">
                                                        <i class="bi bi-graph-up-arrow text-success me-1"></i>
                                                        <strong>Resumen de Ventas Mensual</strong>
                                                        <br><small class="text-muted">Muestra ventas totales (historial_ventas_matriz)</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Cards Gráficos -->
                                    <div class="col-12 mb-3">
                                        <h6 class="border-bottom pb-2"><i class="bi bi-pie-chart"></i> Gráficos</h6>
                                        <div class="row">
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" name="dashboard_cards[]" value="grafico_estados_cotizaciones" id="card_grafico_estados_cotizaciones">
                                                    <label class="form-check-label" for="card_grafico_estados_cotizaciones">
                                                        <i class="bi bi-pie-chart-fill text-primary me-1"></i>
                                                        <strong>Estados de Cotizaciones</strong>
                                                        <br><small class="text-muted">Muestra gráfico de estados (aceptadas, pendientes, rechazadas)</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Cards Tablas -->
                                    <div class="col-12 mb-3">
                                        <h6 class="border-bottom pb-2"><i class="bi bi-table"></i> Tablas Recientes</h6>
                                        <div class="row">
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" name="dashboard_cards[]" value="tabla_ultimos_contactos" id="card_tabla_ultimos_contactos">
                                                    <label class="form-check-label" for="card_tabla_ultimos_contactos">
                                                        <i class="bi bi-clock-history text-info me-1"></i>
                                                        <strong>Últimos Contactos</strong>
                                                        <br><small class="text-muted">Lista de los últimos contactos agendados</small>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" name="dashboard_cards[]" value="tabla_ultimas_cotizaciones" id="card_tabla_ultimas_cotizaciones">
                                                    <label class="form-check-label" for="card_tabla_ultimas_cotizaciones">
                                                        <i class="bi bi-file-earmark-text me-1"></i>
                                                        <strong>Últimas Cotizaciones</strong>
                                                        <br><small class="text-muted">Muestra las últimas cotizaciones creadas</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Cards Resumen -->
                                    <div class="col-12 mb-3">
                                        <h6 class="border-bottom pb-2"><i class="bi bi-star"></i> Resumen Rápido</h6>
                                        <div class="row">
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" name="dashboard_cards[]" value="resumen_rapido" id="card_resumen_rapido">
                                                    <label class="form-check-label" for="card_resumen_rapido">
                                                        <i class="bi bi-trophy-fill text-warning me-1"></i>
                                                        <strong>Resumen Rápido</strong>
                                                        <br><small class="text-muted">Muestra cliente top, ticket promedio, frecuencia y conversión</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ============================================ -->
                    <!-- CLIENTES -->
                    <!-- ============================================ -->
                    <div class="card mb-3">
                        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center" style="cursor: pointer;">
                            <span><strong><i class="bi bi-card-checklist"></i> Clientes</strong></span>
                            <i class="bi bi-chevron-down collapse-icon" style="transition: transform 0.3s ease;"></i>
                        </div>
                        <div class="collapse show" id="collapseClientesPermisos">
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
                        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center" style="cursor: pointer;">
                            <span><strong><i class="bi bi-graph-up"></i> Ventas</strong></span>
                            <i class="bi bi-chevron-down collapse-icon" style="transition: transform 0.3s ease;"></i>
                        </div>
                        <div class="collapse show" id="collapseVentasPermisos">
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
                    <!-- PERFILES DEL USUARIO (CRM, SUCURSAL, REPARTIDOR) -->
                    <!-- ============================================ -->
                    <div class="card mt-3">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="bi bi-person-badge"></i> Perfiles del Usuario</h6>
                            <small class="text-white-50">Define el perfil principal del usuario para controlar acciones en pedidos</small>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="perfil_crm" 
                                            name="permisos_modulos[perfiles][es_crm]" value="1">
                                        <label class="form-check-label" for="perfil_crm">
                                            <i class="bi bi-star text-primary"></i> <strong>CRM</strong>
                                            <small class="d-block text-muted">
                                                <i class="bi bi-person-plus text-success"></i> Asignar repartidores<br>
                                                <i class="bi bi-eye text-success"></i> Ver todos los pedidos
                                            </small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="perfil_sucursal" 
                                            name="permisos_modulos[perfiles][es_sucursal]" value="1">
                                        <label class="form-check-label" for="perfil_sucursal">
                                            <i class="bi bi-building text-success"></i> <strong>Sucursal</strong>
                                            <small class="d-block text-muted">
                                                <i class="bi bi-check-circle text-success"></i> Marcar como listo<br>
                                                <i class="bi bi-eye text-success"></i> Ver pedidos de su sucursal
                                            </small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="perfil_repartidor" 
                                            name="permisos_modulos[perfiles][es_repartidor]" value="1">
                                        <label class="form-check-label" for="perfil_repartidor">
                                            <i class="bi bi-truck text-warning"></i> <strong>Repartidor</strong>
                                            <small class="d-block text-muted">
                                                <i class="bi bi-truck text-success"></i> Iniciar recorrido<br>
                                                <i class="bi bi-eye text-success"></i> Ver pedidos asignados<br>
                                                <i class="bi bi-exclamation-triangle text-warning"></i> <span class="text-danger">Requiere horario en RH</span>
                                            </small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> 
                                    <strong>Nota:</strong> Un usuario puede tener múltiples perfiles. 
                                    Por ejemplo, un CRM también puede ser Sucursal para gestionar pedidos de su sucursal.
                                </small>
                            </div>
                            <div id="perfilesInfoPermisos" class="mt-2"></div>
                        </div>
                    </div>
                    
                    <!-- ============================================ -->
                    <!-- SEGURIDAD -->
                    <!-- ============================================ -->
                    <div class="card mb-3">
                        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center" style="cursor: pointer;">
                            <span><strong><i class="bi bi-lock"></i> Seguridad</strong></span>
                            <i class="bi bi-chevron-down collapse-icon" style="transition: transform 0.3s ease;"></i>
                        </div>
                        <div class="collapse show" id="collapseSeguridadPermisos">
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
                                
                                <!-- Permisos del Usuario -->
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
                                        <div class="col-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="permiso_seguridad_permisos_editar">
                                                <label class="form-check-label">Editar</label>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="permiso_seguridad_permisos_eliminar">
                                                <label class="form-check-label">Eliminar</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Respaldos -->
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
                                        <div class="col-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="permiso_seguridad_respaldos_crear">
                                                <label class="form-check-label">Crear (Generar)</label>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="permiso_seguridad_respaldos_editar">
                                                <label class="form-check-label">Descargar</label>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="permiso_seguridad_respaldos_eliminar">
                                                <label class="form-check-label">Eliminar</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ============================================ -->
                    <!-- REPORTES -->
                    <!-- ============================================ -->
                    <div class="card mb-3">
                        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center" style="cursor: pointer;">
                            <span><strong><i class="bi bi-clipboard2-data"></i> Reportes</strong></span>
                            <i class="bi bi-chevron-down collapse-icon" style="transition: transform 0.3s ease;"></i>
                        </div>
                        <div class="collapse show" id="collapseReportesPermisos">
                            <div class="card-body py-2">
                                <div class="row">
                                    <div class="col-4 mb-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>Compras por Cliente</span>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="permiso_reportes_compras_cliente_mostrar">
                                                <label class="form-check-label small">Activo</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4 mb-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>Montos Promedio</span>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="permiso_reportes_montos_promedio_mostrar">
                                                <label class="form-check-label small">Activo</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4 mb-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>Sucursales Preferidas</span>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="permiso_reportes_sucursales_preferidas_mostrar">
                                                <label class="form-check-label small">Activo</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4 mb-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>Cotizaciones por Cliente</span>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="permiso_reportes_cotizaciones_cliente_mostrar">
                                                <label class="form-check-label small">Activo</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4 mb-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>Pedidos por Cliente</span>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="permiso_reportes_pedidos_cliente_mostrar">
                                                <label class="form-check-label small">Activo</label>
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
                <button type="button" class="btn btn-primary" onclick="guardarEdicionPermisos()">
                    <i class="bi bi-save"></i> Guardar permisos
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ============================================
// VARIABLES GLOBALES
// ============================================
let loadingPermisos = false;

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

/**
 * Configura la dependencia para el checkbox "Mostrar/Ocultar"
 */
function setupMostrarDependencia(modulo, submodulo) {
    const checkboxMostrar = document.getElementById(`permiso_${modulo}_${submodulo}_mostrar`);
    const checkboxVer = document.getElementById(`permiso_${modulo}_${submodulo}_ver`);
    const checkboxCrear = document.getElementById(`permiso_${modulo}_${submodulo}_crear`);
    const checkboxEditar = document.getElementById(`permiso_${modulo}_${submodulo}_editar`);
    const checkboxEliminar = document.getElementById(`permiso_${modulo}_${submodulo}_eliminar`);
    
    function tieneAlgunaAccionActiva() {
        return (checkboxVer?.checked || false) ||
               (checkboxCrear?.checked || false) ||
               (checkboxEditar?.checked || false) ||
               (checkboxEliminar?.checked || false);
    }
    
    function actualizarMostrar() {
        if (checkboxMostrar) {
            const algunaAccion = tieneAlgunaAccionActiva();
            checkboxMostrar.checked = algunaAccion;
        }
    }
    
    function onMostrarChange() {
        if (!checkboxMostrar.checked) {
            if (checkboxVer) checkboxVer.checked = false;
            if (checkboxCrear) checkboxCrear.checked = false;
            if (checkboxEditar) checkboxEditar.checked = false;
            if (checkboxEliminar) checkboxEliminar.checked = false;
        }
    }
    
    if (checkboxVer) checkboxVer.addEventListener('change', actualizarMostrar);
    if (checkboxCrear) checkboxCrear.addEventListener('change', actualizarMostrar);
    if (checkboxEditar) checkboxEditar.addEventListener('change', actualizarMostrar);
    if (checkboxEliminar) checkboxEliminar.addEventListener('change', actualizarMostrar);
    
    if (checkboxMostrar) {
        checkboxMostrar.addEventListener('change', onMostrarChange);
        actualizarMostrar();
    }
}

// ============================================
// ACTUALIZAR INFORMACIÓN DE PERFILES
// ============================================
function actualizarInfoPerfilesPermisos(perfiles) {
    const infoContainer = document.getElementById('perfilesInfoPermisos');
    if (!infoContainer) return;
    
    const esCRM = perfiles.es_crm || false;
    const esSucursal = perfiles.es_sucursal || false;
    const esRepartidor = perfiles.es_repartidor || false;
    
    let html = '<div class="mt-2"><small class="text-muted">Perfiles asignados:</small> ';
    
    if (esCRM) {
        html += `<span class="badge bg-primary me-1"><i class="bi bi-star"></i> CRM</span>`;
    }
    if (esSucursal) {
        html += `<span class="badge bg-success me-1"><i class="bi bi-building"></i> Sucursal</span>`;
    }
    if (esRepartidor) {
        html += `<span class="badge bg-warning me-1"><i class="bi bi-truck"></i> Repartidor</span>`;
    }
    if (!esCRM && !esSucursal && !esRepartidor) {
        html += `<span class="badge bg-secondary">Sin perfiles asignados</span>`;
    }
    
    html += '</div>';
    
    html += '<div class="mt-1">';
    html += '<small class="text-muted">';
    
    if (esCRM) {
        html += '<i class="bi bi-check-circle text-success"></i> CRM: Asignar repartidores, ver todos los pedidos<br>';
    }
    if (esSucursal) {
        html += '<i class="bi bi-check-circle text-success"></i> Sucursal: Marcar como listo, ver pedidos de su sucursal<br>';
    }
    if (esRepartidor) {
        html += '<i class="bi bi-check-circle text-success"></i> Repartidor: Iniciar recorrido, ver pedidos asignados <span class="text-danger">(requiere horario en RH)</span><br>';
    }
    
    html += '</small></div>';
    
    infoContainer.innerHTML = html;
}

// ============================================
// CONTROLAR ESTADO DE COLLAPSES
// ============================================
function controlarEstadoModulosSegunPermisos(permisos) {
    const tienePermisosClientes = verificarPermisosModulo(permisos.clientes);
    const tienePermisosVentas = verificarPermisosModulo(permisos.ventas);
    const tienePermisosSeguridad = verificarPermisosModulo(permisos.seguridad);
    const tienePermisosReportes = verificarPermisosModulo(permisos.reportes);
    
    setCollapseState('collapseClientesPermisos', tienePermisosClientes);
    setCollapseState('collapseVentasPermisos', tienePermisosVentas);
    setCollapseState('collapseSeguridadPermisos', tienePermisosSeguridad);
    setCollapseState('collapseReportesPermisos', tienePermisosReportes);
}

function verificarPermisosModulo(modulo) {
    if (!modulo) return false;
    
    for (const submodulo in modulo) {
        const permisosSub = modulo[submodulo];
        if (permisosSub.mostrar || permisosSub.ver || permisosSub.crear || permisosSub.editar || permisosSub.eliminar) {
            return true;
        }
    }
    return false;
}

function setCollapseState(collapseId, shouldShow) {
    const collapseElement = document.getElementById(collapseId);
    const header = document.querySelector(`.card-header:has(+ #${collapseId})`);
    const icon = header?.querySelector('.collapse-icon');
    
    if (collapseElement) {
        if (shouldShow) {
            collapseElement.classList.add('show');
            if (icon) icon.style.transform = 'rotate(180deg)';
        } else {
            collapseElement.classList.remove('show');
            if (icon) icon.style.transform = 'rotate(0deg)';
        }
    }
}

function controlarEstadoDashboardCollapsePermisos() {
    const dashboardHeader = document.querySelector('#collapseDashboardPermisos')?.closest('.card')?.querySelector('.card-header');
    const dashboardCollapse = document.getElementById('collapseDashboardPermisos');
    const icon = dashboardHeader?.querySelector('.collapse-icon');
    
    if (dashboardCollapse && dashboardHeader) {
        const hayCardsSeleccionados = document.querySelectorAll('input[name="dashboard_cards[]"]:checked').length > 0;
        
        if (hayCardsSeleccionados) {
            dashboardCollapse.classList.add('show');
            if (icon) icon.style.transform = 'rotate(180deg)';
        } else {
            dashboardCollapse.classList.remove('show');
            if (icon) icon.style.transform = 'rotate(0deg)';
        }
    }
}

// ============================================
// CARGA DE DATOS DE PERMISOS
// ============================================
function cargarDatosPermisos(id) {
    if (loadingPermisos) return;
    
    id = parseInt(id);
    if (isNaN(id)) {
        console.error('ID inválido:', id);
        return;
    }
    
    loadingPermisos = true;
    
    fetch(`/seguridad/permisos/${id}/edit`, {
        headers: { 
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // ID del usuario
            document.getElementById('edit_permisos_usuario_id').value = data.data.id;
            
            // Información del usuario
            document.getElementById('modalPermisosUsuarioNombre').textContent = data.data.nombre;
            document.getElementById('modalPermisosUsuarioInfo').textContent = `${data.data.nombre} (${data.data.usuario})`;
            
            // Estado de repartidor
            const estadoRepartidor = document.getElementById('modalPermisosEstadoRepartidor');
            if (data.data.es_repartidor && data.data.tiene_horario) {
                estadoRepartidor.innerHTML = '<i class="bi bi-check-circle"></i> Repartidor (con horario)';
                estadoRepartidor.className = 'badge bg-success';
            } else if (data.data.es_repartidor && !data.data.tiene_horario) {
                estadoRepartidor.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Repartidor (sin horario)';
                estadoRepartidor.className = 'badge bg-warning';
            } else {
                estadoRepartidor.innerHTML = '<i class="bi bi-x-circle"></i> No es repartidor';
                estadoRepartidor.className = 'badge bg-secondary';
            }
            
            // Cargar permisos
            const permisos = data.permisos || {};
            
            // Función auxiliar para establecer checkbox
            const setCheckbox = (id, valor) => {
                const checkbox = document.getElementById(id);
                if (checkbox) {
                    checkbox.checked = valor === true;
                }
            };
            
            // ============================================
            // CLIENTES
            // ============================================
            setCheckbox('permiso_clientes_directorio_mostrar', permisos.clientes?.directorio?.mostrar);
            setCheckbox('permiso_clientes_directorio_ver', permisos.clientes?.directorio?.ver);
            setCheckbox('permiso_clientes_directorio_crear', permisos.clientes?.directorio?.crear);
            setCheckbox('permiso_clientes_directorio_editar', permisos.clientes?.directorio?.editar);
            setCheckbox('permiso_clientes_directorio_eliminar', permisos.clientes?.directorio?.eliminar);
            
            setCheckbox('permiso_clientes_enfermedades_mostrar', permisos.clientes?.enfermedades?.mostrar);
            setCheckbox('permiso_clientes_enfermedades_ver', permisos.clientes?.enfermedades?.ver);
            setCheckbox('permiso_clientes_enfermedades_crear', permisos.clientes?.enfermedades?.crear);
            setCheckbox('permiso_clientes_enfermedades_editar', permisos.clientes?.enfermedades?.editar);
            setCheckbox('permiso_clientes_enfermedades_eliminar', permisos.clientes?.enfermedades?.eliminar);
            
            setCheckbox('permiso_clientes_intereses_mostrar', permisos.clientes?.intereses?.mostrar);
            setCheckbox('permiso_clientes_intereses_ver', permisos.clientes?.intereses?.ver);
            setCheckbox('permiso_clientes_intereses_crear', permisos.clientes?.intereses?.crear);
            setCheckbox('permiso_clientes_intereses_editar', permisos.clientes?.intereses?.editar);
            setCheckbox('permiso_clientes_intereses_eliminar', permisos.clientes?.intereses?.eliminar);
            
            // ============================================
            // VENTAS
            // ============================================
            setCheckbox('permiso_ventas_cotizaciones_mostrar', permisos.ventas?.cotizaciones?.mostrar);
            setCheckbox('permiso_ventas_cotizaciones_ver', permisos.ventas?.cotizaciones?.ver);
            setCheckbox('permiso_ventas_cotizaciones_crear', permisos.ventas?.cotizaciones?.crear);
            setCheckbox('permiso_ventas_cotizaciones_editar', permisos.ventas?.cotizaciones?.editar);
            setCheckbox('permiso_ventas_cotizaciones_eliminar', permisos.ventas?.cotizaciones?.eliminar);
            
            setCheckbox('permiso_ventas_pedidos_anticipo_mostrar', permisos.ventas?.pedidos_anticipo?.mostrar);
            setCheckbox('permiso_ventas_pedidos_anticipo_ver', permisos.ventas?.pedidos_anticipo?.ver);
            setCheckbox('permiso_ventas_pedidos_anticipo_crear', permisos.ventas?.pedidos_anticipo?.crear);
            setCheckbox('permiso_ventas_pedidos_anticipo_editar', permisos.ventas?.pedidos_anticipo?.editar);
            setCheckbox('permiso_ventas_pedidos_anticipo_eliminar', permisos.ventas?.pedidos_anticipo?.eliminar);
            
            setCheckbox('permiso_ventas_agenda_contactos_mostrar', permisos.ventas?.agenda_contactos?.mostrar);
            setCheckbox('permiso_ventas_agenda_contactos_ver', permisos.ventas?.agenda_contactos?.ver);
            setCheckbox('permiso_ventas_agenda_contactos_crear', permisos.ventas?.agenda_contactos?.crear);
            setCheckbox('permiso_ventas_agenda_contactos_editar', permisos.ventas?.agenda_contactos?.editar);
            setCheckbox('permiso_ventas_agenda_contactos_eliminar', permisos.ventas?.agenda_contactos?.eliminar);
            
            // ============================================
            // SEGURIDAD
            // ============================================
            setCheckbox('permiso_seguridad_usuarios_mostrar', permisos.seguridad?.usuarios?.mostrar);
            setCheckbox('permiso_seguridad_usuarios_ver', permisos.seguridad?.usuarios?.ver);
            setCheckbox('permiso_seguridad_usuarios_crear', permisos.seguridad?.usuarios?.crear);
            setCheckbox('permiso_seguridad_usuarios_editar', permisos.seguridad?.usuarios?.editar);
            setCheckbox('permiso_seguridad_usuarios_eliminar', permisos.seguridad?.usuarios?.eliminar);
            
            // ============================================
            // PERMISOS
            // ============================================
            setCheckbox('permiso_seguridad_permisos_mostrar', permisos.seguridad?.permisos?.mostrar);
            setCheckbox('permiso_seguridad_permisos_ver', permisos.seguridad?.permisos?.ver);
            setCheckbox('permiso_seguridad_permisos_editar', permisos.seguridad?.permisos?.editar);
            setCheckbox('permiso_seguridad_permisos_eliminar', permisos.seguridad?.permisos?.eliminar);
            
            setCheckbox('permiso_seguridad_respaldos_mostrar', permisos.seguridad?.respaldos?.mostrar);
            setCheckbox('permiso_seguridad_respaldos_ver', permisos.seguridad?.respaldos?.ver);
            setCheckbox('permiso_seguridad_respaldos_crear', permisos.seguridad?.respaldos?.crear);
            setCheckbox('permiso_seguridad_respaldos_editar', permisos.seguridad?.respaldos?.editar);
            setCheckbox('permiso_seguridad_respaldos_eliminar', permisos.seguridad?.respaldos?.eliminar);

            // ============================================
            // REPORTES
            // ============================================
            setCheckbox('permiso_reportes_compras_cliente_mostrar', permisos.reportes?.compras_cliente?.mostrar);
            setCheckbox('permiso_reportes_montos_promedio_mostrar', permisos.reportes?.montos_promedio?.mostrar);
            setCheckbox('permiso_reportes_sucursales_preferidas_mostrar', permisos.reportes?.sucursales_preferidas?.mostrar);
            setCheckbox('permiso_reportes_cotizaciones_cliente_mostrar', permisos.reportes?.cotizaciones_cliente?.mostrar);
            setCheckbox('permiso_reportes_pedidos_cliente_mostrar', permisos.reportes?.pedidos_cliente?.mostrar);

            // ============================================
            // PERFILES
            // ============================================
            const perfiles = permisos.perfiles || {};
            setCheckbox('perfil_crm', perfiles.es_crm);
            setCheckbox('perfil_sucursal', perfiles.es_sucursal);
            setCheckbox('perfil_repartidor', perfiles.es_repartidor);
            
            actualizarInfoPerfilesPermisos(perfiles);

            // ============================================
            // DASHBOARD CARDS
            // ============================================
            document.querySelectorAll('input[name="dashboard_cards[]"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            
            if (data.dashboard_cards && Array.isArray(data.dashboard_cards)) {
                data.dashboard_cards.forEach(cardKey => {
                    const checkbox = document.querySelector(`input[name="dashboard_cards[]"][value="${cardKey}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
            }

            controlarEstadoModulosSegunPermisos(permisos);
            //controlarEstadoDashboardCollapsePermisos();
            
            // Configurar eventos de colapso manual
            inicializarCollapseManualPermisos();
            
        } else {
            console.error('Error en la respuesta:', data);
            if (window.mostrarToast) window.mostrarToast('Error al cargar datos del usuario', 'danger');
        }
        loadingPermisos = false;
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
        loadingPermisos = false;
    });
}

// ============================================
// EVENTOS PARA COLLAPSE CON ÍCONOS GIRATORIOS
// ============================================
function inicializarCollapseManualPermisos() {
    document.querySelectorAll('#modalEditarPermisos .card-header').forEach(header => {
        header.removeEventListener('click', toggleCollapseManualPermisos);
        header.addEventListener('click', toggleCollapseManualPermisos);
        
        const targetElement = header.nextElementSibling;
        const icon = header.querySelector('.collapse-icon');
        
        if (targetElement && targetElement.classList.contains('collapse') && icon) {
            if (targetElement.classList.contains('show')) {
                icon.style.transform = 'rotate(180deg)';
            } else {
                icon.style.transform = 'rotate(0deg)';
            }
        }
    });
}

function toggleCollapseManualPermisos(event) {
    const header = event.currentTarget;
    const targetElement = header.nextElementSibling;
    const icon = header.querySelector('.collapse-icon');
    
    if (targetElement && targetElement.classList.contains('collapse')) {
        if (targetElement.classList.contains('show')) {
            targetElement.classList.remove('show');
            if (icon) icon.style.transform = 'rotate(0deg)';
        } else {
            targetElement.classList.add('show');
            if (icon) icon.style.transform = 'rotate(180deg)';
        }
    }
}

// ============================================
// GUARDAR EDICIÓN DE PERMISOS
// ============================================
window.guardarEdicionPermisos = function() {
    const id = document.getElementById('edit_permisos_usuario_id').value;
    
    if (!id) {
        if (window.mostrarToast) window.mostrarToast('Error: No se encontró el ID del usuario', 'danger');
        return;
    }
    
    // VALIDAR QUE AL MENOS UN PERFIL ESTÉ SELECCIONADO
    const esCRM = document.getElementById('perfil_crm')?.checked || false;
    const esSucursal = document.getElementById('perfil_sucursal')?.checked || false;
    const esRepartidor = document.getElementById('perfil_repartidor')?.checked || false;
    
    if (!esCRM && !esSucursal && !esRepartidor) {
        if (window.mostrarToast) {
            window.mostrarToast('Debes seleccionar al menos un perfil (CRM, Sucursal o Repartidor)', 'warning');
        }
        return;
    }
    
    // Construir objeto de permisos
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
                ver: document.getElementById('permiso_seguridad_permisos_ver')?.checked || false,
                editar: document.getElementById('permiso_seguridad_permisos_editar')?.checked || false,
                eliminar: document.getElementById('permiso_seguridad_permisos_eliminar')?.checked || false
            },
            respaldos: {
                mostrar: document.getElementById('permiso_seguridad_respaldos_mostrar')?.checked || false,
                ver: document.getElementById('permiso_seguridad_respaldos_ver')?.checked || false,
                crear: document.getElementById('permiso_seguridad_respaldos_crear')?.checked || false,
                editar: document.getElementById('permiso_seguridad_respaldos_editar')?.checked || false,
                eliminar: document.getElementById('permiso_seguridad_respaldos_eliminar')?.checked || false
            }
        },
        reportes: {
            compras_cliente: {
                mostrar: document.getElementById('permiso_reportes_compras_cliente_mostrar')?.checked || false,
                ver: document.getElementById('permiso_reportes_compras_cliente_mostrar')?.checked || false
            },
            montos_promedio: {
                mostrar: document.getElementById('permiso_reportes_montos_promedio_mostrar')?.checked || false,
                ver: document.getElementById('permiso_reportes_montos_promedio_mostrar')?.checked || false
            },
            sucursales_preferidas: {
                mostrar: document.getElementById('permiso_reportes_sucursales_preferidas_mostrar')?.checked || false,
                ver: document.getElementById('permiso_reportes_sucursales_preferidas_mostrar')?.checked || false
            },
            cotizaciones_cliente: {
                mostrar: document.getElementById('permiso_reportes_cotizaciones_cliente_mostrar')?.checked || false,
                ver: document.getElementById('permiso_reportes_cotizaciones_cliente_mostrar')?.checked || false
            },
            pedidos_cliente: {
                mostrar: document.getElementById('permiso_reportes_pedidos_cliente_mostrar')?.checked || false,
                ver: document.getElementById('permiso_reportes_pedidos_cliente_mostrar')?.checked || false
            }
        }
    };

    // PERFILES
    permisos.perfiles = {
        es_crm: document.getElementById('perfil_crm')?.checked || false,
        es_sucursal: document.getElementById('perfil_sucursal')?.checked || false,
        es_repartidor: document.getElementById('perfil_repartidor')?.checked || false
    };

    // Dashboard cards
    const dashboardCards = [];
    document.querySelectorAll('input[name="dashboard_cards[]"]:checked').forEach(checkbox => {
        dashboardCards.push(checkbox.value);
    });

    const formData = {
        dashboard_cards: dashboardCards,
        permisos_modulos: permisos,
        _token: '{{ csrf_token() }}',
        _method: 'PUT'
    };

    const btn = document.querySelector('#modalEditarPermisos .btn-primary');
    const originalText = btn ? btn.innerHTML : 'Guardar';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';
    }

    fetch(`/seguridad/permisos/${id}`, {
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
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarPermisos'));
            if (modal) modal.hide();
            
            if (window.mostrarToast) window.mostrarToast('Permisos actualizados correctamente', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al actualizar', 'danger');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });
};

// ============================================
// CONFIGURACIÓN DE DEPENDENCIAS AL ABRIR EL MODAL
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalEditarPermisos');
    if (modal) {
        modal.addEventListener('show.bs.modal', function() {
            // Configurar dependencias después de que se carguen los datos
            setTimeout(() => {
                setupPermisoDependencia('clientes', 'directorio');
                setupPermisoDependencia('clientes', 'enfermedades');
                setupPermisoDependencia('clientes', 'intereses');
                setupDependenciaInversa('clientes', 'directorio');
                setupDependenciaInversa('clientes', 'enfermedades');
                setupDependenciaInversa('clientes', 'intereses');
                setupMostrarDependencia('clientes', 'directorio');
                setupMostrarDependencia('clientes', 'enfermedades');
                setupMostrarDependencia('clientes', 'intereses');

                setupPermisoDependencia('ventas', 'cotizaciones');
                setupPermisoDependencia('ventas', 'pedidos_anticipo');
                setupPermisoDependencia('ventas', 'agenda_contactos');
                setupDependenciaInversa('ventas', 'cotizaciones');
                setupDependenciaInversa('ventas', 'pedidos_anticipo');
                setupDependenciaInversa('ventas', 'agenda_contactos');
                setupMostrarDependencia('ventas', 'cotizaciones');
                setupMostrarDependencia('ventas', 'pedidos_anticipo');
                setupMostrarDependencia('ventas', 'agenda_contactos');

                setupPermisoDependencia('seguridad', 'usuarios');
                setupPermisoDependencia('seguridad', 'permisos');
                setupPermisoDependencia('seguridad', 'respaldos');
                setupDependenciaInversa('seguridad', 'usuarios');
                setupDependenciaInversa('seguridad', 'permisos');
                setupDependenciaInversa('seguridad', 'respaldos');
                setupMostrarDependencia('seguridad', 'usuarios');
                setupMostrarDependencia('seguridad', 'permisos');
                setupMostrarDependencia('seguridad', 'respaldos');
            }, 300);
        });
        
        modal.addEventListener('hidden.bs.modal', function() {
            loadingPermisos = false;
        });
    }
});

// Eventos para dashboard cards
//document.querySelectorAll('input[name="dashboard_cards[]"]').forEach(checkbox => {
//    checkbox.addEventListener('change', controlarEstadoDashboardCollapsePermisos);
//});
</script>
@endpush