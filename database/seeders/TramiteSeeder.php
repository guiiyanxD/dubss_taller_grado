<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Postulacion;
use App\Models\Tramite;
use App\Models\TramiteHistorial;
use App\Models\Documento;
use App\Models\Notificacion;
use App\Models\EstadoTramite;
use App\Models\User;
use Carbon\Carbon;

class TramiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $postulaciones = Postulacion::all();
        $estadoPendiente = EstadoTramite::where('nombre', 'PENDIENTE')->first();
        $estadoValidacion = EstadoTramite::where('nombre', 'EN_VALIDACION')->first();
        $estadoValidado = EstadoTramite::where('nombre', 'VALIDADO')->first();
        $operador = User::role('Operador')->first();
        $sistemUser = User::role('Dpto. Sistema')->first();

        foreach ($postulaciones as $index => $postulacion) {
            $fechaCreacion = $postulacion->fecha_postulacion;
            
            // Determinar estado según el índice
            $estadoActual = match($index % 4) {
                0 => $estadoPendiente,
                1 => $estadoValidacion,
                2, 3 => $estadoValidado,
            };

            // Crear trámite
            $tramite = Tramite::create([
                'id_postulacion' => $postulacion->id,
                'codigo' => 'TRA-' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                'fecha_creacion' => $fechaCreacion,
                'clasificado' => $estadoActual->id >= 6 ? 'SI' : 'NO',
                'fecha_clasificacion' => $estadoActual->id >= 6 ? $fechaCreacion->copy()->addDays(3) : null,
                'estado_actual' => $estadoActual->id,
            ]);

            // Crear historial inicial
            TramiteHistorial::create([
                'id_tramite' => $tramite->id,
                'observaciones' => 'Trámite creado automáticamente',
                'revisador_por' => $sistemUser->id,
                'fecha_revision' => $fechaCreacion,
                'estado_anterior' => null,
                'estado_nuevo' => 'PENDIENTE',
            ]);

            // Si está en validación o validado, agregar más historial
            if ($estadoActual->id >= 2) {
                TramiteHistorial::create([
                    'id_tramite' => $tramite->id,
                    'observaciones' => 'Documentación física recibida',
                    'revisador_por' => $operador->id,
                    'fecha_revision' => $fechaCreacion->copy()->addDays(1),
                    'estado_anterior' => 'PENDIENTE',
                    'estado_nuevo' => 'EN_VALIDACION',
                ]);
            }

            if ($estadoActual->id >= 3) {
                TramiteHistorial::create([
                    'id_tramite' => $tramite->id,
                    'observaciones' => 'Documentación validada correctamente',
                    'revisador_por' => $operador->id,
                    'fecha_revision' => $fechaCreacion->copy()->addDays(2),
                    'estado_anterior' => 'EN_VALIDACION',
                    'estado_nuevo' => 'VALIDADO',
                ]);
            }

            // Crear documentos
            $tiposDocumento = ['CI', 'Kardex', 'Comprobante Domicilio', 'Formulario'];
            foreach ($tiposDocumento as $tipo) {
                Documento::create([
                    'id_tramite' => $tramite->id,
                    'tipo_documento' => $tipo,
                    'nombre_archivo' => strtolower(str_replace(' ', '_', $tipo)) . '_' . $tramite->codigo . '.pdf',
                    'ruta_digital' => '/documentos/' . $tramite->codigo . '/' . strtolower(str_replace(' ', '_', $tipo)) . '.pdf',
                    'estado_fisico' => $estadoActual->id >= 3 ? 'VALIDADO' : 'PENDIENTE',
                    'digitalizado_por' => $estadoActual->id >= 3 ? $operador->id : null,
                    'fecha_presentacion' => $fechaCreacion,
                    'fecha_digitalizacion' => $estadoActual->id >= 3 ? $fechaCreacion->copy()->addDays(2) : null,
                    'observaciones' => null,
                    'motivo_rechazo' => null,
                    'validado_por' => $estadoActual->id >= 3 ? $operador->id : null,
                ]);
            }

            // Crear notificación
            Notificacion::create([
                'id_estudiante' => $postulacion->id_estudiante,
                'id_tramite' => $tramite->id,
                'tipo' => 'INFORMACION',
                'titulo' => 'Trámite Creado',
                'mensaje' => 'Su trámite ' . $tramite->codigo . ' ha sido registrado exitosamente.',
                'leido' => $index % 3 === 0, // Algunos leídos
                'fecha_creacion' => $fechaCreacion,
                'fecha_lectura' => $index % 3 === 0 ? $fechaCreacion->copy()->addHours(2) : null,
                'canal' => 'SISTEMA',
            ]);
        }

        $totalTramites = Tramite::count();
        $this->command->info('✅ Trámites creados: ' . $totalTramites);
        $this->command->info('   - Con historial de cambios de estado');
        $this->command->info('   - Con 4 documentos por trámite');
        $this->command->info('   - Con notificaciones');
    }
}
