<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FamiliasSeeder extends Seeder
{
    public function run(): void
    {
        $familias = [
            ['num_familia' => '001', 'nombre' => 'Pañales y Cuidado Infantil', 'descripcion' => 'Pañales, toallitas, cremas'],
            ['num_familia' => '007', 'nombre' => 'Medicamentos Genéricos', 'descripcion' => 'Medicamentos de patente genérica'],
            ['num_familia' => '037', 'nombre' => 'Material de Curación', 'descripcion' => 'Gasas, vendas, apósitos'],
            ['num_familia' => '041', 'nombre' => 'Medicamentos de Patente', 'descripcion' => 'Medicamentos de marca'],
            ['num_familia' => '002', 'nombre' => 'Alimentación Infantil', 'descripcion' => 'Leches, papillas, biberones'],
            ['num_familia' => '003', 'nombre' => 'Vitaminas y Suplementos', 'descripcion' => 'Vitaminas, minerales, suplementos'],
            ['num_familia' => '004', 'nombre' => 'Higiene Personal', 'descripcion' => 'Jabones, shampoos, cepillos'],
            ['num_familia' => '005', 'nombre' => 'Equipos Médicos', 'descripcion' => 'Termómetros, tensiómetros, nebulizadores'],
        ];

        foreach ($familias as $familia) {
            DB::table('cat_familias')->insert($familia);
        }
    }
}