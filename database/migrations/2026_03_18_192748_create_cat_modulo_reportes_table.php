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
        Schema::create('cat_modulo_reportes', function (Blueprint $table) {
            $table->integer('id_reportes_modulo', true);
            $table->boolean('compras_cliente')->default(false);
            $table->boolean('frecuencia_compra')->default(false);
            $table->boolean('montos_promedio')->default(false);
            $table->boolean('sucursales_preferidas')->default(false);
            $table->boolean('cotizaciones_cliente')->default(false);
            $table->boolean('cotizaciones_concretadas')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cat_modulo_reportes');
    }
};
