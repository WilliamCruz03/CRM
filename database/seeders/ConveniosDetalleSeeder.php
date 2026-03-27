<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConveniosDetalleSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener IDs de convenios por convenio (código)
        $inapam = DB::table('cat_convenios')->where('convenio', '0000000000037')->first();
        $tec = DB::table('cat_convenios')->where('convenio', '0000000000041')->first();
        $maestros = DB::table('cat_convenios')->where('convenio', '0000000000007')->first();
        
        // Obtener IDs de familias por num_familia
        $familia001 = DB::table('cat_familias')->where('num_familia', '001')->first();
        $familia002 = DB::table('cat_familias')->where('num_familia', '002')->first();
        $familia003 = DB::table('cat_familias')->where('num_familia', '003')->first();
        $familia004 = DB::table('cat_familias')->where('num_familia', '004')->first();
        $familia005 = DB::table('cat_familias')->where('num_familia', '005')->first();
        
        if ($inapam && $familia001) {
            DB::table('cat_convenios_detalle')->insert([
                ['id_convenio' => $inapam->id, 'id_familia' => $familia001->id_familia, 'porcentaje_descuento' => 7.00, 'created_at' => now(), 'updated_at' => now()],
                ['id_convenio' => $inapam->id, 'id_familia' => $familia004->id_familia, 'porcentaje_descuento' => 7.00, 'created_at' => now(), 'updated_at' => now()],
                ['id_convenio' => $inapam->id, 'id_familia' => $familia005->id_familia, 'porcentaje_descuento' => 7.00, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
        
        if ($tec && $familia003) {
            DB::table('cat_convenios_detalle')->insert([
                ['id_convenio' => $tec->id, 'id_familia' => $familia003->id_familia, 'porcentaje_descuento' => 5.00, 'created_at' => now(), 'updated_at' => now()],
                ['id_convenio' => $tec->id, 'id_familia' => $familia005->id_familia, 'porcentaje_descuento' => 5.00, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
        
        if ($maestros && $familia002 && $familia003 && $familia004) {
            DB::table('cat_convenios_detalle')->insert([
                ['id_convenio' => $maestros->id, 'id_familia' => $familia002->id_familia, 'porcentaje_descuento' => 10.00, 'created_at' => now(), 'updated_at' => now()],
                ['id_convenio' => $maestros->id, 'id_familia' => $familia003->id_familia, 'porcentaje_descuento' => 10.00, 'created_at' => now(), 'updated_at' => now()],
                ['id_convenio' => $maestros->id, 'id_familia' => $familia004->id_familia, 'porcentaje_descuento' => 10.00, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }
}