<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogo_general', function (Blueprint $table) {
            $table->id('id_catalogo_general');
            $table->unsignedBigInteger('id_sucursal')->nullable();
            $table->string('ean', 13)->nullable();
            $table->string('descripcion', 150);
            $table->decimal('inventario', 10, 2)->default(0);
            $table->decimal('costo', 10, 2)->default(0);
            $table->decimal('precio', 10, 2)->default(0);
            $table->string('num_familia', 6)->nullable();
            $table->boolean('activo')->default(true);
            
            $table->foreign('id_sucursal')->references('id_sucursal')->on('sucursales')->onDelete('set null');
            $table->foreign('num_familia')->references('num_familia')->on('cat_familias')->onDelete('set null');
            $table->index('num_familia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogo_general');
    }
};