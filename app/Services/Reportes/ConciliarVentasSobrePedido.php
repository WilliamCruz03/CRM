<?php

namespace App\Services\Reportes;

use App\Models\CatalogoMaestro;
use App\Models\Reportes\HistorialVenta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ConciliarVentasSobrePedido
{
    public function obtener(Builder $registrados, Collection $filas): Collection
    {
        if ($filas->isEmpty()) return $filas;

        $pedidos = (clone $registrados)->reorder()->select([
            'id_pedido', 'id_cliente', 'id_sucursal_origen', 'status', 'folio_ticket_entrega',
            'fecha_encargo', 'fecha_pedido', 'fecha_entrega_real',
        ]);
        $cabeceras = (clone $pedidos)->get()->keyBy('id_pedido');
        foreach ($filas as $fila) {
            $cabecera = $cabeceras->get($fila->id_pedido);
            $fila->id_cliente = $cabecera->id_cliente ?? 0;
            $fila->id_sucursal_origen = $cabecera->id_sucursal_origen;
            $fila->folio_ticket_entrega = $cabecera->folio_ticket_entrega;
            $fila->estado = $cabecera->status === 3 ? 'Entregado' : 'En proceso';
            $fila->fecha_encargo = ($cabecera->fecha_encargo ?? $cabecera->fecha_pedido)?->toDateString();
        }

        $tickets = $pedidos->where('status', 3)->whereNotNull('folio_ticket_entrega')->whereNotNull('fecha_entrega_real');
        $historial = new HistorialVenta;
        $tabla = $historial->getConnection()->getDatabaseName() . '.dbo.' . $historial->getTable();
        $ventas = $registrados->getConnection()->query()->fromSub($tickets, 'ticket')
            ->join($tabla . ' as venta', function ($join) {
                $join->on('ticket.id_sucursal_origen', '=', 'venta.id_sucursal')
                    ->whereRaw('ticket.folio_ticket_entrega = TRY_CONVERT(int, venta.F_NUMTICKE)')
                    ->whereRaw('venta.FECHA_DT >= CAST(COALESCE(ticket.fecha_encargo, ticket.fecha_pedido) AS date)')
                    ->whereRaw('venta.FECHA_DT < DATEADD(day, 1, CAST(ticket.fecha_entrega_real AS date))');
            })
            ->whereRaw('TRY_CONVERT(decimal(18,2), venta.F_MONTO) <> 0')
            ->where(fn ($query) => $query->whereNull('venta.F_STATUS')->orWhereNotIn('venta.F_STATUS', ['C', 'D']))
            ->whereNotNull('venta.F_CODBAR')
            ->groupByRaw('ticket.id_pedido, ticket.id_sucursal_origen, ticket.folio_ticket_entrega, TRIM(venta.F_CODBAR), CAST(venta.FECHA_DT AS date)')
            ->selectRaw('ticket.id_pedido, ticket.id_sucursal_origen AS sucursal, ticket.folio_ticket_entrega AS ticket,
                TRIM(venta.F_CODBAR) AS codigo, CAST(venta.FECHA_DT AS date) AS fecha,
                SUM(TRY_CONVERT(decimal(18,2), venta.F_MONTO)) AS monto')
            ->get();
        $codigosTemporales = $filas->contains(fn ($fila) => preg_match('/^T[0-9]{12}$/D', (string) $fila->codigo))
            ? $ventas->pluck('codigo')->unique() : collect();
        $catalogo = $codigosTemporales->chunk(1000)
            ->flatMap(fn ($codigos) => CatalogoMaestro::whereIn('EAN', $codigos->all())->get(['EAN', 'descripcion']));

        return $this->conciliar($filas, $ventas, $catalogo);
    }

    public function conciliar(Collection $filas, Collection $ventas, Collection $catalogo): Collection
    {
        $porPedido = $ventas->groupBy('id_pedido');
        $descripciones = $catalogo->groupBy(fn ($articulo) => trim((string) $articulo->EAN));
        $normalizar = static fn ($texto) => trim(preg_replace('/[^A-Z0-9]+/', ' ', strtoupper(Str::ascii((string) $texto))));
        $asignaciones = collect();

        foreach ($filas as $fila) {
            $fila->monto_vendido = null;
            $fila->ean_vendido = null;
            $codigo = trim((string) $fila->codigo);
            $temporales = preg_match('/^T[0-9]{12}$/D', $codigo);
            if ($temporales) $fila->temporal = $codigo;
            $lineas = $porPedido->get($fila->id_pedido, collect());
            if ($temporales) {
                $descripcion = $normalizar($fila->descripcion);
                $candidatos = $lineas->pluck('codigo')->unique()->filter(fn ($ean) => $descripcion !== ''
                    && $descripciones->get($ean)?->contains(fn ($articulo) => $normalizar($articulo->descripcion) === $descripcion));
                if ($candidatos->count() !== 1) continue;
                $codigo = $candidatos->first();
            }
            $coincidencias = $lineas->filter(fn ($venta) => trim((string) $venta->codigo) === (string) $codigo);
            // El ticket puede repetirse en distintas fechas: no adjudicar una venta ambigua.
            if ($coincidencias->isEmpty() || $coincidencias->pluck('fecha')->unique()->count() !== 1) continue;

            $fila->ean_vendido = $codigo;
            $venta = $coincidencias->first();
            $clave = isset($venta->sucursal, $venta->ticket)
                ? $venta->sucursal . '|' . $venta->ticket . '|' . $venta->fecha . '|' . $codigo
                : $fila->id_pedido . '|' . $codigo;
            $asignaciones->push((object) ['clave' => $clave, 'fila' => $fila, 'monto' => $coincidencias->sum('monto')]);
        }

        foreach ($asignaciones->groupBy('clave') as $coincidencias) {
            // Repartir la venta si el EAN aparece en varias líneas o pedidos del mismo ticket.
            $centavos = (int) round($coincidencias->first()->monto * 100);
            $cantidad = $coincidencias->sum(fn ($item) => (float) $item->fila->cantidad);
            $restante = $centavos;
            foreach ($coincidencias->values() as $indice => $item) {
                $parte = $indice === $coincidencias->count() - 1 ? $restante
                    : (int) round($centavos * ($cantidad > 0 ? (float) $item->fila->cantidad / $cantidad : 1 / $coincidencias->count()));
                $item->fila->monto_vendido = $parte / 100;
                $restante -= $parte;
            }
        }

        return $filas;
    }
}
