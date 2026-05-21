{{-- resources/views/reportes/partials/filtros_fecha.blade.php --}}
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card card-secondary">
            <div class="card-header">
                <h5 class="card-title">Filtros de Fecha</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label>Rápido:</label>
                        <select name="filtro_fecha" class="form-control" onchange="this.form.submit()">
                            <option value="hoy" {{ request('filtro_fecha') == 'hoy' ? 'selected' : '' }}>Hoy</option>
                            <option value="esta_semana" {{ request('filtro_fecha') == 'esta_semana' ? 'selected' : '' }}>Esta semana</option>
                            <option value="este_mes" {{ request('filtro_fecha') == 'este_mes' ? 'selected' : '' }}>Este mes</option>
                            <option value="este_ano" {{ request('filtro_fecha') == 'este_ano' ? 'selected' : '' }}>Este año</option>
                            <option value="personalizado" {{ request('filtro_fecha') == 'personalizado' ? 'selected' : '' }}>Personalizado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Fecha Inicio:</label>
                        <input type="date" name="fecha_inicio" class="form-control" 
                               value="{{ request('fecha_inicio', $fechaInicio ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label>Fecha Fin:</label>
                        <input type="date" name="fecha_fin" class="form-control" 
                               value="{{ request('fecha_fin', $fechaFin ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">Aplicar Filtros</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>