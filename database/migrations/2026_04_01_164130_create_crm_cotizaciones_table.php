<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/* EJECUTAR MIGRACION
php artisan migrate:refresh --path=/database/migrations/2026_04_01_164130_create_crm_cotizaciones_table.php
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('crm_cotizaciones_detalle');
        Schema::dropIfExists('crm_cotizaciones');

        Schema::create('crm_cotizaciones', function (Blueprint $table) {
            $table->id('id_cotizacion');
            $table->string('folio', 20)->unique();
            $table->unsignedInteger('id_cliente');
            $table->unsignedBigInteger('id_fase');
            $table->unsignedBigInteger('id_clasificacion')->nullable();
            $table->unsignedBigInteger('id_sucursal_asignada')->nullable();
            $table->decimal('importe_total', 12, 2)->default(0);
            $table->text('comentarios')->nullable();
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamp('fecha_ultima_modificacion')->useCurrent()->useCurrentOnUpdate();
            $table->unsignedInteger('creado_por')->nullable();
            $table->unsignedInteger('modificado_por')->nullable();
            $table->boolean('activo')->default(true);
            $table->tinyInteger('certeza')->nullable()->default(0);
            $table->boolean('enviado')->default(false);
            $table->timestamp('fecha_envio')->nullable();
            $table->date('fecha_entrega_sugerida')->nullable();
            $table->foreignId('cotizacion_origen_id')->nullable()->constrained('crm_cotizaciones', 'id_cotizacion');
            $table->integer('version')->default(1);

            $table->foreign('id_cliente')->references('id_Cliente')->on('catalogo_cliente_maestro');
            $table->foreign('id_fase')->references('id_fase')->on('cat_fases');
            $table->foreign('id_clasificacion')->references('id_clasificacion')->on('cat_clasificaciones');
            $table->foreign('id_sucursal_asignada')->references('id_sucursal')->on('sucursales');
            $table->foreign('creado_por')->references('id_personal_empresa')->on('personal_empresa');
            $table->foreign('modificado_por')->references('id_personal_empresa')->on('personal_empresa');

            $table->index('id_cliente');
            $table->index('id_fase');
            $table->index('fecha_creacion');
            $table->index('certeza');
            $table->index('enviado');
            $table->index('cotizacion_origen_id');
        });

        Schema::create('crm_cotizaciones_detalle', function (Blueprint $table) {
            $table->id('id_cotizacion_detalle');
            $table->foreignId('id_cotizacion')->constrained('crm_cotizaciones', 'id_cotizacion')->onDelete('cascade');
            $table->foreignId('id_producto')->constrained('catalogo_general', 'id_catalogo_general');
            $table->string('codbar', 13)->nullable();
            $table->string('descripcion', 150)->nullable();
            $table->integer('cantidad')->default(1);
            $table->decimal('precio_unitario', 10, 2)->default(0);
            $table->decimal('descuento', 5, 2)->default(0);
            $table->decimal('importe', 12, 2)->default(0);
            $table->foreignId('id_convenio')->nullable()->constrained('cat_convenios', 'id_convenio');
            $table->foreignId('id_sucursal_surtido')->nullable()->constrained('sucursales', 'id_sucursal');
            $table->timestamp('fecha_actualizacion')->useCurrent();
            $table->boolean('activo')->default(true);
            $table->boolean('apartado')->default(false);

            $table->index('id_cotizacion');
            $table->index('id_producto');
            $table->index('apartado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_cotizaciones_detalle');
        Schema::dropIfExists('crm_cotizaciones');
    }
};