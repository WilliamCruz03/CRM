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

        $query = HistorialVenta::query()
            ->ventasValidas()
            ->entreFechas($fechaInicio, $fechaFin)
            ->porProducto($ean)
            ->join(
                'fp_central_matriz.dbo.catalogo_cliente_maestro as c',
                'historial_ventas_matriz.IDCLIENTE',
                '=',
                'c.idtarjetaclientefrecuente'
            )
            ->select(
                'c.id_Cliente',
                'c.Nombre',
                'c.apPaterno',
                'c.apMaterno',
                DB::raw('COUNT(DISTINCT historial_ventas_matriz.F_NUMTICKE) AS compras'),
                DB::raw('SUM(CAST(historial_ventas_matriz.F_MONTO AS DECIMAL(18,2))) AS monto_total'),
                DB::raw('MAX(historial_ventas_matriz.FECHA_DT) AS ultima_compra')
            )
            ->groupBy('c.id_Cliente', 'c.Nombre', 'c.apPaterno', 'c.apMaterno');

        $this->aplicarOrden($query, $sortBy);

        $clientes = $query
            ->paginate($perPage)
            ->appends($request->query());

        // Frecuencia por período en PHP (fórmula ITAMAR).
        $clientes->getCollection()->transform(function ($cliente) use ($diasPeriodo) {
            $cliente->frecuencia = $this->frecuenciaService->porPeriodo(
                (int) $diasPeriodo,
                (int) $cliente->compras
            );

            return $cliente;
        });

        return response()->json([
            'success'    => true,
            'articulo'   => [
                'ean'         => $ean,
                'descripcion' => (string) ($producto->descripcion ?? '—'),
            ],
            'filtros' => [
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

    public function tickets(int $clienteId, BusquedaArticuloRequest $request): JsonResponse
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

        // Resolver idtarjetaclientefrecuente a partir de id_Cliente.
        // data() devuelve c.id_Cliente, pero historial_ventas_matriz.IDCLIENTE
        // guarda idtarjetaclientefrecuente. Son IDs distintos.
        $cliente = Cliente::query()
            ->select('id_Cliente', 'idtarjetaclientefrecuente')
            ->find($clienteId);

        if (! $cliente || ! $cliente->idtarjetaclientefrecuente) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente sin tarjeta frecuente asociada.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $idTarjeta = $cliente->idtarjetaclientefrecuente;

        // 1) Tickets que contienen el EAN en el período (ventas válidas).
        $tickets = HistorialVenta::query()
            ->ventasValidas()
            ->entreFechas($fechaInicio, $fechaFin)
            ->porProducto($ean)
            ->porCliente($idTarjeta)
            ->select('F_NUMTICKE', 'FECHA_DT')
            ->distinct()
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

        // 2) Todos los artículos de esos tickets (contexto completo, sin filtrar status).
        $articulosPorTicket = HistorialVenta::query()
            ->porCliente($idTarjeta)
            ->whereIn('F_NUMTICKE', $numerosTickets)
            ->leftJoin(
                'fp_central_matriz.dbo.catalogo_maestro as cm',
                'cm.EAN', '=', 'historial_ventas_matriz.F_CODBAR'
            )
            ->select(
                'historial_ventas_matriz.F_NUMTICKE',
                'historial_ventas_matriz.F_CODBAR',
                DB::raw("COALESCE(cm.descripcion, '—') AS descripcion"),
                DB::raw('SUM(CAST(historial_ventas_matriz.F_MONTO AS DECIMAL(18,2))) AS monto')
            )
            ->groupBy(
                'historial_ventas_matriz.F_NUMTICKE',
                'historial_ventas_matriz.F_CODBAR',
                'cm.descripcion'
            )
            ->get()
            ->groupBy('F_NUMTICKE');

        $ticketsPayload = $tickets->map(function ($ticket) use ($articulosPorTicket) {
            return [
                'F_NUMTICKE' => $ticket->F_NUMTICKE,
                'FECHA_DT'   => $ticket->FECHA_DT,
                'articulos'  => $articulosPorTicket->get($ticket->F_NUMTICKE, collect())
                    ->map(fn ($a) => [
                        'ean'         => $a->F_CODBAR,
                        'descripcion' => $a->descripcion,
                        'monto'       => $a->monto,
                    ])
                    ->values()
                    ->all(),
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
                'fp_central_matriz.dbo.catalogo_maestro as cm',
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
                'fp_central_matriz.dbo.catalogo_maestro as cm',
                'cm.EAN', '=', 'historial_ventas_matriz.F_CODBAR'
            )
            ->join(
                'fp_central_matriz.dbo.grupos_familias as gf',
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
                'fp_central_matriz.dbo.catalogo_maestro as cm',
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