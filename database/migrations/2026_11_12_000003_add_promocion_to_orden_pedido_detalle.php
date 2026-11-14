<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orden_pedido_detalle', function (Blueprint $table): void {
            $table->char('ean_consultado', 13)->nullable();
            $table->string('categoria_promocion', 30)->nullable();
            $table->tinyInteger('porcentaje_promocion')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orden_pedido_detalle', function (Blueprint $table): void {
            $table->dropColumn(['ean_consultado', 'categoria_promocion', 'porcentaje_promocion']);
        });
    }
};
