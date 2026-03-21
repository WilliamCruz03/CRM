@extends('layouts.app')

@section('title', 'Cotizaciones - CRM')
@section('page-title', 'Gestión de Cotizaciones')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <h3><i class="bi bi-file-earmark-text"></i> Gestión de Cotizaciones</h3>
        <p class="text-muted">Monitorea el estado e interacciones de las cotizaciones</p>
    </div>

    @can('ventas.cotizaciones.ver')
    <!-- Search and Actions -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" id="buscarCotizacion" placeholder="Buscar por folio, cliente o repartidor...">
            </div>
        </div>
        <div class="col-md-6 text-end">
            @can('ventas.cotizaciones.crear')
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevaCotizacion">
                <i class="bi bi-plus-circle"></i> Nueva Cotización
            </button>
            @endcan
        </div>
    </div>

    <!-- Tabla de Cotizaciones -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover" id="tablaCotizaciones">
                    <thead>
                        32
                            <th>Folio</th>
                            <th>Cliente</th>
                            <th>Fecha y hora de emisión</th>
                            <th>Monto</th>
                            <th>Repartidor</th>
                            <th>Estado</th>
                            <th>Clasificación</th>
                            <th>Último Contacto</th>
                            <th>Acciones</th>
                        </thead>
                    <tbody id="cotizacionesTableBody">
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="bi bi-file-earmark-text" style="font-size: 2rem; color: #ccc;"></i>
                                <p class="text-muted mt-2">No hay cotizaciones registradas</p>
                                @can('ventas.cotizaciones.crear')
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevaCotizacion">
                                    <i class="bi bi-plus"></i> Crear primera cotización
                                </button>
                                @endcan
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-3">
            <div class="text-muted small">
                Mostrando <span id="registrosMostrados">0</span> registros
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> No tienes permiso para ver las cotizaciones.
    </div>
    @endcan
</div>

<!-- Modals -->
@include('ventas.cotizaciones.partials.modal-nueva-cotizacion')
@include('ventas.cotizaciones.partials.modal-editar-cotizacion')
@endsection

@push('scripts')
<script>
// Buscador
document.getElementById('buscarCotizacion')?.addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase().trim();
    const rows = document.querySelectorAll('#cotizacionesTableBody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (searchTerm.length === 0 || text.includes(searchTerm)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    document.getElementById('registrosMostrados').textContent = visibleCount;
});
</script>
@endpush