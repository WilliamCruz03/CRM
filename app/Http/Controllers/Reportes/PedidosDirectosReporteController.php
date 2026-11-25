<?php

namespace App\Http\Controllers\Reportes;

use App\Exports\PedidosDirectosExport;
use App\Exports\PedidosDirectosDetalleExport;
use App\Models\Cliente;
use App\Services\Reportes\ConciliarVentasSobrePedido;
use App\Models\Pedidos\OrdenPedido;
use App\Models\Pedidos\OrdenPedidoDetalle;
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
        return $this->articulosDePedidos($this->pedidosEnRango($pedidos, $inicio, $fin))
            ->groupBy('id_sucursal_origen')->map(fn ($filas) => $filas->sum('monto_vendido'));
    }

    private function detallePedidos($pedidos, string $inicio, string $fin, array $datos)
    {
        $registrados = $this->pedidosEnRango($pedidos, $inicio, $fin);
        if ($datos['detalle'] === 'clientes') {
            $articulos = OrdenPedidoDetalle::query()->where('se_elimino', false)->groupBy('id_pedido')
                ->selectRaw('id_pedido, SUM(cantidad) AS unidades, SUM(importe) AS total');
            $resumen = (clone $registrados)->leftJoinSub($articulos, 'articulos', 'articulos.id_pedido', '=', 'orden_pedido.id_pedido')
                ->groupByRaw('COALESCE(id_cliente, 0)')
                ->selectRaw('COALESCE(id_cliente, 0) AS cliente, SUM(COALESCE(articulos.unidades, 0)) AS unidades,
                    SUM(COALESCE(anticipo_cliente, 0)) AS anticipo, SUM(COALESCE(articulos.total, 0)) AS estimado,
                    SUM(CASE WHEN status = 2 AND articulos.total > COALESCE(anticipo_cliente, 0)
                        THEN articulos.total - COALESCE(anticipo_cliente, 0) ELSE 0 END) AS saldo')->get();
            $clientes = Cliente::whereIntegerInRaw('id_Cliente', $resumen->pluck('cliente')->filter()->all())
                ->get(['id_Cliente', 'Nombre', 'apPaterno', 'apMaterno'])->keyBy('id_Cliente');
            $vendido = $this->articulosDePedidos($registrados)->groupBy('id_cliente')
                ->map(fn ($filas) => $filas->sum('monto_vendido'));

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
                $articulo->monto_vendido === null ? null : (float) $articulo->monto_vendido,
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
            ->addSelect('id_detalle_pedido', 'id_pedido', 'ean', 'ean_consultado')
            ->orderBy('id_pedido')->orderBy('id_detalle_pedido')->get();
        return app(ConciliarVentasSobrePedido::class)->obtener($registrados, $filas);
    }
}
