<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogosBasicosSeeder extends Seeder
{
    public function run(): void
    {
        // Fases - NO incluir id_fase
        DB::table('cat_fases')->insert([
            ['fase' => 'En proceso', 'descripcion' => 'Cotización en proceso de elaboración', 'orden' => 1, 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['fase' => 'Completada', 'descripcion' => 'Cotización completada exitosamente', 'orden' => 2, 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['fase' => 'Cancelada', 'descripcion' => 'Cotización cancelada', 'orden' => 3, 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
        
        // Clasificaciones - NO incluir id_clasificacion
        DB::table('cat_clasificaciones')->insert([
            ['clasificacion' => 'Tienda', 'descripcion' => 'Venta a tienda física', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['clasificacion' => 'Programa de gobierno', 'descripcion' => 'Venta a programas gubernamentales', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['clasificacion' => 'Empresas', 'descripcion' => 'Venta a empresas', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['clasificacion' => 'Otro', 'descripcion' => 'Otras clasificaciones', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}