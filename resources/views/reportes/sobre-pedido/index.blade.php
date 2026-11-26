@extends('layouts.app')

@section('title', 'Reporte de Sobre pedido')
@section('page-title', 'Reporte de Sobre pedido')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form id="filtrosReporteSobrePedido" method="get" action="{{ route('reportes.sobre-pedido.index', [], false) }}" class="row g-3 align-items-end">
                <div class="col-sm-6 col-lg-3">
                    <label for="sucursal" class="form-label">Sucursal</label>
                    <select class="form-select" id="sucursal" name="sucursal" @disabled($sucursales->isEmpty())>
                        @forelse($sucursales as $sucursal)
                            <option value="{{ $sucursal->getKey() }}" @selected($sucursal->getKey() == $sucursalFiltro)>{{ $sucursal->nombre }}</option>
                        @empty
                            <option>Sin sucursal disponible para este reporte</option>
                        @endforelse
                        @if($sucursalId === 0 && $sucursales->isNotEmpty())
                            <option value="" @selected(!$sucursalFiltro)>General</option>
                        @endif
                    </select>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label for="inicio" class="form-label">Desde</label>
                    <input type="date" class="form-control" id="inicio" name="inicio" value="{{ $inicio }}" required>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label for="fin" class="form-label">Hasta</label>
                    <input type="date" class="form-control" id="fin" name="fin" value="{{ $fin }}" required>
                </div>
                <div class="col-sm-6 col-lg-3 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <a id="excelReporteSobrePedido" class="btn btn-success"
                        @if($consultado && $filas->isNotEmpty())
                            href="{{ route('reportes.sobre-pedido.excel', ['inicio' => $inicio, 'fin' => $fin, 'sucursal' => $sucursalFiltro], false) }}"
                        @else
                            hidden
                        @endif
                    ><i class="bi bi-file-earmark-excel"></i> Excel</a>
                </div>
            </form>
            @if($errors->any())
                <div class="alert alert-warning mt-3 mb-0" role="alert">{{ $errors->first() }}</div>
            @endif
        </div>
    </div>

    <div id="resultadoSobrePedido" aria-live="polite">
        @if($consultado)
            @include('reportes.sobre-pedido.resultado')
        @endif
    </div>
</div>
@endsection

@push('scripts')
    <div class="modal fade" id="clientesReporteSobrePedido" tabindex="-1" aria-labelledby="tituloClientesSobrePedido">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title fs-5" id="tituloClientesSobrePedido"></h2>
                        <p class="small text-white mb-0" data-periodo></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" aria-live="polite"></div>
            </div>
        </div>
    </div>
@endpush



@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1" defer></script>
    <script src="{{ asset('js/reporte-sobre-pedido.js') }}?v={{ filemtime(public_path('js/reporte-sobre-pedido.js')) }}" defer></script>
@endpush
