    @if($filas->isNotEmpty())
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Pedidos generados por sucursal</div>
            <div class="card-body" style="height: 320px">
                <canvas id="graficaSobrePedido" data-grafica="{{ json_encode($filas->map(fn ($fila) => [$fila[0], $fila[1]])->values(), JSON_THROW_ON_ERROR) }}" role="img" aria-label="Gráfica de barras de pedidos generados por sucursal"></canvas>
            </div>
        </div>
    @endif
    <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">Resumen por sucursal</div>
        <div class="px-3 py-2 small text-muted">El resumen usa la fecha de encargo y, si está vacía, la de registro. El importe vendido en POS usa la fecha de la venta.</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        @foreach($encabezados as $encabezado)
                            <th scope="col" @class(['text-end' => $loop->index > 1])>
                                <button type="button" class="btn btn-sm btn-link text-reset text-decoration-none p-0" data-orden="{{ $loop->index }}">{{ $encabezado }} <i class="bi bi-arrow-down-up" aria-hidden="true"></i></button>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($filas as $fila)
                        <tr style="cursor: pointer" data-sucursal="{{ $fila[0] }}"
                            data-clientes="{{ route('reportes.sobre-pedido.index', ['sucursal' => $fila[8], 'inicio' => $inicio, 'fin' => $fin, 'detalle' => 'clientes'], false) }}">
                            <td>{{ $loop->iteration }}</td>
                            <th scope="row"><button type="button" class="btn btn-sm p-0 text-reset fw-semibold" aria-expanded="false">{{ $fila[0] }}</button></th>
                            @foreach(array_slice($fila, 1, 7) as $valor)
                                <td class="text-end text-nowrap" data-valor="{{ $valor }}">{{ $loop->index >= 4
                                    ? '$' . number_format($valor, 2)
                                    : number_format($valor, $loop->index === 1 && floor($valor) !== $valor ? 2 : 0) }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($encabezados) }}" class="text-muted text-center py-4">No hay sucursales disponibles para tu cuenta en este reporte.</td></tr>
                    @endforelse
                </tbody>
                @if($general)
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td></td>
                            <th scope="row">{{ $general[0] }}</th>
                            @foreach(array_slice($general, 1) as $valor)
                                <td class="text-end text-nowrap">{{ $loop->index >= 4
                                    ? '$' . number_format($valor, 2)
                                    : number_format($valor, $loop->index === 1 && floor($valor) !== $valor ? 2 : 0) }}</td>
                            @endforeach
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
