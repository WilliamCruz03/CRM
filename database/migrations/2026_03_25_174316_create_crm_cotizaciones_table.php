<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_cotizaciones', function (Blueprint $table) {
            $table->id('id_cotizacion');
            $table->string('folio', 20)->unique();
            $table->unsignedBigInteger('id_cliente');
            $table->unsignedBigInteger('id_fase');
            $table->unsignedBigInteger('id_clasificacion')->nullable();
            $table->unsignedBigInteger('id_sucursal_asignada')->nullable();
            $table->decimal('importe_total', 12, 2)->default(0);
            $table->text('comentarios')->nullable();
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamp('fecha_ultima_modificacion')->useCurrent()->useCurrentOnUpdate();
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->unsignedBigInteger('modificado_por')->nullable();
            $table->boolean('activo')->default(true);
            
            $table->foreign('id_cliente')->references('id_Cliente')->on('catalogo_cliente_maestro');
            $table->foreign('id_fase')->references('id_fase')->on('cat_fases');
            $table->foreign('id_clasificacion')->references('id_clasificacion')->on('cat_clasificaciones');
            $table->foreign('id_sucursal_asignada')->references('id_sucursal')->on('sucursales');
            $table->foreign('creado_por')->references('id_personal_empresa')->on('personal_empresa');
            $table->foreign('modificado_por')->references('id_personal_empresa')->on('personal_empresa');
            
            $table->index('id_cliente');
            $table->index('id_fase');
            $table->index('fecha_creacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_cotizaciones');
    }
};