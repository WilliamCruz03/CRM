<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Contacto</th>
                <th>Dirección</th>
                <th>Enfermedades</th>
                <th>Preferencias</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clientes as $cliente)
            <tr id="cliente-row-{{ $cliente->id }}">
                <td><span class="badge bg-secondary">{{ $cliente->id }}</span></td>
                <td>
                    <strong>{{ $cliente->nombre_completo }}</strong>
                </td>
                <td>
                    <div class="small">
                        <i class="bi bi-envelope text-muted"></i> {{ $cliente->email }}<br>
                        @if($cliente->telefono)
                            <i class="bi bi-telephone text-muted"></i> {{ $cliente->telefono }}
                        @endif
                    </div>
                </td>
                <td>
                    <small>{{ $cliente->direccion_completa }}</small>
                </td>
                <td>
                    @forelse($cliente->enfermedades->take(2) as $enfermedad)
                        <span class="badge bg-info">{{ $enfermedad->nombre }}</span>
                    @empty
                        <span class="text-muted small">-</span>
                    @endforelse
                    @if($cliente->enfermedades->count() > 2)
                        <span class="badge bg-secondary">+{{ $cliente->enfermedades->count() - 2 }}</span>
                    @endif
                </td>
                <td>
                    @forelse($cliente->preferencias->take(1) as $preferencia)
                        <small class="text-muted">{{ \Illuminate\Support\Str::limit($preferencia->descripcion, 30) }}</small>
                    @empty
                        <span class="text-muted small">-</span>
                    @endforelse
                </td>
                <td>
                    <span class="badge-status {{ $cliente->estado == 'Activo' ? 'badge-active' : 'badge-inactive' }}">
                        {{ $cliente->estado }}
                    </span>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <a href="{{ route('clientes.show', $cliente->id) }}" 
                           class="btn btn-sm btn-outline-info btn-action" title="Ver detalles">
                            <i class="bi bi-eye"></i>
                        </a>
                        <!-- Deshabilitado del index para evitar confusión, se edita desde la vista de detalles
                        <button type="button" class="btn btn-sm btn-outline-primary btn-action" 
                                onclick="editarCliente({{ $cliente->id }})" title="Editar cliente">
                            <i class="bi bi-pencil"></i>
                        </button>
                        -->
                        <button type="button" class="btn btn-sm btn-outline-danger btn-action" 
                                onclick="confirmarEliminar('cliente', {{ $cliente->id }}, '{{ $cliente->nombre_completo }}')" 
                                title="Eliminar cliente">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center py-4">
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

<div class="mt-3">
    {{ $clientes->links() }}
</div>