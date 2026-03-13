<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnfermedadController;
use App\Http\Controllers\PreferenciaController; // 👈 IMPORTANTE: Agregar esta línea

/*
Route::get('/', function () {
    return view('welcome');
});
*/

Route::get('/', [DashboardController::class, "index"])->name("dashboard.index");

//Dashboard
Route::get("/dashboard", [DashboardController::class, "index"])->name("dashboard");

// Clientes
Route::prefix("clientes")->name("clientes.")->group(function () {
    Route::get("/", [ClienteController::class, "index"])->name("index");
    Route::get("/{id}", [ClienteController::class, "show"])->name("show");
    Route::get("/{id}/edit", [ClienteController::class, "edit"])->name("edit");
    Route::put("/{id}", [ClienteController::class, "update"])->name("update");
    Route::delete("/{id}", [ClienteController::class, "destroy"])->name("destroy");
    Route::post("/", [ClienteController::class, "store"])->name("store");
    
    // Eliminar enfermedad de un cliente
    Route::delete('/{clienteId}/enfermedades/{enfermedadId}', function($clienteId, $enfermedadId) {
        $cliente = App\Models\Cliente::findOrFail($clienteId);
        $cliente->enfermedades()->detach($enfermedadId);
        
        return response()->json([
            'success' => true, 
            'message' => 'Enfermedad eliminada del cliente correctamente'
        ]);
    })->name('clientes.enfermedades.destroy');
});

// Enfermedades
Route::prefix('enfermedades')->name('enfermedades.')->group(function () {
    Route::get('/', [EnfermedadController::class, 'index'])->name('index');
    Route::post('/', [EnfermedadController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [EnfermedadController::class, 'edit'])->name('edit');
    Route::put('/{id}', [EnfermedadController::class, 'update'])->name('update');
    Route::delete('/{id}', [EnfermedadController::class, 'destroy'])->name('destroy');
    Route::get('/todas', [EnfermedadController::class, 'getTodas'])->name('enfermedades.todas');
    
    // Rutas para categorías
    Route::post('/categorias', [EnfermedadController::class, 'storeCategoria'])->name('categorias.store');
    Route::get('/categorias', [EnfermedadController::class, 'getCategorias'])->name('categorias.index');
});

// Preferencias
Route::resource('preferencias', PreferenciaController::class);
Route::get('/preferencias/cliente/{clienteId}', [PreferenciaController::class, 'getByCliente'])->name('preferencias.por-cliente');
// Buscar clientes para el modal de preferencias
Route::get('/clientes/buscar', [ClienteController::class, 'search'])->name('clientes.search');