@extends('layouts.app')

@section('title', 'Búsqueda por Artículo / Cliente')

@section('page-title', 'Generación de Reportes')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="bi bi-search"></i> Búsqueda de Compras
                    </h3>
                </div>

                {{-- Tabs --}}
                <div class="card-body pb-0">
                    <ul class="nav nav-tabs" id="reporteTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active"
                                    id="tab-articulo-btn"
                                    data-bs-toggle="tab"
                                    data-bs-target="#tab-articulo"
                                    type="button" role="tab"
                                    aria-controls="tab-articulo"
                                    aria-selected="true">
                                <i class="bi bi-upc-scan"></i> Por Artículo
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link"
                                    id="tab-cliente-btn"
                                    data-bs-toggle="tab"
                                    data-bs-target="#tab-cliente"
                                    type="button" role="tab"
                                    aria-controls="tab-cliente"
                                    aria-selected="false">
                                <i class="bi bi-person-lines-fill"></i> Por Cliente
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="tab-content" id="reporteTabsContent">

                    {{-- ========================================================= --}}
                    {{-- TAB 1 — POR ARTÍCULO (INTACTO)                             --}}
                    {{-- ========================================================= --}}
                    <div class="tab-pane fade show active" id="tab-articulo" role="tabpanel" aria-labelledby="tab-articulo-btn">

                        {{-- Filtros tab 1 --}}
                        <div class="card-body border-bottom">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label for="inputEan" class="form-label">Código de barras</label>
                                    <input type="text"
                                           id="inputEan"
                                           class="form-control"
                                           placeholder="Ej. 45 o 0000000000045"
                                           autocomplete="off">
                                    <div class="form-text">Se completan ceros a la izquierda automáticamente.</div>
                                </div>
                                <div class="col-md-3">
                                    <label for="inputFechaInicio" class="form-label">Fecha inicio</label>
                                    <input type="date" id="inputFechaInicio" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label for="inputFechaFin" class="form-label">Fecha fin</label>
                                    <input type="date" id="inputFechaFin" class="form-control">
                                </div>
                            </div>

                            <div class="row g-3 align-items-end">
                                <div class="col-md-2 d-grid">
                                    <button type="button" id="btnBuscar" class="btn btn-primary">
                                        <i class="bi bi-search"></i> Buscar
                                    </button>
                                </div>
                                <div class="col-md-2 d-grid">
                                    <button type="button" id="btnLimpiarFiltros" class="btn btn-outline-secondary">
                                        <i class="bi bi-eraser"></i> Limpiar
                                    </button>
                                </div>
                            </div>

                            <div id="articuloHeader" class="mt-3 d-none">
                                <div class="alert alert-info mb-0">
                                    <strong>Código:</strong> <span id="articuloEan">—</span>
                                    &mdash;
                                    <strong>Descripción:</strong> <span id="articuloDescripcion">—</span>
                                </div>
                            </div>

                            <div id="errorContainer" class="mt-3 d-none">
                                <div class="alert alert-danger d-flex align-items-center justify-content-between mb-0">
                                    <span id="errorMessage">Ocurrió un error.</span>
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="btnReintentar">
                                        <i class="bi bi-arrow-clockwise"></i> Reintentar
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Layout dos columnas tab 1 --}}
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-lg-7">
                                    <div id="loadingIndicator" class="text-center py-4 d-none">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Cargando...</span>
                                        </div>
                                        <p class="mt-2">Cargando datos...</p>                                                                 
                                    </div>

                                    <div id="resultadosContainer">
                                        <div class="text-center text-muted py-5" id="placeholderInicial">
                                            <i class="bi bi-upc-scan fs-1 d-block mb-2"></i>
                                            Ingrese un código de barras y un período, luego presione Buscar.
                                        </div>

                                        {{-- KPIs del artículo --}}
                                        <div id="kpisWrapper" class="d-none mb-3">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <div class="border rounded p-3">
                                                        <div class="text-muted small">Artículo con mayor costo</div>
                                                        <div class="fw-bold" id="kpiArticuloCaro">—</div>
                                                        <div class="small text-muted" id="kpiArticuloCaroCosto">—</div>
                                                        <div class="small text-secondary mt-1">
                                                            <i class="bi bi-info-circle"></i>
                                                            Artículo con mayor costo entre los vendidos en el período
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="border rounded p-3">
                                                        <div class="text-muted small">Inventario por costo</div>
                                                        <div class="fw-bold" id="kpiInventarioCosto">—</div>
                                                        <div class="small text-muted" id="kpiInventarioSucursales">—</div>
                                                        <div class="small text-secondary mt-1">
                                                            <i class="bi bi-info-circle"></i>
                                                            Costo de compra de las unidades vendidas en el período
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="border rounded p-3">
                                                        <div class="text-muted small">Producto con mayor venta</div>
                                                        <div class="fw-bold" id="kpiProductoMayorVenta">—</div>
                                                        <div class="small text-muted" id="kpiProductoMayorVentaMonto">—</div>
                                                        <div class="small text-secondary mt-1">
                                                            <i class="bi bi-info-circle"></i>
                                                            Producto con más venta en el período seleccionado
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="tablaWrapper" class="d-none">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped table-hover align-middle mb-0" id="tablaClientesArticulo">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th class="sortable" data-sort-key="nombre" style="cursor: pointer;">
                                                                Cliente <span class="sort-indicator"></span>
                                                            </th>
                                                            <th class="sortable" data-sort-key="telefono" style="cursor: pointer;">
                                                                Teléfono <span class="sort-indicator"></span>
                                                            </th>
                                                            <th class="sortable" data-sort-key="sucursal" style="cursor: pointer;">
                                                                Sucursal <span class="sort-indicator"></span>
                                                            </th>
                                                            <th class="sortable text-end" data-sort-key="compras" style="cursor: pointer;">
                                                                Tickets <span class="sort-indicator"></span>
                                                            </th>
                                                            <th class="sortable text-end" data-sort-key="frecuencia" style="cursor: pointer;">
                                                                Frecuencia <span class="sort-indicator"></span>
                                                            </th>
                                                            <th class="sortable" data-sort-key="primera_compra" style="cursor: pointer;">
                                                                Primera compra <span class="sort-indicator"></span>
                                                            </th>
                                                            <th class="sortable" data-sort-key="ultima_compra" style="cursor: pointer;">
                                                                Última compra <span class="sort-indicator"></span>
                                                            </th>
                                                            <th class="sortable text-end" data-sort-key="monto_total" style="cursor: pointer;">
                                                                Importe <span class="sort-indicator"></span>
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tablaClientesBody"></tbody>
                                                </table>
                                            </div>

                                            <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
                                                <small class="text-muted" id="resumenMostrando"></small>
                                                <div id="paginacionContainer"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-5">
                                    <div id="panelDetalle" class="border rounded p-3 h-100">
                                        <div class="text-center text-muted py-5" id="placeholderDetalle">
                                            <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                                            Seleccione un cliente para ver el detalle de sus tickets.
                                        </div>

                                        <div id="detalleWrapper" class="d-none">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="mb-0" id="detalleClienteNombre">—</h6>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCerrarDetalle">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </div>

                                            <div id="detalleLoading" class="text-center py-3 d-none">
                                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                            </div>

                                            <div id="detalleTickets"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ========================================================= --}}
                    {{-- TAB 2 — POR CLIENTE                                        --}}
                    {{-- ========================================================= --}}
                    <div class="tab-pane fade" id="tab-cliente" role="tabpanel" aria-labelledby="tab-cliente-btn">

                    {{-- Filtros tab 2 --}}
                    <div class="card-body border-bottom">
                        <div class="row g-3 align-items-end">
                            {{-- Paso 1: cliente con autocomplete --}}
                            <div class="col-md-4 position-relative">
                                <label for="inputBuscarCliente" class="form-label">Cliente (nombre o teléfono)</label>
                                <input type="text" id="inputBuscarCliente" class="form-control"
                                    placeholder="Escriba al menos 3 caracteres..." autocomplete="off">
                                <div class="form-text">Seleccione un cliente de la lista.</div>
                                <div id="sugerenciasClientes"
                                    class="list-group position-absolute w-100 shadow-sm d-none"
                                    style="z-index: 1050; max-height: 320px; overflow-y: auto;"></div>
                            </div>

                            {{-- Paso 2: fechas --}}
                            <div class="col-md-3">
                                <label for="inputFechaInicioCli" class="form-label">Fecha inicio</label>
                                <input type="date" id="inputFechaInicioCli" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label for="inputFechaFinCli" class="form-label">Fecha fin</label>
                                <input type="date" id="inputFechaFinCli" class="form-control">
                            </div>

                            {{-- Paso 3: botones --}}
                            <div class="col-md-1 d-grid">
                                <button type="button" id="btnBuscarCliente" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                            </div>
                            <div class="col-md-1 d-grid">
                                <button type="button" id="btnLimpiarCliente" class="btn btn-outline-secondary">
                                    <i class="bi bi-eraser"></i> Limpiar
                                </button>
                            </div>
                        </div>

                        {{-- Cliente seleccionado --}}
                        <div id="clienteSeleccionadoWrap" class="mt-3 d-none">
                            <div class="alert alert-info d-flex justify-content-between align-items-center mb-0">
                                <span>
                                    <i class="bi bi-person-check"></i>
                                    <strong>Cliente:</strong>
                                    <span id="clienteSeleccionadoNombre">—</span>
                                    <small class="text-muted" id="clienteSeleccionadoTel"></small>
                                </span>
                                <button type="button" class="btn btn-sm btn-outline-danger" id="btnQuitarCliente" title="Quitar cliente">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>

                        <div id="clienteErrorContainer" class="mt-3 d-none">
                            <div class="alert alert-danger d-flex align-items-center justify-content-between mb-0">
                                <span id="clienteErrorMessage">Ocurrió un error.</span>
                                <button type="button" class="btn btn-sm btn-outline-danger" id="btnReintentarCliente">
                                    <i class="bi bi-arrow-clockwise"></i> Reintentar
                                </button>
                            </div>
                        </div>
                    </div>

                        {{-- Panel resultado tab 2 --}}
                        <div class="card-body">
                            <div class="text-center text-muted py-5" id="clientePlaceholder">
                                <i class="bi bi-person-lines-fill fs-1 d-block mb-2"></i>
                                Busque un cliente por nombre o teléfono, seleccione un resultado y elija el período.
                            </div>

                            <div id="clienteLoading" class="text-center py-4 d-none">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-2">Cargando datos...</p>
                            </div>

                            <div id="clienteResultado" class="d-none">
                                {{-- Encabezado cliente + resumen --}}
                                {{-- KPIs del cliente --}}
                                <div class="row g-3 mb-4" id="clienteKpisWrapper">
                                    <div class="col-md-6">
                                        <div class="border rounded p-3">
                                            <div class="text-muted small">Artículo más comprado</div>
                                            <div class="fw-bold" id="kpiClienteArticuloTop">—</div>
                                            <div class="small text-muted" id="kpiClienteArticuloTopCant">—</div>
                                            <div class="small text-secondary mt-1">
                                                <i class="bi bi-info-circle"></i>
                                                Producto con más unidades vendidas al cliente en el período
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="border rounded p-3">
                                            <div class="text-muted small">Importe total comprado</div>
                                            <div class="fw-bold" id="kpiClienteImporteTotal">—</div>
                                            <div class="small text-muted" id="kpiClienteTickets">—</div>
                                            <div class="small text-secondary mt-1">
                                                <i class="bi bi-info-circle"></i>
                                                Suma de todos los tickets válidos del cliente en el período
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <h5 class="mb-1" id="clienteNombreCompleto">—</h5>
                                    <i class="bi bi-telephone text-muted"></i>
                                    <small class="text-muted" id="clienteTelefono">—</small>
                                </div>

                                <div class="row g-3 mb-4" id="clienteResumen">
                                    <div class="col-md-4">
                                        <div class="border rounded p-3">
                                            <div class="text-muted small">Tickets</div>
                                            <div class="fs-5 fw-bold" id="resumenTickets">—</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border rounded p-3">
                                            <div class="text-muted small">Artículos Comprados</div>
                                            <div class="fs-5 fw-bold" id="resumenLineas">—</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border rounded p-3">
                                            <div class="text-muted small">Importe total</div>
                                            <div class="fs-5 fw-bold" id="resumenImporte">—</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Gráfica grupos madre --}}
                                <div class="mb-4">
                                    <h6 class="mb-2">Grupos madre (top 10 por importe)</h6>
                                    <div style="position: relative; height: 320px;">
                                        <canvas id="graficaGruposMadre"></canvas>
                                    </div>
                                </div>

                                {{-- Tabla artículos --}}
                                <div class="mb-4">
                                    <h3 class="mb-2">Artículos comprados</h3>
                                    <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                                        <table class="table table-bordered table-striped table-sm align-middle mb-0" id="tablaArticulosCliente">
                                            <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                                                <tr>
                                                    <th class="sortable-cli-articulos" data-sort-key="ean" style="cursor: pointer;">
                                                        EAN <span class="sort-indicator-cli-art"></span>
                                                    </th>
                                                    <th class="sortable-cli-articulos" data-sort-key="descripcion" style="cursor: pointer;">
                                                        Descripción <span class="sort-indicator-cli-art"></span>
                                                    </th>
                                                    <th class="sortable-cli-articulos text-end" data-sort-key="repeticiones" style="cursor: pointer;">
                                                        Repeticiones <span class="sort-indicator-cli-art"></span>
                                                    </th>
                                                    <th class="sortable-cli-articulos text-end" data-sort-key="importe" style="cursor: pointer;">
                                                        Importe <span class="sort-indicator-cli-art"></span>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody id="tablaArticulosClienteBody"></tbody>
                                        </table>
                                    </div>
                                </div>

                                {{-- Tabla tickets --}}
                                <div>
                                    <h3 class="mb-2">Tickets</h3>
                                    <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                                        <table class="table table-bordered table-striped table-sm align-middle mb-0" id="tablaTicketsCliente">
                                            <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                                                <tr>
                                                    <th class="sortable-cli-tickets" data-sort-key="ticket" style="cursor: pointer;">
                                                        Ticket <span class="sort-indicator-cli-tk"></span>
                                                    </th>
                                                    <th class="sortable-cli-tickets" data-sort-key="fecha" style="cursor: pointer;">
                                                        Fecha <span class="sort-indicator-cli-tk"></span>
                                                    </th>
                                                    <th class="text-end">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tablaTicketsClienteBody"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>{{-- /tab-content --}}
            </div>
        </div>
    </div>
</div>

{{-- Modal detalle ticket tab 2 --}}
<div class="modal fade" id="modalTicketCliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle ticket <span id="modalTicketNumero"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="modalTicketLoading" class="text-center py-3 d-none">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>EAN</th>
                                <th>Descripción</th>
                                <th class="text-end">Monto</th>
                            </tr>
                        </thead>
                        <tbody id="modalTicketArticulosBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        window.REPORTE_BUSQUEDA_ARTICULO_DATA_URL            = @json(route('reportes.compras_cliente.busqueda-articulo.data'));
        window.REPORTE_BUSQUEDA_ARTICULO_TICKETS_URL         = @json(route('reportes.compras_cliente.busqueda-articulo.tickets', ['clienteId' => '__ID__']));
        window.REPORTE_BUSQUEDA_CLIENTE_SEARCH_URL           = @json(route('reportes.compras_cliente.busqueda-articulo.clientes'));
        window.REPORTE_BUSQUEDA_CLIENTE_DATA_URL             = @json(route('reportes.compras_cliente.busqueda-articulo.cliente-data', ['clienteId' => '__ID__']));
        window.REPORTE_BUSQUEDA_CLIENTE_TICKET_URL           = @json(route('reportes.compras_cliente.busqueda-articulo.cliente-ticket', ['clienteId' => '__ID__', 'ticket' => '__TICKET__']));
    </script>
    @vite(['resources/js/reportes/busqueda_articulo.js'])
@endpush