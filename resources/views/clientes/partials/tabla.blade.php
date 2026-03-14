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
            <tr id="cliente-row-{{ $cliente->id_Cliente }}">
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
                    @forelse($cliente->enfermedades->take(2) as $patologia)
                        <span class="badge bg-info">{{ $patologia->descripcion }}</span>
                    @empty
                        <span class="text-muted small">-</span>
                    @endforelse
                    @if($cliente->enfermedades->count() > 2)
                        <span class="badge bg-secondary">+{{ $cliente->enfermedades->count() - 2 }}</span>
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
                    <div class="btn-group" role="group">
                        <a href="{{ route('clientes.show', $cliente->id_Cliente) }}"
                           class="btn btn-sm btn-outline-info btn-action" title="Ver detalles">
                            <i class="bi bi-eye"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                onclick="confirmarEliminar('cliente', {{ $cliente->id_Cliente }}, '{{ $cliente->nombre_completo }}')"
                                title="Eliminar cliente">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-4">
                    <i class="bi bi-people" style="font-size: 2rem; color: #ccc;"></i>
                    <p class="text-muted mt-2">No hay clientes registrados</p>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoCliente">
                        <i class="bi bi-plus"></i> Agregar primer cliente
                    </button>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Paginación --}}
@if(method_exists($clientes, 'links'))
<div class="d-flex justify-content-between align-items-center mt-3 px-3 pb-3">
    <div class="text-muted small">
        Mostrando {{ $clientes->firstItem() }} - {{ $clientes->lastItem() }} de {{ $clientes->total() }} registros
    </div>
    <nav>
        <ul class="pagination pagination-sm mb-0">
            {{ $clientes->links() }}
        </ul>
    </nav>
</div>
@endif