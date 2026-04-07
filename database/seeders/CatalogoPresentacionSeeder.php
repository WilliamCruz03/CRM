<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CatalogoGeneral;
use App\Models\CatSalesPresentacion;

class CatalogoPresentacionSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar la tabla antes de insertar (opcional)
        DB::table('catalogo_presentacion')->truncate();
        
        // Asociaciones basadas en descripción de productos
        $asociaciones = [
            // PARACETAMOL - Buscar en descripciones que contengan Paracetamol, Tempra
            ['sustancia' => 'PARACETAMOL', 'palabras' => ['Paracetamol', 'Tempra']],
            
            // IBUPROFENO - Buscar en descripciones que contengan Ibuprofeno, Advil
            ['sustancia' => 'IBUPROFENO', 'palabras' => ['Ibuprofeno', 'Advil']],
            
            // OMEPRAZOL - Buscar en descripciones que contengan Omeprazol, Losec
            ['sustancia' => 'OMEPRAZOL', 'palabras' => ['Omeprazol', 'Losec']],
            
            // VITAMINA C
            ['sustancia' => 'ACIDO ASCORBICO', 'palabras' => ['Vitamina C']],
            
            // COMPLEJO B
            ['sustancia' => 'COMPLEJO B', 'palabras' => ['Complejo B']],
            
            // CALCIO
            ['sustancia' => 'CALCIO', 'palabras' => ['Calcio']],
        ];
        
        $insertados = 0;
        
        foreach ($asociaciones as $asociacion) {
            // Buscar presentaciones por sustancia
            $presentaciones = CatSalesPresentacion::where('sustancia', 'LIKE', "%{$asociacion['sustancia']}%")->get();
            
            if ($presentaciones->isEmpty()) {
                $this->command->warn("No se encontraron presentaciones para: {$asociacion['sustancia']}");
                continue;
            }
            
            // Buscar productos por cada palabra clave
            foreach ($asociacion['palabras'] as $palabra) {
                $productos = CatalogoGeneral::where('descripcion', 'LIKE', "%{$palabra}%")->get();
                
                foreach ($productos as $producto) {
                    foreach ($presentaciones as $presentacion) {
                        // Verificar si ya existe la relación
                        $existe = DB::table('catalogo_presentacion')
                            ->where('id_catalogo_general', $producto->id_catalogo_general)
                            ->where('id_presentacion', $presentacion->id)
                            ->exists();
                        
                        if (!$existe) {
                            DB::table('catalogo_presentacion')->insert([
                                'id_catalogo_general' => $producto->id_catalogo_general,
                                'id_presentacion' => $presentacion->id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            $insertados++;
                            $this->command->info("✓ Asociado: {$producto->descripcion} → {$presentacion->sustancia}");
                        }
                    }
                }
            }
        }
        
        $this->command->info("Se insertaron {$insertados} relaciones en catalogo_presentacion");
    }
}