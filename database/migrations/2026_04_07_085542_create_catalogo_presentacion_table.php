<?php
// database/migrations/2026_04_07_085542_create_catalogo_presentacion_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogo_presentacion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_catalogo_general');
            $table->unsignedInteger('id_presentacion'); // id de cat_sales_presentacion
            $table->timestamps();
            
            $table->foreign('id_catalogo_general')
                  ->references('id_catalogo_general')
                  ->on('catalogo_general')
                  ->onDelete('cascade');
            
            // Nota: cat_sales_presentacion está en otra BD, no se puede crear FK directa
            $table->index('id_presentacion');
            $table->unique(['id_catalogo_general', 'id_presentacion'], 'unique_catalogo_presentacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogo_presentacion');
    }
};