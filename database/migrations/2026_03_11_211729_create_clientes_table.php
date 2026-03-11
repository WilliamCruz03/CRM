<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellidos');
            $table->string('email')->unique();
            $table->string('telefono')->nullable();
            $table->string('calle')->nullable();
            $table->string('colonia')->nullable();
            $table->string('ciudad')->nullable();
            $table->enum('estado', ['Activo', 'Inactivo'])->default('Activo');
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Para eliminar sin borrar realmente
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};