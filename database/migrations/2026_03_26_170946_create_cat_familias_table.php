<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cat_familias', function (Blueprint $table) {
            $table->id('id_familia');
            $table->string('num_familia', 6)->unique(); // 001, 037, 041, 007
            $table->string('nombre', 100);
            $table->string('descripcion', 200)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cat_familias');
    }
};