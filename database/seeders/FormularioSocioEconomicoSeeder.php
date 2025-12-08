<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Estudiante;
use App\Models\FormularioSocioEconomico;
use App\Models\GrupoFamiliar;
use App\Models\MiembroFamiliar;
use App\Models\DependenciaEconomica;
use App\Models\IngresoEconomico;
use App\Models\Residencia;
use App\Models\TenenciaVivienda;
use Carbon\Carbon;

class FormularioSocioEconomicoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estudiantes = Estudiante::all();

        foreach ($estudiantes as $index => $estudiante) {
            // Crear formulario base
            $formulario = FormularioSocioEconomico::create([
                'id_estudiante' => $estudiante->id_usuario,
                'validado_por' => $index < 7, // Los primeros 7 validados
                'fecha_llenado' => Carbon::now()->subDays(rand(5, 20)),
                'completado' => true,
                'telefono_referencia' => '7' . rand(1000000, 9999999),
                'comentario_personal' => 'Solicito la beca para poder continuar mis estudios.',
                'observaciones' => null,
                'discapacidad' => $index === 5, // Solo 1 con discapacidad
                'comentario_discapacidad' => $index === 5 ? 'Discapacidad visual parcial' : null,
                'otro_beneficio' => false,
                'comentario_otro_beneficio' => null,
                'lugar_procedencia' => ['El Alto', 'Achacachi', 'Viacha', 'Copacabana', 'Sorata'][rand(0, 4)],
            ]);

            // Grupo Familiar
            $cantidadFamiliares = rand(3, 6);
            $grupoFamiliar = GrupoFamiliar::create([
                'id_formulario' => $formulario->id,
                'cantidad_hijos' => 0,
                'cantidad_familiares' => $cantidadFamiliares,
                'tiene_hijos' => false,
                'puntaje' => round(rand(50, 100) / 10, 2),
                'puntaje_total' => round(rand(50, 100) / 10, 2),
            ]);

            // Miembros del grupo familiar
            $parentescos = ['Padre', 'Madre', 'Hermano/a', 'Hermano/a'];
            $ocupaciones = ['Comerciante', 'Albañil', 'Ama de casa', 'Estudiante'];
            
            for ($i = 0; $i < min($cantidadFamiliares, 4); $i++) {
                MiembroFamiliar::create([
                    'id_grupo_familiar' => $grupoFamiliar->id,
                    'nombre_completo' => 'Familiar ' . ($i + 1) . ' de ' . $estudiante->usuario->nombres,
                    'parentesco' => $parentescos[$i],
                    'edad' => $i < 2 ? rand(40, 60) : rand(10, 25),
                    'ocupacion' => $ocupaciones[$i],
                    'observacion' => null,
                ]);
            }

            // Dependencia Económica
            $dependencia = DependenciaEconomica::create([
                'id_formulario' => $formulario->id,
                'tipo_dependencia' => ['Total', 'Parcial', 'Ninguna'][rand(0, 2)],
                'nota_ocupacion_dependiente' => 'Trabaja informalmente',
                'id_ocupacion_dependiente' => null,
                'puntaje' => round(rand(30, 90) / 10, 1),
                'puntaje_total' => round(rand(30, 90) / 10, 1),
            ]);

            // Ingreso Económico
            IngresoEconomico::create([
                'id_dependencia_eco' => $dependencia->id,
                'rango_monto' => ['0-1000', '1001-2000', '2001-3000', '3001-5000'][rand(0, 3)],
                'puntaje' => round(rand(40, 100) / 10, 2),
            ]);

            // Residencia
            Residencia::create([
                'id_formulario' => $formulario->id,
                'provincia' => 'Murillo',
                'zona' => ['Villa Fátima', 'El Alto', 'Sopocachi', 'Miraflores'][rand(0, 3)],
                'calle' => 'Calle ' . rand(1, 50),
                'cant_banhos' => rand(1, 2),
                'cant_salas' => rand(0, 1),
                'cant_dormitorios' => rand(1, 3),
                'cantt_comedor' => rand(0, 1),
                'barrio' => 'Barrio ' . chr(rand(65, 70)),
                'cant_patios' => rand(0, 1),
                'puntaje_total' => round(rand(40, 90) / 10, 2),
            ]);

            // Tenencia Vivienda
            TenenciaVivienda::create([
                'id_formulario' => $formulario->id,
                'tipo_tenencia' => ['Propia', 'Alquilada', 'Prestada'][rand(0, 2)],
                'detalle_tenencia' => 'En proceso de pago',
                'puntaje' => round(rand(30, 90) / 10, 1),
                'puntaje_total' => round(rand(30, 90) / 10, 1),
            ]);
        }

        $this->command->info('✅ Formularios socioeconómicos creados: ' . $estudiantes->count());
        $this->command->info('   - Con grupo familiar completo');
        $this->command->info('   - Con datos de residencia');
        $this->command->info('   - Con información económica');
    }
}
