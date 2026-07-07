@php
    // Obtener configuraciones UNA SOLA VEZ antes del ciclo
    $diasCancelacion = App\Models\Configuracion::getValor('dias_cancelacion_cotizacion', 7);
    $diasResaltado = App\Models\Configuracion::getValor('dias_resaltado_alerta', 2);
@endphp

<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-light">
            <tr>
                <th>Folio</th>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Importe</th>
                <th>Fase</th>
                <th>Clasificación</th>
                <th>Certeza</th>
                <th>Días sin contacto</th>
                <th>Seguimiento</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="cotizacionesTableBody">
            @forelse($cotizaciones as $cotizacion)
                @php
                    // Calcular días transcurridos correctamente
                    $fechaCreacion = $cotizacion->fecha_creacion;
                    $diasSinContacto = 0;
                    
                    if ($fechaCreacion) {
                        $fechaCreacionDate = \Carbon\Carbon::parse($fechaCreacion->format('Y-m-d'));
                        $hoyDate = \Carbon\Carbon::parse(now()->format('Y-m-d'));
                        $diasSinContacto = $fechaCreacionDate->diffInDays($hoyDate);
                    }
                    
                    // Determinar tipo de alerta usando las variables ya definidas
                    $alertaFuerte = $cotizacion->fase_nombre === 'En proceso' && $diasSinContacto >= $diasCancelacion;
                    $alertaSuave = $cotizacion->fase_nombre === 'En proceso' && 
                                $diasSinContacto >= $diasResaltado && 
                                $diasSinContacto < $diasCancelacion;
                    
                    $claseAlerta = '';
                    
                    if ($alertaFuerte) {
                        switch ($cotizacion->certeza) {
                            case 3:
                                $claseAlerta = 'cotizacion-alerta-alta';
                                break;
                            case 2:
                                $claseAlerta = 'cotizacion-alerta-media';
                                break;
                            default:
                                $claseAlerta = 'cotizacion-alerta-baja';
                                break;
                        }
                    } elseif ($alertaSuave) {
                        $claseAlerta = 'cotizacion-resaltado';
                    }
                    
                    $faseClass = match($cotizacion->fase_nombre) {
                        'En proceso' => 'bg-warning',
                        'Completada' => 'bg-success',
                        'Cancelada' => 'bg-danger',
                        default => 'bg-secondary'
                    };
                @endphp
                <tr id="cotizacion-row-{{ $cotizacion->id_cotizacion }}" data-id-cotizacion="{{ $cotizacion->id_cotizacion }}" class="{{ $claseAlerta }}">
                    <td>
                        <span class="badge bg-secondary">{{ $cotizacion->folio }}</span>
                        @if($cotizacion->enviado)
                            <i class="bi bi-envelope-check text-primary" title="Enviada"></i>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $cotizacion->nombre_cliente }}</strong>
                        @php
                            $contactos = [];
                            if ($cotizacion->cliente && $cotizacion->cliente->telefono1) {
                                $contactos[] = '<i class="bi bi-telephone"></i> ' . e($cotizacion->cliente->telefono1);
                            }
                            if ($cotizacion->cliente && $cotizacion->cliente->telefono2) {
                                $contactos[] = '<i class="bi bi-telephone"></i> ' . e($cotizacion->cliente->telefono2) . ' <span class="text-muted">(secundario)</span>';
                            }
                            if ($cotizacion->cliente && $cotizacion->cliente->email1) {
                                $contactos[] = '<i class="bi bi-envelope"></i> ' . e($cotizacion->cliente->email1);
                            }
                            $contactoMostrar = !empty($contactos) ? implode('<br>', $contactos) : '<span class="text-muted">Sin contacto</span>';
                        @endphp
                        <br><small class="text-muted">{!! $contactoMostrar !!}</small>
                    </td>
                    <td>{{ $cotizacion->fecha_creacion ? $cotizacion->fecha_creacion->format('d/m/Y H:i') : '-' }}</td>
                    <td>${{ number_format($cotizacion->importe_total, 2) }}</td>
                    <td><span class="badge {{ $faseClass }}">{{ $cotizacion->fase_nombre }}</span></td>
                    <td>{{ $cotizacion->clasificacion->clasificacion ?? '-' }}</td>
                    <td><span class="badge bg-{{ $cotizacion->certeza_color }}">{{ $cotizacion->certeza_nombre }}</span></td>
                    <td class="text-center">
                        @if($cotizacion->fase_nombre === 'En proceso')
                            @if($diasSinContacto >= $diasCancelacion)
                                <span class="badge bg-danger">{{ $diasSinContacto }} día(s)</span>
                            @elseif($diasSinContacto >= $diasResaltado)
                                <span class="badge bg-warning">{{ $diasSinContacto }} día(s)</span>
                            @else
                                <span class="badge bg-secondary">{{ $diasSinContacto }} día(s)</span>
                            @endif
                            
                            @if($cotizacion->mostrarNotificacion)
                                <i class="bi bi-bell-fill text-warning ms-1" title="¡Requiere seguimiento! No se ha contactado al cliente recientemente."></i>
                            @endif
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if($cotizacion->fase_nombre === 'En proceso')
                        <button type="button" class="btn btn-sm btn-outline-primary btn-action"
                                onclick="abrirModalSeguimiento({{ $cotizacion->id_cotizacion }}, '{{ $cotizacion->folio }}')"
                                title="Seguimiento">
                            <i class="bi bi-chat-dots"></i>
                        </button>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if($cotizacion->enviado && $cotizacion->fase_nombre === 'Completada' && !$cotizacion->es_pedido)
                        <button type="button" class="btn btn-sm btn-success btn-action"
                                onclick="mostrarModalPedido({{ $cotizacion->id_cotizacion }}, '{{ addslashes($cotizacion->folio) }}')"
                                title="Convertir en pedido">
                            <i class="bi bi-cart-check"></i> Pedido
                        </button>
                        @endif
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-info btn-action"
                                    onclick="verCotizacion({{ $cotizacion->id_cotizacion }})"
                                    title="Ver detalles">
                                <i class="bi bi-eye"></i>
                            </button>
                            
                            @if($permisos['editar'] && !$cotizacion->enviado)
                                <button type="button" class="btn btn-sm btn-outline-primary btn-action btn-editar-cotizacion"
                                        data-id="{{ (int) $cotizacion->id_cotizacion }}"
                                        title="Editar cotización">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            @elseif($permisos['editar'] && $cotizacion->enviado)
                                <button type="button" class="btn btn-sm btn-outline-primary btn-action btn-crear-independiente"
                                        data-id="{{ (int) $cotizacion->id_cotizacion }}"
                                        title="Crear cotización independiente (sin versionado)">
                                    <i class="bi bi-files"></i>
                                </button>
                            @endif
                            
                            @if($permisos['editar'])
                            <button type="button" class="btn btn-sm {{ $cotizacion->enviado ? 'btn-outline-secondary' : 'btn-outline-success' }} btn-action"
                                    onclick="enviarCotizacion({{ $cotizacion->id_cotizacion }}, '{{ addslashes($cotizacion->folio) }}')"
                                    title="{{ $cotizacion->enviado ? 'Descargar ticket PDF' : 'Generar y descargar ticket PDF' }}">
                                <i class="bi {{ $cotizacion->enviado ? 'bi-file-pdf' : 'bi-send' }}"></i>
                                {{ $cotizacion->enviado ? 'PDF' : 'Enviar' }}
                            </button>
                            @endif
                            
                            @if($permisos['eliminar'])
                            <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                    onclick="confirmarEliminar('cotizacion', {{ $cotizacion->id_cotizacion }}, '{{ addslashes($cotizacion->folio) }}')"
                                    title="Eliminar cotización">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center py-4">
                        <i class="bi bi-file-earmark-text" style="font-size: 2rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">No hay cotizaciones registradas</p>
                        @if($permisos['crear'] ?? false)
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaCotizacion">
                            <i class="bi bi-plus"></i> Crear primera cotización
                        </button>
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($cotizaciones->hasPages())
<div class="d-flex justify-content-end mt-3">
    {{ $cotizaciones->appends(request()->query())->links('pagination::bootstrap-5') }}
</div>
@endif