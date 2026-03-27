<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogoGeneralSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            // Familia 001 - Pañales
            ['id_sucursal' => 1, 'ean' => '7501234567890', 'descripcion' => 'Pañales etapa 1 (24 pzas)', 'inventario' => 150, 'costo' => 85.00, 'precio' => 110.00, 'num_familia' => '001'],
            ['id_sucursal' => 2, 'ean' => '7501234567895', 'descripcion' => 'Pañales etapa 2 (28 pzas)', 'inventario' => 120, 'costo' => 90.00, 'precio' => 115.00, 'num_familia' => '001'],
            ['id_sucursal' => 3, 'ean' => '7501234567898', 'descripcion' => 'Pañales etapa 3 (32 pzas)', 'inventario' => 100, 'costo' => 95.00, 'precio' => 120.00, 'num_familia' => '001'],
            ['id_sucursal' => 4, 'ean' => '7501234567900', 'descripcion' => 'Pañales etapa 4 (36 pzas)', 'inventario' => 90, 'costo' => 100.00, 'precio' => 130.00, 'num_familia' => '001'],
            ['id_sucursal' => 1, 'ean' => '7501234567903', 'descripcion' => 'Toallitas húmedas (80 pzas)', 'inventario' => 200, 'costo' => 30.00, 'precio' => 40.00, 'num_familia' => '001'],
            
            // Familia 002 - Alimentación Infantil
            ['id_sucursal' => 1, 'ean' => '7501234567892', 'descripcion' => 'Biberón 240 ml', 'inventario' => 80, 'costo' => 60.00, 'precio' => 85.00, 'num_familia' => '002'],
            ['id_sucursal' => 2, 'ean' => '7501234567893', 'descripcion' => 'Leche en polvo etapa 1 (800g)', 'inventario' => 50, 'costo' => 180.00, 'precio' => 250.00, 'num_familia' => '002'],
            ['id_sucursal' => 3, 'ean' => '7501234567904', 'descripcion' => 'Papillas de frutas (4pzas)', 'inventario' => 60, 'costo' => 45.00, 'precio' => 70.00, 'num_familia' => '002'],
            
            // Familia 003 - Vitaminas y Suplementos
            ['id_sucursal' => 1, 'ean' => '7501234567905', 'descripcion' => 'Vitamina C 1000mg (30 tabs)', 'inventario' => 100, 'costo' => 80.00, 'precio' => 120.00, 'num_familia' => '003'],
            ['id_sucursal' => 2, 'ean' => '7501234567906', 'descripcion' => 'Complejo B (30 tabs)', 'inventario' => 85, 'costo' => 65.00, 'precio' => 95.00, 'num_familia' => '003'],
            ['id_sucursal' => 3, 'ean' => '7501234567907', 'descripcion' => 'Calcio + Vitamina D (60 tabs)', 'inventario' => 70, 'costo' => 110.00, 'precio' => 160.00, 'num_familia' => '003'],
            
            // Familia 004 - Higiene Personal
            ['id_sucursal' => 1, 'ean' => '7501234567908', 'descripcion' => 'Jabón antibacterial (3pzas)', 'inventario' => 150, 'costo' => 25.00, 'precio' => 45.00, 'num_familia' => '004'],
            ['id_sucursal' => 2, 'ean' => '7501234567909', 'descripcion' => 'Shampoo anticaspa (400ml)', 'inventario' => 80, 'costo' => 55.00, 'precio' => 85.00, 'num_familia' => '004'],
            ['id_sucursal' => 4, 'ean' => '7501234567910', 'descripcion' => 'Cepillo dental suave', 'inventario' => 120, 'costo' => 12.00, 'precio' => 25.00, 'num_familia' => '004'],
            
            // Familia 005 - Equipos Médicos
            ['id_sucursal' => 1, 'ean' => '7501234567901', 'descripcion' => 'Termómetro digital', 'inventario' => 25, 'costo' => 80.00, 'precio' => 120.00, 'num_familia' => '005'],
            ['id_sucursal' => 2, 'ean' => '7501234567902', 'descripcion' => 'Aspirador nasal', 'inventario' => 40, 'costo' => 45.00, 'precio' => 70.00, 'num_familia' => '005'],
            ['id_sucursal' => 3, 'ean' => '7501234567911', 'descripcion' => 'Tensiómetro digital', 'inventario' => 15, 'costo' => 250.00, 'precio' => 380.00, 'num_familia' => '005'],
            
            // Medicamentos Genéricos (Familia 007)
            ['id_sucursal' => 1, 'ean' => '7501234567912', 'descripcion' => 'Paracetamol 500mg (10 tabs)', 'inventario' => 200, 'costo' => 8.00, 'precio' => 15.00, 'num_familia' => '007'],
            ['id_sucursal' => 2, 'ean' => '7501234567913', 'descripcion' => 'Ibuprofeno 400mg (10 tabs)', 'inventario' => 150, 'costo' => 12.00, 'precio' => 22.00, 'num_familia' => '007'],
            ['id_sucursal' => 3, 'ean' => '7501234567914', 'descripcion' => 'Omeprazol 20mg (14 caps)', 'inventario' => 100, 'costo' => 25.00, 'precio' => 45.00, 'num_familia' => '007'],
            
            // Medicamentos Patente (Familia 041)
            ['id_sucursal' => 1, 'ean' => '7501234567915', 'descripcion' => 'Tempra 500mg (10 tabs)', 'inventario' => 120, 'costo' => 15.00, 'precio' => 28.00, 'num_familia' => '041'],
            ['id_sucursal' => 2, 'ean' => '7501234567916', 'descripcion' => 'Advil 400mg (10 tabs)', 'inventario' => 90, 'costo' => 20.00, 'precio' => 35.00, 'num_familia' => '041'],
            ['id_sucursal' => 4, 'ean' => '7501234567917', 'descripcion' => 'Losec 20mg (14 caps)', 'inventario' => 60, 'costo' => 45.00, 'precio' => 75.00, 'num_familia' => '041'],
        ];

        foreach ($productos as $producto) {
            DB::table('catalogo_general')->insert($producto);
        }
    }
}