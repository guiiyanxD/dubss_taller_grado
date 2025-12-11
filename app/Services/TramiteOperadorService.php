<?php

namespace App\Services;

use App\Models\Tramite;
use App\Models\Postulacion;
use App\Models\Estudiante;
use App\Models\EstadoTramite;
use App\Models\Notificacion;
use Illuminate\Support\Facades\DB;

class TramiteOperadorService
{
    // Service trabaja directamente con Eloquent Models
    // No requiere Repository para este caso de uso

    /**
     * Buscar trámite por CI del estudiante
     */
    public function buscarPorCI(string $ci): ?array
    {
        $estudiante = Estudiante::whereHas('usuario', function ($q) use ($ci) {
            $q->where('ci', $ci);
        })->first();

        if (!$estudiante) {
            return null;
        }

        $tramite = Tramite::with([
            'postulacion.estudiante.usuario',
            'postulacion.beca',
            'postulacion.convocatoria',
            'postulacion.formulario.grupoFamiliar.miembrosFamiliares',
            'postulacion.formulario.dependenciaEconomica.ingresosEconomicos',
            'postulacion.formulario.residencia',
            'postulacion.formulario.tenenciaVivienda',
            'estadoActual',
            'documentos',
        ])
            ->whereHas('postulacion', function ($q) use ($estudiante) {
                $q->where('id_estudiante', $estudiante->id_usuario);
            })
            ->latest()
            ->first();

        if (!$tramite) {
            return null;
        }

        return $this->formatearTramiteCompleto($tramite);
    }

    /**
     * Obtener trámites pendientes de validación
     */
    public function obtenerTramitesPendientes(): array
    {
        $estadoPendiente = 1; //EstadoTramite::where('nombre', 'PENDIENTE')->first();
        $estadoEnValidacion = 2 ; //EstadoTramite::where('nombre', 'EN_VALIDACION')->first();

        $tramites = Tramite::select([
            'id',
            'id_postulacion',
            'estado_actual',
            'created_at'
        ])->with([
            'postulacion:id,id_estudiante,id_beca', // Solo IDs
            'postulacion.estudiante.usuario:id,ci,nombres,email', // Solo necesarios
            'postulacion.beca:id,nombre',
            'estadoActual:id,nombre',
        ])
            ->whereIn('estado_actual', [$estadoPendiente, $estadoEnValidacion])
            ->orderBy('created_at', 'asc')
            ->get(); // CAMBIO CRÍTICO: Usamos get() en lugar de paginate()

        // CAMBIO CRÍTICO: Devolvemos el array de modelos mapeados directamente
        return $tramites->toArray();
    }

    /**
     * Obtener el detalle de un trámite específico con datos limitados para su validación.
     */
    public function obtenerParaValidacion(int $id): array
    {
        $tramite = Tramite::select([
            'id', 'codigo', 'estado_actual', 'created_at', 'id_postulacion',
            'fecha_creacion', 'clasificado', 'fecha_clasificacion' // Campos base del trámite
        ])->with([
            'postulacion:id,id_estudiante,id_beca',
            'postulacion.estudiante:id_usuario,carrera,semestre',
            'postulacion.estudiante.usuario:id,nombres,ci,email',
            'postulacion.beca:id,nombre,id_convocatoria',
            'postulacion.beca.convocatoria:id,nombre',
            'estadoActual:id,nombre',
            'documentos:id,id_tramite,nombre_archivo,ruta_digital,estado_fisico',
            'historial:id,id_tramite,estado_anterior,estado_nuevo,observaciones,revisador_por,fecha_revision',
            'historial.revisador:id,nombres,apellidos',

        ])->findOrFail($id);

        // Mapeo (o usar TramiteResource aquí si existiera)
        return $tramite->toArray();
    }

    /**
     * Validar trámite (Aprobar o Rechazar)
     */
    public function validarTramite(
        int $tramiteId,
        string $accion,
        array $documentosValidados,
        ?string $observaciones,
        int $operadorId
    ): array {
        DB::beginTransaction();

        try {
            $tramite = Tramite::findOrFail($tramiteId);
            $estadoAnterior = $tramite->estadoActual->nombre ?? 'EN_VALIDACION';
            //return dd($estadoAnterior);
            if ($accion === 'APROBAR') {
                $nuevoEstado = EstadoTramite::where('nombre', 'VALIDADO')->first();
                $mensaje = 'Documentación aprobada correctamente';
                $tipoNotif = 'ALERTA';
                $tituloNotif = 'Documentos validados';
                $mensajeNotif = 'Tu documentación ha sido aprobada. El próximo paso es la digitalización.';
            } else {
                $nuevoEstado = EstadoTramite::where('nombre', 'RECHAZADO')->first();
                $mensaje = 'Trámite rechazado';
                $tipoNotif = 'RESULTADO';
                $tituloNotif = 'Documentos rechazados';
                $mensajeNotif = 'Tu documentación fue rechazada. Motivo: ' . $observaciones;
            }

            // Actualizar trámite
            $tramite->update([
                'estado_actual' => $nuevoEstado->id,
            ]);
            return dd($tramite);
            // Registrar en historial
            $tramite->historial()->create([
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $nuevoEstado->nombre,
                'observaciones' => $observaciones ?? ($accion === 'APROBAR' ? 'Todos los documentos correctos' : 'Documentos rechazados'),
                'revisador_por' => $operadorId,
                'fecha_revision' => now(),
            ]);

            // Actualizar documentos
            if ($accion === 'APROBAR' && !empty($documentosValidados)) {
                foreach ($documentosValidados as $doc) {
                    $tramite->documentos()->updateOrCreate(
                        ['tipo_documento' => $doc['tipo']],
                        ['validado' => true]
                    );
                }
            }

            // Crear notificación
            Notificacion::create([
                'id_estudiante' => $tramite->postulacion->id_estudiante,
                'id_tramite' => $tramite->id,
                'tipo' => $tipoNotif,
                'titulo' => $tituloNotif,
                'mensaje' => $mensajeNotif,
                'leido' => false,
                'canal' => 'sistema',
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => $mensaje,
                'tramite' => $tramite,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtener trámites validados (listos para digitalizar)
     */
    public function obtenerTramitesValidados(int $page = 1, int $perPage = 15): array
    {
        $estadoValidado = EstadoTramite::where('nombre', 'VALIDADO')->first();

        $tramites = Tramite::with([
            'postulacion.estudiante.usuario',
            'postulacion.beca',
            'estadoActual',
        ])
            ->where('estado_actual', $estadoValidado->id)
            ->orderBy('updated_at', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $tramites->items(),
            'current_page' => $tramites->currentPage(),
            'last_page' => $tramites->lastPage(),
            'per_page' => $tramites->perPage(),
            'total' => $tramites->total(),
        ];
    }

    /**
     * Obtener historial de trámites procesados por un operador
     */
    public function obtenerHistorialOperador(int $operadorId, int $page = 1, int $perPage = 15): array
    {
        $tramites = Tramite::with([
            'postulacion.estudiante.usuario',
            'postulacion.beca',
            'estadoActual',
        ])
            ->whereHas('historial', function ($q) use ($operadorId) {
                $q->where('revisador_por', $operadorId);
            })
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $tramites->items(),
            'current_page' => $tramites->currentPage(),
            'last_page' => $tramites->lastPage(),
            'per_page' => $tramites->perPage(),
            'total' => $tramites->total(),
        ];
    }

    /**
     * Obtener detalle completo de un trámite
     */
    public function obtenerDetalleCompleto(int $tramiteId): ?array
    {
        $tramite = Tramite::with([
            'postulacion.estudiante.usuario',
            'postulacion.beca.requisitos',
            'postulacion.convocatoria',
            'postulacion.formulario.grupoFamiliar.miembrosFamiliares',
            'postulacion.formulario.dependenciaEconomica.ingresosEconomicos',
            'postulacion.formulario.residencia',
            'postulacion.formulario.tenenciaVivienda',
            'estadoActual',
            'documentos',
            'historial.revisador',
        ])->find($tramiteId);

        if (!$tramite) {
            return null;
        }

        return $this->formatearTramiteCompleto($tramite);
    }

    /**
     * Obtener estadísticas del operador
     */
    public function obtenerEstadisticasOperador(int $operadorId): array
    {
        $totalProcesados = Tramite::whereHas('historial', function ($q) use ($operadorId) {
            $q->where('revisador_por', $operadorId);
        })->count();

        $estadoPendiente = EstadoTramite::where('nombre', 'PENDIENTE')->first();
        $pendientes = Tramite::where('estado_actual', $estadoPendiente->id)->count();

        $estadoValidado = EstadoTramite::where('nombre', 'VALIDADO')->first();
        $validadosHoy = Tramite::whereHas('historial', function ($q) use ($operadorId) {
            $q->where('revisador_por', $operadorId)
              ->whereDate('created_at', today());
        })->where('estado_actual', $estadoValidado->id)->count();

        return [
            'total_procesados' => $totalProcesados,
            'pendientes' => $pendientes,
            'validados_hoy' => $validadosHoy,
        ];
    }

    /**
     * Formatear trámite para respuesta
     */
    private function formatearTramiteCompleto(Tramite $tramite): array
    {
        $postulacion = $tramite->postulacion;
        $estudiante = $postulacion->estudiante;
        $formulario = $postulacion->formulario;

        return [
            'id' => $tramite->id,
            'codigo' => $tramite->codigo,
            'estado_actual' => [
                'nombre' => $tramite->estadoActual->nombre ?? 'PENDIENTE',
                'descripcion' => $tramite->estadoActual->descripcion ?? '',
                'color' => $tramite->estadoActual->color ?? '#gray',
            ],
            'clasificado' => $tramite->clasificado === 'SI',
            'fecha_creacion' => $tramite->created_at->format('Y-m-d H:i:s'),
            'estudiante' => [
                'nombre' => $estudiante->usuario->nombres . ' ' . $estudiante->usuario->apellidos,
                'ci' => $estudiante->usuario->ci,
                'email' => $estudiante->usuario->email,
                'telefono' => $estudiante->usuario->telefono,
                'carrera' => $estudiante->carrera,
                'semestre' => $estudiante->semestre,
            ],
            'beca' => [
                'nombre' => $postulacion->beca->nombre,
                'codigo' => $postulacion->beca->codigo,
                'monto' => $postulacion->beca->monto,
                'requisitos' => $postulacion->beca->requisitos->map(fn($r) => [
                    'nombre' => $r->nombre,
                    'descripcion' => $r->descripcion,
                ]),
            ],
            'formulario' => $formulario ? [
                'estado_civil' => $formulario->estado_civil,
                'tiene_hijos' => $formulario->tiene_hijos,
                'grupo_familiar' => $formulario->grupoFamiliar ? [
                    'cantidad_familiares' => $formulario->grupoFamiliar->cantidad_familiares,
                    'miembros' => $formulario->grupoFamiliar->miembrosFamiliares->map(fn($m) => [
                        'nombre' => $m->nombre . ' ' . $m->apellido,
                        'parentesco' => $m->parentesco,
                        'edad' => $m->edad,
                        'ocupacion' => $m->ocupacion,
                    ]),
                ] : null,
                'dependencia_economica' => $formulario->dependenciaEconomica ? [
                    'tipo' => $formulario->dependenciaEconomica->tipo_dependencia,
                    'ingreso' => $formulario->dependenciaEconomica->ingresoEconomico ? [
                        'fuente' => $formulario->dependenciaEconomica->ingresoEconomico->fuente_ingreso,
                        'monto' => $formulario->dependenciaEconomica->ingresoEconomico->monto_mensual,
                    ] : null,
                ] : null,
                'residencia' => $formulario->residencia ? [
                    'zona' => $formulario->residencia->zona,
                    'direccion' => $formulario->residencia->direccion,
                    'tipo_vivienda' => $formulario->residencia->tipo_vivienda,
                    'servicios' => [
                        'agua' => $formulario->residencia->tiene_agua_potable,
                        'luz' => $formulario->residencia->tiene_luz_electrica,
                        'alcantarillado' => $formulario->residencia->tiene_alcantarillado,
                        'internet' => $formulario->residencia->tiene_internet,
                    ],
                ] : null,
                'tenencia_vivienda' => $formulario->tenenciaVivienda ? [
                    'tipo' => $formulario->tenenciaVivienda->tipo_tenencia,
                ] : null,
            ] : null,
            'documentos' => $tramite->documentos->map(fn($d) => [
                'id' => $d->id,
                'tipo' => $d->tipo_documento,
                'nombre_archivo' => $d->nombre_archivo,
                'validado' => $d->validado,
                'fecha_subida' => $d->fecha_subida?->format('Y-m-d H:i:s'),
            ]),
            'documentos_requeridos' => [
                ['nombre' => 'Cédula de Identidad', 'obligatorio' => true],
                ['nombre' => 'Kardex Académico', 'obligatorio' => true],
                ['nombre' => 'Comprobante de Domicilio', 'obligatorio' => true],
                ['nombre' => 'Certificado de Ingresos', 'obligatorio' => false],
            ],
        ];
    }
}
