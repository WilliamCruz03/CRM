<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cat_convenios_familias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_convenio');
            $table->unsignedBigInteger('id_familia');
            $table->decimal('porcentaje_descuento', 5, 2);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->foreign('id_convenio')->references('id_convenio')->on('cat_convenios')->onDelete('cascade');
            $table->foreign('id_familia')->references('id_familia')->on('cat_familias')->onDelete('cascade');
            $table->unique(['id_convenio', 'id_familia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cat_convenios_familias');
    }
};