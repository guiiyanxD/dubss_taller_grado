<?php

namespace App\Services;

use App\Models\Beca;
use App\Models\Convocatoria;
use App\Models\Requisito;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Servicio para gestión de Becas
 *
 * CU-A6: Gestionar Becas
 */
class BecaService
{
    /**
     * Obtener becas paginadas con filtros
     */

    public function listar(array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Beca::query()
            ->with(['convocatoria']) // Quitamos 'requisitos' de aquí porque solo queremos contarlos
            ->withCount(['postulaciones', 'requisitos']); // SQL cuenta mucho más rápido


        if (!empty($filtros['convocatoria_id'])) {
            $query->where('id_convocatoria', $filtros['convocatoria_id']);
        }


        if (!empty($filtros['busqueda'])) {
            $busqueda = '%' . $filtros['busqueda'] . '%';
            $query->where(function($q) use ($busqueda) {
                $q->where('nombre', 'ILIKE', $busqueda)
                  ->orWhereHas('convocatoria', function($qConv) use ($busqueda) {
                      $qConv->where('nombre', 'ILIKE', $busqueda);
                  });
            });
        }

        $becas = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
        return $becas->through(function ($beca) {
            return [
                'id' => $beca->id,
                'nombre' => $beca->nombre,
                'descripcion' => $beca->descripcion,
                'monto' => $beca->monto,
                'cupos_disponibles' => $beca->cupos_disponibles,
                'convocatoria' => [
                    'id' => $beca->convocatoria->id,
                    'nombre' => $beca->convocatoria->nombre,
                    'estado' => $beca->convocatoria->estado,
                ],

                'total_requisitos' => $beca->requisitos_count,
                'total_postulaciones' => $beca->postulaciones_count,
                'created_at' => $beca->created_at->format('Y-m-d H:i:s'),
            ];
        });
    }

    /**
     * Obtener una beca con sus requisitos
     */
    public function obtener(int $id): array
    {
        $beca = Beca::with(['convocatoria', 'requisitos', 'postulaciones'])->findOrFail($id);

        return [
            'id' => $beca->id,
            'nombre' => $beca->nombre,
            'descripcion' => $beca->descripcion,
            'monto' => $beca->monto,
            'cupos_disponibles' => $beca->cupos_disponibles,
            'convocatoria' => [
                'id' => $beca->convocatoria->id,
                'nombre' => $beca->convocatoria->nombre,
                'estado' => $beca->convocatoria->estado,
                'fecha_inicio' => $beca->convocatoria->fecha_inicio->format('Y-m-d'),
                'fecha_fin' => $beca->convocatoria->fecha_fin->format('Y-m-d'),
            ],
            'requisitos' => $beca->requisitos->map(function ($requisito) {
                return [
                    'id' => $requisito->id,
                    'nombre' => $requisito->nombre,
                    'descripcion' => $requisito->descripcion,
                    'tipo' => $requisito->tipo,
                    'obligatorio' => $requisito->obligatorio,
                ];
            }),
            'total_postulaciones' => $beca->postulaciones->count(),
            'created_at' => $beca->created_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Crear una nueva beca
     */
    public function crear(array $datos): Beca
    {
        DB::beginTransaction();

        try {
            // Validar que la convocatoria existe y no está finalizada
            $convocatoria = Convocatoria::findOrFail($datos['id_convocatoria']);

            if ($convocatoria->estado === 'FINALIZADA') {
                throw new \Exception('No se pueden agregar becas a una convocatoria finalizada');
            }

            $beca = Beca::create([
                'id_convocatoria' => $datos['id_convocatoria'],
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
                'monto' => $datos['monto'],
                'cupos_disponibles' => $datos['cupos_disponibles'],
            ]);

            // Asociar requisitos si se enviaron
            if (isset($datos['requisitos']) && is_array($datos['requisitos'])) {
                $beca->requisitos()->sync($datos['requisitos']);
            }

            Log::info('Beca creada', [
                'beca_id' => $beca->id,
                'nombre' => $beca->nombre,
                'convocatoria_id' => $datos['id_convocatoria'],
            ]);

            DB::commit();

            return $beca->fresh(['requisitos']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear beca: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Actualizar una beca
     */
    public function actualizar(int $id, array $datos): Beca
    {
        DB::beginTransaction();

        try {
            $beca = Beca::with('convocatoria')->findOrFail($id);

            // No permitir editar si la convocatoria está finalizada
            if ($beca->convocatoria->estado === 'FINALIZADA') {
                throw new \Exception('No se puede editar una beca de una convocatoria finalizada');
            }

            // No permitir reducir cupos si ya hay más postulaciones aprobadas
            if (isset($datos['cupos_disponibles'])) {
                $aprobadas = $beca->postulaciones()->where('estado_postulado', 'APROBADO')->count();
                if ($datos['cupos_disponibles'] < $aprobadas) {
                    throw new \Exception("No se pueden reducir los cupos por debajo de {$aprobadas} (postulaciones ya aprobadas)");
                }
            }

            $beca->update($datos);

            // Actualizar requisitos si se enviaron
            if (isset($datos['requisitos']) && is_array($datos['requisitos'])) {
                $beca->requisitos()->sync($datos['requisitos']);
            }

            Log::info('Beca actualizada', [
                'beca_id' => $beca->id,
                'cambios' => $datos,
            ]);

            DB::commit();

            return $beca->fresh(['requisitos']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar beca: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Eliminar una beca
     */
    public function eliminar(int $id): bool
    {
        DB::beginTransaction();

        try {
            $beca = Beca::with('convocatoria')->findOrFail($id);

            // No permitir eliminar si tiene postulaciones
            if ($beca->postulaciones()->count() > 0) {
                throw new \Exception('No se puede eliminar una beca con postulaciones');
            }

            // No permitir eliminar si la convocatoria está activa
            if ($beca->convocatoria->estado === 'ACTIVA') {
                throw new \Exception('No se puede eliminar una beca de una convocatoria activa');
            }

            // Desasociar requisitos
            $beca->requisitos()->detach();

            $beca->delete();

            Log::info('Beca eliminada', [
                'beca_id' => $id,
            ]);

            DB::commit();

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar beca: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Asociar requisitos a una beca
     */
    public function asociarRequisitos(int $becaId, array $requisitosIds): Beca
    {
        DB::beginTransaction();

        try {
            $beca = Beca::findOrFail($becaId);

            // Validar que los requisitos existen
            $requisitosExistentes = Requisito::whereIn('id', $requisitosIds)->count();
            if ($requisitosExistentes !== count($requisitosIds)) {
                throw new \Exception('Algunos requisitos no existen');
            }

            $beca->requisitos()->sync($requisitosIds);

            Log::info('Requisitos asociados a beca', [
                'beca_id' => $becaId,
                'requisitos_count' => count($requisitosIds),
            ]);

            DB::commit();

            return $beca->fresh(['requisitos']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al asociar requisitos: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener estadísticas de una beca
     */
    public function obtenerEstadisticas(int $id): array
    {
        $beca = Beca::with('postulaciones')->findOrFail($id);

        $totalPostulaciones = $beca->postulaciones->count();
        $aprobadas = $beca->postulaciones->where('estado_postulado', 'APROBADO')->count();
        $denegadas = $beca->postulaciones->where('estado_postulado', 'DENEGADO')->count();
        $pendientes = $totalPostulaciones - $aprobadas - $denegadas;

        $cuposOcupados = $aprobadas;
        $cuposDisponibles = $beca->cupos_disponibles - $cuposOcupados;
        $tasaOcupacion = $beca->cupos_disponibles > 0
            ? round(($cuposOcupados / $beca->cupos_disponibles) * 100, 1)
            : 0;

        $presupuestoEjecutado = 0;
        $presupuestoTotal = 0;

        return [
            'total_postulaciones' => $totalPostulaciones,
            'aprobadas' => $aprobadas,
            'denegadas' => $denegadas,
            'pendientes' => $pendientes,
            'cupos_totales' => $beca->cupos_disponibles,
            'cupos_ocupados' => $cuposOcupados,
            'cupos_disponibles' => $cuposDisponibles,
            'tasa_ocupacion' => $tasaOcupacion,
            'presupuesto_total' => $presupuestoTotal,
            'presupuesto_ejecutado' => $presupuestoEjecutado,
        ];
    }
}
