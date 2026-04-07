<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CatalogoGeneral;
use App\Models\CatSalesPresentacion;

class AsociarProductosMedicamentosSeeder extends Seeder
{
    public function run(): void
    {
        // Ejemplo: Asociar productos por nombre o EAN
        
        // Asociaciones sugeridas basadas en descripción de productos
        $asociaciones = [
            // Paracetamol / Tempra
            ['descripcion_contains' => 'Paracetamol', 'sustancia' => 'PARACETAMOL'],
            ['descripcion_contains' => 'Tempra', 'sustancia' => 'PARACETAMOL'],
            
            // Ibuprofeno / Advil
            ['descripcion_contains' => 'Ibuprofeno', 'sustancia' => 'IBUPROFENO'],
            ['descripcion_contains' => 'Advil', 'sustancia' => 'IBUPROFENO'],
            
            // Omeprazol / Losec
            ['descripcion_contains' => 'Omeprazol', 'sustancia' => 'OMEPRAZOL'],
            ['descripcion_contains' => 'Losec', 'sustancia' => 'OMEPRAZOL'],
            
            // Vitaminas
            ['descripcion_contains' => 'Vitamina C', 'sustancia' => 'ACIDO ASCORBICO'],
            ['descripcion_contains' => 'Complejo B', 'sustancia' => 'COMPLEJO B'],
            ['descripcion_contains' => 'Calcio', 'sustancia' => 'CALCIO'],
        ];
        
        foreach ($asociaciones as $asociacion) {
            $productos = CatalogoGeneral::where('descripcion', 'LIKE', "%{$asociacion['descripcion_contains']}%")->get();
            $presentaciones = CatSalesPresentacion::where('sustancia', 'LIKE', "%{$asociacion['sustancia']}%")->get();
            
            foreach ($productos as $producto) {
                foreach ($presentaciones as $presentacion) {
                    // Evitar duplicados
                    $exists = DB::table('catalogo_presentacion')
                        ->where('id_catalogo_general', $producto->id_catalogo_general)
                        ->where('id_presentacion', $presentacion->id)
                        ->exists();
                    
                    if (!$exists) {
                        DB::table('catalogo_presentacion')->insert([
                            'id_catalogo_general' => $producto->id_catalogo_general,
                            'id_presentacion' => $presentacion->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $this->command->info("Asociado: {$producto->descripcion} -> {$presentacion->sustancia}");
                    }
                }
            }
        }
    }
}