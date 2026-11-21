    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="py-3 small fw-bold">ID</th>
                            <th class="py-3 small fw-bold">Nombre</th>
                            <th class="py-3 small fw-bold">Usuario</th>
                            <th class="py-3 small fw-bold">Estado</th>
                            <th class="py-3 small fw-bold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="permisosTableBody">
                        @forelse($usuarios as $usuario)
                        <tr id="usuario-row-{{ $usuario->id_personal_empresa }}">
                            <td><span class="badge bg-secondary">{{ $usuario->id_personal_empresa }}</span></td>
                            <td><strong>{{ $usuario->nombre_completo }}</strong></td>
                            <td><span class="badge bg-primary">{{ $usuario->usuario }}</span></td>
                            <td>
                                @if($usuario->Activo)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Inactivo</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @if($puedeEditar)
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-action"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditarPermisos"
                                            data-usuario-id="{{ $usuario->id_personal_empresa }}"
                                            title="Editar permisos">
                                        <i class="bi bi-key"></i>
                                    </button>
                                    @endif
                                    @if($puedeEliminar)
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                            onclick="confirmarEliminar('permisos', {{ $usuario->id_personal_empresa }}, '{{ addslashes($usuario->usuario) }}')"
                                            title="Eliminar usuario">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="bi bi-people" style="font-size: 2rem; color: #ccc;"></i>
                                <p class="text-muted mt-2">No hay usuarios registrados</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Paginación -->
            @if(method_exists($usuarios, 'hasPages') && $usuarios->hasPages())
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 paginacion-bloque">

                {{-- Mostrando X hasta Y de Z resultados --}}
                <div class="text-muted small">
                    Mostrando <strong>{{ $usuarios->firstItem() }}</strong> hasta <strong>{{ $usuarios->lastItem() }}</strong>
                    de <strong>{{ $usuarios->total() }}</strong> resultados
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
                    {{ $usuarios->appends(request()->query())->links('pagination.bootstrap-5') }}
                </div>
            </div>
            @endif
        </div>
    </div>
