<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cliente_enfermedad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained()->onDelete('cascade');
            $table->foreignId('enfermedad_id')->constrained()->onDelete('cascade');
            $table->text('notas')->nullable(); // Observaciones adicionales
            $table->string('severidad')->nullable(); // Leve, Moderada, Grave
            $table->timestamps();
            
            // Evitar duplicados
            $table->unique(['cliente_id', 'enfermedad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_enfermedad');
    }
};