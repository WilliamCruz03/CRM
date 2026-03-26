<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SucursalesSeeder::class,
            ConveniosSeeder::class,
            CatalogoGeneralSeeder::class,
            CotizacionesCatalogosSeeder::class, // Este ya lo tenías para fases y clasificaciones
        ]);
    }
}
