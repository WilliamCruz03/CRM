    @php($puedeCancelarPedido = auth()->user()->puede('ventas', 'pedidos_anticipo', 'eliminar'))
    <div class="card">
        <div class="card-body">
            <h2 class="h5">Pedidos registrados</h2>
            <form method="get" action="{{ route('ventas.sobre-pedido.index', [], false) }}" data-buscar-pedidos class="mb-3 crm-filter-bar">
                @foreach(request()->only(['per_page', 'orden', 'direccion']) as $clave => $valor)
                    @if(is_scalar($valor))<input type="hidden" name="{{ $clave }}" value="{{ $valor }}">@endif
                @endforeach
                <div class="row g-3 align-items-end">
                    @if($sucursalAsignada === 0)
                    <div class="col-md-4">
                        <label for="sucursalListadoPedidos" class="form-label">Sucursal</label>
                        <select id="sucursalListadoPedidos" name="sucursal_listado" class="form-select">
                            <option value="">{{ $sucursalAsignada === 0 ? 'Todas las sucursales' : 'Mi sucursal' }}</option>
                            @foreach($sucursalesOrigen as $sucursal)
                                <option value="{{ $sucursal->getKey() }}" @selected($sucursalListado === (int) $sucursal->getKey())>{{ $sucursal->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="{{ $sucursalAsignada === 0 ? 'col-md-8' : 'col-12' }}">
                        <label for="buscarPedidosDirectos" class="form-label">Buscar pedidos</label>
                        <div class="search-box">
                            <i class="bi bi-search" aria-hidden="true"></i>
                            <input type="text" id="buscarPedidosDirectos" name="buscar" class="form-control" maxlength="100" autocomplete="off"
                                value="{{ $busquedaPedidos }}" placeholder="Buscar por folio, cliente, artículo, ticket...">
                            <button type="button" class="bi bi-x-circle-fill" id="limpiarBuscarPedidosDirectos" data-limpiar-busqueda-pedidos
                                style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); z-index: 10; color: #6c757d; cursor: pointer; display: {{ $busquedaPedidos !== '' ? 'block' : 'none' }}; font-size: 1.2rem; background: none; border: none; padding: 0; line-height: 1;"
                                title="Limpiar búsqueda" aria-label="Limpiar búsqueda"></button>
                        </div>
                    </div>

                </div>
                <span id="cargandoListadoPedidos" class="spinner-border spinner-border-sm text-primary mt-2 d-none" role="status" aria-label="Consultando pedidos"></span>
                <button type="button" class="btn btn-sm btn-outline-primary mt-2 d-none" data-reintentar-listado-pedidos>Reintentar</button>
            </form>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light"><tr>
                        @foreach(['sucursal' => 'Sucursal', 'folio' => 'Folio', 'cliente' => 'Cliente', 'articulos' => 'Artículos', 'anticipo' => 'Anticipo', 'fecha' => 'Fecha de encargo', 'estado' => 'Estado', 'ticket' => 'Número de ticket'] as $clave => $etiqueta)
                            @continue($clave === 'sucursal' && $sucursalAsignada !== 0)
                            @php($seleccionado = $ordenPedidos === $clave)
                            <th class="py-3 small fw-bold" @if($seleccionado) aria-sort="{{ $direccionPedidos === 'asc' ? 'ascending' : 'descending' }}" @endif>
                                <a class="text-reset text-decoration-none" data-orden-pedidos href="{{ request()->fullUrlWithQuery(['orden' => $clave, 'direccion' => $seleccionado && $direccionPedidos === 'asc' ? 'desc' : 'asc', 'page' => 1]) }}">
                                    {{ $etiqueta }} <i class="bi {{ $seleccionado ? ($direccionPedidos === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : 'bi-arrow-down-up' }}" aria-hidden="true"></i>
                                </a>
                            </th>
                        @endforeach
                        <th class="py-3 small fw-bold">Acciones</th>
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
                                <td class="text-nowrap">
                                    @if($pedido->folio_ticket_entrega !== null && $pedido->folio_ticket_entrega !== '')
                                        {{ $pedido->folio_ticket_entrega }}
                                    @else
                                        <span class="text-muted">Sin ticket</span>
                                    @endif
                                </td>
                                <td>
                                    @include('ventas.pedidos-directos.ticket-link')
                                    @if($pedido->status === 2 && $puedeCancelarPedido)
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Cancelar pedido" aria-label="Cancelar pedido"
                                            data-cancelar-pedido-directo="{{ route('ventas.sobre-pedido.destroy', $pedido->getKey(), false) }}"
                                            data-pedido-id="{{ $pedido->getKey() }}" data-folio="{{ $pedido->folio_pedido }}"
                                            data-cliente="{{ $pedido->clienteDirecto?->nombre_completo ?? 'Cliente sin registro' }}">
                                            <i class="bi bi-trash" aria-hidden="true"></i>
                                        </button>
                                    @endif
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
                            <tr><td colspan="{{ $sucursalAsignada === 0 ? 9 : 8 }}">{{ $busquedaPedidos !== '' ? 'No se encontraron pedidos con esa búsqueda.' : 'Todavía no hay pedidos registrados.' }}</td></tr>
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
