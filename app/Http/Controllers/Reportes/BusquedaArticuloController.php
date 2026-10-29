<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reportes\BusquedaArticuloRequest;
use App\Models\Reportes\HistorialVenta;
use App\Services\Reportes\FrecuenciaCompraService;
use App\Support\EanNormalizer;
use Carbon\Carbon;
use Illuminate\Console\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use App\Models\CatalogoGeneral;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Http\Requests\Reportes\ClienteDataRequest;

final class BusquedaArticuloController extends Controller
{
    private const PER_PAGE_DEFAULT = 15;
    private const PER_PAGE_PERMITIDOS = [5, 10, 15, 20, 50];
    private const SORT_BY_DEFAULT = 'compras_desc';

    public function __construct(
        private readonly FrecuenciaCompraService $frecuenciaService,
    ) {}

    public function index(): View
    {
        return view('reportes.busqueda_articulo.index');
    }

    public function data(BusquedaArticuloRequest $request): JsonResponse
    {
        set_time_limit(300);

        $ean = (string) $request->input('ean');
        $fechaInicio = (string) $request->input('fecha_inicio');
        $fechaFin = (string) $request->input('fecha_fin');

        $perPage = $this->resolverPerPage($request->input('per_page'));
        $sortBy = $this->resolverSortBy($request->input('sort_by'));
        $page = max(1, (int) $request->input('page', 1));
        $offset = ($page - 1) * $perPage;

        $producto = $this->buscarProductoEnCatalogo($ean);
        if ($producto === null) {
            return response()->json([
                'success' => false,
                'message' => 'Código de barras no encontrado',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $diasPeriodo = Carbon::parse($fechaInicio)->diffInDays(Carbon::parse($fechaFin));
        $idsExcluir = ['0000000007295', '0000000004489'];

        $query = HistorialVenta::query()
            ->ventasValidas()
            ->entreFechas($fechaInicio, $fechaFin)
            ->porProducto($ean)
            ->whereNotIn('historial_ventas_matriz.IDCLIENTE', $idsExcluir)
            ->leftJoin(
                'fp_central_matriz.dbo.catalogo_cliente_maestro as c',
                'historial_ventas_matriz.IDCLIENTE',
                '=',
                'c.idtarjetaclientefrecuente'
            )
            ->leftJoin(
                'fp_central_matriz.dbo.sucursales as s',
                'historial_ventas_matriz.id_sucursal',
                '=',
                's.id_sucursal'
            )
            ->where(function ($q) {
                $q->whereNull('c.id_Cliente')
                ->orWhereIn('c.status', Cliente::getActiveStatuses());
            })
            ->select(
                'c.id_Cliente',
                DB::raw("historial_ventas_matriz.IDCLIENTE AS idtarjetaclientefrecuente"),
                DB::raw("COALESCE(NULLIF(LTRIM(RTRIM(CONCAT(c.Nombre, ' ', c.apPaterno, ' ', c.apMaterno))), ''), 'CLIENTE NO IDENTIFICADO') AS nombre_cliente"),
                DB::raw("COALESCE(c.telefono1, c.telefono2) AS telefono"),
                's.id_sucursal',
                DB::raw("COALESCE(s.nombre, '—') AS sucursal"),
                DB::raw('COUNT(DISTINCT historial_ventas_matriz.F_NUMTICKE) AS compras'),
                DB::raw('COUNT(DISTINCT CAST(historial_ventas_matriz.FECHA_DT AS DATE)) AS dias_unicos'),
                DB::raw('SUM(CAST(historial_ventas_matriz.F_MONTO AS DECIMAL(18,2))) AS monto_total'),
                DB::raw('MAX(CAST(historial_ventas_matriz.FECHA_DT AS DATE)) AS ultima_compra'),
                DB::raw('MIN(CAST(historial_ventas_matriz.FECHA_DT AS DATE)) AS primera_compra')
            )
            ->groupBy(
                'c.id_Cliente',
                'historial_ventas_matriz.IDCLIENTE',
                'c.Nombre',
                'c.apPaterno',
                'c.apMaterno',
                'c.telefono1',
                'c.telefono2',
                'c.status',
                's.id_sucursal',
                's.nombre'
            );

        $this->aplicarOrden($query, $sortBy);

        // Traer 1 de más para saber si hay página siguiente.
        $clientesRaw = $query
            ->offset($offset)
            ->limit($perPage + 1)
            ->get();

        $hasMore = $clientesRaw->count() > $perPage;
        $clientes = $clientesRaw->take($perPage);

        // Frecuencia calculada en PHP.
        $clientes->transform(function ($cliente) {
            $diasUnicos = (int) ($cliente->dias_unicos ?? 0);

            if ($diasUnicos < 2) {
                $cliente->frecuencia = null;
            } else {
                $diasEntreCompras = Carbon::parse($cliente->primera_compra)
                    ->diffInDays(Carbon::parse($cliente->ultima_compra));
                $cliente->frecuencia = $diasEntreCompras / ($diasUnicos - 1);
            }

            return $cliente;
        });

        $kpis = $this->calcularKpis($ean, $fechaInicio, $fechaFin);

        return response()->json([
            'success'    => true,
            'articulo'   => [
                'ean'         => $ean,
                'descripcion' => (string) ($producto->descripcion ?? '—'),
            ],
            'kpis'       => $kpis,
            'filtros'    => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin'    => $fechaFin,
                'per_page'     => $perPage,
                'sort_by'      => $sortBy,
                'dias_periodo' => (int) $diasPeriodo,
            ],
            'clientes'   => $clientes->values(),
            'paginacion' => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'has_more'     => $hasMore,
                'links'        => '',  // los links se generan en el front
            ],
        ]);
    }

    /**
     * KPIs del tab 1:
     *  - articulo_mayor_costo: el artículo de mayor costo unitario entre los EANs que aparecen
     *    en los tickets del EAN buscado (catalogo_general tiene costo; catalogo_maestro no).
     *  - inventario_por_costo: valor del inventario (inventario × costo) del EAN buscado por sucursal.
     *  - producto_mayor_venta: EAN con mayor importe vendido en todo el rango (sin filtrar por EAN buscado).
     */
    private function calcularKpis(string $ean, string $fechaInicio, string $fechaFin): array
    {
        set_time_limit(120);

        $idsExcluir = ['0000000007295', '0000000004489'];

        // ------------------------------------------------------------------
        // Query 1: todas las líneas del EAN buscado en el rango (base para KPI 1 y KPI 2).
        // ------------------------------------------------------------------
        $lineasBase = HistorialVenta::query()
            ->ventasValidas()
            ->entreFechas($fechaInicio, $fechaFin)
            ->porProducto($ean)
            ->whereNotIn('historial_ventas_matriz.IDCLIENTE', $idsExcluir)
            ->select(
                'historial_ventas_matriz.F_NUMTICKE',
                'historial_ventas_matriz.id_sucursal',
                'historial_ventas_matriz.F_MONTO',
                'historial_ventas_matriz.F_CANT',
                'historial_ventas_matriz.F_COSTO'
            )
            ->get();

        // Tickets únicos del EAN buscado.
        $ticketsConEan = $lineasBase->pluck('F_NUMTICKE')->unique()->values()->all();

        // Costo por sucursal (KPI 2).
        $costoPorSucursal = $lineasBase
            ->groupBy('id_sucursal')
            ->map(function ($g, $idSucursal) {
                $costo = 0;
                $unidades = 0;
                foreach ($g as $l) {
                    $cant = (float) ($l->F_CANT ?? 0);
                    $costoUnit = (float) ($l->F_COSTO ?? 0);
                    $costo += $cant * $costoUnit;
                    $unidades += $cant;
                }
                return [
                    'sucursal_id' => $idSucursal,
                    'costo_total' => $costo,
                    'unidades'    => $unidades,
                ];
            })
            ->values();

        // Nombres de sucursal.
        $idsSucursales = $costoPorSucursal->pluck('sucursal_id')->filter()->unique()->all();
        $nombresSucursal = empty($idsSucursales) ? [] : DB::connection('sqlsrvM')
            ->table('sucursales')
            ->whereIn('id_sucursal', $idsSucursales)
            ->pluck('nombre', 'id_sucursal')
            ->all();

        $inventarioPorCosto = $costoPorSucursal->map(fn ($r) => [
            'sucursal_id' => $r['sucursal_id'],
            'sucursal'    => $nombresSucursal[$r['sucursal_id']] ?? '—',
            'costo_total' => $r['costo_total'],
            'unidades'    => $r['unidades'],
        ])->all();

        // ------------------------------------------------------------------
        // Query 2: EANs del ticket del EAN buscado (base para KPI 1).
        // ------------------------------------------------------------------
        $eanEnTickets = [];
        if (!empty($ticketsConEan)) {
            $eanEnTickets = HistorialVenta::query()
                ->ventasValidas()
                ->whereIn('F_NUMTICKE', $ticketsConEan)
                ->whereNotNull('F_CODBAR')
                ->where('F_CODBAR', '!=', '')
                ->select('F_CODBAR')
                ->distinct()
                ->pluck('F_CODBAR')
                ->all();
        }

        // Artículo de mayor costo entre esos EANs (KPI 1).
        $articuloMayorCosto = null;
        if (!empty($eanEnTickets)) {
            $row = DB::connection('sqlsrvM')
                ->table('catalogo_general')
                ->whereIn('ean', $eanEnTickets)
                ->whereNotNull('costo')
                ->where('costo', '>', 0)
                ->orderByDesc(DB::raw('CAST(costo AS DECIMAL(18,2))'))
                ->select('ean', 'descripcion', 'costo')
                ->first();

            if ($row) {
                $articuloMayorCosto = [
                    'ean'         => $row->ean,
                    'descripcion' => $row->descripcion,
                    'costo'       => $row->costo,
                ];
            }
        }

        // ------------------------------------------------------------------
        // Query 3: producto con mayor venta en TODO el período (KPI 3).
        // ------------------------------------------------------------------
        $productoMayorVenta = HistorialVenta::query()
            ->ventasValidas()
            ->entreFechas($fechaInicio, $fechaFin)
            ->whereNotIn('historial_ventas_matriz.IDCLIENTE', $idsExcluir)
            ->select(
                'F_CODBAR as ean',
                DB::raw('SUM(CAST(F_MONTO AS DECIMAL(18,2))) AS monto')
            )
            ->groupBy('F_CODBAR')
            ->orderByDesc('monto')
            ->first();

        if ($productoMayorVenta) {
            $desc = DB::connection('sqlsrvM')
                ->table('catalogo_maestro')
                ->where('EAN', $productoMayorVenta->ean)
                ->value('descripcion');

            $productoMayorVenta = [
                'ean'         => $productoMayorVenta->ean,
                'descripcion' => $desc ?: '—',
                'monto'       => $productoMayorVenta->monto,
            ];
        }

        return [
            'articulo_mayor_costo' => $articuloMayorCosto,
            'inventario_por_costo' => $inventarioPorCosto,
            'producto_mayor_venta' => $productoMayorVenta,
        ];
    }

    public function tickets(string $clienteId, BusquedaArticuloRequest $request): JsonResponse
    {
        $ean = (string) $request->input('ean');
        $fechaInicio = (string) $request->input('fecha_inicio');
        $fechaFin = (string) $request->input('fecha_fin');

        $producto = $this->buscarProductoEnCatalogo($ean);
        if ($producto === null) {
            return response()->json([
                'success' => false,
                'message' => 'Código de barras no encontrado',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Resolver idtarjetaclientefrecuente.
        $cliente = Cliente::query()
            ->select('id_Cliente', 'idtarjetaclientefrecuente')
            ->find($clienteId);

        $idTarjeta = ($cliente && $cliente->idtarjetaclientefrecuente)
            ? $cliente->idtarjetaclientefrecuente
            : $clienteId;

        $idSucursal = $request->input('id_sucursal');

        // 1) Tickets que contienen el EAN en el período (ventas válidas).
        $ticketsQuery = HistorialVenta::query()
            ->ventasValidas()
            ->entreFechas($fechaInicio, $fechaFin)
            ->porProducto($ean)
            ->porCliente($idTarjeta);

        if ($idSucursal) {
            $ticketsQuery->where('historial_ventas_matriz.id_sucursal', $idSucursal);
        }

        $tickets = $ticketsQuery
            ->select('F_NUMTICKE', DB::raw('MIN(FECHA_DT) AS FECHA_DT'))
            ->groupBy('F_NUMTICKE')
            ->orderBy('FECHA_DT', 'DESC')
            ->get();

        $numerosTickets = $tickets->pluck('F_NUMTICKE')->all();

        if ($numerosTickets === []) {
            return response()->json([
                'success'  => true,
                'articulo' => [
                    'ean'         => $ean,
                    'descripcion' => (string) ($producto->descripcion ?? '—'),
                ],
                'tickets'  => [],
            ]);
        }

        // 2) Líneas del detalle (sin agrupar todavía) con todas las columnas.
        $lineas = HistorialVenta::query()
            ->porCliente($idTarjeta)
            ->whereIn('historial_ventas_matriz.F_NUMTICKE', $numerosTickets)
            ->leftJoin(
                'fp_central_matriz.dbo.catalogo_maestro as cm',
                'cm.EAN', '=', 'historial_ventas_matriz.F_CODBAR'
            )
            ->leftJoin(
                'fp_central_matriz.dbo.sucursales as s',
                'historial_ventas_matriz.id_sucursal', '=', 's.id_sucursal'
            )
            ->select(
                'historial_ventas_matriz.F_NUMTICKE',
                'historial_ventas_matriz.FECHA_DT',
                'historial_ventas_matriz.id_sucursal',
                'historial_ventas_matriz.F_NUMCAJA',
                'historial_ventas_matriz.F_NUMCAJER',
                'historial_ventas_matriz.F_CODBAR as codigo',
                DB::raw("COALESCE(cm.descripcion, '—') AS descripcion"),
                'historial_ventas_matriz.F_CANT as cantidad',
                'historial_ventas_matriz.F_MONTO as monto',
                DB::raw("COALESCE(historial_ventas_matriz.F_PORDESC, 0) AS pordesc"),
                DB::raw("COALESCE(historial_ventas_matriz.PORDESCONV, 0) AS pordesconv"),
                DB::raw("COALESCE(s.nombre, '—') AS sucursal")
            )
            ->orderBy('historial_ventas_matriz.F_NUMTICKE')
            ->orderBy('historial_ventas_matriz.F_NUMREG')
            ->get();

        // 3) Agrupar por ticket y, dentro de cada ticket, por EAN.
        //    Si el mismo EAN aparece en varias líneas, se suman cantidad y monto.
        $lineasPorTicket = $lineas
            ->groupBy('F_NUMTICKE')
            ->map(function ($grupo) {
                return $grupo
                    ->groupBy('codigo')
                    ->map(function ($items, $codigo) {
                        $first = $items->first();
                        return (object) [
                            'sucursal'    => $first->sucursal,
                            'FECHA_DT'    => $first->FECHA_DT,
                            'id_sucursal' => $first->id_sucursal,
                            'F_NUMCAJA'   => $first->F_NUMCAJA,
                            'F_NUMCAJER'  => $first->F_NUMCAJER,
                            'codigo'      => $codigo,
                            'descripcion' => $first->descripcion,
                            'cantidad'    => $items->sum('cantidad'),
                            'pordesc'     => $first->pordesc,
                            'pordesconv'  => $first->pordesconv,
                            'monto'       => $items->sum('monto'),
                        ];
                    })
                    ->values();
            });

        // 4) Resolver nombre del cajero por (id_sucursal, F_NUMCAJER).
        $nombresCajero = $this->resolverNombresCajero($lineas);

        // 5) Construir payload.
        $ticketsPayload = $tickets->map(function ($ticket) use ($lineasPorTicket, $nombresCajero) {
            $lineasTicket = $lineasPorTicket->get($ticket->F_NUMTICKE, collect());

            $totalTicket = $lineasTicket->sum(fn ($l) => (float) $l->monto);

            return [
                'F_NUMTICKE' => $ticket->F_NUMTICKE,
                'FECHA_DT'   => $ticket->FECHA_DT,
                'total'      => $totalTicket,
                'lineas'     => $lineasTicket->map(function ($l) use ($nombresCajero) {
                    $key = ($l->id_sucursal ?? '') . '|' . trim((string) ($l->F_NUMCAJER ?? ''));
                    return [
                        'sucursal'   => $l->sucursal,
                        'fecha'      => $l->FECHA_DT,
                        'num_caja'   => $l->F_NUMCAJA,
                        'codigo'     => $l->codigo,
                        'descripcion'=> $l->descripcion,
                        'cantidad'   => $l->cantidad,
                        'pordesc'    => $l->pordesc,
                        'pordesconv' => $l->pordesconv,
                        'num_cajero' => $l->F_NUMCAJER,
                        'nom_cajero' => $nombresCajero[$key] ?? '—',
                        'monto'      => $l->monto,
                    ];
                })->values()->all(),
            ];
        });

        return response()->json([
            'success'  => true,
            'articulo' => [
                'ean'         => $ean,
                'descripcion' => (string) ($producto->descripcion ?? '—'),
            ],
            'tickets'  => $ticketsPayload,
        ]);
    }


    /**
     * Resuelve el nombre del cajero cruzando F_NUMCAJER + id_sucursal → no_ven_caj → personal_empresa.
     *
     * Mapeo de sucursales activas:
     *   1 = JARDIN           → nc_jdn
     *   2 = MERCADO          → nc_mdo
     *   3 = ZACATIPAN        → nc_zac
     *   4 = BOULEVARD        → nc_blv
     *   7 = HUEJUTLA MORELOS → nc_hum
     *
     * Sucursales inactivas (sin cajeros actuales): 6 = SAN FELIPE (nc_sfo).
     * Se dejan fuera del mapa: SAN MIGUEL (nc_smg) y HUEJUTLA CENTRO (nc_huc) ya no operan.
     */
    private function resolverNombresCajero($lineas): array
    {
        $mapa = [
            '1' => 'nc_jdn',
            '2' => 'nc_mdo',
            '3' => 'nc_zac',
            '4' => 'nc_blv',
            '7' => 'nc_hum',
        ];

        $resultado = [];

        // Agrupar pares (sucursal, cajero) — 1 sola vez.
        $pares = $lineas
            ->groupBy(fn ($l) => ($l->id_sucursal ?? '') . '|' . trim((string) ($l->F_NUMCAJER ?? '')))
            ->keys()
            ->filter(fn ($k) => !str_ends_with($k, '|'))
            ->values()
            ->all();

        // Inicializar todos como "—".
        foreach ($pares as $par) {
            $resultado[$par] = '—';
        }

        // Agrupar cajeros por sucursal.
        $paresPorSucursal = [];
        foreach ($pares as $par) {
            [$sucursalId, $numCajero] = explode('|', $par);
            if (!isset($mapa[$sucursalId]) || $numCajero === '') {
                continue;
            }
            $paresPorSucursal[$sucursalId][] = $numCajero;
        }

        // 1 query por sucursal (máximo 5).
        foreach ($paresPorSucursal as $sucursalId => $cajeros) {
            $col = $mapa[$sucursalId];

            $rows = DB::connection('sqlsrvM')
                ->table('no_ven_caj as nvc')
                ->join('personal_empresa as pe', 'pe.id_personal_empresa', '=', 'nvc.id_personal_empresa')
                ->whereIn(DB::raw("LTRIM(RTRIM(nvc.{$col}))"), $cajeros)
                ->select(
                    DB::raw("LTRIM(RTRIM(nvc.{$col})) AS num_cajero"),
                    'pe.Nombre', 'pe.ApPaterno', 'pe.ApMaterno'
                )
                ->get();

            foreach ($rows as $row) {
                $nombre = trim(($row->Nombre ?? '') . ' ' . ($row->ApPaterno ?? '') . ' ' . ($row->ApMaterno ?? ''));
                $resultado[$sucursalId . '|' . $row->num_cajero] = $nombre ?: '—';
            }
        }

        return $resultado;
    }

    // ------------------------------------------------------------------
    // TAB 2 — Búsqueda por Cliente
    // ------------------------------------------------------------------

    /**
     * Busca clientes por nombre o teléfono en catalogo_cliente_maestro (sqlsrvM).
     * Query: ?q=texto
     */
    public function clientes(Request $request): JsonResponse
    {
        set_time_limit(120);

        $q = trim((string) $request->input('q', ''));

        if ($q === '' || mb_strlen($q) < 2) {
            return response()->json([
                'success'  => false,
                'message'  => 'Ingrese al menos 2 caracteres para buscar.',
                'clientes' => [],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $like = '%' . $q . '%';

        $clientes = Cliente::query()
            ->select('id_Cliente', 'Nombre', 'apPaterno', 'apMaterno', 'telefono1', 'telefono2', 'idtarjetaclientefrecuente', 'status')
            ->where(function (Builder $w) use ($like): void {
                // Nombre completo concatenado (con ISNULL + LTRIM/RTRIM por cada parte).
                $w->whereRaw(
                    "LTRIM(RTRIM(ISNULL(Nombre, '') + ' ' + ISNULL(apPaterno, '') + ' ' + ISNULL(apMaterno, ''))) COLLATE Latin1_General_CI_AI LIKE ?",
                    [$like]
                )
                // Teléfonos individuales.
                ->orWhere('telefono1', 'LIKE', $like)
                ->orWhere('telefono2', 'LIKE', $like);
            })
            ->whereIn('status', Cliente::getActiveStatuses())
            ->orderBy('Nombre')
            ->orderBy('apPaterno')
            ->limit(50)
            ->get();

        $clientes = $clientes->map(function ($c) {
            return [
                'id_Cliente'                => $c->id_Cliente,
                'nombre_completo'           => $c->nombre_completo,
                'telefono1'                 => $c->telefono1,
                'telefono2'                 => $c->telefono2,
                'idtarjetaclientefrecuente' => $c->idtarjetaclientefrecuente,
            ];
        });

        return response()->json([
            'success'  => true,
            'clientes' => $clientes,
        ]);
    }

    /**
     * Data agregada del cliente seleccionado en el rango de fechas.
     * Query: ?fecha_inicio=...&fecha_fin=...
     */
    public function clienteData(int $clienteId, ClienteDataRequest $request): JsonResponse
    {
        set_time_limit(300);

        $fechaInicio = (string) $request->input('fecha_inicio');
        $fechaFin = (string) $request->input('fecha_fin');

        $cliente = Cliente::query()
            ->select('id_Cliente', 'Nombre', 'apPaterno', 'apMaterno', 'telefono1', 'telefono2', 'idtarjetaclientefrecuente', 'status')
            ->find($clienteId);

        if (! $cliente || ! $cliente->idtarjetaclientefrecuente) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente no encontrado o sin tarjeta frecuente asociada.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! in_array($cliente->status, Cliente::getActiveStatuses(), true)) {
            return response()->json([
                'success' => false,
                'message' => 'El cliente no está activo.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $idTarjeta = $cliente->idtarjetaclientefrecuente;

        // ------------------------------------------------------------------
        // 1 sola query: todas las líneas del cliente en el rango,
        // con descripción de catálogo_maestro + numFam + grupo madre.
        // ------------------------------------------------------------------
        $lineas = HistorialVenta::query()
            ->ventasValidas()
            ->entreFechas($fechaInicio, $fechaFin)
            ->porCliente($idTarjeta)
            ->leftJoin(
                DB::raw('(SELECT EAN, MAX(descripcion) AS descripcion, MAX(numFam) AS numFam
                        FROM fp_central_matriz.dbo.catalogo_maestro
                        GROUP BY EAN) as cm'),
                'cm.EAN', '=', 'historial_ventas_matriz.F_CODBAR'
            )
            ->leftJoin(
                DB::raw('(SELECT numfamilia, MAX(id_grupo_madre) AS id_grupo_madre, MAX(descripciongrupomadre) AS descripciongrupomadre
                        FROM fp_central_matriz.dbo.grupos_familias
                        GROUP BY numfamilia) as gf'),
                'gf.numfamilia', '=', 'cm.numFam'
            )
            ->select(
                'historial_ventas_matriz.F_NUMTICKE',
                'historial_ventas_matriz.FECHA_DT',
                'historial_ventas_matriz.F_CODBAR',
                'historial_ventas_matriz.F_MONTO',
                'historial_ventas_matriz.F_CANT',
                DB::raw("COALESCE(cm.descripcion, '—') AS descripcion"),
                DB::raw("COALESCE(gf.id_grupo_madre, 0) AS id_grupo_madre"),
                DB::raw("COALESCE(gf.descripciongrupomadre, 'Sin grupo') AS descripciongrupomadre")
            )
            ->get();

        // ------------------------------------------------------------------
        // Agrupación en PHP (sin más queries a historial_ventas_matriz).
        // ------------------------------------------------------------------

        // Tickets cabecera (únicos).
        $tickets = $lineas
            ->groupBy('F_NUMTICKE')
            ->map(fn ($g) => [
                'F_NUMTICKE' => $g->first()->F_NUMTICKE,
                'FECHA_DT'   => $g->first()->FECHA_DT,
            ])
            ->sortByDesc('FECHA_DT')
            ->values();

        // Artículos agregados por EAN.
        $articulos = $lineas
            ->groupBy('F_CODBAR')
            ->map(function ($g, $ean) {
                return [
                    'ean'          => $ean,
                    'descripcion'  => $g->first()->descripcion,
                    'repeticiones' => $g->count(),
                    'importe'      => $g->sum(fn ($l) => (float) $l->F_MONTO),
                ];
            })
            ->sortByDesc(fn ($a) => [$a['repeticiones'], $a['importe']])
            ->values();

        // Grupos madre agregados (top 10 por importe).
        $gruposMadre = $lineas
            ->where('id_grupo_madre', '>', 0)
            ->groupBy('id_grupo_madre')
            ->map(function ($g, $idGrupo) {
                $first = $g->first();
                return [
                    'id_grupo_madre'        => $idGrupo,
                    'descripciongrupomadre' => $first->descripciongrupomadre,
                    'importe'               => $g->sum(fn ($l) => (float) $l->F_MONTO),
                    'repeticiones'          => $g->count(),
                ];
            })
            ->sortByDesc('importe')
            ->take(10)
            ->values();

        // Totales.
        $totales = [
            'total_tickets' => $tickets->count(),
            'total_lineas'  => $lineas->count(),
            'importe_total' => $lineas->sum(fn ($l) => (float) $l->F_MONTO),
        ];

        // Artículo top (por unidades).
        $articuloTopData = null;
        $articuloTop = $lineas
            ->groupBy('F_CODBAR')
            ->map(function ($g, $ean) {
                return [
                    'ean'         => $ean,
                    'descripcion' => $g->first()->descripcion,
                    'cantidad'    => $g->sum(fn ($l) => (float) $l->F_CANT),
                    'importe'     => $g->sum(fn ($l) => (float) $l->F_MONTO),
                ];
            })
            ->sortByDesc('cantidad')
            ->first();

        if ($articuloTop) {
            $articuloTopData = [
                'ean'         => $articuloTop['ean'],
                'descripcion' => $articuloTop['descripcion'] ?: '—',
                'cantidad'    => $articuloTop['cantidad'],
                'importe'     => $articuloTop['importe'],
            ];
        }

        return response()->json([
            'success'  => true,
            'cliente'  => [
                'id_Cliente'      => $cliente->id_Cliente,
                'nombre_completo' => $cliente->nombre_completo,
                'telefono1'       => $cliente->telefono1,
                'telefono2'       => $cliente->telefono2,
            ],
            'filtros'  => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin'    => $fechaFin,
            ],
            'resumen'  => $totales,
            'kpis'     => [
                'articulo_top' => $articuloTopData,
            ],
            'grafica_grupos_madre' => $gruposMadre,
            'articulos' => $articulos,
            'tickets'   => $tickets,
        ]);
    }

    /**
     * Detalle de un ticket puntual del cliente (todos sus artículos).
     * Query: ?fecha_inicio=...&fecha_fin=...
     */
    public function clienteTicket(int $clienteId, string $ticket, ClienteDataRequest $request): JsonResponse
    {
        set_time_limit(120);

        $fechaInicio = (string) $request->input('fecha_inicio');
        $fechaFin = (string) $request->input('fecha_fin');

        $cliente = Cliente::query()
            ->select('id_Cliente', 'idtarjetaclientefrecuente')
            ->find($clienteId);

        if (! $cliente || ! $cliente->idtarjetaclientefrecuente) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente no encontrado.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Query principal: artículos del ticket (si no hay, es que el ticket no es del cliente o no existe).
        $articulos = HistorialVenta::query()
            ->entreFechas($fechaInicio, $fechaFin)
            ->porCliente($cliente->idtarjetaclientefrecuente)
            ->where('F_NUMTICKE', $ticket)
            ->leftJoin(
                DB::raw('(SELECT EAN, MAX(descripcion) AS descripcion
                        FROM fp_central_matriz.dbo.catalogo_maestro
                        GROUP BY EAN) as cm'),
                'cm.EAN', '=', 'historial_ventas_matriz.F_CODBAR'
            )
            ->select(
                'historial_ventas_matriz.F_CODBAR as ean',
                DB::raw("COALESCE(cm.descripcion, '—') AS descripcion"),
                DB::raw('SUM(CAST(historial_ventas_matriz.F_MONTO AS DECIMAL(18,2))) AS monto')
            )
            ->groupBy('historial_ventas_matriz.F_CODBAR', 'cm.descripcion')
            ->get()
            ->map(fn ($a) => [
                'ean'         => $a->ean,
                'descripcion' => $a->descripcion,
                'monto'       => $a->monto,
            ]);

        if ($articulos->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket no encontrado para este cliente en el período.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success'   => true,
            'ticket'    => $ticket,
            'articulos' => $articulos,
        ]);
    }

    // ------------------------------------------------------------------
    // Helpers privados
    // ------------------------------------------------------------------

    private function buscarProductoEnCatalogo(string $ean): ?object
    {
        return DB::connection('sqlsrvM')
            ->table('catalogo_maestro')
            ->select('EAN as ean', 'descripcion')
            ->where('EAN', $ean)
            ->first();
    }

    private function resolverPerPage(mixed $raw): int
    {
        $perPage = (int) ($raw ?? self::PER_PAGE_DEFAULT);

        return in_array($perPage, self::PER_PAGE_PERMITIDOS, true)
            ? $perPage
            : self::PER_PAGE_DEFAULT;
    }

    private function resolverSortBy(?string $raw): string
    {
        return $raw ?? self::SORT_BY_DEFAULT;
    }

    private function aplicarOrden($query, string $sortBy): void
    {
        match ($sortBy) {
            'compras_desc'       => $query->orderByDesc('compras'),
            'compras_asc'        => $query->orderBy('compras'),
            'frecuencia_asc'     => $query->orderBy('compras', 'DESC'),   // proxy: menos compras → más días/compras
            'frecuencia_desc'    => $query->orderBy('compras'),           // proxy: más compras → menos días/compras
            'ultima_compra_desc' => $query->orderByDesc('ultima_compra'),
            'monto_desc'         => $query->orderByDesc('monto_total'),
            default              => $query->orderByDesc('compras'),
        };
    }
}