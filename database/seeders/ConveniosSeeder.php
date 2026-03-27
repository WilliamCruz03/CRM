<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConveniosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cat_convenios')->insert([
            ['convenio' => '0000000000037', 'nombre' => 'INAPAM', 'tipo' => 'C', 'num_familia' => '001', 'created_at' => now(), 'updated_at' => now()],
            ['convenio' => '0000000000041', 'nombre' => 'Convenio TEC', 'tipo' => 'C', 'num_familia' => '003', 'created_at' => now(), 'updated_at' => now()],
            ['convenio' => '0000000000007', 'nombre' => 'Convenio Maestros', 'tipo' => 'C', 'num_familia' => '002', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}