// database/migrations/2025_03_21_000000_restructure_permisos_tables.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Crear tabla para submódulos de clientes
        Schema::create('cat_submodulos_clientes', function (Blueprint $table) {
            $table->id('id_submodulo_cliente');
            $table->string('nombre', 50); // 'directorio', 'enfermedades', 'intereses'
            $table->timestamps();
        });
        
        // Insertar submódulos de clientes
        DB::table('cat_submodulos_clientes')->insert([
            ['nombre' => 'directorio', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'enfermedades', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'intereses', 'created_at' => now(), 'updated_at' => now()],
        ]);
        
        // Crear tabla para submódulos de ventas
        Schema::create('cat_submodulos_ventas', function (Blueprint $table) {
            $table->id('id_submodulo_venta');
            $table->string('nombre', 50); // 'cotizaciones', 'pedidos_anticipo', 'seguimiento_ventas', 'seguimiento_cotizaciones', 'agenda_contactos'
            $table->timestamps();
        });
        
        // Insertar submódulos de ventas
        DB::table('cat_submodulos_ventas')->insert([
            ['nombre' => 'cotizaciones', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'pedidos_anticipo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'seguimiento_ventas', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'seguimiento_cotizaciones', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'agenda_contactos', 'created_at' => now(), 'updated_at' => now()],
        ]);
        
        // Crear tabla para submódulos de seguridad
        Schema::create('cat_submodulos_seguridad', function (Blueprint $table) {
            $table->id('id_submodulo_seguridad');
            $table->string('nombre', 50); // 'usuarios', 'permisos', 'respaldos'
            $table->timestamps();
        });
        
        // Insertar submódulos de seguridad
        DB::table('cat_submodulos_seguridad')->insert([
            ['nombre' => 'usuarios', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'permisos', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'respaldos', 'created_at' => now(), 'updated_at' => now()],
        ]);
        
        // Crear tabla para submódulos de reportes
        Schema::create('cat_submodulos_reportes', function (Blueprint $table) {
            $table->id('id_submodulo_reporte');
            $table->string('nombre', 50);
            $table->timestamps();
        });
        
        // Insertar submódulos de reportes
        DB::table('cat_submodulos_reportes')->insert([
            ['nombre' => 'compras_cliente', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'frecuencia_compra', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'montos_promedio', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'sucursales_preferidas', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'cotizaciones_cliente', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'cotizaciones_concretadas', 'created_at' => now(), 'updated_at' => now()],
        ]);
        
        // Nueva tabla de permisos granulares
        Schema::create('permisos_granulares', function (Blueprint $table) {
            $table->id('id_permiso_granular');
            $table->unsignedInteger('id_personal_empresa');
            $table->string('modulo', 30); // 'clientes', 'ventas', 'seguridad', 'reportes'
            $table->string('submodulo', 50); // nombre del submódulo
            $table->boolean('mostrar')->default(false); // mostrar/ocultar submódulo
            $table->boolean('ver')->default(false);
            $table->boolean('crear')->default(false);
            $table->boolean('editar')->default(false);
            $table->boolean('eliminar')->default(false);
            $table->timestamps();
            
            $table->foreign('id_personal_empresa')
                  ->references('id_personal_empresa')
                  ->on('personal_empresa')
                  ->onDelete('cascade');
            
            $table->unique(['id_personal_empresa', 'modulo', 'submodulo'], 'unique_permiso');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permisos_granulares');
        Schema::dropIfExists('cat_submodulos_reportes');
        Schema::dropIfExists('cat_submodulos_seguridad');
        Schema::dropIfExists('cat_submodulos_ventas');
        Schema::dropIfExists('cat_submodulos_clientes');
    }
};