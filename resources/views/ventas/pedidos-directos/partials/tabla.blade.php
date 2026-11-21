    <div class="card">
        <div class="card-body">
            <h2 class="h5">Pedidos registrados</h2>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light"><tr>
                        @if($sucursalAsignada === 0)
                            <th class="py-3 small fw-bold">Sucursal</th>
                        @endif
                        <th class="py-3 small fw-bold">Folio</th><th class="py-3 small fw-bold">Cliente</th><th class="py-3 small fw-bold">Artículos</th><th class="py-3 small fw-bold">Anticipo</th><th class="py-3 small fw-bold">Fecha de encargo</th><th class="py-3 small fw-bold">Estado</th><th class="py-3 small fw-bold">Acciones</th>
                    </tr></thead>
                    <tbody>
                        @forelse($pedidos as $pedido)
                            <tr>
                                @if($sucursalAsignada === 0)
                                    <td>{{ $pedido->sucursalOrigen?->nombre ?? '—' }}</td>
                                @endif
                                <td>{{ $pedido->folio_pedido }}</td>
                                <td>{{ $pedido->clienteDirecto?->nombre_completo ?? '—' }}</td>
                                <td>
                                    @foreach($pedido->detalles as $detalle)
                                        <div>{{ $detalle->cantidad }} × {{ $detalle->descripcion_sobre_pedido }}</div>
                                    @endforeach
                                </td>
                                <td>${{ number_format($pedido->anticipo_cliente, 2) }}</td>
                                <td class="text-nowrap">{{ ($pedido->fecha_encargo ?? $pedido->fecha_pedido)?->format('d/m/Y') ?? '—' }}</td>
                                <td>
                                    <span class="badge {{ $pedido->status === 3 ? 'text-bg-success' : 'text-bg-warning' }}">
                                        {{ $pedido->status === 3 ? 'Entregado' : $pedido->status_nombre }}
                                    </span>
                                    @if($pedido->fecha_entrega_real)
                                        <div class="small text-muted">{{ $pedido->fecha_entrega_real->format('d/m/Y H:i') }}</div>
                                    @endif
                                </td>
                                <td>
                                    @include('ventas.pedidos-directos.ticket-link')
                                    @if(auth()->user()->es_crm)
                                        <button type="button" class="btn btn-sm btn-outline-primary" title="Editar pedido" aria-label="Editar pedido"
                                            data-editar-pedido="{{ route('ventas.sobre-pedido.actualizar', $pedido->getKey(), false) }}"
                                            data-folio="{{ $pedido->folio_pedido }}" data-estado="{{ $pedido->status }}"
                                            data-fecha="{{ $pedido->fecha_encargo?->toDateString() }}"
                                            data-anticipo="{{ $pedido->anticipo_cliente }}" data-ticket="{{ $pedido->folio_ticket_entrega }}">
                                            <i class="bi bi-pencil" aria-hidden="true"></i>
                                        </button>
                                    @endif
                                    @if($pedido->status === 2 && ($sucursalAsignada !== 0 || auth()->user()->es_crm))
                                        <button type="button" class="btn btn-sm btn-outline-success"
                                            data-finalizar="{{ route('ventas.sobre-pedido.finalizar', $pedido->id_pedido) }}">Entregado</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ $sucursalAsignada === 0 ? 8 : 7 }}">Todavía no hay pedidos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($pedidos->total() > 0)
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 paginacion-bloque">
                <div class="text-muted small">
                    Mostrando <strong>{{ $pedidos->firstItem() ?? 0 }}</strong> hasta <strong>{{ $pedidos->lastItem() ?? 0 }}</strong>
                    de <strong>{{ $pedidos->total() }}</strong> resultados
                </div>
                <div class="d-flex flex-wrap align-items-center gap-3">
                <form method="get" action="{{ route('ventas.sobre-pedido.index', [], false) }}" class="d-flex align-items-center gap-2">
                    <label for="pedidosPorPagina" class="text-muted small">Mostrar</label>
                    <select id="pedidosPorPagina" name="per_page" class="form-select form-select-sm w-auto" aria-label="Pedidos por página">
                        @foreach($opcionesPorPagina as $opcion)
                            <option value="{{ $opcion }}" @selected($pedidos->perPage() === $opcion)>{{ $opcion }}</option>
                        @endforeach
                    </select>
                    <span class="text-muted small text-nowrap">por página</span>
                </form>
                {{ $pedidos->links('pagination.bootstrap-5') }}
                </div>
            </div>
            @endif
        </div>
    </div>
