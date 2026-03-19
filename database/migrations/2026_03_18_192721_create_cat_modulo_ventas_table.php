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
        Schema::create('cat_modulo_ventas', function (Blueprint $table) {
            $table->integer('id_ventas_modulo', true);
            $table->boolean('cotizaciones')->default(false);
            $table->boolean('pedidos_anticipo')->default(false);
            $table->boolean('seguimiento_ventas')->default(false);
            $table->boolean('seguimiento_cotizaciones')->default(false);
            $table->boolean('agenda_contactos')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cat_modulo_ventas');
    }
};
