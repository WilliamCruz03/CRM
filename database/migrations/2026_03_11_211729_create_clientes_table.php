<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id('id_Cliente'); // Clave primaria personalizada
            $table->integer('sucursal_origen')->default(0); // 0 = CRM
            $table->string('nombre');
            $table->string('apPaterno');
            $table->string('apMaterno')->nullable();
            $table->string('titulo')->nullable(); // ING, LIC, SR., etc.
            $table->enum('status', ['CLIENTE', 'PROSPECTO', 'BLOQUEADO'])->default('PROSPECTO');
            $table->string('telefono1')->nullable();
            $table->string('telefono2')->nullable();
            $table->string('email1')->unique();
            $table->string('email2')->nullable();
            $table->text('domicilio')->nullable();
            $table->enum('sexo', ['M', 'F', 'OTRO'])->nullable();
            $table->date('fechaNac')->nullable();
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->integer('id_operador')->nullable(); // Quién creó el registro
            $table->integer('pais_id')->nullable();
            $table->integer('estado_id')->nullable();
            $table->integer('municipio_id')->nullable();
            $table->integer('localidad_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Índices para búsquedas rápidas
            $table->index('nombre');
            $table->index('apPaterno');
            $table->index('email1');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};