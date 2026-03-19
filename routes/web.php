<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnfermedadController;
use App\Http\Controllers\PreferenciaController;
use App\Http\Controllers\InteresController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\Auth\LoginController;

// ============================================
// RUTAS PÚBLICAS
// ============================================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ============================================
// DASHBOARD (accesible, el controlador verifica auth)
// ============================================
Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/test-auth', function() {
    if (auth()->check()) {
        return "Usuario autenticado: " . auth()->user()->usuario;
    } else {
        return "No autenticado";
    }
});

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
    
    Route::delete('/{clienteId}/enfermedades/{enfermedadId}', function($clienteId, $enfermedadId) {
        $cliente = App\Models\Cliente::findOrFail($clienteId);
        $cliente->enfermedades()->detach($enfermedadId);
        return response()->json([
            'success' => true, 
            'message' => 'Enfermedad eliminada del cliente correctamente'
        ]);
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
    return response()->json([
        'success' => true,
        'data' => $patologias
    ]);
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
    Route::get('/crear', [CotizacionController::class, 'create'])->name('create');
    Route::post('/', [CotizacionController::class, 'store'])->name('store');
    Route::get('/{id}', [CotizacionController::class, 'show'])->name('show');
    Route::get('/{id}/editar', [CotizacionController::class, 'edit'])->name('edit');
    Route::put('/{id}', [CotizacionController::class, 'update'])->name('update');
    Route::delete('/{id}', [CotizacionController::class, 'destroy'])->name('destroy');
});

// ============================================
// SEGURIDAD - USUARIOS
// ============================================
Route::prefix('seguridad/usuarios')->name('seguridad.usuarios.')->group(function () {
    Route::get('/', [UsuarioController::class, 'index'])->name('index');
    Route::get('/{id}', [UsuarioController::class, 'show'])->name('show');
    Route::post('/', [UsuarioController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [UsuarioController::class, 'edit'])->name('edit');
    Route::put('/{id}', [UsuarioController::class, 'update'])->name('update');
    Route::delete('/{id}', [UsuarioController::class, 'destroy'])->name('destroy');
});

// ============================================
// FALLBACK
// ============================================
Route::fallback(function () {
    return redirect()->route('login');
});