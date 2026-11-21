    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 small fw-bold">Fecha y Hora</th>
                            <th class="py-3 small fw-bold">Cliente</th>
                            <th class="py-3 small fw-bold">Asunto</th>
                            <th class="py-3 small fw-bold">Tipo</th>
                            <th class="py-3 small fw-bold">Estado</th>
                            <th style="width: 120px" class="py-3 small fw-bold">Acciones</th>
                            <!--
                            @if($permisos['editar'] || $permisos['eliminar'])
                                <th style="width: 120px" class="py-3 small fw-bold">Acciones</th>
                            @endif
                            -->
                        </tr>
                    </thead>
                    <tbody id="contactosTableBody">
                        @forelse($contactos as $contacto)
                        <tr data-id-agenda="{{ $contacto->id_agenda_contacto }}" data-estado="{{ $contacto->estado }}" data-fecha="{{ $contacto->fecha }}">
                            <td>
                                {{ $contacto->fecha->format('d/m/Y') }} <br>
                                <small class="text-muted">{{ substr($contacto->hora, 0, 5) }} hrs</small>
                            </td>
                            <td>
                                <strong>{{ $contacto->nombre_cliente }}</strong><br>
                                <small class="text-muted"><i class="bi bi-telephone"></i> {{ $contacto->telefono_cliente }}</small>
                            </td>
                            <td>{{ $contacto->asunto }}</td>
                            <td>
                                @php
                                    $tipoInfo = $tiposAgenda->firstWhere('id_tipo', $contacto->tipo);
                                    $tipoNombre = $tipoInfo->nombre ?? 'Desconocido';
                                    $tipoClass = match($contacto->tipo) {
                                        1 => 'bg-info',
                                        2 => 'bg-primary',
                                        3 => 'bg-primary',
                                        4 => 'bg-success',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $tipoClass }}">{{ $tipoNombre }}</span>
                            </td>
                            <td>
                                @php
                                    $estadoClass = match($contacto->estado) {
                                        1 => 'bg-warning',
                                        2 => 'bg-success',
                                        3 => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $estadoClass }}">{{ $contacto->estado_nombre }}</span>
                            </td>
                            @if($permisos['editar'] || $permisos['eliminar'])
                            <td>
                                <div class="btn-group" role="group">
                                    @if($permisos['editar'] && $contacto->estado == 1)
                                        <button type="button" class="btn btn-sm btn-outline-success btn-action"
                                                onclick="marcarRealizado({{ $contacto->id_agenda_contacto }}, '{{ $contacto->nombre_cliente }}')"
                                                title="Realizado">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-action"
                                                onclick="reagendarContacto({{ $contacto->id_agenda_contacto }}, '{{ $contacto->nombre_cliente }}')"
                                                title="Reagendar">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                        
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-action"
                                                onclick="editarContacto({{ $contacto->id_agenda_contacto }})"
                                                title="Editar contacto">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    @endif
                                    
                                    @if($permisos['eliminar'])
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                                onclick="confirmarEliminarContacto({{ $contacto->id_agenda_contacto }}, '{{ $contacto->nombre_cliente }}')"
                                                title="Eliminar contacto">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ ($permisos['crear'] || $permisos['eliminar']) ? 6 : 5 }}" class="text-center py-4">
                                <i class="bi bi-calendar-x" style="font-size: 2rem; color: #ccc;"></i>
                                @if(request()->filled('search_term'))
                                    {{-- Caso: hay búsqueda activa pero no hay resultados --}}
                                    <p class="text-muted mt-2">
                                        No se encontraron contactos para 
                                        "<strong>{{ request('search_term') }}</strong>"
                                    </p>
                                    <button class="btn btn-sm btn-outline-primary" onclick="document.getElementById('buscarContacto').value=''; filtrarContactos();">
                                        <i class="bi bi-arrow-left"></i> Ver todos los contactos
                                    </button>
                                @else
                                    {{-- Caso: tabla vacía de verdad --}}
                                    <p class="text-muted mt-2">No hay contactos programados</p>
                                    @if($permisos['crear'])
                                        <button class="btn btn-sm btn-primary" onclick="document.getElementById('btnNuevoContacto').click()">
                                            <i class="bi bi-plus-circle"></i> Programar primer contacto
                                        </button>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<!-- Paginación -->
@if(method_exists($contactos, 'hasPages') && $contactos->hasPages())
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 px-3 pb-3 paginacion-bloque">

    {{-- Mostrando X hasta Y de Z resultados --}}
    <div class="text-muted small">
        Mostrando <strong>{{ $contactos->firstItem() }}</strong> hasta <strong>{{ $contactos->lastItem() }}</strong>
        de <strong>{{ $contactos->total() }}</strong> resultados
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
        {{ $contactos->appends(request()->query())->links('pagination.bootstrap-5') }}
    </div>
</div>
@endif

