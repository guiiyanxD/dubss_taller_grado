<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Beca;
use App\Models\Convocatoria;
use App\Models\Requisito;

class BecaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener la convocatoria activa
        $convocatoriaActiva = Convocatoria::where('fecha_fin', '>', now())->first();

        if (!$convocatoriaActiva) {
            $this->command->warn('⚠️  No hay convocatoria activa. Usando la primera convocatoria.');
            $convocatoriaActiva = Convocatoria::first();
        }

        // Becas para la convocatoria activa
        $becas = [
            [
                'nombre' => 'Beca Excelencia Académica',
                'descripcion' => 'Beca completa para estudiantes con promedio superior a 80',
                'codigo' => 'BEA-2024-I',
                'version' => '1.0',
                'periodo' => '2024-I',
                'cupos_disponibles' => 50,
                'requisitos_ids' => [1, 2, 3, 4, 5],
            ],
            [
                'nombre' => 'Beca Vulnerabilidad Socioeconómica',
                'descripcion' => 'Beca para estudiantes en situación de vulnerabilidad',
                'codigo' => 'BVS-2024-I',
                'version' => '1.0',
                'periodo' => '2024-I',
                'cupos_disponibles' => 100,
                'requisitos_ids' => [1, 2, 3, 4, 5, 6, 7, 8],
            ],
            [
                'nombre' => 'Beca Alimentación',
                'descripcion' => 'Subsidio para alimentación en el comedor universitario',
                'codigo' => 'BAL-2024-I',
                'version' => '1.0',
                'periodo' => '2024-I',
                'cupos_disponibles' => 200,
                'requisitos_ids' => [1, 2, 3, 4],
            ],
            [
                'nombre' => 'Beca Transporte',
                'descripcion' => 'Subsidio mensual para transporte',
                'codigo' => 'BTR-2024-I',
                'version' => '1.0',
                'periodo' => '2024-I',
                'cupos_disponibles' => 150,
                'requisitos_ids' => [1, 2, 3, 4],
            ],
            [
                'nombre' => 'Beca Material Educativo',
                'descripcion' => 'Subsidio para compra de material educativo y libros',
                'codigo' => 'BME-2024-I',
                'version' => '1.0',
                'periodo' => '2024-I',
                'cupos_disponibles' => 75,
                'requisitos_ids' => [1, 2, 3],
            ],
        ];

        foreach ($becas as $becaData) {
            $requisitosIds = $becaData['requisitos_ids'];
            unset($becaData['requisitos_ids']);

            $beca = Beca::create([
                'id_convocatoria' => $convocatoriaActiva->id,
                ...$becaData
            ]);

            // Asociar requisitos
            $beca->requisitos()->attach($requisitosIds);
        }

        $this->command->info('✅ Becas creadas: ' . count($becas));
        $this->command->info('   Total de cupos disponibles: ' . array_sum(array_column($becas, 'cupos_disponibles')));
    }
}
