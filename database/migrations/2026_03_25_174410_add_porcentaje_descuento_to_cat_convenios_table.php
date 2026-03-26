<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cat_convenios', function (Blueprint $table) {
            $table->decimal('porcentaje_descuento', 5, 2)->nullable()->after('tipo');
        });
    }

    public function down(): void
    {
        Schema::table('cat_convenios', function (Blueprint $table) {
            $table->dropColumn('porcentaje_descuento');
        });
    }
};