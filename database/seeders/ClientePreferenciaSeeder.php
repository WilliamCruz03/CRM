<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;
use App\Models\Preferencia;
use App\Models\Enfermedad;
use Carbon\Carbon;

class ClientePreferenciaSeeder extends Seeder
{
    public function run(): void
    {
        // Crear clientes de ejemplo
        $clientes = [
            [
                'nombre' => 'María',
                'apellidos' => 'González',
                'email' => 'maria.gonzalez@gmail.com',
                'telefono' => '123456789',
                'calle' => 'Av. Principal 123',
                'colonia' => 'Centro',
                'ciudad' => 'Tamazunchale'
            ],
            [
                'nombre' => 'Carlos',
                'apellidos' => 'Ramírez',
                'email' => 'carlos.ramirez@gmail.com',
                'telefono' => '8187654321',
                'calle' => 'Loma bonita 45',
                'colonia' => 'Loma bonita',
                'ciudad' => 'Tamazunchale'
            ],
            [
                'nombre' => 'Ana',
                'apellidos' => 'López',
                'email' => 'ana.lopez@gmail.com',
                'telefono' => '3322114155',
                'calle' => 'Zaragoza 678',
                'colonia' => 'Zona Centro',
                'ciudad' => 'Tamazunchale',
                'estado' => 'Inactivo'
            ],
            [
                'nombre' => 'Jorge',
                'apellidos' => 'Hernández',
                'email' => 'jorge.hdz@gmail.com',
                'telefono' => '5598765432',
                'notas' => 'Prefiere entregas en horario matutino'
            ],
        ];

        foreach ($clientes as $clienteData) {
            $cliente = Cliente::create($clienteData);
            
            // Asignar algunas enfermedades aleatorias
            $enfermedades = Enfermedad::inRandomOrder()->limit(rand(0, 3))->get();
            foreach ($enfermedades as $enfermedad) {
                $cliente->enfermedades()->attach($enfermedad->id, [
                    'severidad' => rand(0, 2) == 0 ? 'Leve' : (rand(0, 1) ? 'Moderada' : 'Grave'),
                    'notas' => 'Diagnosticado en ' . rand(2018, 2025)
                ]);
            }
            
            // Crear preferencias para cada cliente
            $preferencias = [
                [
                    'descripcion' => 'Prefiere ser contactado solo por WhatsApp',
                    'categoria' => 'Contacto'
                ],
                [
                    'descripcion' => 'Entregas únicamente en horario matutino (9am - 11am)',
                    'categoria' => 'Entregas'
                ],
                [
                    'descripcion' => 'Le gustaría recibir notificaciones de medicamentos antes de que se le acaben',
                    'categoria' => 'Notificaciones'
                ],
                [
                    'descripcion' => 'Solicita factura electrónica siempre',
                    'categoria' => 'Facturación'
                ],
            ];
            
            // Agregar 1-2 preferencias aleatorias
            $prefSeleccionadas = array_rand($preferencias, rand(1, 2));
            if (is_array($prefSeleccionadas)) {
                foreach ($prefSeleccionadas as $index) {
                    Preferencia::create([
                        'cliente_id' => $cliente->id,
                        'descripcion' => $preferencias[$index]['descripcion'],
                        'categoria' => $preferencias[$index]['categoria'],
                        'fecha_registro' => Carbon::now()->subDays(rand(0, 60))
                    ]);
                }
            } else {
                Preferencia::create([
                    'cliente_id' => $cliente->id,
                    'descripcion' => $preferencias[$prefSeleccionadas]['descripcion'],
                    'categoria' => $preferencias[$prefSeleccionadas]['categoria'],
                    'fecha_registro' => Carbon::now()->subDays(rand(0, 60))
                ]);
            }
        }

        $this->command->info('✅ Clientes y preferencias creados correctamente');
    }
}