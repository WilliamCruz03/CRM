@php
    // Obtener configuraciones UNA SOLA VEZ antes del ciclo
    $sucursalAsignada = $sucursalAsignada ?? 0;
    $esRepartidor = $esRepartidor ?? false;
    $permisos = $permisos ?? [];
    $puedeEditar = $permisos['editar'] ?? false;
    $puedeEliminar = $permisos['eliminar'] ?? false;
@endphp

<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-light">
            <tr>
                <th>Folio Pedido</th>
                <th>Cotización Origen</th>
                <th>Cliente</th>
                <th>Fecha y Hora</th>
                @if($sucursalAsignada == 0)
                    <th>Sucursales</th>
                @endif
                <th>Repartidor</th>
                @if(!$esRepartidor) {{-- Solo mostrar si NO es repartidor --}}
                    <th>Seguimiento</th>
                @endif
                <th>Status</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="pedidosTableBody">
            @forelse($pedidos as $pedido)
            <tr id="pedido-row-{{ $pedido->id_pedido }}" data-id-pedido="{{ $pedido->id_pedido }}">
                <td>
                    <span class="badge bg-primary">{{ $pedido->folio_pedido }}</span>
                </td>
                <td>
                    <span class="badge bg-secondary">{{ $pedido->cotizacion->folio ?? '-' }}</span>
                </td>
                <td>
                    <strong>{{ $pedido->cotizacion->nombre_cliente ?? '-' }}</strong>
                    @if($pedido->cotizacion->cliente)
                        <br><small class="text-muted">
                            <i class="bi bi-telephone"></i> {{ $pedido->cotizacion->cliente->telefono1 ?? '' }}
                        </small>
                    @endif
                </td>
                <td>
                    {{ $pedido->fecha_pedido ? $pedido->fecha_pedido->format('d/m/Y H:i') : '-' }}
                </td>
                
                @if($sucursalAsignada == 0)
                <td>
                    @php
                        $sucursalesPedido = $pedido->sucursales->pluck('sucursal.nombre')->implode(', ');
                    @endphp
                    <span class="badge bg-info">{{ $pedido->sucursales->count() }}</span>
                    <br><small class="text-muted">{{ Str::limit($sucursalesPedido, 50) }}</small>
                </td>
                @endif
                
                <td>
                    @if($pedido->repartidor)
                        {{ $pedido->repartidor->Nombre }} {{ $pedido->repartidor->apPaterno }} {{ $pedido->repartidor->apMaterno }}
                    @else
                        <span class="text-muted">Sin asignar</span>
                    @endif
                </td>

                @if(!$esRepartidor)
                    <td class="text-center">
                        @if(in_array($pedido->status, [2, 3]))
                        <button type="button" class="btn btn-sm btn-outline-primary btn-action"
                                onclick="abrirModalSeguimientoPedido({{ $pedido->id_pedido }}, '{{ $pedido->folio_pedido }}', {{ $pedido->status }})"
                                title="Registrar seguimiento">
                            <i class="bi bi-chat-dots"></i>
                        </button>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                @endif
                
                <!-- Status según tipo de usuario -->
                <td>
                    @if($esRepartidor)
                        {{-- Repartidor: ver estado del pedido para entrega --}}
                        @if($pedido->status == 2)
                            <span class="badge bg-warning">Pendiente de entrega</span>
                        @elseif($pedido->status == 3)
                            <span class="badge bg-success">Entregado</span>
                        @elseif($pedido->status == 1)
                            <span class="badge bg-danger">Cancelado</span>
                        @else
                            <span class="badge bg-secondary">{{ $pedido->status_nombre }}</span>
                        @endif
                    @elseif($sucursalAsignada == 0)
                        {{-- Usuario CRM: ver status general del pedido --}}
                        @if($pedido->status == 2)
                            @php
                                $productosSinSucursal = $pedido->detalles->where('se_elimino', 0)->whereNull('id_sucursal_surtido')->count();
                                $todosProductosAsignados = ($productosSinSucursal === 0);
                                $sucursalesPendientes = $pedido->sucursales->contains('status', 0);
                                $todasSucursalesListas = $pedido->sucursales->isNotEmpty() && !$sucursalesPendientes;
                            @endphp
                            
                            @if(!$todosProductosAsignados)
                                <span class="badge bg-warning">Esperando asignación de sucursal</span>
                            @elseif($todasSucursalesListas && !$pedido->id_repartidor)
                                <span class="badge bg-info">Sucursales listas - Esperando repartidor</span>
                            @elseif($pedido->id_repartidor)
                                <span class="badge bg-primary">Repartidor asignado</span>
                            @else
                                <span class="badge bg-warning">Esperando despacho de sucursales</span>
                            @endif
                        @elseif($pedido->status == 3)
                            <span class="badge bg-success">Finalizado</span>
                        @elseif($pedido->status == 1)
                            <span class="badge bg-danger">Cancelado</span>
                        @else
                            <span class="badge bg-secondary">{{ $pedido->status_nombre }}</span>
                        @endif
                    @else
                        {{-- Usuario de sucursal: ver status de su sucursal --}}
                        @php
                            $miSucursal = $pedido->sucursales->firstWhere('id_sucursal', $sucursalAsignada);
                        @endphp
                        @if($miSucursal)
                            @if($miSucursal->status == 1)
                                <span class="badge bg-success">Despachado</span>
                            @else
                                <span class="badge bg-warning">Pendiente</span>
                            @endif
                        @else
                            <span class="badge bg-secondary">Sin asignar</span>
                        @endif
                    @endif
                </td>
                
                <td>
                    <div class="btn-group" role="group">
                        <!-- Marcar como listo - Solo sucursales -->
                        @if($sucursalAsignada > 0 && ($permisos['ver'] ?? false))
                            @php
                                $miSucursal = $pedido->sucursales->firstWhere('id_sucursal', $sucursalAsignada);
                                $tienePendientes = $miSucursal && $miSucursal->status == 0;
                                
                                // Filtrar productos EXTERNOS solo de esta sucursal
                                $productosExternos = $pedido->detalles->where('se_elimino', 0)
                                    ->where('id_sucursal_surtido', $sucursalAsignada)
                                    ->filter(function($detalle) {
                                        return str_starts_with($detalle->ean, 'T');
                                    })->count();
                                
                                // ✅ Usar el nombre correcto de la columna
                                $sucursalPedidoId = $miSucursal ? $miSucursal->id_pedido_sucursal : null;
                            @endphp

                            <!-- DEBUG -->
                            <div style="background: #f0f0f0; padding: 5px; margin: 5px 0; font-size: 12px; border: 1px solid #ccc;">
                                <strong>DEBUG - Sucursal {{ $sucursalAsignada }}:</strong><br>
                                EANs: 
                                @foreach($pedido->detalles->where('se_elimino', 0)->where('id_sucursal_surtido', $sucursalAsignada) as $detalle)
                                    {{ $detalle->ean }} ({{ str_starts_with($detalle->ean, 'T') ? 'EXTERNO' : 'NORMAL' }})<br>
                                @endforeach
                                <strong>Total externos: {{ $productosExternos }}</strong><br>
                                <strong>sucursalPedidoId: {{ $sucursalPedidoId }}</strong>
                            </div>
                            
                            @if($tienePendientes)
                                <button type="button" class="btn btn-sm btn-outline-success btn-action"
                                        onclick="marcarListoSucursal({{ $pedido->id_pedido }}, {{ $productosExternos }}, {{ $sucursalPedidoId }})"
                                        title="Marcar como listo">
                                    <i class="bi bi-check2-circle"></i>
                                </button>
                            @endif
                        @endif
                        
                        <!-- Ver detalles - SOLO para CRM y Sucursal (NO repartidor) -->
                        @if(!$esRepartidor)
                            <button type="button" class="btn btn-sm btn-outline-info btn-action"
                                    onclick="verPedido({{ $pedido->id_pedido }})"
                                    title="Ver detalles">
                                <i class="bi bi-eye"></i>
                            </button>
                        @endif
                        
                        @php
                        // Calcular condiciones para los botones
                            $sucursalesPendientes = $pedido->sucursales->contains('status', 0);
                            $todasSucursalesListas = $pedido->sucursales->isNotEmpty() && !$sucursalesPendientes;
                            $puedeEditarPedido = ($puedeEditar && $pedido->status == 2 && $sucursalAsignada == 0);
                        @endphp

                        <!-- Editar pedido - solo CRM, NO repartidor -->
                        @if($puedeEditarPedido && !$pedido->id_repartidor && !$esRepartidor)
                            <button type="button" class="btn btn-sm btn-outline-warning btn-action"
                                    onclick="editarPedido({{ $pedido->id_pedido }})"
                                    title="Editar pedido">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                        @endif
                                                    
                        <!-- Descargar PDF - solo para CRM y Sucursal (NO repartidor) -->
                        @if(!$esRepartidor)
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-action"
                                    onclick="descargarPDFPedido({{ $pedido->id_pedido }})"
                                    title="Descargar PDF">
                                <i class="bi bi-file-pdf"></i>
                            </button>
                        @endif
                        
                        <!<!-- Cancelar pedido - disponible para todos con permiso eliminar (CRM, Sucursal, Repartidor) -->
                        @if($puedeEliminar && $pedido->status != 3)
                            <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                    onclick="confirmarCancelarPedido({{ $pedido->id_pedido }}, '{{ $pedido->folio_pedido }}')"
                                    title="Cancelar pedido">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center py-4">
                    <i class="bi bi-truck" style="font-size: 2rem; color: #ccc;"></i>
                    <p class="text-muted mt-2">No hay pedidos registrados</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(method_exists($pedidos, 'hasPages') && $pedidos->hasPages())
<div class="d-flex justify-content-end mt-3">
    {{ $pedidos->appends(request()->query())->links('pagination::bootstrap-5') }}
</div>
@endif