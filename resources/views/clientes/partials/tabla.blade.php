<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th class="ps-3 py-3 small fw-bold">ID</th>
                <th class="ps-3 py-3 small fw-bold">Cliente</th>
                <th class="ps-3 py-3 small fw-bold">Contacto</th>
                <th class="ps-3 py-3 small fw-bold">Dirección</th>
                <th class="ps-3 py-3 small fw-bold">Patologías</th>
                <th class="ps-3 py-3 small fw-bold">Intereses</th>
                <th class="ps-3 py-3 small fw-bold">Status</th>
                <th class="ps-3 py-3 small fw-bold">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clientes as $cliente)
            <tr id="cliente-row-{{ $cliente->id_Cliente }}" class="{{ $cliente->status === 'BLOQUEADO' ? 'table-danger' : '' }}">
                <td><span class="badge bg-secondary">{{ $cliente->id_Cliente }}</span></td>
                <td>
                    <strong>{{ $cliente->nombre_completo }}</strong>
                    @if($cliente->titulo)
                        <br><small class="text-muted">{{ $cliente->titulo }}</small>
                    @endif
                </td>
                <td>
                    <div class="small">
                        @if($cliente->telefono1)
                            <i class="bi bi-telephone text-muted"></i> {{ $cliente->telefono1 }}<br>
                        @endif
                        @if($cliente->telefono2)
                            <i class="bi bi-telephone text-muted"></i> {{ $cliente->telefono2 }} (secundario)<br>
                        @endif
                        @if($cliente->email1)
                            <i class="bi bi-envelope text-muted"></i> {{ $cliente->email1 }}
                        @endif
                        @if(!$cliente->telefono1 && !$cliente->telefono2 && !$cliente->email1)
                            <span class="text-muted">Sin contacto</span>
                        @endif
                    </div>
                </td>
                <td>
                    <small>{{ $cliente->direccion_completa }}</small>
                </td>
                <td>
                    @php
                        $patologiasList = $cliente->patologiasAsociadas ?? collect([]);
                    @endphp
                    @forelse($patologiasList->take(2) as $asociada)
                        <span class="badge bg-info">{{ trim($asociada->patologia) }}</span>
                    @empty
                        <span class="text-muted small">-</span>
                    @endforelse
                    @if($patologiasList->count() > 2)
                        <span class="badge bg-secondary">+{{ $patologiasList->count() - 2 }}</span>
                    @endif
                </td>
                <td>
                    @php
                        $interesesIds = DB::connection('sqlsrv')
                            ->table('crm_cliente_intereses')
                            ->where('id_cliente', $cliente->id_Cliente)
                            ->where('activo', 1)
                            ->pluck('id_interes')
                            ->toArray();
                        
                        $interesesList = collect([]);
                        if (!empty($interesesIds)) {
                            $interesesList = DB::connection('sqlsrvM')
                                ->table('crm_cat_intereses')
                                ->whereIn('id_interes', $interesesIds)
                                ->get(['Descripcion']);
                        }
                    @endphp
                    @forelse($interesesList->take(2) as $interes)
                        <span class="badge bg-primary">{{ trim($interes->Descripcion) }}</span>
                    @empty
                        <span class="text-muted small">-</span>
                    @endforelse
                    @if($interesesList->count() > 2)
                        <span class="badge bg-secondary">+{{ $interesesList->count() - 2 }}</span>
                    @endif
                </td>
                <td>
                    @php
                        $statusClass = match($cliente->status) {
                            'CLIENTE' => 'bg-success',
                            'PROSPECTO' => 'bg-warning',
                            'INACTIVO' => 'bg-secondary',
                            'BLOQUEADO' => 'bg-danger',
                            default => 'bg-secondary'
                        };
                        $statusText = match($cliente->status) {
                            'CLIENTE' => 'Cliente',
                            'PROSPECTO' => 'Prospecto',
                            'INACTIVO' => 'Inactivo',
                            'BLOQUEADO' => 'Bloqueado',
                            default => $cliente->status
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        @if($cliente->status !== 'BLOQUEADO')
                            <a href="{{ route('clientes.show', $cliente->id_Cliente) }}"
                               class="btn btn-sm btn-outline-info btn-action" title="Ver detalles">
                                <i class="bi bi-eye"></i>
                            </a>
                            
                            @if(auth()->user()->puede('clientes', 'directorio', 'editar'))
                            <button type="button" class="btn btn-sm btn-outline-primary btn-action" 
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditarCliente"
                                    data-cliente-id="{{ $cliente->id_Cliente }}"
                                    title="Editar cliente">
                                <i class="bi bi-pencil"></i>
                            </button>
                            @endif
                            
                            @if(auth()->user()->puede('clientes', 'directorio', 'eliminar'))
                            <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                    onclick="confirmarEliminar('cliente', {{ $cliente->id_Cliente }}, '{{ addslashes($cliente->nombre_completo) }}')"
                                    title="Eliminar cliente">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endif
                            <!--
                            @if(auth()->user()->puede('clientes', 'directorio', 'editar'))
                            <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                    onclick="toggleClienteBlock({{ $cliente->id_Cliente }}, '{{ addslashes($cliente->nombre_completo) }}', 'bloquear')"
                                    title="Bloquear cliente">
                                <i class="bi bi-lock"></i>
                            </button>
                            @endif
                            
                        @else
                            @if(auth()->user()->puede('clientes', 'directorio', 'editar'))
                            <button type="button" class="btn btn-sm btn-outline-success btn-action"
                                    onclick="toggleClienteBlock({{ $cliente->id_Cliente }}, '{{ addslashes($cliente->nombre_completo) }}', 'desbloquear')"
                                    title="Desbloquear cliente">
                                <i class="bi bi-unlock"></i> Desbloquear
                            </button>
                            @endif
                        @endif -->
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-4">
                    <i class="bi bi-people" style="font-size: 2rem; color: #ccc;"></i>
                    <p class="text-muted mt-2">No hay clientes registrados</p>
                    @can('clientes.directorio.crear')
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoCliente">
                        <i class="bi bi-plus"></i> Agregar primer cliente
                    </button>
                    @endcan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Paginación -->
@if(method_exists($clientes, 'hasPages') && $clientes->hasPages())
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 paginacion-bloque">

    {{-- Mostrando X hasta Y de Z resultados --}}
    <div class="text-muted small">
        Mostrando <strong>{{ $clientes->firstItem() }}</strong> hasta <strong>{{ $clientes->lastItem() }}</strong>
        de <strong>{{ $clientes->total() }}</strong> resultados
    </div>

    <div class="d-flex align-items-center gap-3">
        {{-- Selector de per_page --}}
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small">Mostrar</span>
            <select id="perPageSelect" class="form-select form-select-sm" style="width: auto;">
                @foreach([5, 10, 15, 20, 50] as $option)
                    <option value="{{ $option }}" {{ (int) request('per_page', 15) === $option ? 'selected' : '' }}>
                        {{ $option }}
                    </option>
                @endforeach
            </select>
            <span class="text-muted small">por página</span>
        </div>

        {{-- Links de paginación --}}
        {{ $clientes->appends(request()->query())->links('pagination.bootstrap-5') }}
    </div>
</div>
@endif