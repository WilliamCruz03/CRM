<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SucursalesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('sucursales')->insert([
            ['nombre' => 'Jardin', 'activo' => 1],
            ['nombre' => 'Mercado', 'activo' => 1],
            ['nombre' => 'Zatipan', 'activo' => 1],
            ['nombre' => 'Boulevard', 'activo' => 1],
        ]);
    }
}