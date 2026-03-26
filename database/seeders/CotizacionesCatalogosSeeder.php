<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CotizacionesCatalogosSeeder extends Seeder
{
    public function run(): void
    {
        // Fases
        $fases = [
            ['id_fase' => 1, 'fase' => 'En proceso', 'descripcion' => 'Cotización en proceso de elaboración', 'orden' => 1, 'activo' => 1],
            ['id_fase' => 2, 'fase' => 'Completada', 'descripcion' => 'Cotización completada exitosamente', 'orden' => 2, 'activo' => 1],
            ['id_fase' => 3, 'fase' => 'Cancelada', 'descripcion' => 'Cotización cancelada', 'orden' => 3, 'activo' => 1],
        ];

        foreach ($fases as $fase) {
            DB::table('cat_fases')->updateOrInsert(
                ['id_fase' => $fase['id_fase']],
                $fase
            );
        }
        
        // Clasificaciones
        $clasificaciones = [
            ['id_clasificacion' => 1, 'clasificacion' => 'Tienda', 'descripcion' => 'Venta a tienda física', 'activo' => 1],
            ['id_clasificacion' => 2, 'clasificacion' => 'Programa de gobierno', 'descripcion' => 'Venta a programas gubernamentales', 'activo' => 1],
            ['id_clasificacion' => 3, 'clasificacion' => 'Empresas', 'descripcion' => 'Venta a empresas', 'activo' => 1],
            ['id_clasificacion' => 4, 'clasificacion' => 'Otro', 'descripcion' => 'Otras clasificaciones', 'activo' => 1],
        ];

        foreach ($clasificaciones as $clasificacion) {
            DB::table('cat_clasificaciones')->updateOrInsert(
                ['id_clasificacion' => $clasificacion['id_clasificacion']],
                $clasificacion
            );
        }
    }
}