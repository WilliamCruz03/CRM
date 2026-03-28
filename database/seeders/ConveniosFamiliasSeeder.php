<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConveniosFamiliasSeeder extends Seeder
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
        $familia007 = DB::table('cat_familias')->where('num_familia', '007')->first();
        $familia037 = DB::table('cat_familias')->where('num_familia', '037')->first();
        $familia041 = DB::table('cat_familias')->where('num_familia', '041')->first();
        
        // INAPAM - 7% en familias 001, 004, 005
        if ($inapam) {
            if ($familia001) {
                DB::table('cat_convenios_familias')->insert([
                    'id_convenio' => $inapam->id_convenio,
                    'id_familia' => $familia001->id_familia,
                    'porcentaje_descuento' => 7.00,
                    'activo' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            if ($familia004) {
                DB::table('cat_convenios_familias')->insert([
                    'id_convenio' => $inapam->id_convenio,
                    'id_familia' => $familia004->id_familia,
                    'porcentaje_descuento' => 7.00,
                    'activo' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            if ($familia005) {
                DB::table('cat_convenios_familias')->insert([
                    'id_convenio' => $inapam->id_convenio,
                    'id_familia' => $familia005->id_familia,
                    'porcentaje_descuento' => 7.00,
                    'activo' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
        
        // Convenio TEC - 5% en familias 003, 005
        if ($tec) {
            if ($familia003) {
                DB::table('cat_convenios_familias')->insert([
                    'id_convenio' => $tec->id_convenio,
                    'id_familia' => $familia003->id_familia,
                    'porcentaje_descuento' => 5.00,
                    'activo' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            if ($familia005) {
                DB::table('cat_convenios_familias')->insert([
                    'id_convenio' => $tec->id_convenio,
                    'id_familia' => $familia005->id_familia,
                    'porcentaje_descuento' => 5.00,
                    'activo' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
        
        // Convenio Maestros - 10% en familias 002, 003, 004
        if ($maestros) {
            if ($familia002) {
                DB::table('cat_convenios_familias')->insert([
                    'id_convenio' => $maestros->id_convenio,
                    'id_familia' => $familia002->id_familia,
                    'porcentaje_descuento' => 10.00,
                    'activo' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            if ($familia003) {
                DB::table('cat_convenios_familias')->insert([
                    'id_convenio' => $maestros->id_convenio,
                    'id_familia' => $familia003->id_familia,
                    'porcentaje_descuento' => 10.00,
                    'activo' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            if ($familia004) {
                DB::table('cat_convenios_familias')->insert([
                    'id_convenio' => $maestros->id_convenio,
                    'id_familia' => $familia004->id_familia,
                    'porcentaje_descuento' => 10.00,
                    'activo' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}