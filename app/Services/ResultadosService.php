<?php

namespace App\Services;

use App\Models\Postulacion;
use App\Models\Convocatoria;
use App\Models\Beca;
use App\Models\Tramite;
use App\Jobs\EnviarResultadoEmailJob;
use Illuminate\Support\Facades\DB;

class ResultadosService
{
    /**
     * Obtener estadísticas generales
     */
    public function obtenerEstadisticasGenerales(?int $convocatoriaId = null): array
    {
        $query = Postulacion::query();

        if ($convocatoriaId) {
            $query->where('id_convocatoria', $convocatoriaId);
        }

        $totalPostulaciones = $query->count();
        $aprobadas = (clone $query)->where('estado_postulado', 'APROBADO')->count();
        $denegadas = (clone $query)->where('estado_postulado', 'DENEGADO')->count();
        $enProceso = (clone $query)->where('estado_postulado', 'PENDIENTE')->count();

        $tasaAprobacion = $totalPostulaciones > 0
            ? round(($aprobadas / $totalPostulaciones) * 100, 2)
            : 0;

        $promedioPuntaje = (clone $query)
            ->whereNotNull('puntaje_final')
            ->avg('puntaje_final');

        // Distribución de puntajes (para histograma)
        $distribucionPuntajes = (clone $query)
            ->whereNotNull('puntaje_final')
            ->selectRaw('
                CASE
                    WHEN puntaje_final BETWEEN 0 AND 20 THEN "0-20"
                    WHEN puntaje_final BETWEEN 21 AND 40 THEN "21-40"
                    WHEN puntaje_final BETWEEN 41 AND 60 THEN "41-60"
                    WHEN puntaje_final BETWEEN 61 AND 80 THEN "61-80"
                    ELSE "81-100"
                END as rango,
                COUNT(*) as total
            ')
            ->groupBy('rango')
            ->get()
            ->pluck('total', 'rango')
            ->toArray();

        // Presupuesto utilizado
        $presupuestoTotal = Beca::when($convocatoriaId, function ($q) use ($convocatoriaId) {
            $q->where('id_convocatoria', $convocatoriaId);
        })->sum(DB::raw('monto * cupos_disponibles'));

        $presupuestoUtilizado = Postulacion::where('estado_postulado', 'APROBADO')
            ->when($convocatoriaId, function ($q) use ($convocatoriaId) {
                $q->where('id_convocatoria', $convocatoriaId);
            })
            ->join('beca', 'postulacion.id_beca', '=', 'beca.id')
            ->sum('beca.monto');

        // Becas con estadísticas
        $becas = Beca::when($convocatoriaId, function ($q) use ($convocatoriaId) {
            $q->where('id_convocatoria', $convocatoriaId);
        })
            ->withCount([
                'postulaciones',
                'postulaciones as aprobadas_count' => function ($q) {
                    $q->where('estado_postulado', 'APROBADO');
                },
            ])
            ->get()
            ->map(fn($b) => [
                'id' => $b->id,
                'nombre' => $b->nombre,
                'cupos' => $b->cupos_disponibles,
                'postulaciones' => $b->postulaciones_count,
                'aprobadas' => $b->aprobadas_count,
                'tasa_ocupacion' => $b->cupos_disponibles > 0
                    ? round(($b->aprobadas_count / $b->cupos_disponibles) * 100, 2)
                    : 0,
            ]);

        return [
            'total_postulaciones' => $totalPostulaciones,
            'aprobadas' => $aprobadas,
            'denegadas' => $denegadas,
            'en_proceso' => $enProceso,
            'tasa_aprobacion' => $tasaAprobacion,
            'promedio_puntaje' => round($promedioPuntaje ?? 0, 2),
            'distribucion_puntajes' => $distribucionPuntajes,
            'presupuesto_total' => $presupuestoTotal,
            'presupuesto_utilizado' => $presupuestoUtilizado,
            'becas' => $becas,
        ];
    }

    /**
     * Obtener ranking completo de una beca
     */
    public function obtenerRankingBeca(int $becaId, int $page = 1, int $perPage = 50, array $filtros = []): array
    {
        $beca = Beca::findOrFail($becaId);

        $query = Postulacion::with([
            'estudiante.user',
            'formulario.grupoFamiliar',
            'formulario.dependenciaEconomica',
            'formulario.residencia',
            'formulario.tenenciaVivienda',
        ])
            ->where('id_beca', $becaId)
            ->whereNotNull('puntaje_final');

        // Aplicar filtros
        if (!empty($filtros['estado'])) {
            $query->where('estado_postulado', $filtros['estado']);
        }

        if (!empty($filtros['carrera'])) {
            $query->whereHas('estudiante', function ($q) use ($filtros) {
                $q->where('carrera', 'LIKE', '%' . $filtros['carrera'] . '%');
            });
        }

        if (!empty($filtros['ciudad'])) {
            $query->whereHas('estudiante.user', function ($q) use ($filtros) {
                $q->where('ciudad', 'LIKE', '%' . $filtros['ciudad'] . '%');
            });
        }

        if (!empty($filtros['puntaje_min'])) {
            $query->where('puntaje_final', '>=', $filtros['puntaje_min']);
        }

        if (!empty($filtros['puntaje_max'])) {
            $query->where('puntaje_final', '<=', $filtros['puntaje_max']);
        }

        // Ordenar por puntaje descendente
        $query->orderBy('puntaje_final', 'desc');

        $postulaciones = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'beca' => [
                'id' => $beca->id,
                'nombre' => $beca->nombre,
                'codigo' => $beca->codigo,
                'cupos_disponibles' => $beca->cupos_disponibles,
            ],
            'ranking' => $postulaciones->items(),
            'pagination' => [
                'current_page' => $postulaciones->currentPage(),
                'last_page' => $postulaciones->lastPage(),
                'per_page' => $postulaciones->perPage(),
                'total' => $postulaciones->total(),
            ],
        ];
    }

    /**
     * Obtener detalle completo de una postulación
     */
    public function obtenerDetalleCompleto(int $postulacionId): ?array
    {
        $postulacion = Postulacion::with([
            'estudiante.user',
            'beca',
            'convocatoria',
            'formulario.grupoFamiliar.miembrosFamiliares',
            'formulario.dependenciaEconomica.ingresosEconomicos',
            'formulario.residencia',
            'formulario.tenenciaVivienda',
            'tramite.estadoActual',
            'tramite.historial.revisador',
            'tramite.documentos',
        ])->find($postulacionId);

        if (!$postulacion) {
            return null;
        }

        $formulario = $postulacion->formulario;

        return [
            'postulacion' => [
                'id' => $postulacion->id,
                'estado' => $postulacion->estado_postulado,
                'puntaje_final' => $postulacion->puntaje_final,
                'posicion_ranking' => $postulacion->posicion_ranking,
                'fecha_postulacion' => $postulacion->fecha_postulacion->format('Y-m-d H:i:s'),
            ],
            'estudiante' => [
                'nombre' => $postulacion->estudiante->user->name,
                'ci' => $postulacion->estudiante->user->ci,
                'email' => $postulacion->estudiante->user->email,
                'telefono' => $postulacion->estudiante->user->telefono,
                'carrera' => $postulacion->estudiante->carrera,
                'semestre' => $postulacion->estudiante->semestre,
            ],
            'beca' => [
                'nombre' => $postulacion->beca->nombre,
                'monto' => $postulacion->beca->monto,
                'cupos' => $postulacion->beca->cupos_disponibles,
            ],
            'puntajes_desglosados' => [
                'grupo_familiar' => $formulario->grupoFamiliar->puntaje_grupo_familiar ?? 0,
                'dependencia' => $formulario->dependenciaEconomica->puntaje_dependencia ?? 0,
                'ingresos' => $formulario->dependenciaEconomica->ingresosEconomicos->sum('puntaje_ingreso') ?? 0,
                'residencia' => $formulario->residencia->puntaje_residencia ?? 0,
                'tenencia' => $formulario->tenenciaVivienda->puntaje_tenencia ?? 0,
                'total' => $postulacion->puntaje_final,
            ],
            'formulario' => [
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
                    'ingresos' => $formulario->dependenciaEconomica->ingresosEconomicos->map(fn($i) => [
                        'fuente' => $i->fuente_ingreso,
                        'monto' => $i->monto_mensual,
                    ]),
                ] : null,
                'residencia' => $formulario->residencia,
                'tenencia_vivienda' => $formulario->tenenciaVivienda,
            ],
            'tramite' => $postulacion->tramite ? [
                'codigo' => $postulacion->tramite->codigo,
                'estado_actual' => $postulacion->tramite->estadoActual->nombre,
                'historial' => $postulacion->tramite->historial->map(fn($h) => [
                    'estado_anterior' => $h->estado_anterior,
                    'estado_nuevo' => $h->estado_nuevo,
                    'observaciones' => $h->observaciones,
                    'fecha' => $h->created_at->format('Y-m-d H:i:s'),
                    'revisador' => $h->revisador ? $h->revisador->name : 'Sistema',
                ]),
                'documentos' => $postulacion->tramite->documentos->map(fn($d) => [
                    'tipo' => $d->tipo_documento,
                    'nombre' => $d->nombre_archivo,
                    'fecha' => $d->fecha_subida?->format('Y-m-d H:i:s'),
                ]),
            ] : null,
        ];
    }

    /**
     * Obtener auditoría de un trámite
     */
    public function obtenerAuditoria(int $tramiteId): ?array
    {
        $tramite = Tramite::with([
            'postulacion.estudiante.user',
            'historial.revisador',
        ])->find($tramiteId);

        if (!$tramite) {
            return null;
        }

        return [
            'tramite' => [
                'codigo' => $tramite->codigo,
                'estudiante' => $tramite->postulacion->estudiante->user->name,
            ],
            'historial' => $tramite->historial->map(fn($h) => [
                'fecha' => $h->created_at->format('Y-m-d H:i:s'),
                'estado_anterior' => $h->estado_anterior,
                'estado_nuevo' => $h->estado_nuevo,
                'accion' => $this->obtenerAccion($h->estado_anterior, $h->estado_nuevo),
                'usuario' => $h->revisador ? $h->revisador->name : 'Sistema',
                'observaciones' => $h->observaciones,
            ]),
        ];
    }

    /**
     * Comparar convocatorias
     */
    public function compararConvocatorias(): array
    {
        $convocatorias = Convocatoria::withCount([
            'postulaciones',
            'postulaciones as aprobadas_count' => function ($q) {
                $q->where('estado_postulado', 'APROBADO');
            },
        ])
            ->with('becas')
            ->orderBy('fecha_inicio', 'desc')
            ->limit(5)
            ->get();

        return $convocatorias->map(fn($c) => [
            'nombre' => $c->nombre,
            'fecha_inicio' => $c->fecha_inicio->format('Y-m-d'),
            'fecha_fin' => $c->fecha_fin->format('Y-m-d'),
            'total_postulaciones' => $c->postulaciones_count,
            'aprobadas' => $c->aprobadas_count,
            'total_cupos' => $c->becas->sum('cupos_disponibles'),
            'presupuesto' => $c->becas->sum(function ($b) {
                return $b->monto * $b->cupos_disponibles;
            }),
        ])->toArray();
    }

    /**
     * Notificar resultados masivos
     */
    public function notificarResultadosMasivos(int $convocatoriaId): array
    {
        $postulaciones = Postulacion::with('estudiante.user')
            ->where('id_convocatoria', $convocatoriaId)
            ->whereIn('estado_postulado', ['APROBADO', 'DENEGADO'])
            ->get();

        $total = $postulaciones->count();

        foreach ($postulaciones as $postulacion) {
            EnviarResultadoEmailJob::dispatch($postulacion);
        }

        $tiempoEstimado = ceil($total / 60); // 60 emails por minuto

        return [
            'total' => $total,
            'tiempo_estimado' => "{$tiempoEstimado} minutos",
        ];
    }

    /**
     * Obtener convocatorias disponibles
     */
    public function obtenerConvocatoriasDisponibles(): array
    {
        return Convocatoria::orderBy('fecha_inicio', 'desc')
            ->get(['id', 'nombre'])
            ->toArray();
    }

    /**
     * Obtener estadísticas filtradas
     */
    public function obtenerEstadisticasFiltradas(array $filtros): array
    {
        return $this->obtenerEstadisticasGenerales($filtros['convocatoria_id'] ?? null);
    }

    /**
     * Helper: Obtener descripción de la acción
     */
    private function obtenerAccion(?string $estadoAnterior, string $estadoNuevo): string
    {
        $acciones = [
            'PENDIENTE' => 'Trámite creado',
            'EN_VALIDACION' => 'Validación iniciada',
            'VALIDADO' => 'Documentos aprobados',
            'RECHAZADO' => 'Documentos rechazados',
            'EN_DIGITALIZACION' => 'Digitalización iniciada',
            'DIGITALIZADO' => 'Digitalización completada',
            'EN_CLASIFICACION' => 'Clasificación iniciada',
            'CLASIFICADO' => 'Clasificación completada',
            'APROBADO' => 'Beca aprobada',
            'DENEGADO' => 'Beca denegada',
        ];

        return $acciones[$estadoNuevo] ?? 'Cambio de estado';
    }
}
