<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permisos_personal', function (Blueprint $table) {
            $table->id('id_permiso');
            
            // Usar integer() en lugar de foreignId() para que coincida con el tipo de personal_empresa
            $table->integer('id_personal_empresa')->unsigned();
            
            // Relaciones con cada módulo (nullable)
            $table->integer('id_cliente_modulo')->unsigned()->nullable();
            $table->integer('id_ventas_modulo')->unsigned()->nullable();
            $table->integer('id_seguridad_modulo')->unsigned()->nullable();
            $table->integer('id_reportes_modulo')->unsigned()->nullable();
            
            $table->integer('id_accion')->unsigned();
            $table->boolean('permitido')->default(true);
            $table->timestamps();
            
            // Definir las llaves foráneas manualmente
            $table->foreign('id_personal_empresa')
                  ->references('id_personal_empresa')
                  ->on('personal_empresa')
                  ->onDelete('cascade');
            
            $table->foreign('id_cliente_modulo')
                  ->references('id_cliente_modulo')
                  ->on('cat_modulo_clientes')
                  ->onDelete('cascade');
            
            $table->foreign('id_ventas_modulo')
                  ->references('id_ventas_modulo')
                  ->on('cat_modulo_ventas')
                  ->onDelete('cascade');
            
            $table->foreign('id_seguridad_modulo')
                  ->references('id_seguridad_modulo')
                  ->on('cat_modulo_seguridad')
                  ->onDelete('cascade');
            
            $table->foreign('id_reportes_modulo')
                  ->references('id_reportes_modulo')
                  ->on('cat_modulo_reportes')
                  ->onDelete('cascade');
            
            $table->foreign('id_accion')
                  ->references('id_accion')
                  ->on('cat_acciones')
                  ->onDelete('cascade');
            
            // Índices para búsquedas rápidas
            $table->index(['id_personal_empresa', 'id_accion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permisos_personal');
    }
};