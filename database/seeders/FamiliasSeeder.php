<?php
// database/seeders/FamiliasSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FamiliasSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cat_familias')->insert([
            // Familias existentes
            ['num_familia' => '001', 'nombre' => 'Pañales y Cuidado Infantil', 'descripcion' => 'Pañales, toallitas, cremas', 'activo' => 1],
            ['num_familia' => '002', 'nombre' => 'Alimentación Infantil', 'descripcion' => 'Leches, papillas, biberones', 'activo' => 1],
            ['num_familia' => '003', 'nombre' => 'Vitaminas y Suplementos', 'descripcion' => 'Vitaminas, minerales, suplementos', 'activo' => 1],
            ['num_familia' => '004', 'nombre' => 'Higiene Personal', 'descripcion' => 'Jabones, shampoos, cepillos', 'activo' => 1],
            ['num_familia' => '005', 'nombre' => 'Equipos Médicos', 'descripcion' => 'Termómetros, tensiómetros, nebulizadores', 'activo' => 1],
            ['num_familia' => '007', 'nombre' => 'Medicamentos Genéricos', 'descripcion' => 'Medicamentos de patente genérica', 'activo' => 1],
            ['num_familia' => '037', 'nombre' => 'Material de Curación', 'descripcion' => 'Gasas, vendas, apósitos', 'activo' => 1],
            ['num_familia' => '041', 'nombre' => 'Medicamentos de Patente', 'descripcion' => 'Medicamentos de marca', 'activo' => 1],
            
            // NUEVAS FAMILIAS
            ['num_familia' => '008', 'nombre' => 'Bebidas y Electrolitos', 'descripcion' => 'Refrescos, sueros, bebidas hidratantes', 'activo' => 1],
            ['num_familia' => '009', 'nombre' => 'Snacks y Botanas', 'descripcion' => 'Papas, galletas, botanas', 'activo' => 1],
            ['num_familia' => '010', 'nombre' => 'Dulces y Chocolates', 'descripcion' => 'Dulces, chocolates, caramelos', 'activo' => 1],
            ['num_familia' => '011', 'nombre' => 'Cuidado Bucal', 'descripcion' => 'Pastas dentales, enjuagues, cepillos', 'activo' => 1],
            ['num_familia' => '012', 'nombre' => 'Cuidado Capilar', 'descripcion' => 'Shampoos, acondicionadores, tratamientos', 'activo' => 1],
            ['num_familia' => '013', 'nombre' => 'Cuidado Facial', 'descripcion' => 'Crema, protector solar, limpiadores', 'activo' => 1],
        ]);
    }
}