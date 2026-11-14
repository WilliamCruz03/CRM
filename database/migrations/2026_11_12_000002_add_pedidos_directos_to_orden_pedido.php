<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orden_pedido', function (Blueprint $table): void {
            $table->integer('id_cotizacion')->nullable()->change();
            $table->boolean('sobre_pedido')->default(false);
            $table->integer('id_cliente')->nullable();
            $table->integer('id_sucursal_origen')->nullable();
            $table->decimal('anticipo_cliente', 12, 2)->nullable();
        });

        Schema::table('orden_pedido_detalle', function (Blueprint $table): void {
            $table->string('descripcion_sobre_pedido', 255)->nullable();
        });
    }

    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::table('orden_pedido')->whereNull('id_cotizacion')->exists()) {
            throw new RuntimeException('Existen pedidos sin cotización vinculada.');
        }

        Schema::table('orden_pedido', function (Blueprint $table): void {
            $table->integer('id_cotizacion')->nullable(false)->change();
            $table->dropColumn(['sobre_pedido', 'id_cliente', 'id_sucursal_origen', 'anticipo_cliente']);
        });

        Schema::table('orden_pedido_detalle', function (Blueprint $table): void {
            $table->dropColumn('descripcion_sobre_pedido');
        });
    }
};
