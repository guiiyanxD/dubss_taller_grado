<?php

namespace App\Services;

use App\Models\Postulacion;
use App\Models\Convocatoria;
use App\Models\Beca;
use Illuminate\Support\Facades\DB;

/**
 * Servicio para obtener estadísticas y resultados de postulaciones
 *
 * CU-A3: Visualizar Resultados de Clasificación
 */
class ResultadosService
{

    public function obtenerConvocatoriasDisponibles(){
        return Convocatoria::orderBy('created_at', 'desc')->get(['id', 'nombre']);
    }
    /**
     * Obtener estadísticas del dashboard de resultados
     */
    public function obtenerEstadisticasDashboard(?int $convocatoriaId = null): array
    {
        // Si no se especifica convocatoria, usar la activa
        //if (!$convocatoriaId) {
        //    $convocatoria = Convocatoria::where('estado', 'ACTIVA')->first();
        //    $convocatoriaId = $convocatoria?->id;
        //}

        if (!$convocatoriaId) {
            return $this->estadisticasVacias();
        }

        // Query base
        $query = Postulacion::where('id_convocatoria', $convocatoriaId);

        // Totales
        $totalPostulaciones = (clone $query)->count();
        $totalAprobadas = (clone $query)->where('estado_postulado', 'APROBADO')->count();
        $totalDenegadas = (clone $query)->where('estado_postulado', 'DENEGADO')->count();
        $totalPendientes = (clone $query)->whereNull('estado_postulado')->count();

        // Promedios
        $promedioPuntaje = (clone $query)->whereNotNull('puntaje_final')->avg('puntaje_final') ?? 0;

        // Distribución por puntajes
        // CORRECCIÓN: Usar comillas simples (') en lugar de comillas dobles (")
        // PostgreSQL usa " para identificadores de columna, ' para strings
        $distribucionPuntajes = (clone $query)
            ->whereNotNull('puntaje_final')
            ->selectRaw("
                CASE
                    WHEN puntaje_final BETWEEN 0 AND 20 THEN '0-20'
                    WHEN puntaje_final BETWEEN 21 AND 40 THEN '21-40'
                    WHEN puntaje_final BETWEEN 41 AND 60 THEN '41-60'
                    WHEN puntaje_final BETWEEN 61 AND 80 THEN '61-80'
                    ELSE '81-100'
                END as rango,
                COUNT(*) as total
            ")
            ->groupBy('rango')
            ->get()
            ->pluck('total', 'rango')
            ->toArray();


        $distribucionCompleta = [
            '0-20' => $distribucionPuntajes['0-20'] ?? 0,
            '21-40' => $distribucionPuntajes['21-40'] ?? 0,
            '41-60' => $distribucionPuntajes['41-60'] ?? 0,
            '61-80' => $distribucionPuntajes['61-80'] ?? 0,
            '81-100' => $distribucionPuntajes['81-100'] ?? 0,
        ];


        $presupuestoEjecutado = Postulacion::where('id_convocatoria', $convocatoriaId)
            ->where('estado_postulado', 'APROBADO')
            ->join('beca', 'postulacion.id_beca', '=', 'beca.id');
//            ->sum('beca.monto');


        $tasaAprobacion = $totalPostulaciones > 0
            ? round(($totalAprobadas / $totalPostulaciones) * 100, 1)
            : 0;

        return [
            'total_postulaciones' => $totalPostulaciones,
            'total_aprobadas' => $totalAprobadas,
            'total_denegadas' => $totalDenegadas,
            'total_pendientes' => $totalPendientes,
            'promedio_puntaje' => round($promedioPuntaje, 2),
            'distribucion_puntajes' => $distribucionCompleta,
            //'presupuesto_ejecutado' => round($presupuestoEjecutado, 2),
            'tasa_aprobacion' => $tasaAprobacion,
        ];
    }

    /**
     * Obtener ranking de una beca específica
     */
    public function obtenerRankingBeca(int $becaId): array
    {
        $beca = Beca::with('convocatoria')->findOrFail($becaId);

        $postulaciones = Postulacion::where('id_beca', $becaId)
            ->with([
                'estudiante' => function($query) {
                    $query->select('id', 'ci', 'nombre_completo', 'carrera', 'email');
                },
                'tramite.estadoActual' => function($query) {
                    $query->select('id', 'nombre');
                },
            ])
            ->whereNotNull('puntaje_final')
            ->orderBy('puntaje_final', 'desc')
            ->get()
            ->map(function ($postulacion, $index) use ($beca) {
                $posicion = $index + 1;
                $resultado = $posicion <= $beca->cupos_disponibles ? 'APROBADO' : 'DENEGADO';

                return [
                    'id' => $postulacion->id,
                    'posicion' => $posicion,
                    'estudiante' => [
                        'ci' => $postulacion->estudiante->ci,
                        'nombre_completo' => $postulacion->estudiante->nombre_completo,
                        'carrera' => $postulacion->estudiante->carrera,
                        'email' => $postulacion->estudiante->email,
                    ],
                    'puntaje_final' => $postulacion->puntaje_final,
                    'ranking' => $postulacion->ranking,
                    'resultado' => $resultado,
                    'estado_tramite' => $postulacion->tramite?->estadoActual?->nombre ?? 'SIN TRÁMITE',
                    'fecha_postulacion' => $postulacion->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return [
            'beca' => [
                'id' => $beca->id,
                'nombre' => $beca->nombre,
                'descripcion' => $beca->descripcion,
                'monto' => $beca->monto,
                'cupos_disponibles' => $beca->cupos_disponibles,
                'convocatoria' => [
                    'id' => $beca->convocatoria->id,
                    'nombre' => $beca->convocatoria->nombre,
                ],
            ],
            'ranking' => $postulaciones->toArray(),
            'total_postulantes' => $postulaciones->count(),
            'aprobados' => $postulaciones->where('resultado', 'APROBADO')->count(),
            'denegados' => $postulaciones->where('resultado', 'DENEGADO')->count(),
        ];
    }

    /**
     * Obtener estadísticas por beca de una convocatoria
     */
    public function obtenerEstadisticasPorBeca(int $convocatoriaId): array
    {
        $becas = Beca::where('id_convocatoria', $convocatoriaId)
            ->with(['postulaciones' => function($query) {
                $query->select('id', 'id_beca', 'estado_postulado', 'puntaje_final');
            }])
            ->get()
            ->map(function ($beca) {
                $postulaciones = $beca->postulaciones;
                $aprobadas = $postulaciones->where('estado_postulado', 'APROBADO')->count();
                $denegadas = $postulaciones->where('estado_postulado', 'DENEGADO')->count();
                $pendientes = $postulaciones->whereNull('estado_postulado')->count();
                $promedioPuntaje = $postulaciones->whereNotNull('puntaje_final')->avg('puntaje_final') ?? 0;

                $tasaOcupacion = $beca->cupos_disponibles > 0
                    ? round(($aprobadas / $beca->cupos_disponibles) * 100, 1)
                    : 0;

                $presupuestoEjecutado = $aprobadas * $beca->monto;

                return [
                    'id' => $beca->id,
                    'nombre' => $beca->nombre,
                    'monto' => $beca->monto,
                    'cupos_disponibles' => $beca->cupos_disponibles,
                    'total_postulantes' => $postulaciones->count(),
                    'aprobadas' => $aprobadas,
                    'denegadas' => $denegadas,
                    'pendientes' => $pendientes,
                    'promedio_puntaje' => round($promedioPuntaje, 2),
                    'tasa_ocupacion' => $tasaOcupacion,
                    'presupuesto_ejecutado' => $presupuestoEjecutado,
                ];
            });

        return $becas->toArray();
    }

    /**
     * Obtener distribución demográfica de beneficiarios
     */
    public function obtenerDistribucionDemografica(int $convocatoriaId): array
    {

        $porCarrera = Postulacion::where('id_convocatoria', $convocatoriaId)
            ->where('estado_postulado', 'APROBADO')
            ->join('estudiante', 'postulacion.id_estudiante', '=', 'estudiante.id')
            ->selectRaw('estudiante.carrera, COUNT(*) as total')
            ->groupBy('estudiante.carrera')
            ->orderBy('total', 'desc')
            ->get()
            ->pluck('total', 'carrera')
            ->toArray();


        $porGenero = [];
        try {
            $porGenero = Postulacion::where('id_convocatoria', $convocatoriaId)
                ->where('estado_postulado', 'APROBADO')
                ->join('estudiante', 'postulacion.id_estudiante', '=', 'estudiante.id')
                ->selectRaw('estudiante.genero, COUNT(*) as total')
                ->groupBy('estudiante.genero')
                ->get()
                ->pluck('total', 'genero')
                ->toArray();
        } catch (\Exception $e) {
            // Si no existe la columna género, ignorar
            \Log::warning('Columna genero no existe en tabla estudiante');
        }

        return [
            'por_carrera' => $porCarrera,
            'por_genero' => $porGenero,
        ];
    }

    /**
     * Comparar convocatorias
     */
    public function compararConvocatorias(array $convocatoriaIds): array
    {
        $comparaciones = [];

        foreach ($convocatoriaIds as $convocatoriaId) {
            $convocatoria = Convocatoria::find($convocatoriaId);

            if (!$convocatoria) {
                continue;
            }

            $query = Postulacion::where('id_convocatoria', $convocatoriaId);

            $comparaciones[] = [
                'convocatoria' => [
                    'id' => $convocatoria->id,
                    'nombre' => $convocatoria->nombre,
                ],
                'total_postulaciones' => (clone $query)->count(),
                'total_aprobadas' => (clone $query)->where('estado_postulado', 'APROBADO')->count(),
                'promedio_puntaje' => round((clone $query)->whereNotNull('puntaje_final')->avg('puntaje_final') ?? 0, 2),
                'presupuesto_ejecutado' => Postulacion::where('id_convocatoria', $convocatoriaId)
                    ->where('estado_postulado', 'APROBADO')
                    ->join('beca', 'postulacion.id_beca', '=', 'beca.id')
                    ->sum('beca.monto'),
            ];
        }

        return $comparaciones;
    }

    /**
     * Estadísticas vacías (cuando no hay convocatoria)
     */
    private function estadisticasVacias(): array
    {
        return [
            'total_postulaciones' => 0,
            'total_aprobadas' => 0,
            'total_denegadas' => 0,
            'total_pendientes' => 0,
            'promedio_puntaje' => 0,
            'distribucion_puntajes' => [
                '0-20' => 0,
                '21-40' => 0,
                '41-60' => 0,
                '61-80' => 0,
                '81-100' => 0,
            ],
            'presupuesto_ejecutado' => 0,
            'tasa_aprobacion' => 0,
        ];
    }
}
