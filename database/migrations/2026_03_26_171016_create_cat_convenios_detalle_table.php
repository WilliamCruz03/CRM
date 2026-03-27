<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cat_convenios_detalle', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_convenio');
            $table->unsignedBigInteger('id_familia');
            $table->decimal('porcentaje_descuento', 5, 2);
            $table->timestamps();
            
            // Cambiar ON DELETE CASCADE a ON DELETE NO ACTION para evitar ciclos
            $table->foreign('id_convenio')->references('id')->on('cat_convenios')->onDelete('cascade');
            $table->foreign('id_familia')->references('id_familia')->on('cat_familias')->onDelete('no action');
            $table->unique(['id_convenio', 'id_familia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cat_convenios_detalle');
    }
};