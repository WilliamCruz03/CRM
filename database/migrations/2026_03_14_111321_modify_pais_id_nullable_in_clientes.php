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
         DB::statement('ALTER TABLE catalogo_cliente_maestro ALTER COLUMN pais_id INT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE catalogo_cliente_maestro ALTER COLUMN pais_id INT NOT NULL');
    }
};
