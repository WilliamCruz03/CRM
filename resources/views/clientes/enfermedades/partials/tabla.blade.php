    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                            <th class="py-3 small fw-bold">ID</th>
                            <th class="py-3 small fw-bold">Patología</th>
                            <th class="py-3 small fw-bold text-center">Fecha de registro</th>
                            <th class="py-3 small fw-bold">Acciones</th>
                        </thead>
                    <tbody id="patologiasTableBody">
                        @forelse($patologias as $patologia)
                        <tr id="patologia-row-{{ $patologia->id_patologia }}">
                            <td><span class="badge bg-secondary">{{ $loop->iteration }}</span></td>
                            <td>{{ $patologia->descripcion }}</td>
                            <td>
                                <small class="text-muted">
                                    <i class="bi bi-calendar3"></i> 
                                    {{ $patologia->fecha_creacion ? \Carbon\Carbon::parse($patologia->fecha_creacion)->format('d/m/Y H:i') : 'No especificada' }}
                                </small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @if($puedeEditar)
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-action"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditarPatologia"
                                            data-patologia-id="{{ $patologia->id_patologia }}"
                                            title="Editar patología">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @endif
                                    @if($puedeEliminar)
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                            onclick="confirmarEliminar('patologia', {{ $patologia->id_patologia }}, '{{ addslashes($patologia->descripcion) }}')"
                                            title="Eliminar patología">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <i class="bi bi-heart-pulse" style="font-size: 2rem; color: #ccc;"></i>
                                <p class="text-muted mt-2">No hay patologías registradas</p>
                                @if($puedeCrear)
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevaPatologia">
                                    <i class="bi bi-plus"></i> Agregar primera patología
                                </button>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Paginación -->
        @if(method_exists($patologias, 'hasPages') && $patologias->hasPages())
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 px-3 pb-3 paginacion-bloque">

            {{-- Mostrando X hasta Y de Z resultados --}}
            <div class="text-muted small">
                Mostrando <strong>{{ $patologias->firstItem() }}</strong> hasta <strong>{{ $patologias->lastItem() }}</strong>
                de <strong>{{ $patologias->total() }}</strong> resultados
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
                {{ $patologias->appends(request()->query())->links('pagination.bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>
