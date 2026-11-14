@extends('layouts.app')

@section('title', 'Sobre pedido - CRM')
@section('page-title', 'Sobre pedido')

@section('content')
<div id="moduloSobrePedido" class="container-fluid"
    data-buscar-clientes="{{ route('ventas.sobre-pedido.clientes.buscar') }}"
    data-buscar-articulos="{{ route('ventas.sobre-pedido.articulos.buscar') }}"
    data-categorias="{{ json_encode($categorias, JSON_THROW_ON_ERROR) }}"
    data-umbral-publico="{{ \App\Services\Ventas\RegistrarPedidoDirecto::UMBRAL_PROMOCION_PUBLICO_CENTAVOS }}"
    data-csrf="{{ csrf_token() }}">
    <div id="estadoCargaSobrePedido" class="alert alert-warning" role="status">
        Si los controles no se activan, recarga la página. Si el problema continúa, verifica que el servidor publique js/pedidos-directos.js.
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h5">Pedidos con Anticipo</h2>
            <label for="buscarClienteDirecto" class="form-label">Buscar cliente por nombre o teléfono</label>
            <div class="input-group">
                <input id="buscarClienteDirecto" type="search" class="form-control" autocomplete="off">
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse"
                    data-bs-target="#altaClienteDirecto" aria-controls="altaClienteDirecto" aria-expanded="false">Nuevo cliente</button>
            </div>
            <div id="resultadosClienteDirecto" class="list-group mt-2" aria-live="polite"></div>
            <div aria-live="polite">
                <div id="tarjetaClienteDirecto" class="mt-3 rounded-3 border border-primary-subtle bg-primary-subtle text-primary-emphasis p-3" hidden>
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-person-circle fs-3" aria-hidden="true"></i>
                        <div class="text-break flex-grow-1">
                            <div class="small">Cliente seleccionado</div>
                            <div id="clienteDirectoSeleccionado" class="fs-4 fw-bold"></div>
                        </div>
                        <button type="button" id="quitarClienteDirecto" class="btn-close flex-shrink-0"
                            aria-label="Quitar cliente seleccionado" title="Quitar cliente seleccionado"></button>
                    </div>
                </div>
            </div>

            <div class="collapse" id="altaClienteDirecto">
                <form id="formClienteDirecto" class="border rounded p-3 my-3" method="post" novalidate action="{{ route('ventas.sobre-pedido.clientes.store') }}">
                    @csrf
                    <div class="row g-2">
                        <label class="col-md-4">Nombre<input name="Nombre" class="form-control" maxlength="255" required></label>
                        <label class="col-md-4">Apellido paterno<input name="apPaterno" class="form-control" maxlength="255" required></label>
                        <label class="col-md-4">Apellido materno<input name="apMaterno" class="form-control" maxlength="255"></label>
                        <label class="col-md-4">Teléfono<input name="telefono1" type="tel" class="form-control" maxlength="20" required></label>
                        <label class="col-md-8">Dirección o referencia<input name="Domicilio" class="form-control" maxlength="500"></label>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <button type="submit" class="btn btn-outline-primary">Guardar y seleccionar cliente</button>
                        <button type="button" class="btn btn-outline-secondary" id="cancelarClienteDirecto">Cancelar</button>
                    </div>
                </form>
            </div>

            <form id="formSobrePedido" method="post" novalidate action="{{ route('ventas.sobre-pedido.store') }}" class="mt-3">
                @csrf
                <input name="id_cliente" type="hidden">
                <div class="row g-3 mb-3">
            <label class="col-md-3">Tipo de venta
                <select name="tipo_venta" class="form-select" required>
                    <option value="">Selecciona</option>
                    <option value="convenio">Convenio</option>
                    <option value="publico">Público en general</option>
                </select>
            </label>
            <label class="col-md-3">Convenio
                <select name="id_convenio" class="form-select" disabled>
                    <option value="">Selecciona un convenio</option>
                    @foreach($convenios as $convenio)
                        <option value="{{ $convenio->id }}">{{ $convenio->convenio }}</option>
                    @endforeach
                </select>
            </label>
                    <label class="col-md-3">Fecha de encargo
                        <input name="fecha_encargo" type="date" class="form-control"
                            value="{{ today()->toDateString() }}" max="{{ today()->toDateString() }}" required>
                    </label>
                    <div class="col-md-3">
                        <label for="sucursalOrigenPedido" class="form-label">Sucursal asignada</label>
                        @if($sucursalAsignada === 0)
                            <select id="sucursalOrigenPedido" name="id_sucursal_origen" class="form-select" required aria-label="Sucursal de origen">
                                <option value="">Selecciona una sucursal</option>
                                @foreach($sucursalesOrigen as $sucursal)
                                    <option value="{{ $sucursal->getKey() }}">{{ $sucursal->nombre }}</option>
                                @endforeach
                            </select>
                        @else
                            <input id="sucursalOrigenPedido" class="form-control" disabled
                                value="{{ $sucursalesOrigen->first()?->nombre ?? ('Sucursal ' . $sucursalAsignada) }}">
                            <small class="text-muted">Para corregir la asignación, solicita el cambio en tu cuenta.</small>
                        @endif
                    </div>
                </div>
                <div class="table-responsive border rounded mb-2">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Código de barras</th><th>Descripción</th><th>Cantidad</th><th>Precio aproximado</th><th class="table-warning">
                            Categoría de descuento · Buen Fin
                            @if(is_file(public_path('images/logo-buen-fin.png')))
                                <img src="{{ asset('images/logo-buen-fin.png') }}" alt="Buen Fin" width="32">
                            @endif
                        </th><th>Acciones</th></tr></thead>
                        <tbody id="articulosSobrePedido"></tbody>
                        <template id="filaArticuloSobrePedido">
                            <tr data-articulo>
                                <td style="min-width: 10rem">
                                    <input aria-label="Código de barras" name="ean_consultado" class="form-control" inputmode="numeric" autocomplete="off" maxlength="13" pattern="[0-9]{1,13}">
                                    <div class="small text-muted mt-1" data-estado-ean aria-live="polite" hidden></div>
                                </td>
                                <td style="min-width: 14rem"><input aria-label="Descripción" name="descripcion" class="form-control" maxlength="255" required></td>
                                <td style="min-width: 6rem"><input aria-label="Cantidad" name="cantidad" type="number" value="1" min="1" max="999" step="1" autocomplete="off" class="form-control" required></td>
                                <td style="min-width: 9rem"><input aria-label="Precio aproximado" name="precio_unitario" type="number" min="0.01" max="99999999.99" step="0.01" class="form-control" required></td>
                                <td style="min-width: 15rem">
                                    <select aria-label="Categoría de descuento Buen Fin" name="categoria_promocion" class="form-select border-warning" required disabled></select>
                                </td>
                                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" data-quitar aria-label="Quitar artículo"><i class="bi bi-trash"></i></button></td>
                            </tr>
                        </template>
                    </table>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" id="agregarArticuloSobrePedido">Agregar artículo</button>
                <div class="row align-items-end g-3 mt-2">
                    <div class="col-md-4">
                        <label for="anticipoPedidoDirecto" class="form-label">Anticipo recibido</label>
                        <input id="anticipoPedidoDirecto" name="anticipo_cliente" type="number" min="0"
                            max="9999999999.99" step="0.01" class="form-control" required>
                    </div>
                    <div class="col-md-5" id="totalesPedidoDirecto" aria-live="polite"></div>
                    <div class="col-md-3 d-flex flex-wrap gap-2">
                        <button class="btn btn-success btn-action" type="submit">Vista previa</button>
                        <button class="btn btn-outline-secondary btn-action" type="button" id="limpiarPedidoDirecto">Limpiar</button>
                    </div>
                </div>
            </form>


        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h2 class="h5">Pedidos registrados</h2>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light"><tr>
                        @if($sucursalAsignada === 0)
                            <th>Sucursal</th>
                        @endif
                        <th>Folio</th><th>Cliente</th><th>Artículos</th><th>Anticipo</th><th>Fecha de encargo</th><th>Estado</th><th>Acciones</th>
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
                                    @if($pedido->status === 2 && $sucursalAsignada !== 0)
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
                    <select id="pedidosPorPagina" name="per_page" class="form-select form-select-sm w-auto" aria-label="Pedidos por página" onchange="this.form.submit()">
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
</div>
<div class="modal fade" id="vistaPreviaPedidoDirecto" tabindex="-1" aria-labelledby="tituloVistaPreviaPedido">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="tituloVistaPreviaPedido">Vista previa del ticket</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="bg-light border rounded p-3 mb-3">
                    <div class="small text-muted">Cliente</div>
                    <div class="fs-5 fw-bold" data-cliente-previo></div>
                </div>
                <h3 class="h6">Artículos sobre pedido</h3>
                <ul class="list-group mb-3" data-articulos-previos></ul>
                <p class="small text-muted" data-aviso-promocion></p>
                <dl class="bg-light border rounded p-3 mb-0">
                    <div class="d-flex justify-content-between gap-3"><dt>Total estimado</dt><dd data-total-previo></dd></div>
                    <div class="d-flex justify-content-between gap-3"><dt>Anticipo recibido</dt><dd class="fw-bold mb-0" data-anticipo-previo></dd></div>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Editar captura</button>
                <button type="button" class="btn btn-outline-primary" data-agregar-previo>Agregar artículo</button>
                <button type="button" class="btn btn-success" data-guardar-previo>Guardar pedido</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="confirmacionPedidoDirecto" tabindex="-1"
    aria-labelledby="tituloConfirmacionPedido" aria-describedby="mensajeConfirmacionPedido">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="tituloConfirmacionPedido"></h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p id="mensajeConfirmacionPedido" class="mb-0"></p>
                <div class="mt-3" data-ticket-entrega hidden>
                    <label for="folioTicketEntrega" class="form-label">Número de ticket</label>
                    <input id="folioTicketEntrega" type="text" class="form-control" inputmode="numeric"
                        maxlength="6" autocomplete="off" placeholder="Hasta 6 dígitos">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" data-confirmar>Confirmar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@php
    $archivoPedidosDirectos = public_path('js/pedidos-directos.js');
    $urlPedidosDirectos = parse_url(asset('js/pedidos-directos.js'), PHP_URL_PATH);
@endphp
@if(is_file($archivoPedidosDirectos))
    <script src="{{ $urlPedidosDirectos }}?v={{ hash_file('sha256', $archivoPedidosDirectos) }}" defer></script>
@endif
@endpush
