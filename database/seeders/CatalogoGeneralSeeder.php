<?php
// database/seeders/CatalogoGeneralSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogoGeneralSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('catalogo_general')->insert([
            // ========== PAÑALES Y CUIDADO INFANTIL (001) ==========
            // Pañales etapa 1
            ['id_sucursal' => 1, 'ean' => '7501234567890', 'descripcion' => 'Pañales etapa 1 (24 pzas)', 'inventario' => 150, 'costo' => 85.00, 'precio' => 110.00, 'num_familia' => '001', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567890', 'descripcion' => 'Pañales etapa 1 (24 pzas)', 'inventario' => 80, 'costo' => 85.00, 'precio' => 110.00, 'num_familia' => '001', 'activo' => 1],
            ['id_sucursal' => 3, 'ean' => '7501234567890', 'descripcion' => 'Pañales etapa 1 (24 pzas)', 'inventario' => 60, 'costo' => 85.00, 'precio' => 110.00, 'num_familia' => '001', 'activo' => 1],
            ['id_sucursal' => 4, 'ean' => '7501234567890', 'descripcion' => 'Pañales etapa 1 (24 pzas)', 'inventario' => 40, 'costo' => 85.00, 'precio' => 110.00, 'num_familia' => '001', 'activo' => 1],
            
            // Pañales etapa 2
            ['id_sucursal' => 1, 'ean' => '7501234567895', 'descripcion' => 'Pañales etapa 2 (28 pzas)', 'inventario' => 120, 'costo' => 90.00, 'precio' => 115.00, 'num_familia' => '001', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567895', 'descripcion' => 'Pañales etapa 2 (28 pzas)', 'inventario' => 60, 'costo' => 90.00, 'precio' => 115.00, 'num_familia' => '001', 'activo' => 1],
            ['id_sucursal' => 3, 'ean' => '7501234567895', 'descripcion' => 'Pañales etapa 2 (28 pzas)', 'inventario' => 45, 'costo' => 90.00, 'precio' => 115.00, 'num_familia' => '001', 'activo' => 1],
            
            // Toallitas húmedas
            ['id_sucursal' => 1, 'ean' => '7501234567891', 'descripcion' => 'Toallitas húmedas (80 pzas)', 'inventario' => 200, 'costo' => 30.00, 'precio' => 40.00, 'num_familia' => '001', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567891', 'descripcion' => 'Toallitas húmedas (80 pzas)', 'inventario' => 120, 'costo' => 30.00, 'precio' => 40.00, 'num_familia' => '001', 'activo' => 1],
            ['id_sucursal' => 3, 'ean' => '7501234567891', 'descripcion' => 'Toallitas húmedas (80 pzas)', 'inventario' => 90, 'costo' => 30.00, 'precio' => 40.00, 'num_familia' => '001', 'activo' => 1],
            ['id_sucursal' => 4, 'ean' => '7501234567891', 'descripcion' => 'Toallitas húmedas (80 pzas)', 'inventario' => 60, 'costo' => 30.00, 'precio' => 40.00, 'num_familia' => '001', 'activo' => 1],
            
            // Crema para pañal
            ['id_sucursal' => 1, 'ean' => '7501234567896', 'descripcion' => 'Crema para pañal (100g)', 'inventario' => 100, 'costo' => 25.00, 'precio' => 45.00, 'num_familia' => '001', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567896', 'descripcion' => 'Crema para pañal (100g)', 'inventario' => 70, 'costo' => 25.00, 'precio' => 45.00, 'num_familia' => '001', 'activo' => 1],
            ['id_sucursal' => 3, 'ean' => '7501234567896', 'descripcion' => 'Crema para pañal (100g)', 'inventario' => 50, 'costo' => 25.00, 'precio' => 45.00, 'num_familia' => '001', 'activo' => 1],
            
            // ========== ALIMENTACIÓN INFANTIL (002) ==========
            ['id_sucursal' => 1, 'ean' => '7501234567892', 'descripcion' => 'Biberón 240 ml', 'inventario' => 80, 'costo' => 60.00, 'precio' => 85.00, 'num_familia' => '002', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567892', 'descripcion' => 'Biberón 240 ml', 'inventario' => 45, 'costo' => 60.00, 'precio' => 85.00, 'num_familia' => '002', 'activo' => 1],
            ['id_sucursal' => 3, 'ean' => '7501234567892', 'descripcion' => 'Biberón 240 ml', 'inventario' => 30, 'costo' => 60.00, 'precio' => 85.00, 'num_familia' => '002', 'activo' => 1],
            
            ['id_sucursal' => 1, 'ean' => '7501234567893', 'descripcion' => 'Leche en polvo etapa 1 (800g)', 'inventario' => 50, 'costo' => 180.00, 'precio' => 250.00, 'num_familia' => '002', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567893', 'descripcion' => 'Leche en polvo etapa 1 (800g)', 'inventario' => 35, 'costo' => 180.00, 'precio' => 250.00, 'num_familia' => '002', 'activo' => 1],
            ['id_sucursal' => 4, 'ean' => '7501234567893', 'descripcion' => 'Leche en polvo etapa 1 (800g)', 'inventario' => 25, 'costo' => 180.00, 'precio' => 250.00, 'num_familia' => '002', 'activo' => 1],
            
            // Papillas
            ['id_sucursal' => 1, 'ean' => '7501234567904', 'descripcion' => 'Papillas de frutas (4pzas)', 'inventario' => 60, 'costo' => 45.00, 'precio' => 70.00, 'num_familia' => '002', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567904', 'descripcion' => 'Papillas de frutas (4pzas)', 'inventario' => 40, 'costo' => 45.00, 'precio' => 70.00, 'num_familia' => '002', 'activo' => 1],
            ['id_sucursal' => 3, 'ean' => '7501234567904', 'descripcion' => 'Papillas de frutas (4pzas)', 'inventario' => 25, 'costo' => 45.00, 'precio' => 70.00, 'num_familia' => '002', 'activo' => 1],
            
            // ========== VITAMINAS Y SUPLEMENTOS (003) ==========
            ['id_sucursal' => 1, 'ean' => '7501234567905', 'descripcion' => 'Vitamina C 1000mg (30 tabs)', 'inventario' => 100, 'costo' => 80.00, 'precio' => 120.00, 'num_familia' => '003', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567905', 'descripcion' => 'Vitamina C 1000mg (30 tabs)', 'inventario' => 60, 'costo' => 80.00, 'precio' => 120.00, 'num_familia' => '003', 'activo' => 1],
            ['id_sucursal' => 3, 'ean' => '7501234567905', 'descripcion' => 'Vitamina C 1000mg (30 tabs)', 'inventario' => 40, 'costo' => 80.00, 'precio' => 120.00, 'num_familia' => '003', 'activo' => 1],
            ['id_sucursal' => 4, 'ean' => '7501234567905', 'descripcion' => 'Vitamina C 1000mg (30 tabs)', 'inventario' => 25, 'costo' => 80.00, 'precio' => 120.00, 'num_familia' => '003', 'activo' => 1],
            
            ['id_sucursal' => 1, 'ean' => '7501234567906', 'descripcion' => 'Complejo B (30 tabs)', 'inventario' => 85, 'costo' => 65.00, 'precio' => 95.00, 'num_familia' => '003', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567906', 'descripcion' => 'Complejo B (30 tabs)', 'inventario' => 55, 'costo' => 65.00, 'precio' => 95.00, 'num_familia' => '003', 'activo' => 1],
            ['id_sucursal' => 4, 'ean' => '7501234567906', 'descripcion' => 'Complejo B (30 tabs)', 'inventario' => 35, 'costo' => 65.00, 'precio' => 95.00, 'num_familia' => '003', 'activo' => 1],
            
            ['id_sucursal' => 1, 'ean' => '7501234567907', 'descripcion' => 'Calcio + Vitamina D (60 tabs)', 'inventario' => 70, 'costo' => 110.00, 'precio' => 160.00, 'num_familia' => '003', 'activo' => 1],
            ['id_sucursal' => 3, 'ean' => '7501234567907', 'descripcion' => 'Calcio + Vitamina D (60 tabs)', 'inventario' => 45, 'costo' => 110.00, 'precio' => 160.00, 'num_familia' => '003', 'activo' => 1],
            
            // ========== HIGIENE PERSONAL (004) ==========
            ['id_sucursal' => 1, 'ean' => '7501234567908', 'descripcion' => 'Jabón antibacterial (3pzas)', 'inventario' => 150, 'costo' => 25.00, 'precio' => 45.00, 'num_familia' => '004', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567908', 'descripcion' => 'Jabón antibacterial (3pzas)', 'inventario' => 90, 'costo' => 25.00, 'precio' => 45.00, 'num_familia' => '004', 'activo' => 1],
            ['id_sucursal' => 3, 'ean' => '7501234567908', 'descripcion' => 'Jabón antibacterial (3pzas)', 'inventario' => 60, 'costo' => 25.00, 'precio' => 45.00, 'num_familia' => '004', 'activo' => 1],
            
            ['id_sucursal' => 1, 'ean' => '7501234567909', 'descripcion' => 'Shampoo anticaspa (400ml)', 'inventario' => 80, 'costo' => 55.00, 'precio' => 85.00, 'num_familia' => '004', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567909', 'descripcion' => 'Shampoo anticaspa (400ml)', 'inventario' => 50, 'costo' => 55.00, 'precio' => 85.00, 'num_familia' => '004', 'activo' => 1],
            ['id_sucursal' => 4, 'ean' => '7501234567909', 'descripcion' => 'Shampoo anticaspa (400ml)', 'inventario' => 35, 'costo' => 55.00, 'precio' => 85.00, 'num_familia' => '004', 'activo' => 1],
            
            // ========== EQUIPOS MÉDICOS (005) ==========
            ['id_sucursal' => 1, 'ean' => '7501234567901', 'descripcion' => 'Termómetro digital', 'inventario' => 25, 'costo' => 80.00, 'precio' => 120.00, 'num_familia' => '005', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567901', 'descripcion' => 'Termómetro digital', 'inventario' => 15, 'costo' => 80.00, 'precio' => 120.00, 'num_familia' => '005', 'activo' => 1],
            ['id_sucursal' => 3, 'ean' => '7501234567901', 'descripcion' => 'Termómetro digital', 'inventario' => 10, 'costo' => 80.00, 'precio' => 120.00, 'num_familia' => '005', 'activo' => 1],
            
            ['id_sucursal' => 1, 'ean' => '7501234567911', 'descripcion' => 'Tensiómetro digital', 'inventario' => 15, 'costo' => 250.00, 'precio' => 380.00, 'num_familia' => '005', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567911', 'descripcion' => 'Tensiómetro digital', 'inventario' => 8, 'costo' => 250.00, 'precio' => 380.00, 'num_familia' => '005', 'activo' => 1],
            ['id_sucursal' => 4, 'ean' => '7501234567911', 'descripcion' => 'Tensiómetro digital', 'inventario' => 5, 'costo' => 250.00, 'precio' => 380.00, 'num_familia' => '005', 'activo' => 1],
            
            // ========== MATERIAL DE CURACIÓN (037) ==========
            ['id_sucursal' => 1, 'ean' => '7501234567920', 'descripcion' => 'Gasas estériles (10pzas)', 'inventario' => 300, 'costo' => 15.00, 'precio' => 25.00, 'num_familia' => '037', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567920', 'descripcion' => 'Gasas estériles (10pzas)', 'inventario' => 200, 'costo' => 15.00, 'precio' => 25.00, 'num_familia' => '037', 'activo' => 1],
            ['id_sucursal' => 3, 'ean' => '7501234567920', 'descripcion' => 'Gasas estériles (10pzas)', 'inventario' => 150, 'costo' => 15.00, 'precio' => 25.00, 'num_familia' => '037', 'activo' => 1],
            ['id_sucursal' => 4, 'ean' => '7501234567920', 'descripcion' => 'Gasas estériles (10pzas)', 'inventario' => 100, 'costo' => 15.00, 'precio' => 25.00, 'num_familia' => '037', 'activo' => 1],
            
            ['id_sucursal' => 1, 'ean' => '7501234567921', 'descripcion' => 'Venda elástica (5cm)', 'inventario' => 120, 'costo' => 20.00, 'precio' => 35.00, 'num_familia' => '037', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567921', 'descripcion' => 'Venda elástica (5cm)', 'inventario' => 80, 'costo' => 20.00, 'precio' => 35.00, 'num_familia' => '037', 'activo' => 1],
            ['id_sucursal' => 3, 'ean' => '7501234567921', 'descripcion' => 'Venda elástica (5cm)', 'inventario' => 60, 'costo' => 20.00, 'precio' => 35.00, 'num_familia' => '037', 'activo' => 1],
            
            ['id_sucursal' => 1, 'ean' => '7501234567922', 'descripcion' => 'Apósito adhesivo (20pzas)', 'inventario' => 250, 'costo' => 8.00, 'precio' => 15.00, 'num_familia' => '037', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567922', 'descripcion' => 'Apósito adhesivo (20pzas)', 'inventario' => 180, 'costo' => 8.00, 'precio' => 15.00, 'num_familia' => '037', 'activo' => 1],
            
            // ========== MEDICAMENTOS GENÉRICOS (007) ==========
            ['id_sucursal' => 1, 'ean' => '7501234567912', 'descripcion' => 'Paracetamol 500mg (10 tabs)', 'inventario' => 200, 'costo' => 8.00, 'precio' => 15.00, 'num_familia' => '007', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567912', 'descripcion' => 'Paracetamol 500mg (10 tabs)', 'inventario' => 120, 'costo' => 8.00, 'precio' => 15.00, 'num_familia' => '007', 'activo' => 1],
            ['id_sucursal' => 3, 'ean' => '7501234567912', 'descripcion' => 'Paracetamol 500mg (10 tabs)', 'inventario' => 90, 'costo' => 8.00, 'precio' => 15.00, 'num_familia' => '007', 'activo' => 1],
            ['id_sucursal' => 4, 'ean' => '7501234567912', 'descripcion' => 'Paracetamol 500mg (10 tabs)', 'inventario' => 60, 'costo' => 8.00, 'precio' => 15.00, 'num_familia' => '007', 'activo' => 1],
            
            ['id_sucursal' => 1, 'ean' => '7501234567913', 'descripcion' => 'Ibuprofeno 400mg (10 tabs)', 'inventario' => 150, 'costo' => 12.00, 'precio' => 22.00, 'num_familia' => '007', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567913', 'descripcion' => 'Ibuprofeno 400mg (10 tabs)', 'inventario' => 100, 'costo' => 12.00, 'precio' => 22.00, 'num_familia' => '007', 'activo' => 1],
            ['id_sucursal' => 3, 'ean' => '7501234567913', 'descripcion' => 'Ibuprofeno 400mg (10 tabs)', 'inventario' => 80, 'costo' => 12.00, 'precio' => 22.00, 'num_familia' => '007', 'activo' => 1],
            
            ['id_sucursal' => 1, 'ean' => '7501234567914', 'descripcion' => 'Omeprazol 20mg (14 caps)', 'inventario' => 100, 'costo' => 25.00, 'precio' => 45.00, 'num_familia' => '007', 'activo' => 1],
            ['id_sucursal' => 3, 'ean' => '7501234567914', 'descripcion' => 'Omeprazol 20mg (14 caps)', 'inventario' => 70, 'costo' => 25.00, 'precio' => 45.00, 'num_familia' => '007', 'activo' => 1],
            
            // ========== MEDICAMENTOS DE PATENTE (041) ==========
            ['id_sucursal' => 1, 'ean' => '7501234567915', 'descripcion' => 'Tempra 500mg (10 tabs)', 'inventario' => 120, 'costo' => 15.00, 'precio' => 28.00, 'num_familia' => '041', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567915', 'descripcion' => 'Tempra 500mg (10 tabs)', 'inventario' => 70, 'costo' => 15.00, 'precio' => 28.00, 'num_familia' => '041', 'activo' => 1],
            ['id_sucursal' => 3, 'ean' => '7501234567915', 'descripcion' => 'Tempra 500mg (10 tabs)', 'inventario' => 45, 'costo' => 15.00, 'precio' => 28.00, 'num_familia' => '041', 'activo' => 1],
            
            ['id_sucursal' => 1, 'ean' => '7501234567916', 'descripcion' => 'Advil 400mg (10 tabs)', 'inventario' => 90, 'costo' => 20.00, 'precio' => 35.00, 'num_familia' => '041', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567916', 'descripcion' => 'Advil 400mg (10 tabs)', 'inventario' => 55, 'costo' => 20.00, 'precio' => 35.00, 'num_familia' => '041', 'activo' => 1],
            ['id_sucursal' => 4, 'ean' => '7501234567916', 'descripcion' => 'Advil 400mg (10 tabs)', 'inventario' => 30, 'costo' => 20.00, 'precio' => 35.00, 'num_familia' => '041', 'activo' => 1],
            
            ['id_sucursal' => 1, 'ean' => '7501234567917', 'descripcion' => 'Losec 20mg (14 caps)', 'inventario' => 60, 'costo' => 45.00, 'precio' => 75.00, 'num_familia' => '041', 'activo' => 1],
            ['id_sucursal' => 4, 'ean' => '7501234567917', 'descripcion' => 'Losec 20mg (14 caps)', 'inventario' => 35, 'costo' => 45.00, 'precio' => 75.00, 'num_familia' => '041', 'activo' => 1],
            
            // ========== BEBIDAS Y ELECTROLITOS (008) ==========
            ['id_sucursal' => 1, 'ean' => '7501234567930', 'descripcion' => 'Electrolit 600ml sabor naranja', 'inventario' => 200, 'costo' => 25.00, 'precio' => 45.00, 'num_familia' => '008', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567930', 'descripcion' => 'Electrolit 600ml sabor naranja', 'inventario' => 150, 'costo' => 25.00, 'precio' => 45.00, 'num_familia' => '008', 'activo' => 1],
            ['id_sucursal' => 3, 'ean' => '7501234567930', 'descripcion' => 'Electrolit 600ml sabor naranja', 'inventario' => 100, 'costo' => 25.00, 'precio' => 45.00, 'num_familia' => '008', 'activo' => 1],
            ['id_sucursal' => 4, 'ean' => '7501234567930', 'descripcion' => 'Electrolit 600ml sabor naranja', 'inventario' => 80, 'costo' => 25.00, 'precio' => 45.00, 'num_familia' => '008', 'activo' => 1],
            
            ['id_sucursal' => 1, 'ean' => '7501234567931', 'descripcion' => 'Electrolit 600ml sabor limón', 'inventario' => 180, 'costo' => 25.00, 'precio' => 45.00, 'num_familia' => '008', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567931', 'descripcion' => 'Electrolit 600ml sabor limón', 'inventario' => 120, 'costo' => 25.00, 'precio' => 45.00, 'num_familia' => '008', 'activo' => 1],
            ['id_sucursal' => 3, 'ean' => '7501234567931', 'descripcion' => 'Electrolit 600ml sabor limón', 'inventario' => 90, 'costo' => 25.00, 'precio' => 45.00, 'num_familia' => '008', 'activo' => 1],
            
            ['id_sucursal' => 1, 'ean' => '7501234567932', 'descripcion' => 'Refresco Coca Cola 600ml', 'inventario' => 300, 'costo' => 12.00, 'precio' => 22.00, 'num_familia' => '008', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567932', 'descripcion' => 'Refresco Coca Cola 600ml', 'inventario' => 250, 'costo' => 12.00, 'precio' => 22.00, 'num_familia' => '008', 'activo' => 1],
            ['id_sucursal' => 3, 'ean' => '7501234567932', 'descripcion' => 'Refresco Coca Cola 600ml', 'inventario' => 200, 'costo' => 12.00, 'precio' => 22.00, 'num_familia' => '008', 'activo' => 1],
            ['id_sucursal' => 4, 'ean' => '7501234567932', 'descripcion' => 'Refresco Coca Cola 600ml', 'inventario' => 180, 'costo' => 12.00, 'precio' => 22.00, 'num_familia' => '008', 'activo' => 1],
            
            ['id_sucursal' => 1, 'ean' => '7501234567933', 'descripcion' => 'Refresco Sprite 600ml', 'inventario' => 280, 'costo' => 12.00, 'precio' => 22.00, 'num_familia' => '008', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567933', 'descripcion' => 'Refresco Sprite 600ml', 'inventario' => 220, 'costo' => 12.00, 'precio' => 22.00, 'num_familia' => '008', 'activo' => 1],
            ['id_sucursal' => 3, 'ean' => '7501234567933', 'descripcion' => 'Refresco Sprite 600ml', 'inventario' => 180, 'costo' => 12.00, 'precio' => 22.00, 'num_familia' => '008', 'activo' => 1],
            
            // ========== SNACKS Y BOTANAS (009) ==========
            ['id_sucursal' => 1, 'ean' => '7501234567940', 'descripcion' => 'Papas Sabritas 70g', 'inventario' => 400, 'costo' => 15.00, 'precio' => 28.00, 'num_familia' => '009', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567940', 'descripcion' => 'Papas Sabritas 70g', 'inventario' => 350, 'costo' => 15.00, 'precio' => 28.00, 'num_familia' => '009', 'activo' => 1],
            ['id_sucursal' => 3, 'ean' => '7501234567940', 'descripcion' => 'Papas Sabritas 70g', 'inventario' => 300, 'costo' => 15.00, 'precio' => 28.00, 'num_familia' => '009', 'activo' => 1],
            ['id_sucursal' => 4, 'ean' => '7501234567940', 'descripcion' => 'Papas Sabritas 70g', 'inventario' => 250, 'costo' => 15.00, 'precio' => 28.00, 'num_familia' => '009', 'activo' => 1],
            
            ['id_sucursal' => 1, 'ean' => '7501234567941', 'descripcion' => 'Galletas Marías 150g', 'inventario' => 500, 'costo' => 10.00, 'precio' => 18.00, 'num_familia' => '009', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567941', 'descripcion' => 'Galletas Marías 150g', 'inventario' => 450, 'costo' => 10.00, 'precio' => 18.00, 'num_familia' => '009', 'activo' => 1],
            
            // ========== DULCES Y CHOCOLATES (010) ==========
            ['id_sucursal' => 1, 'ean' => '7501234567950', 'descripcion' => 'Chocolate Hershey\'s 40g', 'inventario' => 300, 'costo' => 18.00, 'precio' => 32.00, 'num_familia' => '010', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567950', 'descripcion' => 'Chocolate Hershey\'s 40g', 'inventario' => 250, 'costo' => 18.00, 'precio' => 32.00, 'num_familia' => '010', 'activo' => 1],
            ['id_sucursal' => 3, 'ean' => '7501234567950', 'descripcion' => 'Chocolate Hershey\'s 40g', 'inventario' => 200, 'costo' => 18.00, 'precio' => 32.00, 'num_familia' => '010', 'activo' => 1],
            
            // ========== CUIDADO BUCAL (011) ==========
            ['id_sucursal' => 1, 'ean' => '7501234567960', 'descripcion' => 'Cepillo dental suave', 'inventario' => 120, 'costo' => 12.00, 'precio' => 25.00, 'num_familia' => '011', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567960', 'descripcion' => 'Cepillo dental suave', 'inventario' => 90, 'costo' => 12.00, 'precio' => 25.00, 'num_familia' => '011', 'activo' => 1],
            ['id_sucursal' => 3, 'ean' => '7501234567960', 'descripcion' => 'Cepillo dental suave', 'inventario' => 70, 'costo' => 12.00, 'precio' => 25.00, 'num_familia' => '011', 'activo' => 1],
            ['id_sucursal' => 4, 'ean' => '7501234567960', 'descripcion' => 'Cepillo dental suave', 'inventario' => 50, 'costo' => 12.00, 'precio' => 25.00, 'num_familia' => '011', 'activo' => 1],
            
            ['id_sucursal' => 1, 'ean' => '7501234567961', 'descripcion' => 'Pasta dental Colgate 120g', 'inventario' => 200, 'costo' => 18.00, 'precio' => 32.00, 'num_familia' => '011', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567961', 'descripcion' => 'Pasta dental Colgate 120g', 'inventario' => 150, 'costo' => 18.00, 'precio' => 32.00, 'num_familia' => '011', 'activo' => 1],
            ['id_sucursal' => 3, 'ean' => '7501234567961', 'descripcion' => 'Pasta dental Colgate 120g', 'inventario' => 120, 'costo' => 18.00, 'precio' => 32.00, 'num_familia' => '011', 'activo' => 1],
            
            // ========== CUIDADO CAPILAR (012) ==========
            ['id_sucursal' => 1, 'ean' => '7501234567970', 'descripcion' => 'Shampoo Pantene 400ml', 'inventario' => 100, 'costo' => 45.00, 'precio' => 75.00, 'num_familia' => '012', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567970', 'descripcion' => 'Shampoo Pantene 400ml', 'inventario' => 70, 'costo' => 45.00, 'precio' => 75.00, 'num_familia' => '012', 'activo' => 1],
            ['id_sucursal' => 4, 'ean' => '7501234567970', 'descripcion' => 'Shampoo Pantene 400ml', 'inventario' => 40, 'costo' => 45.00, 'precio' => 75.00, 'num_familia' => '012', 'activo' => 1],
            
            // ========== CUIDADO FACIAL (013) ==========
            ['id_sucursal' => 1, 'ean' => '7501234567980', 'descripcion' => 'Protector solar FPS 50', 'inventario' => 80, 'costo' => 85.00, 'precio' => 140.00, 'num_familia' => '013', 'activo' => 1],
            ['id_sucursal' => 2, 'ean' => '7501234567980', 'descripcion' => 'Protector solar FPS 50', 'inventario' => 50, 'costo' => 85.00, 'precio' => 140.00, 'num_familia' => '013', 'activo' => 1],
            ['id_sucursal' => 3, 'ean' => '7501234567980', 'descripcion' => 'Protector solar FPS 50', 'inventario' => 30, 'costo' => 85.00, 'precio' => 140.00, 'num_familia' => '013', 'activo' => 1],
        ]);
    }
}