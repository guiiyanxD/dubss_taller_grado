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
            'fecha_inicio' => Carbon::now()->subDays(15),
            'fecha_fin' => Carbon::now()->addDays(15),
        ]);

        // Convocatoria Futura
        Convocatoria::create([
            'nombre' => 'Convocatoria Becas Socioeconómicas 2024-II',
            'descripcion' => 'Segunda convocatoria de becas socioeconómicas del año 2024',
            'fecha_inicio' => Carbon::now()->addMonths(3),
            'fecha_fin' => Carbon::now()->addMonths(4),
        ]);

        // Convocatoria Pasada (Cerrada)
        Convocatoria::create([
            'nombre' => 'Convocatoria Becas Socioeconómicas 2023-II',
            'descripcion' => 'Segunda convocatoria de becas socioeconómicas del año 2023',
            'fecha_inicio' => Carbon::now()->subMonths(6),
            'fecha_fin' => Carbon::now()->subMonths(5),
        ]);

        $this->command->info('✅ Convocatorias creadas: 3');
        $this->command->info('   - 1 Activa (en curso)');
        $this->command->info('   - 1 Futura');
        $this->command->info('   - 1 Cerrada');
    }
}
