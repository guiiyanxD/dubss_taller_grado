<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Convocatoria;
use Carbon\Carbon;

class ConvocatoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Convocatoria Actual (Activa)
        Convocatoria::create([
            'nombre' => 'Convocatoria Becas Socioeconómicas 2024-I',
            'descripcion' => 'Primera convocatoria de becas socioeconómicas del año 2024 para estudiantes de pregrado',
            'estado' => 'ACTIVA',
            'fecha_inicio' => Carbon::now()->subDays(15),
            'fecha_fin' => Carbon::now()->addDays(15),
        ]);


        Convocatoria::create([
            'nombre' => 'Convocatoria Becas Socioeconómicas 2024-II',
            'descripcion' => 'Segunda convocatoria de becas socioeconómicas del año 2024',
            'estado' => 'ACTIVA',
            'fecha_inicio' => Carbon::now()->subDays(3),
            'fecha_fin' => Carbon::now()->addMonths(1),
        ]);

        // Convocatoria Pasada (FINALIZADA)
        Convocatoria::create([
            'nombre' => 'Convocatoria Becas Socioeconómicas 2023-II',
            'descripcion' => 'Segunda convocatoria de becas socioeconómicas del año 2023',
            'estado' => 'FINALIZADA',
            'fecha_inicio' => Carbon::now()->subMonths(6),
            'fecha_fin' => Carbon::now()->subMonths(5),
        ]);

    }
}
