<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cat_convenios', function (Blueprint $table) {
            $table->id('id_convenio');
            $table->string('convenio', 13)->unique();
            $table->string('nombre', 100);
            $table->string('tipo', 5)->default('C');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cat_convenios');
    }
};