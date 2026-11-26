<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ventas\GuardarPedidoDirectoRequest;
use App\Models\CatalogoMaestro;
use App\Models\Cliente;
use App\Models\Cotizaciones\CatConvenio;
use App\Models\Pedidos\OrdenPedido;
use App\Models\Sucursal;
use App\Services\Ventas\RegistrarPedidoDirecto;
use App\Services\Ventas\CancelarPedidoDirecto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PedidoDirectoController extends Controller
{
    public function __construct()
    {
        $this->middleware(function (Request $request, \Closure $next) {
            abort_unless($request->user()->puede('ventas', 'pedidos_anticipo', 'ver'), 403);

            return $next($request);
        });
    }

    public function index(Request $request): View|JsonResponse
    {
        $datosListado = $request->validate(['buscar' => 'nullable|string|max:100', 'orden' => 'nullable|string', 'direccion' => 'nullable|in:asc,desc', 'sucursal_listado' => 'nullable|integer|min:1']);
        $busquedaPedidos = trim($datosListado['buscar'] ?? '');
        $ordenPedidos = $datosListado['orden'] ?? '';
        if (!in_array($ordenPedidos, ['sucursal', 'folio', 'cliente', 'articulos', 'anticipo', 'fecha', 'estado', 'ticket'], true)) {
            $ordenPedidos = '';
        }
        $direccionPedidos = $datosListado['direccion'] ?? 'asc';
        $opcionesPorPagina = [5, 10, 15, 20, 50];
        $porPagina = filter_var($request->query('per_page'), FILTER_VALIDATE_INT);
        if (!in_array($porPagina, $opcionesPorPagina, true)) {
            $porPagina = 15;
        }

        $sucursalAsignada = (int) $request->user()->sucursal_asignada;
        $sucursalesOrigen = Sucursal::query()
            ->whereIn('id_sucursal', $sucursalAsignada ? [$sucursalAsignada] : OrdenPedido::SUCURSALES_SOBRE_PEDIDO)
            ->orderBy('id_sucursal')->get(['id_sucursal', 'nombre']);
        $sucursalListado = isset($datosListado['sucursal_listado']) ? (int) $datosListado['sucursal_listado'] : null;
        abort_if($sucursalAsignada !== 0 && $sucursalListado !== null && $sucursalListado !== $sucursalAsignada, 403);
        abort_if($sucursalListado !== null && !$sucursalesOrigen->contains('id_sucursal', $sucursalListado), 422, 'Selecciona una sucursal del listado.');
        $pedidos = OrdenPedido::pedidosDirectosVisibles($request->user())
            ->with(['clienteDirecto', 'detalles' => fn ($query) => $query->where('se_elimino', false)])
            ->when($sucursalAsignada === 0, fn ($query) => $query->with('sucursalOrigen:id_sucursal,nombre'))
            ->when($sucursalListado, fn ($query) => $query->where('id_sucursal_origen', $sucursalListado))
            ->buscarDirectos($busquedaPedidos)
            ->ordenarDirectos($ordenPedidos, $direccionPedidos)
            ->paginate($porPagina)->withQueryString();

        if ($request->ajax()) {
            return response()->json(['html' => view('ventas.pedidos-directos.partials.tabla', compact(
                'pedidos', 'opcionesPorPagina', 'sucursalAsignada', 'busquedaPedidos', 'ordenPedidos', 'direccionPedidos', 'sucursalesOrigen', 'sucursalListado',
            ))->render()]);
        }

        $convenios = CatConvenio::where('status', 1)->where('tipo', 'C')
            ->orderBy('convenio')->get(['id', 'convenio']);
        $categorias = (config('pedidos_directos.categorias') ?? (require config_path('pedidos_directos.php'))['categorias']);

        return view('ventas.pedidos-directos.index', compact(
            'pedidos', 'convenios', 'categorias', 'opcionesPorPagina', 'sucursalAsignada', 'sucursalesOrigen', 'busquedaPedidos', 'ordenPedidos', 'direccionPedidos', 'sucursalListado',
        ));
    }

    public function buscarArticulo(Request $request): JsonResponse
    {
        $datos = $request->validate(
            ['ean' => 'required|string|regex:/^[0-9]{1,13}$/D'],
            ['ean.regex' => 'El código de barras debe contener entre 1 y 13 dígitos.'],
        );
        $ean = str_pad($datos['ean'], 13, '0', STR_PAD_LEFT);
        $producto = CatalogoMaestro::where('EAN', $ean)
            ->first(['descripcion', 'precio_publico_farmapronto_centro']);
        $articulo = $producto ? [
            'descripcion' => $producto->descripcion,
            'precio' => $producto->precio_publico_farmapronto_centro,
        ] : null;

        return response()->json(['ean' => $ean, 'articulo' => $articulo]);
    }

    public function buscarClientes(Request $request): JsonResponse
    {
        $datos = $request->validate(['q' => 'required|string|min:2|max:100']);
        $termino = '%' . $datos['q'] . '%';
        $clientes = Cliente::activos()
            ->where(function ($query) use ($termino) {
                $query->whereRaw("CONCAT(Nombre, ' ', apPaterno, ' ', COALESCE(apMaterno, '')) LIKE ?", [$termino])
                    ->orWhere('telefono1', 'like', $termino)
                    ->orWhere('telefono2', 'like', $termino);
            })
            ->orderBy('Nombre')
            ->limit(8)
            ->get(['id_Cliente', 'Nombre', 'apPaterno', 'apMaterno', 'telefono1', 'telefono2']);

        return response()->json($clientes->map(fn (Cliente $cliente) => [
            'id' => $cliente->getKey(),
            'nombre' => $cliente->nombre_completo,
            'telefono' => $cliente->telefono1 ?: $cliente->telefono2,
        ]));
    }

    public function guardarCliente(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'Nombre' => 'required|string|max:255|regex:/^[\pL\pM\s]+$/u',
            'apPaterno' => 'required|string|max:255|regex:/^[\pL\pM\s]+$/u',
            'apMaterno' => 'nullable|string|max:255|regex:/^[\pL\pM\s]+$/u',
            'telefono1' => 'required|string|max:20|regex:/^[0-9+\-\s]+$/',
            'Domicilio' => 'nullable|string|max:500',
        ]);

        foreach (['Nombre', 'apPaterno', 'apMaterno', 'Domicilio'] as $campo) {
            if (isset($datos[$campo])) {
                $datos[$campo] = mb_strtoupper($datos[$campo], 'UTF-8');
            }
        }

        $cliente = Cliente::create($datos + [
            'sucursal_origen' => 0,
            'status' => Cliente::STATUS_PROSPECTO,
            'id_operador' => $request->user()->getKey(),
            'fecha_creacion' => now(),
        ]);

        return response()->json([
            'id' => $cliente->getKey(),
            'nombre' => $cliente->nombre_completo,
            'telefono' => $cliente->telefono1,
        ], 201);
    }

    public function store(GuardarPedidoDirectoRequest $request, RegistrarPedidoDirecto $registro): JsonResponse
    {
        $pedido = $registro->guardar($request->validated(), $request->user());

        return response()->json([
            'folio' => $pedido->folio_pedido,
            'ticket' => route('ventas.sobre-pedido.ticket', $pedido->getKey(), false),
        ], 201);
    }

    public function actualizar(Request $request, int $id): JsonResponse
    {
        $usuario = $request->user();
        abort_unless($usuario->es_crm, 403);

        $datos = $request->validate([
            'fecha_encargo' => 'nullable|date_format:Y-m-d|before_or_equal:' . today()->toDateString(),
            'anticipo_cliente' => 'required|numeric|decimal:0,2|min:0|max:9999999999.99',
            'status' => 'required|integer|in:2,3',
            'folio_ticket_entrega' => 'nullable|digits_between:1,6',
        ], [
            'fecha_encargo.*' => 'Ingresa una fecha de encargo válida que sea hoy o anterior.',
            'anticipo_cliente.*' => 'Ingresa un anticipo válido con hasta dos decimales.',
            'folio_ticket_entrega.*' => 'Ingresa un número de ticket de hasta 6 dígitos.',
        ]);

        $consulta = OrdenPedido::pedidosDirectosVisibles($usuario);
        $consulta->getConnection()->transaction(function () use ($consulta, $id, $datos): void {
            $pedido = $consulta->lockForUpdate()->findOrFail($id);
            $anticipo = (int) round($datos['anticipo_cliente'] * 100);
            $anticipoAnterior = $pedido->anticipo_cliente === null
                ? null : (int) round($pedido->anticipo_cliente * 100);
            if ($anticipo !== $anticipoAnterior) {
                $total = (int) round($pedido->detalles()->where('se_elimino', false)->sum('importe') * 100);
                $minimo = intdiv($total + 1, 2);
                if ($anticipo < $minimo) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'anticipo_cliente' => 'El anticipo mínimo es $' . number_format($minimo / 100, 2) . '.',
                    ]);
                }
            }

            $entregado = (int) $datos['status'] === 3;
            $pedido->forceFill([
                'fecha_encargo' => $datos['fecha_encargo'] ?? null,
                'anticipo_cliente' => $datos['anticipo_cliente'],
                'status' => (int) $datos['status'],
                'fecha_entrega_real' => $entregado ? ($pedido->fecha_entrega_real ?? now()) : null,
                'folio_ticket_entrega' => $entregado ? ($datos['folio_ticket_entrega'] ?? null) : null,
            ])->save();
        });

        return response()->json(['message' => 'Pedido actualizado.']);
    }

    public function destroy(Request $request, int $id, CancelarPedidoDirecto $cancelacion): JsonResponse
    {
        abort_unless($request->user()->puede('ventas', 'pedidos_anticipo', 'eliminar'), 403, 'No tienes permiso para cancelar pedidos.');
        $datos = $request->validate(['motivo' => 'required|string'], [
            'motivo.required' => 'Debe ingresar un motivo de cancelación.',
            'motivo.string' => 'Ingresa un motivo de cancelación válido.',
        ]);
        $cancelacion->cancelar($id, $request->user(), $datos['motivo']);

        return response()->json(['success' => true, 'message' => 'Pedido cancelado correctamente.']);
    }

    public function finalizar(Request $request, int $id): JsonResponse
    {
        $datos = $request->validate(
            ['folio_ticket_entrega' => 'nullable|digits_between:1,6'],
            ['folio_ticket_entrega.*' => 'Ingresa un número de ticket de hasta 6 dígitos.'],
        );
        $usuario = $request->user();
        $pedido = OrdenPedido::pedidosDirectosVisibles($usuario)
            ->when(!$usuario->es_crm, fn ($query) => $query->deOrigenVisible($usuario))
            ->findOrFail($id);
        $cambios = [
            'status' => 3,
            'fecha_entrega_real' => now(),
        ];
        if (isset($datos['folio_ticket_entrega'])) {
            if (!$pedido->getConnection()->getSchemaBuilder()->hasColumn($pedido->getTable(), 'folio_ticket_entrega')) {
                return response()->json(['message' => 'Falta habilitar la columna de ticket de entrega en el servidor.'], 422);
            }
            $cambios['folio_ticket_entrega'] = (int) $datos['folio_ticket_entrega'];
        }
        $actualizado = OrdenPedido::whereKey($pedido->getKey())->where('status', 2)->update($cambios);

        if (!$actualizado) {
            return response()->json(['message' => 'El pedido ya fue entregado.'], 409);
        }

        return response()->json(['message' => 'Pedido marcado como entregado.']);
    }
}
