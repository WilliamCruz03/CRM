    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                            <th class="py-3 small fw-bold">ID</th>
                            <th class="py-3 small fw-bold">Interés</th>
                            <th class="py-3 small fw-bold text-center">Fecha de registro</th>
                            <th class="py-3 small fw-bold">Acciones</th>
                        </thead>
                    <tbody id="interesesTableBody">
                        @forelse($intereses as $interes)
                        <tr id="interes-row-{{ $interes->id_interes }}">
                            <td><span class="badge bg-secondary">{{ $loop->iteration }}</span></td>
                            <td>{{ $interes->Descripcion }}</td>
                            <td>
                                <small class="text-muted">
                                    <i class="bi bi-calendar3"></i> 
                                    {{ $interes->fecha_creacion ? \Carbon\Carbon::parse($interes->fecha_creacion)->format('d/m/Y H:i') : 'No especificada' }}
                                </small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @if($puedeEditar)
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-action"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditarInteres"
                                            data-interes-id="{{ $interes->id_interes }}"
                                            title="Editar interés">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @endif
                                    @if($puedeEliminar)
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                            onclick="confirmarEliminar('interes', {{ $interes->id_interes }}, '{{ addslashes($interes->Descripcion) }}')"
                                            title="Eliminar interés">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <i class="bi bi-star" style="font-size: 2rem; color: #ccc;"></i>
                                <p class="text-muted mt-2">No hay intereses registrados</p>
                                @if($puedeCrear)
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevoInteres">
                                    <i class="bi bi-plus"></i> Agregar primer interés
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
        @if(method_exists($intereses, 'hasPages') && $intereses->hasPages())
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 px-3 pb-3 paginacion-bloque">

            {{-- Mostrando X hasta Y de Z resultados --}}
            <div class="text-muted small">
                Mostrando <strong>{{ $intereses->firstItem() }}</strong> hasta <strong>{{ $intereses->lastItem() }}</strong>
                de <strong>{{ $intereses->total() }}</strong> resultados
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
                {{ $intereses->appends(request()->query())->links('pagination.bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>
