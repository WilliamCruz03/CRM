<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConveniosSeeder extends Seeder
{
    public function run(): void
    {
        $convenios = [
            ['id' => 1, 'convenio' => 'INAPAM', 'tipo' => 'C', 'porcentaje_descuento' => 7.00, 'status' => 1],
            ['id' => 2, 'convenio' => 'Convenio TEC', 'tipo' => 'C', 'porcentaje_descuento' => 5.00, 'status' => 1],
            ['id' => 3, 'convenio' => 'Convenio Maestros', 'tipo' => 'C', 'porcentaje_descuento' => 10.00, 'status' => 1],
        ];

        foreach ($convenios as $convenio) {
            DB::table('cat_convenios')->updateOrInsert(
                ['id' => $convenio['id']],
                $convenio
            );
        }
    }
}