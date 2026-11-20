<?php

namespace App\Http\Controllers\Reportes;

use App\Exports\PedidosDirectosExport;
use App\Exports\PedidosDirectosDetalleExport;
use App\Models\Cliente;
use App\Models\CatalogoMaestro;
use App\Models\Pedidos\OrdenPedido;
use App\Models\Pedidos\OrdenPedidoDetalle;
use App\Models\Reportes\HistorialVenta;
use App\Models\Sucursal;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PedidosDirectosReporteController
{
    public function __invoke(Request $request)
    {
        $usuario = $request->user();
        abort_unless($usuario->puede('ventas', 'pedidos_anticipo', 'ver'), 403);

        $datos = $request->validate([
            'inicio' => 'nullable|required_with:fin|date_format:Y-m-d',
            'fin' => 'nullable|required_with:inicio|date_format:Y-m-d|after_or_equal:inicio',
            'sucursal' => 'nullable|integer|min:1',
            'detalle' => 'nullable|in:clientes,articulos',
            'cliente' => 'required_if:detalle,articulos|nullable|integer|min:0',
        ]);
        $inicio = $datos['inicio'] ?? today()->startOfMonth()->toDateString();
        $fin = $datos['fin'] ?? today()->toDateString();
        $sucursalId = (int) $usuario->sucursal_asignada;
        abort_if($sucursalId !== 0 && isset($datos['sucursal']) && (int) $datos['sucursal'] !== $sucursalId, 403);

        $sucursalFiltro = $sucursalId ?: ($datos['sucursal'] ?? null);
        $sucursales = Sucursal::query()->whereIn('id_sucursal', OrdenPedido::SUCURSALES_SOBRE_PEDIDO)
            ->when($sucursalId, fn ($query) => $query->whereKey($sucursalId))
            ->orderBy('id_sucursal')->get(['id_sucursal', 'nombre']);
        abort_if(isset($datos['sucursal']) && !$sucursales->contains('id_sucursal', $sucursalFiltro), 404);
        $consultado = isset($datos['inicio'], $datos['fin']);
        abort_if(isset($datos['detalle']) && (!$consultado || !$sucursalFiltro), 422);
        if (!$consultado) {
            abort_if($request->routeIs('reportes.sobre-pedido.excel'), 422, 'Selecciona un rango de fechas.');

            return view('reportes.sobre-pedido.index', compact('sucursales', 'inicio', 'fin', 'sucursalId', 'sucursalFiltro', 'consultado'));
        }

        $finExclusivo = CarbonImmutable::parse($fin)->addDay()->toDateString();
        $pedidos = OrdenPedido::query()->where('activo', true)->where('sobre_pedido', true)
            ->whereIn('status', [2, 3])
            ->whereIn('id_sucursal_origen', $sucursales->modelKeys())
            ->when($sucursalFiltro, fn ($query) => $query->where('id_sucursal_origen', $sucursalFiltro));
        if (isset($datos['detalle'])) {
            return $this->detallePedidos($pedidos, $inicio, $finExclusivo, $datos);
        }
        $articulos = OrdenPedidoDetalle::query()->where('se_elimino', false)
            ->groupBy('id_pedido')
            ->selectRaw('id_pedido, SUM(cantidad) AS unidades, SUM(importe) AS total');

        $resumen = (clone $pedidos)
            ->leftJoinSub($articulos, 'articulos', 'articulos.id_pedido', '=', 'orden_pedido.id_pedido')
            ->whereRaw('COALESCE(fecha_encargo, fecha_pedido) >= ? AND COALESCE(fecha_encargo, fecha_pedido) < ?', [$inicio, $finExclusivo])
            ->groupBy('id_sucursal_origen')
            ->selectRaw('id_sucursal_origen, COUNT(*) AS pedidos,
                SUM(COALESCE(articulos.unidades, 0)) AS unidades,
                SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) AS cerrados,
                SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) AS pendientes,
                SUM(COALESCE(articulos.total, 0)) AS cotizado,
                SUM(CASE WHEN status = 2 AND articulos.total > COALESCE(anticipo_cliente, 0)
                    THEN articulos.total - COALESCE(anticipo_cliente, 0) ELSE 0 END) AS saldo')
            ->get()->keyBy('id_sucursal_origen');

        $ventas = $this->ventasEntregadas($pedidos, $inicio, $finExclusivo);
        $filas = $sucursales->filter(fn (Sucursal $sucursal) => !$sucursalFiltro || $sucursal->getKey() == $sucursalFiltro)
            ->map(function (Sucursal $sucursal) use ($resumen, $ventas) {
            $totales = $resumen->get($sucursal->getKey());

            return [
                $sucursal->nombre,
                (int) ($totales->pedidos ?? 0),
                (float) ($totales->unidades ?? 0),
                (int) ($totales->cerrados ?? 0),
                (int) ($totales->pendientes ?? 0),
                (float) ($totales->cotizado ?? 0),
                (float) ($ventas[$sucursal->getKey()] ?? 0),
                (float) ($totales->saldo ?? 0),
                $sucursal->getKey(),
            ];
        });
        $general = $sucursalId === 0 && !$sucursalFiltro && $filas->isNotEmpty()
            ? ['General', ...array_map(fn (int $columna) => $filas->sum($columna), range(1, 7))]
            : null;
        $encabezados = ['#', 'Sucursal', 'Pedidos generados', 'Unidades encargadas', 'Pedidos cerrados',
            'Pedidos en proceso', 'Importe estimado / cotizado', 'Importe vendido (POS)', 'Saldo pendiente'];

        if ($request->routeIs('reportes.sobre-pedido.excel')) {
            $filasExcel = $filas->values()->map(fn (array $fila, int $indice) => [$indice + 1, ...array_slice($fila, 0, 8)]);
            if ($general) {
                $filasExcel->push([null, ...$general]);
            }

            $registrados = $this->pedidosEnRango($pedidos, $inicio, $finExclusivo);
            $resumenExcel = new PedidosDirectosExport($filasExcel, $encabezados, $inicio, $fin);
            $detalleExcel = new PedidosDirectosDetalleExport($this->filasDetalleExcel($registrados, $sucursales->keyBy('id_sucursal')));
            $libro = new class($resumenExcel, $detalleExcel) implements WithMultipleSheets {
                public function __construct(
                    private readonly PedidosDirectosExport $resumen,
                    private readonly PedidosDirectosDetalleExport $detalle,
                ) {}

                public function sheets(): array
                {
                    return [$this->resumen, $this->detalle];
                }
            };

            return Excel::download($libro, "sobre-pedido-{$inicio}-{$fin}.xlsx");
        }

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('reportes.sobre-pedido.resultado', compact('filas', 'general', 'encabezados', 'inicio', 'fin'))->render(),
                'excel' => $filas->isEmpty() ? null : route('reportes.sobre-pedido.excel', [
                    'inicio' => $inicio, 'fin' => $fin, 'sucursal' => $sucursalFiltro,
                ], false),
            ]);
        }

        return view('reportes.sobre-pedido.index', compact('filas', 'general', 'encabezados', 'sucursales', 'inicio', 'fin', 'sucursalId', 'sucursalFiltro', 'consultado'));
    }

    private function ventasEntregadas(\Illuminate\Database\Eloquent\Builder $pedidos, string $inicio, string $fin)
    {
        $ventas = $this->lineasVenta($pedidos, $inicio, $fin);

        return $ventas->whereExists((clone $this->encargoPorVenta($pedidos, $inicio, $fin))->selectRaw('1'))
            ->groupBy('venta.id_sucursal')
            ->selectRaw('venta.id_sucursal, SUM(TRY_CONVERT(decimal(18,2), venta.F_MONTO)) AS monto')
            ->pluck('monto', 'id_sucursal');
    }

    private function encargoPorVenta($pedidos, string $inicio, string $fin)
    {
        return (clone $pedidos)->where('status', 3)->whereNotNull('folio_ticket_entrega')
            ->where('fecha_entrega_real', '>=', $inicio)->where('fecha_entrega_real', '<', $fin)
            ->join('orden_pedido_detalle as detalle', 'detalle.id_pedido', '=', 'orden_pedido.id_pedido')
            ->where('detalle.se_elimino', false)
            ->whereColumn('id_sucursal_origen', 'venta.id_sucursal')
            ->whereRaw("COALESCE(NULLIF(CASE WHEN detalle.ean NOT LIKE 'T%' THEN detalle.ean END, ''), detalle.ean_consultado) = venta.F_CODBAR")
            ->whereRaw('folio_ticket_entrega = TRY_CONVERT(int, venta.F_NUMTICKE)')
            ->whereRaw('CAST(fecha_entrega_real AS date) = CAST(venta.FECHA_DT AS date)')
            ->toBase();
    }

    private function lineasVenta($pedidos, string $inicio, string $fin)
    {
        $historial = new HistorialVenta;
        $tabla = $historial->getConnection()->getDatabaseName() . '.dbo.' . $historial->getTable();
        return $pedidos->getConnection()->table($tabla . ' as venta')
            ->where('venta.FECHA_DT', '>=', $inicio)->where('venta.FECHA_DT', '<', $fin)
            ->whereRaw('TRY_CONVERT(decimal(18,2), venta.F_MONTO) <> 0')
            ->where(fn ($query) => $query->whereNull('venta.F_STATUS')->orWhereNotIn('venta.F_STATUS', ['C', 'D']));
    }

    private function detallePedidos($pedidos, string $inicio, string $fin, array $datos)
    {
        $registrados = $this->pedidosEnRango($pedidos, $inicio, $fin);
        if ($datos['detalle'] === 'clientes') {
            $articulos = OrdenPedidoDetalle::query()->where('se_elimino', false)->groupBy('id_pedido')
                ->selectRaw('id_pedido, SUM(cantidad) AS unidades, SUM(importe) AS total');
            $resumen = $registrados->leftJoinSub($articulos, 'articulos', 'articulos.id_pedido', '=', 'orden_pedido.id_pedido')
                ->groupByRaw('COALESCE(id_cliente, 0)')
                ->selectRaw('COALESCE(id_cliente, 0) AS cliente, SUM(COALESCE(articulos.unidades, 0)) AS unidades,
                    SUM(COALESCE(anticipo_cliente, 0)) AS anticipo, SUM(COALESCE(articulos.total, 0)) AS estimado,
                    SUM(CASE WHEN status = 2 AND articulos.total > COALESCE(anticipo_cliente, 0)
                        THEN articulos.total - COALESCE(anticipo_cliente, 0) ELSE 0 END) AS saldo')->get();
            $clientes = Cliente::whereIntegerInRaw('id_Cliente', $resumen->pluck('cliente')->filter()->all())
                ->get(['id_Cliente', 'Nombre', 'apPaterno', 'apMaterno'])->keyBy('id_Cliente');
            $encargo = $this->encargoPorVenta($pedidos, $inicio, $fin)->orderBy('orden_pedido.id_pedido')->limit(1);
            $ventas = $this->lineasVenta($pedidos, $inicio, $fin)
                ->selectSub($encargo->selectRaw('COALESCE(orden_pedido.id_cliente, 0)'), 'cliente')
                ->selectRaw('TRY_CONVERT(decimal(18,2), venta.F_MONTO) AS monto');
            $vendido = $pedidos->getConnection()->query()->fromSub($ventas, 'ventas')->whereNotNull('cliente')
                ->groupBy('cliente')->selectRaw('cliente, SUM(monto) AS monto')->pluck('monto', 'cliente');

            return response()->json($resumen->map(fn ($fila) => [
                'id' => (int) $fila->cliente,
                'nombre' => $clientes->get($fila->cliente)?->nombre_completo ?: ($fila->cliente ? "Cliente {$fila->cliente}" : 'Cliente sin registro'),
                'unidades' => $fila->unidades,
                'anticipo' => $fila->anticipo,
                'estimado' => $fila->estimado,
                'vendido' => $vendido[$fila->cliente] ?? 0,
                'saldo' => $fila->saldo,
            ])->sortBy('nombre')->values());
        }

        $cliente = (int) $datos['cliente'];
        $registrados->where(fn ($query) => $cliente === 0
            ? $query->whereNull('id_cliente')->orWhere('id_cliente', 0)
            : $query->where('id_cliente', $cliente));

        return response()->json($this->articulosDePedidos($registrados));
    }

    private function pedidosEnRango(\Illuminate\Database\Eloquent\Builder $pedidos, string $inicio, string $fin): \Illuminate\Database\Eloquent\Builder
    {
        return (clone $pedidos)->whereRaw(
            'COALESCE(fecha_encargo, fecha_pedido) >= ? AND COALESCE(fecha_encargo, fecha_pedido) < ?',
            [$inicio, $fin],
        );
    }

    private function filasDetalleExcel(\Illuminate\Database\Eloquent\Builder $registrados, \Illuminate\Support\Collection $sucursales): \Illuminate\Support\Collection
    {
        $pedidos = (clone $registrados)->with('clienteDirecto:id_Cliente,Nombre,apPaterno,apMaterno')
            ->get(['id_pedido', 'id_cliente', 'id_sucursal_origen', 'folio_pedido', 'status', 'folio_ticket_entrega', 'fecha_encargo', 'fecha_pedido'])
            ->keyBy('id_pedido');

        return $this->articulosDePedidos($registrados)->map(function ($articulo) use ($pedidos, $sucursales) {
            $pedido = $pedidos->get($articulo->id_pedido);
            $vendido = $pedido->status === 3;
            $fecha = $pedido->fecha_encargo ?? $pedido->fecha_pedido;

            return [
                $sucursales->get($pedido->id_sucursal_origen)->nombre,
                $pedido->clienteDirecto?->nombre_completo ?: 'Cliente sin registro',
                $pedido->folio_pedido,
                (string) ($articulo->ean_vendido ?? $articulo->codigo),
                $articulo->descripcion,
                (float) $articulo->cantidad,
                (float) $articulo->monto,
                $vendido ? 'Sí' : 'No',
                $vendido && $pedido->folio_ticket_entrega !== null ? (string) $pedido->folio_ticket_entrega : null,
                $fecha ? Date::dateTimeToExcel($fecha->copy()->startOfDay()) : null,
            ];
        });
    }

    private function articulosDePedidos(\Illuminate\Database\Eloquent\Builder $registrados): \Illuminate\Support\Collection
    {
        $filas = OrdenPedidoDetalle::query()->where('se_elimino', false)
            ->whereIn('id_pedido', (clone $registrados)->select('id_pedido'))
            ->selectRaw("COALESCE(NULLIF(TRIM(ean_consultado), ''), TRIM(ean)) AS codigo,
                descripcion_sobre_pedido AS descripcion, cantidad, precio_unitario AS precio,
                CAST(cantidad * precio_unitario AS decimal(18,2)) AS monto")
            ->addSelect('id_pedido', 'ean', 'ean_consultado')
            ->orderBy('id_pedido')->orderBy('id_detalle_pedido')->get();
        $temporales = $filas->filter(fn ($fila) => preg_match('/^T[0-9]{12}$/D', (string) $fila->codigo));
        if ($temporales->isEmpty()) {
            return $filas;
        }

        $tickets = (clone $registrados)->where('status', 3)->whereNotNull('folio_ticket_entrega')
            ->whereNotNull('fecha_entrega_real')
            ->selectRaw('id_pedido, id_sucursal_origen, folio_ticket_entrega, CAST(fecha_entrega_real AS date) AS fecha');
        $historial = new HistorialVenta;
        $tabla = $historial->getConnection()->getDatabaseName() . '.dbo.' . $historial->getTable();
        $ventas = $registrados->getConnection()->query()->fromSub($tickets, 'ticket')
            ->join($tabla . ' as venta', function ($join) {
                $join->on('ticket.id_sucursal_origen', '=', 'venta.id_sucursal')
                    ->whereRaw('ticket.folio_ticket_entrega = TRY_CONVERT(int, venta.F_NUMTICKE)')
                    ->whereColumn('venta.FECHA_DT', '>=', 'ticket.fecha')
                    ->whereRaw('venta.FECHA_DT < DATEADD(day, 1, ticket.fecha)');
            })
            ->where('venta.F_MONTO', '<>', 0)
            ->where(fn ($query) => $query->whereNull('venta.F_STATUS')->orWhereNotIn('venta.F_STATUS', ['C', 'D']))
            ->whereNotNull('venta.F_CODBAR')
            ->distinct()->get(['ticket.id_pedido', 'venta.F_CODBAR']);
        $catalogo = $ventas->pluck('F_CODBAR')->unique()->chunk(1000)
            ->flatMap(fn ($codigos) => CatalogoMaestro::whereIn('EAN', $codigos->all())->get(['EAN', 'descripcion']))
            ->groupBy(fn ($articulo) => trim((string) $articulo->EAN));
        $porPedido = $ventas->groupBy('id_pedido');
        $normalizar = static fn ($texto) => trim(preg_replace('/[^A-Z0-9]+/', ' ', strtoupper(\Illuminate\Support\Str::ascii((string) $texto))));

        foreach ($temporales as $fila) {
            $fila->temporal = $fila->codigo;
            $codigos = ($porPedido->get($fila->id_pedido) ?? collect())
                ->pluck('F_CODBAR')->map(fn ($codigo) => trim((string) $codigo))->filter()->unique();
            $descripcion = $normalizar($fila->descripcion);
            $candidatos = $codigos->filter(fn ($codigo) => $descripcion !== ''
                && $catalogo->get($codigo)?->contains(fn ($articulo) => $normalizar($articulo->descripcion) === $descripcion));
            $fila->ean_vendido = $candidatos->count() === 1 ? $candidatos->first() : null;
        }

        return $filas;
    }
}
