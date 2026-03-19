<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cat_modulo_clientes', function (Blueprint $table) {
            $table->integer('id_cliente_modulo', true);
            $table->boolean('clientes')->default(false);
            $table->boolean('enfermedades')->default(false);
            $table->boolean('intereses')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cat_modulo_clientes');
    }
};
