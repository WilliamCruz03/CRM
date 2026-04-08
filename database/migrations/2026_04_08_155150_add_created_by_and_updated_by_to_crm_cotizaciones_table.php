<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_cotizaciones', function (Blueprint $table) {
            // Agregar columna para el usuario que creó la cotización
            $table->unsignedBigInteger('creado_por')->nullable()->after('comentarios');
            $table->foreign('creado_por')->references('id_personal_empresa')->on('personal_empresa')->onDelete('set null');
            
            // Agregar columna para el usuario que modificó por última vez la cotización
            $table->unsignedBigInteger('modificado_por')->nullable()->after('creado_por');
            $table->foreign('modificado_por')->references('id_personal_empresa')->on('personal_empresa')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('crm_cotizaciones', function (Blueprint $table) {
            $table->dropForeign(['creado_por']);
            $table->dropForeign(['modificado_por']);
            $table->dropColumn(['creado_por', 'modificado_por']);
        });
    }
};