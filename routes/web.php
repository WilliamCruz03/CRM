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

// ============================================
// RUTAS PÚBLICAS (sin autenticación)
// ============================================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ============================================
// RUTAS PROTEGIDAS (requieren autenticación)
// ============================================
Route::middleware(['auth', 'check.activo'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/debug-permisos', function() {
        $user = auth()->user();
        
        return [
            'usuario' => $user->usuario,
            'modulos_acceso' => $user->modulosConAcceso(),
            'permisos_individuales' => [
                'clientes.mostrar' => $user->puedeVerModulo('clientes'),
                'clientes.ver' => $user->puede('clientes', 'ver'),
                'enfermedades.ver' => $user->puede('enfermedades', 'ver'),
                'intereses.ver' => $user->puede('intereses', 'ver'),
                'cotizaciones.ver' => $user->puede('cotizaciones', 'ver'),
                'seguridad.ver' => $user->puede('seguridad', 'ver'),
            ],
            'permisos_bd' => $user->permisos()->with(['accion', 'moduloClientes', 'moduloVentas', 'moduloSeguridad', 'moduloReportes'])->get()->map(function($p) {
                return [
                    'accion' => $p->accion ? $p->accion->nombre : null,
                    'cliente_modulo' => $p->id_cliente_modulo ? 'tiene' : null,
                    'ventas_modulo' => $p->id_ventas_modulo ? 'tiene' : null,
                    'seguridad_modulo' => $p->id_seguridad_modulo ? 'tiene' : null,
                    'reportes_modulo' => $p->id_reportes_modulo ? 'tiene' : null,
                ];
            }),
        ];
    })->middleware('auth');

    // ============================================
    // NOTIFICACIONES
    // ============================================
    Route::get('/notificaciones/cotizaciones', [NotificacionController::class, 'getNotificaciones'])->name('notificaciones.cotizaciones');
    
    // ============================================
    // CLIENTES
    // ============================================
    Route::get('/clientes/buscar', [ClienteController::class, 'search'])->name('clientes.search');
    
    Route::prefix("clientes")->name("clientes.")->group(function () {
        Route::get("/", [ClienteController::class, "index"])->name("index");
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
        })->name('clientes.enfermedades.destroy');
    });
    
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
    // API PATOLOGÍAS
    // ============================================
    Route::get('/patologias/todas', function() {
        $patologias = App\Models\Patologia::all(['id_patologia', 'descripcion']);
        return response()->json(['success' => true, 'data' => $patologias]);
    })->name('patologias.todas');
    
    // ============================================
    // RELACIONES CLIENTE-PATOLOGÍA
    // ============================================
    Route::delete('/clientes/{clienteId}/patologias', [ClienteController::class, 'eliminarPatologia'])->name('clientes.patologias.destroy');
    Route::delete('/clientes/{clienteId}/patologias/{patologiaAsociadaId}', [ClienteController::class, 'eliminarPatologiaPorId'])->name('clientes.patologias.destroy.porId');
    
    // ============================================
    // INTERESES
    // ============================================
    Route::resource('intereses', InteresController::class);
    
    // ============================================
    // VENTAS - COTIZACIONES
    // ============================================
    Route::prefix('ventas/cotizaciones')->name('ventas.cotizaciones.')->group(function () {
        Route::get('/', [CotizacionController::class, 'index'])->name('index');
        Route::get('/clientes/buscar', [CotizacionController::class, 'buscarClientes'])->name('clientes.buscar');
        Route::get('/productos/buscar', [CotizacionController::class, 'buscarProductos'])->name('productos.buscar');
        Route::get('/catalogos', [CotizacionController::class, 'catalogos'])->name('catalogos');
        Route::get('/productos-por-sucursal/{sucursalId}', [CotizacionController::class, 'productosPorSucursal'])->name('productos.por-sucursal');
        Route::post('/', [CotizacionController::class, 'store'])->name('store');
        Route::get('/{id}', [CotizacionController::class, 'show'])->name('show');
        Route::put('/{id}', [CotizacionController::class, 'update'])->name('update');
        Route::delete('/{id}', [CotizacionController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/enviar', [CotizacionController::class, 'enviar'])->name('ventas.cotizaciones.enviar');
        Route::post('/{id}/version', [CotizacionController::class, 'crearVersion'])->name('ventas.cotizaciones.version');
        Route::get('/{id}/versiones', [CotizacionController::class, 'versiones'])->name('ventas.cotizaciones.versiones');
        Route::get('/{id}/preparar-version', [CotizacionController::class, 'prepararNuevaVersion'])->name('ventas.cotizaciones.preparar-version');
        Route::post('/{id}/guardar-version', [CotizacionController::class, 'guardarNuevaVersion'])->name('ventas.cotizaciones.guardar-version');
        Route::get('/{id}/ticket', [CotizacionController::class, 'ticket'])->name('ventas.cotizaciones.ticket');
        Route::get('/{id}/preview-ticket', [CotizacionController::class, 'previewTicket'])->name('ventas.cotizaciones.preview-ticket');
        Route::post('/{id}/marcar-enviada', [CotizacionController::class, 'marcarComoEnviada'])->name('ventas.cotizaciones.marcar-enviada');
        Route::post('/guardar-producto-externo', [CotizacionController::class, 'guardarProductoExterno'])->name('guardar-producto-externo');
        Route::post('/{id}/generar-pedido', [CotizacionController::class, 'generarPedido'])->name('generar-pedido');
    });

    // ============================================
    // SEGUIMIENTOS
    //============================================
    // Seguimiento (reutilizable para cotizaciones y pedidos)
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
        Route::get('/{id}/productos-externos', [PedidoController::class, 'productosExternosPedido'])->name('productos-externos');
        Route::get('/{id}/sucursal-id', [PedidoController::class, 'obtenerSucursalIdPedido'])->name('sucursal-id');
        Route::post('/reprogramar-producto', [PedidoController::class, 'reprogramarProducto'])->name('reprogramar-producto');
        Route::post('/reprogramar-multi', [PedidoController::class, 'reprogramarMulti'])->name('reprogramar-multi');
        
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
    Route::prefix('recorridos')->name('recorridos.')->middleware(['auth', 'check.activo'])->group(function () {
        Route::post('/iniciar', [PedidoController::class, 'iniciarRecorrido'])->name('iniciar');
        Route::post('/finalizar', [PedidoController::class, 'finalizarRecorrido'])->name('finalizar');
    });

    // ============================================
    // PRODUCTOS - STOCK POR SUCURSAL (para pedidos)
    // ============================================
    Route::get('/productos/stock-por-sucursal', [PedidoController::class, 'stockPorSucursal'])->name('productos.stock-por-sucursal');

    // Agenda Contactos
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
        Route::get('/repartidores', [UsuarioController::class, 'repartidoresLista'])->name('repartidores');
        Route::post('/', [UsuarioController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [UsuarioController::class, 'edit'])->name('edit');
        Route::put('/{id}', [UsuarioController::class, 'update'])->name('update');
        Route::delete('/{id}', [UsuarioController::class, 'destroy'])->name('destroy');
    });

    // Ruta para sucursales activas
    Route::get('/sucursales/activas', function() {
        $sucursales = App\Models\Sucursal::where('activo', 1)->get(['id_sucursal', 'nombre']);
        return response()->json(['success' => true, 'data' => $sucursales]);
    })->name('sucursales.activas')->middleware('auth');

    // ============================================
    // PERMISOS (show)
    // ============================================

    Route::prefix('seguridad/permisos')->name('seguridad.permisos.')->group(function () {
    Route::get('/', [PermisoController::class, 'index'])->name('index');
    });

    // ============================================
    // RUTA PARA VERIFICAR ESTADO DEL USUARIO (tiempo real)
    // ============================================
    Route::middleware('auth')->group(function () {
        Route::get('/user/check-status', function () {
            return response()->json([
                'active' => auth()->user()->Activo ? true : false
            ]);
        })->name('user.check.status');
    });

    // ============================================
    // REPORTES
    // ============================================
    
    // Reportes de Ventas
    Route::prefix('reportes')->name('reportes.')->middleware('auth')->group(function () {
        Route::get('/ventas', [VentasController::class, 'index'])->name('ventas.index');
        Route::get('/ventas/clientes', [VentasController::class, 'clientes'])->name('ventas.clientes');
        Route::get('/ventas/cliente/{id}', [VentasController::class, 'detalleCliente'])->name('ventas.cliente.detalle');
        Route::get('/ventas/cliente/{clienteId}/familia/{familiaId}', [VentasController::class, 'detalleFamilia'])->name('ventas.cliente.familia');
        Route::get('/ventas/frecuencia-compra', [VentasController::class, 'frecuenciaCompra'])->name('ventas.frecuencia-compra');
        Route::get('/ventas/montos-promedio', [VentasController::class, 'montosPromedio'])->name('ventas.montos-promedio');
        Route::get('/ventas/top-clientes', [VentasController::class, 'topClientes'])->name('ventas.top-clientes');
        Route::get('/ventas/top-productos', [VentasController::class, 'topProductos'])->name('ventas.top-productos');
        Route::get('/ventas/top-sucursales', [VentasController::class, 'topSucursales'])->name('ventas.top-sucursales');
        Route::get('/ventas/cotizaciones-cliente', [VentasController::class, 'cotizacionesCliente'])->name('ventas.cotizaciones-cliente');
        Route::get('/ventas/cotizaciones-concretadas', [VentasController::class, 'cotizacionesConcretadas'])->name('ventas.cotizaciones-concretadas');
        Route::get('/ventas/exportar/excel', [VentasController::class, 'exportarExcel'])->name('ventas.exportar.excel');
        Route::get('/ventas/exportar/pdf', [VentasController::class, 'exportarPdf'])->name('ventas.exportar.pdf');
    });
});

// ============================================
// FALLBACK - Si alguna ruta no existe
// ============================================
Route::fallback(function () {
    return redirect()->route('login');
});