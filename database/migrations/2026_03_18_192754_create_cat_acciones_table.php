<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     public function up(): void
    {
        Schema::create('cat_acciones', function (Blueprint $table) {
            $table->integer('id_accion', true);
            $table->string('nombre', 30);
            $table->string('descripcion', 100)->nullable();
            $table->boolean('activo')->default(true);
        });

        // Insertar acciones disponibles
        DB::table('cat_acciones')->insert([
            ['nombre' => 'ver', 'descripcion' => 'Puede ver el listado'],
            ['nombre' => 'altas', 'descripcion' => 'Puede crear nuevos registros'],
            ['nombre' => 'edicion', 'descripcion' => 'Puede editar registros existentes'],
            ['nombre' => 'eliminar', 'descripcion' => 'Puede eliminar registros'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cat_acciones');
    }
};
