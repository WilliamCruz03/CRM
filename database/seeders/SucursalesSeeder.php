<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SucursalesSeeder extends Seeder
{
    public function run(): void
    {
        $sucursales = [
            ['id_sucursal' => 1, 'nombre' => 'Jardin', 'activo' => 1],
            ['id_sucursal' => 2, 'nombre' => 'Mercado', 'activo' => 1],
            ['id_sucursal' => 3, 'nombre' => 'Zatipan', 'activo' => 1],
            ['id_sucursal' => 4, 'nombre' => 'Boulevard', 'activo' => 1],
        ];

        foreach ($sucursales as $sucursal) {
            DB::table('sucursales')->updateOrInsert(
                ['id_sucursal' => $sucursal['id_sucursal']],
                $sucursal
            );
        }
    }
}