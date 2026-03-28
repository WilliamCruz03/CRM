<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConveniosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cat_convenios')->insert([
            ['convenio' => '0000000000037', 'nombre' => 'INAPAM', 'tipo' => 'C', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['convenio' => '0000000000041', 'nombre' => 'Convenio TEC', 'tipo' => 'C', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['convenio' => '0000000000007', 'nombre' => 'Convenio Maestros', 'tipo' => 'C', 'activo' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}