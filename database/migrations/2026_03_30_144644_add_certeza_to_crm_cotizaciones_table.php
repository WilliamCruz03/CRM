<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_cotizaciones', function (Blueprint $table) {
            $table->integer('certeza')->nullable()->default(0)->after('id_sucursal_asignada');
        });
        
        Schema::table('crm_cotizaciones_detalle', function (Blueprint $table) {
            $table->boolean('apartado')->default(false)->after('activo');
            $table->index('apartado');
        });
    }

    public function down(): void
    {
        Schema::table('crm_cotizaciones', function (Blueprint $table) {
            $table->dropColumn('certeza');
        });
        
        Schema::table('crm_cotizaciones_detalle', function (Blueprint $table) {
            $table->dropColumn('apartado');
        });
    }
};