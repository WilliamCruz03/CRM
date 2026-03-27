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
            FamiliasSeeder::class,
            ConveniosSeeder::class,
            ConveniosDetalleSeeder::class,
            CatalogoGeneralSeeder::class,
            CatalogosBasicosSeeder::class,
        ]);
    }
}
