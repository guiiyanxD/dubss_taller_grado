<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Estudiante;
use App\Models\Convocatoria;
use App\Models\Beca;
use App\Models\FormularioSocioEconomico;
use App\Models\Postulacion;
use App\Models\User;
use Carbon\Carbon;

class PostulacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $convocatoriaActiva = Convocatoria::where('fecha_fin', '>', now())->first();
        $becas = Beca::where('id_convocatoria', $convocatoriaActiva->id)->get();
        $estudiantes = Estudiante::all();
        $creador = User::role('Dpto. Sistema')->first();

        $estados = ['PENDIENTE', 'EN_VALIDACION', 'APROBADO', 'RECHAZADO'];

        foreach ($estudiantes as $index => $estudiante) {
            $formulario = $estudiante->formulariosSocioEconomicos()->first();
            
            if (!$formulario) {
                continue;
            }

            // Cada estudiante postula a 1-3 becas
            $numPostulaciones = rand(1, min(3, $becas->count()));
            $becasSeleccionadas = $becas->random($numPostulaciones);

            foreach ($becasSeleccionadas as $beca) {
                $puntaje = round(rand(50, 95) + (rand(0, 99) / 100), 2);
                $estado = $estados[array_rand($estados)];

                Postulacion::create([
                    'id_estudiante' => $estudiante->id_usuario,
                    'id_convocatoria' => $convocatoriaActiva->id,
                    'id_formulario' => $formulario->id,
                    'id_beca' => $beca->id,
                    'fecha_postulacion' => Carbon::now()->subDays(rand(1, 15)),
                    'estado_postulado' => $estado,
                    'motivo_rechazo' => $estado === 'RECHAZADO' ? 'Documentación incompleta' : null,
                    'posicion_ranking' => $estado === 'APROBADO' ? rand(1, 100) : null,
                    'puntaje_final' => $puntaje,
                    'creado_por' => $creador->id,
                ]);
            }
        }

        $totalPostulaciones = Postulacion::count();
        $this->command->info('✅ Postulaciones creadas: ' . $totalPostulaciones);
        $this->command->info('   - Pendientes: ' . Postulacion::where('estado_postulado', 'PENDIENTE')->count());
        $this->command->info('   - En Validación: ' . Postulacion::where('estado_postulado', 'EN_VALIDACION')->count());
        $this->command->info('   - Aprobadas: ' . Postulacion::where('estado_postulado', 'APROBADO')->count());
        $this->command->info('   - Rechazadas: ' . Postulacion::where('estado_postulado', 'RECHAZADO')->count());
    }
}
