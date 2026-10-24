<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reportes\BusquedaArticuloRequest;
use App\Models\Reportes\HistorialVenta;
use App\Services\Reportes\FrecuenciaCompraService;
use App\Support\EanNormalizer;
use Carbon\Carbon;
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
        $ean = (string) $request->input('ean');
        $fechaInicio = (string) $request->input('fecha_inicio');
        $fechaFin = (string) $request->input('fecha_fin');

        $perPage = $this->resolverPerPage($request->input('per_page'));
        $sortBy = $this->resolverSortBy($request->input('sort_by'));

        // Validar existencia en catálogo ANTES de ir a ventas (sqlsrvM).
        $producto = $this->buscarProductoEnCatalogo($ean);
        if ($producto === null) {
            return response()->json([
                'success' => false,
                'message' => 'Código de barras no encontrado',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $diasPeriodo = Carbon::parse($fechaInicio)->diffInDays(Carbon::parse($fechaFin));

        // IDs de cliente "público en general" a excluir.
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
            ->select(
                'c.id_Cliente',
                DB::raw("historial_ventas_matriz.IDCLIENTE AS idtarjetaclientefrecuente"),
                DB::raw("COALESCE(NULLIF(LTRIM(RTRIM(CONCAT(c.Nombre, ' ', c.apPaterno, ' ', c.apMaterno))), ''), 'CLIENTE NO IDENTIFICADO') AS nombre_cliente"),
                DB::raw("COALESCE(c.telefono1, c.telefono2) AS telefono"),
                's.id_sucursal',
                DB::raw("COALESCE(s.nombre, '—') AS sucursal"),
                DB::raw('COUNT(DISTINCT historial_ventas_matriz.F_NUMTICKE) AS compras'),
                DB::raw('SUM(CAST(historial_ventas_matriz.F_MONTO AS DECIMAL(18,2))) AS monto_total'),
                DB::raw('MAX(CAST(historial_ventas_matriz.FECHA_DT AS DATE)) AS ultima_compra'),
                DB::raw('MIN(CAST(historial_ventas_matriz.FECHA_DT AS DATE)) AS primera_compra')
            )
            ->groupBy(
                'c.id_Cliente',
                'historial_ventas_matriz.IDCLIENTE',   // <-- agregar
                'c.Nombre',
                'c.apPaterno',
                'c.apMaterno',
                'c.telefono1',
                'c.telefono2',
                's.id_sucursal',
                's.nombre'
            );

        $this->aplicarOrden($query, $sortBy);

        $clientes = $query
            ->paginate($perPage)
            ->appends($request->query());

        // Frecuencia por período en PHP.
        $clientes->getCollection()->transform(function ($cliente) use ($diasPeriodo) {
            $cliente->frecuencia = $this->frecuenciaService->porPeriodo(
                (int) $diasPeriodo,
                (int) $cliente->compras
            );
            return $cliente;
        });

        // KPIs arriba de la tabla.
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
            'clientes'   => $clientes->items(),
            'paginacion' => [
                'total'         => $clientes->total(),
                'current_page'  => $clientes->currentPage(),
                'per_page'      => $clientes->perPage(),
                'last_page'     => $clientes->lastPage(),
                'from'          => $clientes->firstItem(),
                'to'            => $clientes->lastItem(),
                'links'         => (string) $clientes->links('pagination.bootstrap-5'),
            ],
        ]);
    }

    /**
     * KPIs del tab 1:
     *  - articulo_mayor_costo: el artículo más caro por costo (en la sucursal con más ventas del EAN buscado)
     *  - inventario_por_costo: suma de (inventario * costo) por sucursal
     *  - producto_mayor_venta: EAN con mayor monto vendido en el rango (de los clientes que aparecen)
     */
    private function calcularKpis(string $ean, string $fechaInicio, string $fechaFin): array
    {
        // 1) Artículo con mayor costo (en la sucursal con más ventas del EAN buscado).
        $sucursalTop = HistorialVenta::query()
            ->ventasValidas()
            ->entreFechas($fechaInicio, $fechaFin)
            ->porProducto($ean)
            ->select('id_sucursal', DB::raw('SUM(CAST(F_MONTO AS DECIMAL(18,2))) AS monto'))
            ->groupBy('id_sucursal')
            ->orderByDesc('monto')
            ->first();

        $articuloMayorCosto = null;
        if ($sucursalTop) {
            $row = DB::connection('sqlsrvM')
                ->table('catalogo_general')
                ->where('id_sucursal', $sucursalTop->id_sucursal)
                ->orderByDesc('costo')
                ->select('ean', 'descripcion', 'costo')
                ->first();
            if ($row) {
                $articuloMayorCosto = [
                    'ean'         => $row->ean,
                    'descripcion' => $row->descripcion,
                    'costo'       => $row->costo,
                    'sucursal_id' => $sucursalTop->id_sucursal,
                ];
            }
        }

        // 2) Inventario por costo (agrupado por sucursal).
        $inventarioPorCosto = DB::connection('sqlsrvM')
            ->table('catalogo_general')
            ->select(
                'id_sucursal',
                DB::raw('SUM(CAST(inventario AS DECIMAL(18,2)) * CAST(costo AS DECIMAL(18,2))) AS inventario_costo')
            )
            ->groupBy('id_sucursal')
            ->get()
            ->map(fn ($r) => [
                'sucursal_id'      => $r->id_sucursal,
                'inventario_costo' => $r->inventario_costo,
            ]);

        // 3) Producto con mayor venta generada en el rango (de los clientes del reporte).
        $idsExcluir = ['0000000007295', '0000000004489'];
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

        // 2) Líneas del detalle (SIN agrupar por EAN) con todas las columnas.
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

        // 3) Agrupar por ticket.
        $lineasPorTicket = $lineas->groupBy('F_NUMTICKE');

        // 4) Resolver nombre del cajero por (id_sucursal, F_NUMCAJER).
        // NOTA: requiere mapeo id_sucursal -> sufijo (nc_xxx).
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
                    $key = ($l->sucursal ?? '') . '|' . ($l->F_NUMCAJER ?? '');
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
     * Requiere MAPA_SUCURSAL_CAJERO (id_sucursal => columna nc_xxx).
     */
    private function resolverNombresCajero($lineas): array
    {
        $mapa = [
            '1' => 'nc_jdn',  // JARDIN
            '2' => 'nc_mdo',  // MERCADO
            '3' => 'nc_zac',  // ZACATIPAN
            '4' => 'nc_blv',  // BOULEVARD
            '6' => 'nc_sfo',  // SAN FELIPE
            '7' => 'nc_hum',  // HUEJUTLA MORELOS (¿hum o huc?)
        ];

        $resultado = [];

        foreach ($lineas->groupBy(fn ($l) => ($l->id_sucursal ?? '') . '|' . ($l->F_NUMCAJER ?? '')) as $key => $grupo) {
            [$sucursalId, $numCajero] = explode('|', $key);
            if (!$numCajero) {
                $resultado[$key] = '—';
                continue;
            }

            $col = $mapa[$sucursalId] ?? null;
            if (!$col) {
                $resultado[$key] = '—';
                continue;
            }

            $personal = DB::connection('sqlsrvM')
                ->table('no_ven_caj as nvc')
                ->join('personal_empresa as pe', 'pe.id_personal_empresa', '=', 'nvc.id_personal_empresa')
                ->where("nvc.{$col}", $numCajero)
                ->select('pe.Nombre', 'pe.ApPaterno', 'pe.ApMaterno')
                ->first();

            $resultado[$key] = $personal
                ? trim(($personal->Nombre ?? '') . ' ' . ($personal->ApPaterno ?? '') . ' ' . ($personal->ApMaterno ?? '')) ?: '—'
                : '—';
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
            ->select('id_Cliente', 'Nombre', 'apPaterno', 'apMaterno', 'telefono1', 'telefono2', 'idtarjetaclientefrecuente')
            ->where(function (Builder $w) use ($like): void {
                $w->where('Nombre', 'LIKE', $like)
                ->orWhere('apPaterno', 'LIKE', $like)
                ->orWhere('apMaterno', 'LIKE', $like)
                ->orWhere('telefono1', 'LIKE', $like)
                ->orWhere('telefono2', 'LIKE', $like);
            })
            ->orderBy('Nombre')
            ->orderBy('apPaterno')
            ->limit(50)
            ->get()
            ->map(fn (Cliente $c) => [
                'id_Cliente'                  => $c->id_Cliente,
                'nombre_completo'             => $c->nombre_completo,
                'telefono1'                   => $c->telefono1,
                'telefono2'                   => $c->telefono2,
                'idtarjetaclientefrecuente'   => $c->idtarjetaclientefrecuente,
            ]);

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
        $fechaInicio = (string) $request->input('fecha_inicio');
        $fechaFin = (string) $request->input('fecha_fin');

        $cliente = Cliente::query()
            ->select('id_Cliente', 'Nombre', 'apPaterno', 'apMaterno', 'telefono1', 'telefono2', 'idtarjetaclientefrecuente')
            ->find($clienteId);

        if (! $cliente || ! $cliente->idtarjetaclientefrecuente) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente no encontrado o sin tarjeta frecuente asociada.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $idTarjeta = $cliente->idtarjetaclientefrecuente;

        // Base: ventas válidas del cliente en el rango (usa scopes de HistorialVenta).
        $base = HistorialVenta::query()
            ->ventasValidas()
            ->entreFechas($fechaInicio, $fechaFin)
            ->porCliente($idTarjeta);

        // 1) Tickets cabecera.
        $tickets = (clone $base)
            ->select('F_NUMTICKE', 'FECHA_DT')
            ->distinct()
            ->orderBy('FECHA_DT', 'DESC')
            ->get();

        // 2) Artículos agregados por EAN (con descripción de catalogo_maestro).
        $articulos = (clone $base)
            ->leftJoin(
                DB::raw('(SELECT EAN, MAX(descripcion) AS descripcion
                        FROM fp_central_matriz.dbo.catalogo_maestro
                        GROUP BY EAN) as cm'),
                'cm.EAN', '=', 'historial_ventas_matriz.F_CODBAR'
            )
            ->select(
                'historial_ventas_matriz.F_CODBAR as ean',
                DB::raw("COALESCE(cm.descripcion, '—') AS descripcion"),
                DB::raw('COUNT(*) AS repeticiones'),
                DB::raw('SUM(CAST(historial_ventas_matriz.F_MONTO AS DECIMAL(18,2))) AS importe')
            )
            ->groupBy('historial_ventas_matriz.F_CODBAR', 'cm.descripcion')
            ->orderByDesc('repeticiones')
            ->orderByDesc('importe')
            ->get()
            ->map(fn ($a) => [
                'ean'         => $a->ean,
                'descripcion' => $a->descripcion,
                'repeticiones'=> (int) $a->repeticiones,
                'importe'     => $a->importe,
            ]);

        // 3) Grupos madre agregados (para gráfica).
        $gruposMadre = (clone $base)
            ->join(
                DB::raw('(SELECT EAN, MAX(numFam) AS numFam
                        FROM fp_central_matriz.dbo.catalogo_maestro
                        GROUP BY EAN) as cm'),
                'cm.EAN', '=', 'historial_ventas_matriz.F_CODBAR'
            )
            ->join(
                DB::raw('(SELECT numfamilia, MAX(id_grupo_madre) AS id_grupo_madre, MAX(descripciongrupomadre) AS descripciongrupomadre
                        FROM fp_central_matriz.dbo.grupos_familias
                        GROUP BY numfamilia) as gf'),
                'gf.numfamilia', '=', 'cm.numFam'
            )
            ->select(
                'gf.id_grupo_madre',
                'gf.descripciongrupomadre',
                DB::raw('SUM(CAST(historial_ventas_matriz.F_MONTO AS DECIMAL(18,2))) AS importe'),
                DB::raw('COUNT(*) AS repeticiones')
            )
            ->groupBy('gf.id_grupo_madre', 'gf.descripciongrupomadre')
            ->orderByDesc('importe')
            ->limit(10)
            ->get()
            ->map(fn ($g) => [
                'id_grupo_madre'         => $g->id_grupo_madre,
                'descripciongrupomadre'  => $g->descripciongrupomadre,
                'importe'                => $g->importe,
                'repeticiones'           => (int) $g->repeticiones,
            ]);

        // 4) Totales.
        $totales = (clone $base)
            ->select(
                DB::raw('COUNT(DISTINCT historial_ventas_matriz.F_NUMTICKE) AS total_tickets'),
                DB::raw('COUNT(*) AS total_lineas'),
                DB::raw('SUM(CAST(historial_ventas_matriz.F_MONTO AS DECIMAL(18,2))) AS importe_total')
            )
            ->first();

        return response()->json([
            'success'  => true,
            'cliente'  => [
                'id_Cliente'    => $cliente->id_Cliente,
                'nombre_completo' => $cliente->nombre_completo,
                'telefono1'     => $cliente->telefono1,
                'telefono2'     => $cliente->telefono2,
            ],
            'filtros'  => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin'    => $fechaFin,
            ],
            'resumen'  => [
                'total_tickets' => (int) ($totales->total_tickets ?? 0),
                'total_lineas'  => (int) ($totales->total_lineas ?? 0),
                'importe_total' => $totales->importe_total ?? 0,
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
        $fechaInicio = (string) $request->input('fecha_inicio');
        $fechaFin = (string) $request->input('fecha_fin');

        $cliente = Cliente::query()
            ->select('id_Cliente', 'Nombre', 'apPaterno', 'apMaterno', 'idtarjetaclientefrecuente')
            ->find($clienteId);

        if (! $cliente || ! $cliente->idtarjetaclientefrecuente) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente no encontrado.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Validar que el ticket pertenezca al cliente en el rango (evita tickets ajenos).
        $pertenece = HistorialVenta::query()
            ->entreFechas($fechaInicio, $fechaFin)
            ->porCliente($cliente->idtarjetaclientefrecuente)
            ->where('F_NUMTICKE', $ticket)
            ->exists();

        if (! $pertenece) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket no encontrado para este cliente en el período.',
            ], Response::HTTP_NOT_FOUND);
        }

        $articulos = HistorialVenta::query()
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