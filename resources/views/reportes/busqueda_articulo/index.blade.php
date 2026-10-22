@extends('layouts.app')

@section('title', 'Búsqueda por Artículo')

@section('page-title', 'Generación de Reportes')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="bi bi-upc-scan"></i> Búsqueda por Artículo
                    </h3>
                </div>

                {{-- Filtros --}}
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
                        <div class="col-md-2 d-grid">
                            <button type="button" id="btnBuscar" class="btn btn-primary">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                    </div>

                    {{-- Encabezado del artículo encontrado --}}
                    <div id="articuloHeader" class="mt-3 d-none">
                        <div class="alert alert-info mb-0">
                            <strong>Código:</strong> <span id="articuloEan">—</span>
                            &mdash;
                            <strong>Descripción:</strong> <span id="articuloDescripcion">—</span>
                        </div>
                    </div>

                    {{-- Alert de error (patrón clientes.blade.php) --}}
                    <div id="errorContainer" class="mt-3 d-none">
                        <div class="alert alert-danger d-flex align-items-center justify-content-between mb-0">
                            <span id="errorMessage">Ocurrió un error.</span>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="btnReintentar">
                                <i class="bi bi-arrow-clockwise"></i> Reintentar
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Barra post-filtro: orden + per_page --}}
                <div id="barraOrden" class="card-body border-bottom d-none">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="sortBySelect" class="form-label">Ordenar por</label>
                            <select id="sortBySelect" class="form-select">
                                <option value="compras_desc">Más compras primero</option>
                                <option value="compras_asc">Menos compras primero</option>
                                <option value="frecuencia_asc">Frecuencia más baja</option>
                                <option value="frecuencia_desc">Frecuencia más alta</option>
                                <option value="ultima_compra_desc">Última compra (reciente)</option>
                                <option value="monto_desc">Monto total (mayor)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="perPageSelect" class="form-label">Resultados por página</label>
                            <select id="perPageSelect" class="form-select">
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="15" selected>15</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Layout dos columnas: lista clientes / panel detalle --}}
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Columna izquierda: lista de clientes --}}
                        <div class="col-lg-7">
                            <div id="loadingIndicator" class="text-center py-4 d-none">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </div>

                            <div id="resultadosContainer">
                                <div class="text-center text-muted py-5" id="placeholderInicial">
                                    <i class="bi bi-upc-scan fs-1 d-block mb-2"></i>
                                    Ingrese un código de barras y un período, luego presione Buscar.
                                </div>

                                <div id="tablaWrapper" class="d-none">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Cliente</th>
                                                    <th class="text-end">Compras</th>
                                                    <th class="text-end">Frecuencia</th>
                                                    <th>Última compra</th>
                                                    <th class="text-end">Monto total</th>
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

                        {{-- Columna derecha: detalle inline --}}
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
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        window.REPORTE_BUSQUEDA_ARTICULO_DATA_URL    = @json(route('reportes.compras_cliente.busqueda-articulo.data'));
        window.REPORTE_BUSQUEDA_ARTICULO_TICKETS_URL = @json(route('reportes.compras_cliente.busqueda-articulo.tickets', ['clienteId' => '__ID__']));
    </script>
    @vite(['resources/js/reportes/busqueda_articulo.js'])
@endpush