<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_cotizaciones_detalle', function (Blueprint $table) {
            $table->id('id_cotizacion_detalle');
            $table->unsignedBigInteger('id_cotizacion');
            $table->unsignedBigInteger('id_producto');
            $table->string('codbar', 13)->nullable();
            $table->string('descripcion', 150)->nullable();
            $table->integer('cantidad')->default(1);
            $table->decimal('precio_unitario', 10, 2)->default(0);
            $table->decimal('descuento', 5, 2)->default(0);
            $table->decimal('importe', 12, 2)->default(0);
            $table->unsignedBigInteger('id_convenio')->nullable();
            $table->unsignedBigInteger('id_sucursal_surtido')->nullable();
            $table->timestamp('fecha_actualizacion')->useCurrent();
            $table->boolean('activo')->default(true);
            
            $table->foreign('id_cotizacion')->references('id_cotizacion')->on('crm_cotizaciones')->onDelete('cascade');
            $table->foreign('id_producto')->references('id_catalogo_general')->on('catalogo_general');
            $table->foreign('id_convenio')->references('id_convenio')->on('cat_convenios');
            $table->foreign('id_sucursal_surtido')->references('id_sucursal')->on('sucursales');
            
            $table->index('id_cotizacion');
            $table->index('id_producto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_cotizaciones_detalle');
    }
};