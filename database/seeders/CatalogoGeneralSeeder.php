<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogoGeneralSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            // Sucursal Jardin (id_sucursal = 1)
            ['id_catalogo_general' => 1, 'id_sucursal' => 1, 'ean' => '7501234567890', 'descripcion' => 'Pañales bebé etapa 1 (24 pzas)', 'inventario' => 150, 'costo' => 85.00, 'precio' => 110.00, 'activo' => 1],
            ['id_catalogo_general' => 2, 'id_sucursal' => 1, 'ean' => '7501234567891', 'descripcion' => 'Toallitas húmedas (80 pzas)', 'inventario' => 200, 'costo' => 30.00, 'precio' => 40.00, 'activo' => 1],
            ['id_catalogo_general' => 3, 'id_sucursal' => 1, 'ean' => '7501234567892', 'descripcion' => 'Biberón 240 ml anticólicos', 'inventario' => 80, 'costo' => 60.00, 'precio' => 85.00, 'activo' => 1],
            ['id_catalogo_general' => 4, 'id_sucursal' => 1, 'ean' => '7501234567893', 'descripcion' => 'Leche en polvo etapa 1 (800g)', 'inventario' => 50, 'costo' => 180.00, 'precio' => 250.00, 'activo' => 1],
            ['id_catalogo_general' => 5, 'id_sucursal' => 1, 'ean' => '7501234567894', 'descripcion' => 'Cobija para bebé', 'inventario' => 30, 'costo' => 120.00, 'precio' => 180.00, 'activo' => 1],
            
            // Sucursal Mercado (id_sucursal = 2)
            ['id_catalogo_general' => 6, 'id_sucursal' => 2, 'ean' => '7501234567895', 'descripcion' => 'Pañales bebé etapa 2 (28 pzas)', 'inventario' => 120, 'costo' => 90.00, 'precio' => 115.00, 'activo' => 1],
            ['id_catalogo_general' => 7, 'id_sucursal' => 2, 'ean' => '7501234567896', 'descripcion' => 'Crema para pañal (100g)', 'inventario' => 100, 'costo' => 25.00, 'precio' => 45.00, 'activo' => 1],
            ['id_catalogo_general' => 8, 'id_sucursal' => 2, 'ean' => '7501234567897', 'descripcion' => 'Chupón ortopédico', 'inventario' => 60, 'costo' => 20.00, 'precio' => 35.00, 'activo' => 1],
            
            // Sucursal Zatipan (id_sucursal = 3)
            ['id_catalogo_general' => 9, 'id_sucursal' => 3, 'ean' => '7501234567898', 'descripcion' => 'Pañales bebé etapa 3 (32 pzas)', 'inventario' => 100, 'costo' => 95.00, 'precio' => 120.00, 'activo' => 1],
            ['id_catalogo_general' => 10, 'id_sucursal' => 3, 'ean' => '7501234567899', 'descripcion' => 'Juguete mordedor', 'inventario' => 45, 'costo' => 15.00, 'precio' => 30.00, 'activo' => 1],
            
            // Sucursal Boulevard (id_sucursal = 4)
            ['id_catalogo_general' => 11, 'id_sucursal' => 4, 'ean' => '7501234567900', 'descripcion' => 'Pañales bebé etapa 4 (36 pzas)', 'inventario' => 90, 'costo' => 100.00, 'precio' => 130.00, 'activo' => 1],
            ['id_catalogo_general' => 12, 'id_sucursal' => 4, 'ean' => '7501234567901', 'descripcion' => 'Termómetro digital', 'inventario' => 25, 'costo' => 80.00, 'precio' => 120.00, 'activo' => 1],
            ['id_catalogo_general' => 13, 'id_sucursal' => 4, 'ean' => '7501234567902', 'descripcion' => 'Aspirador nasal', 'inventario' => 40, 'costo' => 45.00, 'precio' => 70.00, 'activo' => 1],
        ];

        foreach ($productos as $producto) {
            DB::table('catalogo_general')->updateOrInsert(
                ['id_catalogo_general' => $producto['id_catalogo_general']],
                $producto
            );
        }
    }
}