<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enfermedades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->foreignId('categoria_id')->constrained('categoria_enfermedades');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->unique(['nombre', 'categoria_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enfermedades');
    }
};