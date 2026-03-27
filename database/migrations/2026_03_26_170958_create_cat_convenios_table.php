<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cat_convenios', function (Blueprint $table) {
            $table->id('id');
            $table->string('convenio', 13)->unique(); // 0000000000037
            $table->string('nombre', 100); // INAPAM, CONVENIO TEC, CONVENIO MAESTROS
            $table->string('tipo', 6); // letra C
            $table->string('num_familia', 6); // clave foránea de cat_familias
            $table->timestamps();
            
            $table->foreign('num_familia')->references('num_familia')->on('cat_familias')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cat_convenios');
    }
};