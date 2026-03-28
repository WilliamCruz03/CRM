<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Contacto</th>
                <th>Dirección</th>
                <th>Patologías</th>
                <th>Status</th>
                <th>Acciones</th>
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
                        <i class="bi bi-envelope text-muted"></i> {{ $cliente->email1 }}<br>
                        @if($cliente->telefono1)
                            <i class="bi bi-telephone text-muted"></i> {{ $cliente->telefono1 }}
                        @endif
                        @if($cliente->telefono2)
                            <br><i class="bi bi-telephone text-muted"></i> {{ $cliente->telefono2 }} (sec)
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
                        $statusClass = match($cliente->status) {
                            'CLIENTE' => 'bg-success',
                            'PROSPECTO' => 'bg-warning',
                            'BLOQUEADO' => 'bg-danger',
                            default => 'bg-secondary'
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ $cliente->status }}</span>
                </td>
                <td>
                    <td>
                        <td>
                            <div class="btn-group" role="group">
                                @if($cliente->status !== 'BLOQUEADO')
                                    {{-- Cliente NO bloqueado - mostrar todos los botones --}}
                                    
                                    <a href="{{ route('clientes.show', $cliente->id_Cliente) }}"
                                    class="btn btn-sm btn-outline-info btn-action" title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    
                                    @can('clientes.directorio.editar')
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-action" 
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditarCliente"
                                            data-cliente-id="{{ $cliente->id_Cliente }}"
                                            title="Editar cliente">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @endcan
                                    
                                    @can('clientes.directorio.eliminar')
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                            onclick="confirmarEliminar('cliente', {{ $cliente->id_Cliente }}, '{{ addslashes($cliente->nombre_completo) }}')"
                                            title="Eliminar cliente">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endcan
                                    
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                            onclick="toggleClienteBlock({{ $cliente->id_Cliente }}, '{{ addslashes($cliente->nombre_completo) }}', 'bloquear')"
                                            title="Bloquear cliente">
                                        <i class="bi bi-lock"></i>
                                    </button>
                                @else
                                    {{-- Cliente bloqueado - solo botón desbloquear --}}
                                    <button type="button" class="btn btn-sm btn-outline-success btn-action"
                                            onclick="toggleClienteBlock({{ $cliente->id_Cliente }}, '{{ addslashes($cliente->nombre_completo) }}', 'desbloquear')"
                                            title="Desbloquear cliente">
                                        <i class="bi bi-unlock"></i> Desbloquear
                                    </button>
                                @endif
                            </div>
                        </td>
                    </td>
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

{{-- Paginación simple de Bootstrap --}}
@if(method_exists($clientes, 'links'))
<div class="d-flex justify-content-between align-items-center mt-3 px-3 pb-3">
    <div class="text-muted small">
        Mostrando {{ $clientes->firstItem() }} - {{ $clientes->lastItem() }} de {{ $clientes->total() }} registros
    </div>
    <nav>
        {{ $clientes->links('pagination::bootstrap-5') }}
    </nav>
</div>
@endif