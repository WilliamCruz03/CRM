<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CotizacionesCatalogosSeeder extends Seeder
{
    public function run(): void
    {
        // Fases - sin incluir id_fase (auto-incremental)
        $fases = [
            ['fase' => 'En proceso', 'descripcion' => 'Cotización en proceso de elaboración', 'orden' => 1, 'activo' => 1],
            ['fase' => 'Completada', 'descripcion' => 'Cotización completada exitosamente', 'orden' => 2, 'activo' => 1],
            ['fase' => 'Cancelada', 'descripcion' => 'Cotización cancelada', 'orden' => 3, 'activo' => 1],
        ];

        foreach ($fases as $fase) {
            DB::table('cat_fases')->insert($fase);
        }
        
        // Clasificaciones - sin incluir id_clasificacion (auto-incremental)
        $clasificaciones = [
            ['clasificacion' => 'Tienda', 'descripcion' => 'Venta a tienda física', 'activo' => 1],
            ['clasificacion' => 'Programa de gobierno', 'descripcion' => 'Venta a programas gubernamentales', 'activo' => 1],
            ['clasificacion' => 'Empresas', 'descripcion' => 'Venta a empresas', 'activo' => 1],
            ['clasificacion' => 'Otro', 'descripcion' => 'Otras clasificaciones', 'activo' => 1],
        ];

        foreach ($clasificaciones as $clasificacion) {
            DB::table('cat_clasificaciones')->insert($clasificacion);
        }
    }
}