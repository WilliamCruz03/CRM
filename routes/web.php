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
    // VENTAS - PEDIDOS
    // ============================================
        Route::prefix('ventas/pedidos')->name('ventas.pedidos.')->group(function () {
        // PRIMERO: Rutas específicas (sin parámetros variables)
        Route::get('/repartidores-disponibles', [PedidoController::class, 'repartidoresDisponibles'])->name('repartidores-disponibles');
        
        // SEGUNDO: Rutas con parámetros {id}
        Route::get('/', [PedidoController::class, 'index'])->name('index');
        Route::get('/{id}', [PedidoController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [PedidoController::class, 'edit'])->name('edit');
        Route::delete('/{id}', [PedidoController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/asignar-sucursales', [PedidoController::class, 'asignarSucursales'])->name('asignar-sucursales');
        Route::post('/{id}/asignar-repartidor', [PedidoController::class, 'asignarRepartidor'])->name('asignar-repartidor');
        Route::post('/{id}/entregar', [PedidoController::class, 'entregar'])->name('entregar');
        Route::post('/sucursal/{id}/marcar-listo', [PedidoController::class, 'marcarListoSucursal'])->name('marcar-listo');
        Route::get('/{id}/pdf', [PedidoController::class, 'pdf'])->name('pdf');
    });

    // ============================================
    // PRODUCTOS - STOCK POR SUCURSAL (para pedidos)
    // ============================================
    Route::get('/productos/stock-por-sucursal/{id}', [PedidoController::class, 'stockPorSucursal'])->name('productos.stock-por-sucursal');

    // ============================================
    // SEGURIDAD - USUARIOS
    // ============================================
    Route::prefix('seguridad/usuarios')->name('seguridad.usuarios.')->group(function () {
        Route::get('/', [UsuarioController::class, 'index'])->name('index');
        Route::post('/', [UsuarioController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [UsuarioController::class, 'edit'])->name('edit');
        Route::put('/{id}', [UsuarioController::class, 'update'])->name('update');
        Route::delete('/{id}', [UsuarioController::class, 'destroy'])->name('destroy');
    });

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
});

// ============================================
// FALLBACK - Si alguna ruta no existe
// ============================================
Route::fallback(function () {
    return redirect()->route('login');
});