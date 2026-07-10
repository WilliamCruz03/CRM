<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnfermedadController;
use App\Http\Controllers\InteresController;
use App\Http\Controllers\Ventas\CotizacionController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Ventas\PedidoController;
use App\Http\Controllers\Ventas\AgendaContactosController;
use App\Http\Controllers\Ventas\SeguimientoController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\Reportes\VentasController;
use App\Http\Controllers\Seguridad\RespaldoController;
use App\Http\Controllers\Reportes\CotizacionesClienteController;
use App\Models\Cotizaciones\Cotizacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

// ============================================
// RUTAS PÚBLICAS (sin autenticación)
// ============================================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ============================================
// CSRF TOKEN
// ============================================
Route::get('/api/refresh-csrf', function () {
    return response()->json([
        'success' => true,
        'csrf_token' => csrf_token()
    ]);
})->name('api.refresh-csrf');

// ============================================
// RUTAS PROTEGIDAS (requieren autenticación)
// ============================================
Route::middleware(['auth'])->group(function () {

    // ============================================
    // API ROUTES (Todas dentro del grupo protegido)
    // ============================================
    Route::prefix('api')->group(function () {
        
        // ============================================
        // UBICACIONES Y CATÁLOGOS
        // ============================================
        Route::get('/paises', function(Request $request) {
            try {
                $query = DB::connection('sqlsrvM')
                    ->table('cat_paises')
                    ->where('status', 1);
                
                // Filtrar por término de búsqueda - TOLERANTE A ACENTOS
                if ($request->has('q') && !empty($request->q)) {
                    $termino = $request->q;
                    // Usar COLLATE para ignorar acentos (SQL Server)
                    $query->whereRaw("pais COLLATE SQL_Latin1_General_CP1_CI_AI LIKE ?", ['%' . $termino . '%']);
                }
                
                $paises = $query->orderBy('pais')
                    ->get(['id as value', 'pais as text']);
                
                return response()->json($paises);
            } catch (\Exception $e) {
                \Log::error('Error en /api/paises: ' . $e->getMessage());
                return response()->json([]);
            }
        })->name('api.paises');
        
        Route::get('/estados/{paisId}', [ClienteController::class, 'getEstados'])->name('api.estados');
        Route::get('/municipios/{estadoId}', [ClienteController::class, 'getMunicipios'])->name('api.municipios');
        Route::get('/localidades/{municipioId}', [ClienteController::class, 'getLocalidades'])->name('api.localidades');
        
        // ============================================
        // SUCURSALES
        // ============================================
        Route::get('/sucursales', function() {
            return DB::connection('sqlsrvM')
                ->table('sucursales')
                ->where('activo', 1)
                ->orderBy('nombre')
                ->get(['id_sucursal', 'nombre']);
        })->name('api.sucursales');
        
        // ============================================
        // POLLING PARA ACTUALIZACIÓN DE TABLAS
        // ============================================
        Route::get('/actualizar-tabla', function (Illuminate\Http\Request $request) {
            $modulo = $request->input('modulo');
            $ultimoId = $request->input('ultimo_id', 0);
            
            if ($modulo === 'cotizaciones') {
                $nuevasCotizaciones = Cotizacion::where('id_cotizacion', '>', $ultimoId)
                    ->orderBy('id_cotizacion', 'desc')
                    ->get();
                
                $ultimoNuevoId = $nuevasCotizaciones->isNotEmpty() ? $nuevasCotizaciones->first()->id_cotizacion : $ultimoId;
                
                return response()->json([
                    'hay_cambios' => $nuevasCotizaciones->isNotEmpty(),
                    'registros' => $nuevasCotizaciones->map(function($cotizacion) {
                        $fase = $cotizacion->fase;
                        return [
                            'id_cotizacion' => $cotizacion->id_cotizacion,
                            'fase_nombre' => $fase ? $fase->nombre : 'Desconocida',
                            'fase_color' => $fase ? $fase->color : 'secondary',
                            'es_nuevo' => true
                        ];
                    }),
                    'ultimo_id' => $ultimoNuevoId
                ]);
            }
            
            return response()->json(['hay_cambios' => false]);
        })->name('api.actualizar.tabla');

        // ============================================
        // INVENTARIO DETALLE POR SUCURSAL
        // ============================================
        Route::post('/inventario-detalle', function (Request $request) {
            try {
                $eans = $request->input('eans', []);
                if (empty($eans)) {
                    return response()->json(['success' => true, 'data' => []]);
                }
                
                $eansString = array_map(function($ean) {
                    return trim((string) $ean);
                }, $eans);
                
                $detalleRaw = DB::connection('sqlsrvM')
                    ->table('catalogo_general')
                    ->select(DB::raw("TRIM(CAST(ean as VARCHAR(50))) as ean"),
                        'id_sucursal',
                        'inventario'
                    )
                    ->whereIn(DB::raw("TRIM(CAST(ean as VARCHAR(50)))"), $eansString)
                    ->where('inventario', '>', 0)
                    ->orderBy('id_sucursal')
                    ->get();
                
                // Obtener nombres de sucursales
                $sucursalesNombres = DB::connection('sqlsrvM')
                    ->table('sucursales')
                    ->whereIn('id_sucursal', $detalleRaw->pluck('id_sucursal')->unique()->toArray())
                    ->pluck('nombre', 'id_sucursal')
                    ->toArray();
                
                $resultados = [];
                foreach ($detalleRaw as $row) {
                    $ean = trim((string) $row->ean);
                    if (!isset($resultados[$ean])) {
                        $resultados[$ean] = [];
                    }
                    
                    $nombreSucursal = $sucursalesNombres[$row->id_sucursal] ?? "Sucursal {$row->id_sucursal}";
                    $resultados[$ean][] = [
                        'id_sucursal' => $row->id_sucursal,
                        'nombre' => $nombreSucursal,
                        'inventario' => $row->inventario
                    ];
                }
                
                return response()->json(['success' => true, 'data' => $resultados]);
                
            } catch (\Exception $e) {
                \Log::error('Error en inventario-detalle: ' . $e->getMessage());
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
        })->name('api.inventario-detalle');
    });
    
    // ============================================
    // VERIFICACION DE SESION - ENDPOINT UNIFICADO
    // ============================================
    Route::get('/user/session-ping', function () {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'authenticated' => false,
                'active' => false,
                'reason' => 'not_authenticated',
            ], 401);
        }

        $user = auth()->user();

        if (!$user->Activo) {
            auth()->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return response()->json([
                'success' => false,
                'authenticated' => false,
                'active' => false,
                'reason' => 'user_inactive',
            ], 403);
        }

        session()->put('last_activity', time());

        return response()->json([
            'success' => true,
            'authenticated' => true,
            'active' => true,
        ]);
    })->name('user.session.ping');

    // Alias de compatibilidad
    Route::get('/user/check-status', function () {
        return redirect()->route('user.session.ping');
    })->name('user.check.status');


    Route::get('/keep-alive', function () {
        return redirect()->route('user.session.ping');
    })->name('keep-alive');

    // ============================================
    // NOTIFICACIONES
    // ============================================
    Route::get('/notificaciones/cotizaciones', [NotificacionController::class, 'getNotificaciones'])->name('notificaciones.cotizaciones');

    // ============================================
    // DASHBOARD
    // ============================================
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // ============================================
    // PATOLOGÍAS (API)
    // ============================================
    Route::get('/patologias/todas', function() {
        $patologias = App\Models\Patologia::all(['id_patologia', 'descripcion']);
        return response()->json(['success' => true, 'data' => $patologias]);
    })->name('patologias.todas');
    
    // ============================================
    // SUCURSALES ACTIVAS
    // ============================================
    Route::get('/sucursales/activas', function() {
        $sucursales = App\Models\Sucursal::where('activo', 1)->get(['id_sucursal', 'nombre']);
        return response()->json(['success' => true, 'data' => $sucursales]);
    })->name('sucursales.activas');
    
    // ============================================
    // CLIENTES
    // ============================================
    Route::prefix("clientes")->name("clientes.")->group(function () {
        Route::get("/", [ClienteController::class, "index"])->name("index");
        
        // PRIMERO: Rutas específicas (sin parámetros variables)
        Route::get("/tipos-contacto", [ClienteController::class, "tiposContacto"])->name("tipos-contacto");
        Route::get("/buscar", [ClienteController::class, "search"])->name("search");

        // ============================================
        // INTERESES - RUTAS DE BÚSQUEDA (ANTES DE {id})
        // ============================================
        Route::get('/buscar-intereses', [ClienteController::class, 'buscarIntereses'])
            ->name('buscar-intereses');
        Route::get('/{id}/intereses', [ClienteController::class, 'getInteresesCliente'])
            ->name('intereses');
        Route::post('/asignar-intereses', [ClienteController::class, 'asignarIntereses'])
            ->name('asignar-intereses');
        
        // DESPUÉS: Rutas con parámetros
        Route::get("/{id}", [ClienteController::class, "show"])->name("show");
        Route::get("/{id}/edit", [ClienteController::class, "edit"])->name("edit");
        Route::put("/{id}", [ClienteController::class, "update"])->name("update");
        Route::delete("/{id}", [ClienteController::class, "destroy"])->name("destroy");
        Route::post("/", [ClienteController::class, "store"])->name("store");
        Route::patch("/{id}/toggle-block", [ClienteController::class, "toggleBlock"])->name("toggleBlock");
        
        Route::delete('/{clienteId}/enfermedades/{enfermedadId}', function($clienteId, $enfermedadId) {
            $cliente = App\Models\Cliente::findOrFail($clienteId);
            $cliente->enfermedades()->detach($enfermedadId);
            return response()->json(['success' => true]);
        })->name('enfermedades.destroy');
    });
    
    // ============================================
    // RELACIONES CLIENTE-PATOLOGÍA
    // ============================================
    Route::delete('/clientes/{clienteId}/patologias', [ClienteController::class, 'eliminarPatologia'])->name('clientes.patologias.destroy');
    Route::delete('/clientes/{clienteId}/patologias/{patologiaAsociadaId}', [ClienteController::class, 'eliminarPatologiaPorId'])->name('clientes.patologias.destroy.porId');
    
    // ============================================
    // ENFERMEDADES (Patologías)
    // ============================================
    Route::prefix('enfermedades')->name('enfermedades.')->group(function () {
        Route::get('/', [EnfermedadController::class, 'index'])->name('index');
        Route::post('/', [EnfermedadController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [EnfermedadController::class, 'edit'])->name('edit');
        Route::put('/{id}', [EnfermedadController::class, 'update'])->name('update');
        Route::delete('/{id}', [EnfermedadController::class, 'destroy'])->name('destroy');
        Route::get('/todas', [EnfermedadController::class, 'getTodas'])->name('todas');
    });

    // ============================================
    // INTERESES (CRUD)
    // ============================================
    Route::prefix('intereses')->name('intereses.')->group(function () {
        Route::get('/', [InteresController::class, 'index'])->name('index');
        Route::get('/create', [InteresController::class, 'create'])->name('create');
        Route::post('/', [InteresController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [InteresController::class, 'edit'])->name('edit');
        Route::put('/{id}', [InteresController::class, 'update'])->name('update');
        Route::delete('/{id}', [InteresController::class, 'destroy'])->name('destroy');
    });
    
    // ============================================
    // VENTAS - COTIZACIONES
    // ============================================
    Route::prefix('ventas/cotizaciones')->name('ventas.cotizaciones.')->group(function () {
        Route::get('/', [CotizacionController::class, 'index'])->name('index');
        Route::get('/refrescar', [CotizacionController::class, 'refrescarTabla'])->name('refrescar');
        Route::get('/clientes/buscar', [CotizacionController::class, 'buscarClientes'])->name('clientes.buscar');
        Route::get('/productos/buscar', [CotizacionController::class, 'buscarProductos'])->name('productos.buscar');
        Route::get('/catalogos', [CotizacionController::class, 'catalogos'])->name('catalogos');
        Route::get('/productos-por-sucursal/{sucursalId}', [CotizacionController::class, 'productosPorSucursal'])->name('productos.por-sucursal');
        Route::post('/', [CotizacionController::class, 'store'])->name('store');
        Route::get('/{id}', [CotizacionController::class, 'show'])->name('show');
        Route::put('/{id}', [CotizacionController::class, 'update'])->name('update');
        Route::delete('/{id}', [CotizacionController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/enviar', [CotizacionController::class, 'enviar'])->name('enviar');
        Route::post('/{id}/version', [CotizacionController::class, 'crearVersion'])->name('version');
        Route::get('/{id}/versiones', [CotizacionController::class, 'versiones'])->name('versiones');
        Route::get('/{id}/preparar-version', [CotizacionController::class, 'prepararNuevaVersion'])->name('preparar-version');
        Route::post('/{id}/guardar-version', [CotizacionController::class, 'guardarNuevaVersion'])->name('guardar-version');
        Route::get('/{id}/ticket', [CotizacionController::class, 'ticket'])->name('ticket');
        Route::get('/{id}/preview-ticket', [CotizacionController::class, 'previewTicket'])->name('preview-ticket');
        Route::post('/{id}/marcar-enviada', [CotizacionController::class, 'marcarComoEnviada'])->name('marcar-enviada');
        Route::post('/guardar-producto-externo', [CotizacionController::class, 'guardarProductoExterno'])->name('guardar-producto-externo');
        Route::get('/{id}/disponibilidad-inventario', [CotizacionController::class, 'disponibilidadInventario'])->name('disponibilidad-inventario');
        Route::post('/{id}/generar-pedido', [CotizacionController::class, 'generarPedido'])->name('generar-pedido');
        Route::post('/{id}/generar-pedido-con-asignacion', [CotizacionController::class, 'generarPedidoConAsignacion'])->name('generar-pedido-con-asignacion');
    });

    // ============================================
    // SEGUIMIENTOS
    // ============================================
    Route::prefix('ventas/seguimiento')->name('ventas.seguimiento.')->group(function () {
        Route::get('/cotizacion/{id}', [SeguimientoController::class, 'getCotizacionData'])->name('get.cotizacion');
        Route::get('/pedido/{id}', [SeguimientoController::class, 'getPedidoData'])->name('get.pedido');
        Route::post('/store', [SeguimientoController::class, 'store'])->name('store');
        Route::get('/configuracion-alerta', [SeguimientoController::class, 'getConfiguracionAlerta'])->name('config.alerta');
    });

    // ============================================
    // VENTAS - PEDIDOS
    // ============================================
    Route::prefix('ventas/pedidos')->name('ventas.pedidos.')->group(function () {
        // PRIMERO: Rutas específicas (sin parámetros variables)
        Route::get('/repartidores-disponibles', [PedidoController::class, 'repartidoresDisponibles'])->name('repartidores-disponibles');
        Route::get('/pendientes/crm', [PedidoController::class, 'pedidosPendientesCRM'])->name('pendientes.crm');  
        Route::get('/pendientes/repartidor', [PedidoController::class, 'pedidosPendientesRepartidor'])->name('pendientes.repartidor');
        Route::get('/asignacion-multipedidos', [PedidoController::class, 'vistaAsignacionMultiple'])->name('asignacion.multipedidos');
        Route::get('/repartidor/recorrido', [PedidoController::class, 'vistaRecorridoRepartidor'])->name('repartidor.recorrido');
        
        // Rutas con parámetros específicos (antes de /{id})
        Route::get('/{id}/productos-externos', [PedidoController::class, 'productosExternos'])->name('productos-externos');
        Route::get('/{id}/sucursal-id', [PedidoController::class, 'obtenerSucursalIdPedido'])->name('sucursal-id');
        Route::post('/reprogramar-producto', [PedidoController::class, 'reprogramarProducto'])->name('reprogramar-producto');
        Route::post('/reprogramar-multi', [PedidoController::class, 'reprogramarMulti'])->name('reprogramar-multi');
        Route::get('/refrescar-tabla', [PedidoController::class, 'refrescarTabla'])->name('refrescar-tabla');
        
        // SEGUNDO: Rutas con parámetros {id} (genéricas)
        Route::get('/', [PedidoController::class, 'index'])->name('index');
        Route::get('/{id}', [PedidoController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [PedidoController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PedidoController::class, 'update'])->name('update');
        Route::delete('/{id}', [PedidoController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/asignar-sucursales', [PedidoController::class, 'asignarSucursales'])->name('asignar-sucursales');
        Route::get('/{id}/repartidores', [PedidoController::class, 'vistaAsignarRepartidor'])->name('repartidores.vista');
        Route::get('/{id}/repartidores/status', [PedidoController::class, 'repartidoresConStatus'])->name('repartidores.status');
        Route::post('/{id}/entregar', [PedidoController::class, 'entregar'])->name('entregar');
        Route::post('/sucursal/{id}/marcar-listo', [PedidoController::class, 'marcarListoSucursal'])->name('marcar-listo');
        Route::get('/{id}/pdf', [PedidoController::class, 'pdf'])->name('pdf');
    });

    // Rutas para conversión de EAN (fuera del grupo para evitar conflictos)
    Route::post('/ventas/pedidos/marcar-listo-ean', [PedidoController::class, 'marcarListoConEAN'])->name('ventas.pedidos.marcar-listo-ean');

    // ============================================
    // ASIGNAR REPARTIDOR (Ruta POST sin ID fijo)
    // ============================================
    Route::post('/ventas/pedidos/asignar-repartidor', [PedidoController::class, 'asignarRepartidor'])->name('ventas.pedidos.asignarRepartidor');

    // ============================================
    // RECORRIDOS (Para iniciar y finalizar entregas de pedidos)
    // ============================================
    Route::prefix('recorridos')->name('recorridos.')->group(function () {
        Route::post('/iniciar', [PedidoController::class, 'iniciarRecorrido'])->name('iniciar');
        Route::post('/finalizar', [PedidoController::class, 'finalizarRecorrido'])->name('finalizar');
    });

    // ============================================
    // PRODUCTOS - STOCK POR SUCURSAL (para pedidos)
    // ============================================
    Route::get('/productos/stock-por-sucursal', [PedidoController::class, 'stockPorSucursal'])->name('productos.stock-por-sucursal');

    // ============================================
    // AGENDA CONTACTOS
    // ============================================
    Route::prefix('ventas/agenda-contactos')->name('ventas.agenda_contactos.')->group(function () {
        Route::get('/', [AgendaContactosController::class, 'index'])->name('index');
        Route::post('/', [AgendaContactosController::class, 'store'])->name('store');
        Route::put('/{id}', [AgendaContactosController::class, 'update'])->name('update');
        Route::get('/{id}/edit', [AgendaContactosController::class, 'edit'])->name('edit');
        Route::delete('/{id}', [AgendaContactosController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/estado', [AgendaContactosController::class, 'cambiarEstado'])->name('estado');
        Route::get('/proximos', [AgendaContactosController::class, 'proximosContactos'])->name('proximos');
        Route::patch('/{id}/recordatorio', [AgendaContactosController::class, 'marcarRecordatorioEnviado'])->name('recordatorio');
        Route::get('/clientes/buscar', [AgendaContactosController::class, 'buscarClientes'])->name('clientes.buscar');
        Route::post('/{id}/reagendar', [AgendaContactosController::class, 'reagendar'])->name('reagendar');
        Route::get('/tipos', [AgendaContactosController::class, 'tiposAgenda'])->name('tipos');
    });

    // ============================================
    // SEGURIDAD - USUARIOS
    // ============================================
    Route::prefix('seguridad/usuarios')->name('seguridad.usuarios.')->group(function () {
        Route::get('/', [UsuarioController::class, 'index'])->name('index');
        Route::get('/json', [UsuarioController::class, 'json'])->name('json');
        Route::get('/buscar', [UsuarioController::class, 'buscarUsuarios'])->name('buscar');
        Route::get('/repartidores', [UsuarioController::class, 'repartidoresLista'])->name('repartidores');
        Route::post('/', [UsuarioController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [UsuarioController::class, 'edit'])->name('edit');
        Route::put('/{id}', [UsuarioController::class, 'update'])->name('update');
        Route::delete('/{id}', [UsuarioController::class, 'destroy'])->name('destroy');
    });

    // ============================================
    // SEGURIDAD - PERMISOS
    // ============================================
    Route::prefix('seguridad/permisos')->name('seguridad.permisos.')->group(function () {
        Route::get('/', [PermisoController::class, 'index'])->name('index');
    });

    // ============================================
    // SEGURIDAD - RESPALDOS
    // ============================================
    Route::prefix('seguridad')->name('seguridad.')->group(function () {
        Route::get('/respaldos', [RespaldoController::class, 'index'])->name('respaldos.index');
        Route::post('/respaldos', [RespaldoController::class, 'create'])->name('respaldos.store');
        Route::get('/respaldos/download/{filename}', [RespaldoController::class, 'download'])->name('respaldos.download');
        Route::delete('/respaldos/{filename}', [RespaldoController::class, 'destroy'])->name('respaldos.destroy');
    });
    
    // ============================================
    // REPORTES
    // ============================================

    // Reportes de Compras por Cliente
    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/ventas', [VentasController::class, 'index'])->name('compras_cliente.index');
        Route::get('/ventas/buscar-clientes', [VentasController::class, 'buscarClientes'])->name('compras_cliente.buscar-clientes');
        Route::get('/ventas/clientes', [VentasController::class, 'clientes'])->name('compras_cliente.clientes');
        Route::get('/ventas/clientes/data', [VentasController::class, 'clientesData'])->name('compras_cliente.clientes.data');
        Route::get('/ventas/cliente/{id}', [VentasController::class, 'detalleCliente'])->name('compras_cliente.cliente.detalle');
        Route::get('/ventas/cliente/{clienteId}/grupo-madre/{grupoMadreId}', [VentasController::class, 'detalleGrupoMadre'])->name('compras_cliente.cliente.grupo-madre');
        Route::get('/ventas/frecuencia-compra', [VentasController::class, 'frecuenciaCompra'])->name('compras_cliente.frecuencia-compra');
        Route::get('/ventas/montos-promedio', [VentasController::class, 'montosPromedio'])->name('compras_cliente.montos-promedio');
        Route::get('/ventas/top-clientes', [VentasController::class, 'topClientes'])->name('compras_cliente.top-clientes');
        Route::get('/ventas/top-productos', [VentasController::class, 'topProductos'])->name('compras_cliente.top-productos');
        Route::get('/ventas/top-sucursales', [VentasController::class, 'topSucursales'])->name('compras_cliente.top-sucursales');
        Route::get('/ventas/exportar/excel', [VentasController::class, 'exportarExcel'])->name('compras_cliente.exportar.excel');
        Route::get('/ventas/exportar/pdf', [VentasController::class, 'exportarPdf'])->name('compras_cliente.exportar.pdf');
        Route::get('/ventas/medicamentos', [VentasController::class, 'medicamentos'])->name('compras_cliente.medicamentos');
        Route::get('/ventas/medicamentos/data', [VentasController::class, 'medicamentosData'])->name('compras_cliente.medicamentos.data');

        // Reporte de montos promedio compra
        Route::get('/ventas/montos-promedio-compra', [VentasController::class, 'montosPromedio'])->name('compras_cliente.montos-promedio-compra');
        Route::get('/ventas/montos-promedio-compra/data', [VentasController::class, 'montosPromedioData'])->name('compras_cliente.montos-promedio-compra.data');
        Route::get('/ventas/montos-promedio-compra/detalle/{id}', [VentasController::class, 'detalleComprasCliente'])->name('compras_cliente.montos-promedio-compra.detalle');
        Route::get('/ventas/montos-promedio-compra/exportar/excel', [VentasController::class, 'exportarMontosPromedioExcel'])->name('compras_cliente.montos-promedio-compra.exportar.excel');
        Route::get('/ventas/montos-promedio-compra/exportar/pdf', [VentasController::class, 'exportarMontosPromedioPdf'])->name('compras_cliente.montos-promedio-compra.exportar.pdf');
        Route::get('/ventas/montos-promedio-compra/productos/{clienteId}/{ticket}', [VentasController::class, 'getProductosPorTicket'])->name('compras_cliente.montos-promedio-compra.productos');

        // Sucursales Preferidas
        Route::get('/sucursales-preferidas', [VentasController::class, 'sucursalesPreferidas'])->name('sucursales-preferidas');
        Route::get('/sucursales-preferidas/data', [VentasController::class, 'sucursalesPreferidasData'])->name('sucursales-preferidas.data');
        Route::get('/sucursales-preferidas/exportar/excel', [VentasController::class, 'exportarSucursalesExcel'])->name('sucursales-preferidas.exportar.excel');
        Route::get('/sucursales-preferidas/exportar/pdf', [VentasController::class, 'exportarSucursalesPdf'])->name('sucursales-preferidas.exportar.pdf');
    });

    // Reporte de Cotizaciones por Cliente
    Route::prefix('reportes/cotizaciones-cliente')->name('reportes.cotizaciones-cliente.')->group(function () {
        Route::get('/', [CotizacionesClienteController::class, 'index'])->name('index');
        Route::get('/data', [CotizacionesClienteController::class, 'data'])->name('data');
        Route::get('/cliente/{id}/detalle', [CotizacionesClienteController::class, 'detalleCliente'])->name('cliente.detalle');
        Route::get('/cliente/{id}/data', [CotizacionesClienteController::class, 'detalleData'])->name('cliente.data');
        Route::get('/productos/{cotizacionId}', [CotizacionesClienteController::class, 'getProductos'])->name('productos');
        Route::get('/cliente/{clienteId}/cotizacion/{cotizacionId}/productos', [CotizacionesClienteController::class, 'vistaProductos'])->name('productos.vista');
        Route::get('/exportar/excel', [CotizacionesClienteController::class, 'exportarExcel'])->name('exportar.excel');
        Route::get('/exportar/pdf', [CotizacionesClienteController::class, 'exportarPdf'])->name('exportar.pdf');
    });

    // Pedidos por Cliente

    Route::prefix('reportes/pedidos-cliente')->name('reportes.pedidos-cliente.')->group(function () {
        Route::get('/', [VentasController::class, 'pedidosCliente'])->name('index');
        Route::get('/data', [VentasController::class, 'pedidosClienteData'])->name('data');
        Route::get('/exportar', [VentasController::class, 'exportarPedidosCliente'])->name('exportar');
        Route::get('/cliente/{id}/detalle', [VentasController::class, 'detallePedidoCliente'])->name('cliente.detalle');
        Route::get('/cliente/{id}/data', [VentasController::class, 'detallePedidoClienteData'])->name('cliente.data');
        Route::get('/cliente/{clienteId}/pedido/{pedidoId}/productos', [VentasController::class, 'vistaProductosPedido'])->name('cliente.pedido.productos');
    });
});

// ============================================
// FALLBACK
// ============================================
Route::fallback(function (Request $request) {
    if ($request->is('api/*')) {
        return response()->json([
            'success' => false,
            'message' => 'Ruta no encontrada'
        ], 404);
    }
    return redirect()->route('login');
});