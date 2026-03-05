<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
USE App\Http\Controllers\DashboardController;

/*
Route::get('/', function () {
    return view('welcome');
});
*/

Route::get('/', [DashboardController::class, "index"])->name("dashboard.index");

//Dashboard
Route::get("/dashboard", [DashboardController::class, "index"])->name("dashboard");

// Rutas para el CRUD de clientes
Route::prefix("clientes")->name("clientes.")->group(function () {
    Route::get("/", [ClienteController::class, "index"])->name("index");
    Route::get("/{id}", [ClienteController::class, "show"])->name("show");
    Route::get("/{id}/edit", [ClienteController::class, "edit"])->name("edit");
    Route::put("/{id}", [ClienteController::class, "update"])->name("update");
    Route::delete("/{id}", [ClienteController::class, "destroy"])->name("destroy");
    Route::post("/", [ClienteController::class, "store"])->name("store");
});