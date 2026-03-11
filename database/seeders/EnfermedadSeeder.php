<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoriaEnfermedad; // Asegúrate que el modelo apunte a la tabla correcta
use App\Models\Enfermedad;
use Illuminate\Support\Facades\DB;

class EnfermedadSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar tablas (con los nombres correctos)
        DB::statement('DELETE FROM enfermedades');
        DB::statement('DELETE FROM categoria_enfermedades'); // Cambiado: con 'e'
        DB::statement('DBCC CHECKIDENT ("enfermedades", RESEED, 0)');
        DB::statement('DBCC CHECKIDENT ("categoria_enfermedades", RESEED, 0)'); // Cambiado

        // Crear categorías
        $categorias = [
            [
                'nombre' => 'Crónico-Degenerativa',
                'descripcion' => 'Enfermedades de larga duración y progresión lenta',
                'activo' => true
            ],
            [
                'nombre' => 'Alergia',
                'descripcion' => 'Reacciones alérgicas a diversos agentes',
                'activo' => true
            ],
            [
                'nombre' => 'Respiratoria',
                'descripcion' => 'Enfermedades que afectan el sistema respiratorio',
                'activo' => true
            ],
            [
                'nombre' => 'Cardiovascular',
                'descripcion' => 'Enfermedades del corazón y sistema circulatorio',
                'activo' => true
            ],
            [
                'nombre' => 'Infecciosa',
                'descripcion' => 'Enfermedades causadas por agentes patógenos',
                'activo' => true
            ],
            [
                'nombre' => 'Autoinmune',
                'descripcion' => 'El sistema inmunológico ataca al propio cuerpo',
                'activo' => true
            ]
        ];

        foreach ($categorias as $categoria) {
            CategoriaEnfermedad::create($categoria);
        }

        // Crear enfermedades
        $enfermedades = [
            // Crónico-Degenerativas (categoria_id = 1)
            ['nombre' => 'Hipertensión Arterial', 'categoria_id' => 1, 'descripcion' => 'Presión arterial elevada persistentemente'],
            ['nombre' => 'Diabetes Tipo 1', 'categoria_id' => 1, 'descripcion' => 'Enfermedad autoinmune que afecta la producción de insulina'],
            ['nombre' => 'Diabetes Tipo 2', 'categoria_id' => 1, 'descripcion' => 'Resistencia a la insulina y deficiencia relativa de insulina'],
            ['nombre' => 'Artritis Reumatoide', 'categoria_id' => 1, 'descripcion' => 'Enfermedad inflamatoria crónica de las articulaciones'],
            ['nombre' => 'Osteoporosis', 'categoria_id' => 1, 'descripcion' => 'Pérdida de densidad ósea'],
            
            // Alergias (categoria_id = 2)
            ['nombre' => 'Alergia a Penicilina', 'categoria_id' => 2, 'descripcion' => 'Reacción alérgica a antibióticos penicilínicos'],
            ['nombre' => 'Alergia a Mariscos', 'categoria_id' => 2, 'descripcion' => 'Reacción alérgica a crustáceos y moluscos'],
            ['nombre' => 'Alergia a Lácteos', 'categoria_id' => 2, 'descripcion' => 'Intolerancia o alergia a proteínas de la leche'],
            ['nombre' => 'Rinitis Alérgica', 'categoria_id' => 2, 'descripcion' => 'Alergia estacional o perenne'],
            
            // Respiratorias (categoria_id = 3)
            ['nombre' => 'Asma Bronquial', 'categoria_id' => 3, 'descripcion' => 'Enfermedad inflamatoria crónica de las vías respiratorias'],
            ['nombre' => 'EPOC', 'categoria_id' => 3, 'descripcion' => 'Enfermedad Pulmonar Obstructiva Crónica'],
            ['nombre' => 'Neumonía', 'categoria_id' => 3, 'descripcion' => 'Infección pulmonar aguda'],
            ['nombre' => 'Bronquitis Crónica', 'categoria_id' => 3, 'descripcion' => 'Inflamación de los bronquios'],
            
            // Cardiovasculares (categoria_id = 4)
            ['nombre' => 'Insuficiencia Cardíaca', 'categoria_id' => 4, 'descripcion' => 'El corazón no bombea sangre eficientemente'],
            ['nombre' => 'Arritmia Cardíaca', 'categoria_id' => 4, 'descripcion' => 'Alteraciones en el ritmo cardíaco'],
            ['nombre' => 'Cardiopatía Isquémica', 'categoria_id' => 4, 'descripcion' => 'Reducción del flujo sanguíneo al corazón'],
            
            // Infecciosas (categoria_id = 5)
            ['nombre' => 'Infección Urinaria', 'categoria_id' => 5, 'descripcion' => 'Infección del tracto urinario'],
            ['nombre' => 'Infección Respiratoria', 'categoria_id' => 5, 'descripcion' => 'Infección de vías respiratorias'],
            ['nombre' => 'Gastroenteritis', 'categoria_id' => 5, 'descripcion' => 'Infección intestinal'],
            
            // Autoinmunes (categoria_id = 6)
            ['nombre' => 'Lupus Eritematoso', 'categoria_id' => 6, 'descripcion' => 'Enfermedad autoinmune multisistémica'],
            ['nombre' => 'Esclerosis Múltiple', 'categoria_id' => 6, 'descripcion' => 'Afecta el sistema nervioso central'],
            ['nombre' => 'Enfermedad de Crohn', 'categoria_id' => 6, 'descripcion' => 'Enfermedad inflamatoria intestinal'],
        ];

        foreach ($enfermedades as $enfermedad) {
            Enfermedad::create($enfermedad);
        }

        $this->command->info('✅ Categorías y enfermedades insertadas correctamente');
    }
}