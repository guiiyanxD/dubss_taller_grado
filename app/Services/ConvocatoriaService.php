<?php

namespace App\Services;

use App\Models\Convocatoria;
use App\Models\Beca;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
/**
 * Servicio para gestión de Convocatorias
 *
 * CU-A5: Gestionar Convocatorias
 */
class ConvocatoriaService
{

    /**
     * Obtiene la lista de becas disponibles para una convocatoria específica.
     * @param int $convocatoriaId
     * @return Collection
     */
    public function getBecasByConvocatoria(int $convocatoriaId): Collection
    {
        $convocatoria = Convocatoria::with('becas:id,nombre')->find($convocatoriaId);

        if (!$convocatoria) {
            return collect();
        }

        return $convocatoria->becas->map(function ($beca) {
            return [
                'id' => $beca->id,
                'nombre' => $beca->nombre,
            ];
        });
    }

    /**
     * Obtener convocatorias paginadas con filtros
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function listar(array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Convocatoria::query()
            ->withCount(['becas', 'postulaciones']);

        if (isset($filtros['estado']) && $filtros['estado'] !== '') {
            $query->where('estado', $filtros['estado']);
        }

        if (isset($filtros['anio'])) {
            $query->whereYear('fecha_inicio', $filtros['anio']);
        }

        if (isset($filtros['busqueda'])) {
            // Se usa 'nombre' ILIKE (para PostgreSQL) o LIKE (en MySQL/MariaDB)
            $query->where('nombre', 'ILIKE', '%' . $filtros['busqueda'] . '%');
        }

        $paginator = $query->orderBy('fecha_inicio', 'desc')->paginate($perPage);

        return $paginator->through(function ($convocatoria) {
            return [
                'id' => $convocatoria->id,
                'nombre' => $convocatoria->nombre,
                'descripcion' => $convocatoria->descripcion,
                'fecha_inicio' => $convocatoria->fecha_inicio->format('Y-m-d'),
                'fecha_fin' => $convocatoria->fecha_fin->format('Y-m-d'),
                'estado' => $convocatoria->estado,
                // Usamos los campos _count generados por withCount
                'total_becas' => $convocatoria->becas_count,
                'total_postulaciones' => $convocatoria->postulaciones_count,
                'created_at' => $convocatoria->created_at->format('Y-m-d H:i:s'),
            ];
        });
    }

    /**
     * Obtener una convocatoria con todas sus relaciones
     */
    public function obtener(int $id): array
    {
        $convocatoria = Convocatoria::with([
            'becas.requisitos',
            'postulaciones',
        ])->findOrFail($id);

        return [
            'id' => $convocatoria->id,
            'nombre' => $convocatoria->nombre,
            'descripcion' => $convocatoria->descripcion,
            'fecha_inicio' => $convocatoria->fecha_inicio->format('Y-m-d'),
            'fecha_fin' => $convocatoria->fecha_fin->format('Y-m-d'),
            'estado' => $convocatoria->estado,
            'becas' => $convocatoria->becas->map(function ($beca) {
                return [
                    'id' => $beca->id,
                    'nombre' => $beca->nombre,
                    'monto' => $beca->monto,
                    'cupos_disponibles' => $beca->cupos_disponibles,
                    'requisitos_count' => $beca->requisitos->count(),
                ];
            }),
            'total_postulaciones' => $convocatoria->postulaciones->count(),
            'created_at' => $convocatoria->created_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Crear una nueva convocatoria
     */
    public function crear(array $datos): Convocatoria
    {
        DB::beginTransaction();

        try {
            // Validar que no haya otra convocatoria activa en las mismas fechas
            $this->validarFechas($datos['fecha_inicio'], $datos['fecha_fin']);

            $convocatoria = Convocatoria::create([
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
                'fecha_inicio' => $datos['fecha_inicio'],
                'fecha_fin' => $datos['fecha_fin'],
                'estado' => $datos['estado'] ?? 'BORRADOR',
            ]);

            Log::info('Convocatoria creada', [
                'convocatoria_id' => $convocatoria->id,
                'nombre' => $convocatoria->nombre,
            ]);

            DB::commit();

            return $convocatoria;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear convocatoria: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Actualizar una convocatoria
     */
    public function actualizar(int $id, array $datos): Convocatoria
    {
        DB::beginTransaction();

        try {
            $convocatoria = Convocatoria::findOrFail($id);

            if ($convocatoria->estado === 'FINALIZADA') {
                throw new \Exception('No se puede editar una convocatoria finalizada');
            }

            if (isset($datos['fecha_inicio']) || isset($datos['fecha_fin'])) {
                $fechaInicio = $datos['fecha_inicio'] ?? $convocatoria->fecha_inicio->format('Y-m-d');
                $fechaFin = $datos['fecha_fin'] ?? $convocatoria->fecha_fin->format('Y-m-d');
                $this->validarFechas($fechaInicio, $fechaFin, $id);
            }

            $convocatoria->update($datos);

            Log::info('Convocatoria actualizada', [
                'convocatoria_id' => $convocatoria->id,
                'cambios' => $datos,
            ]);

            DB::commit();

            return $convocatoria->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar convocatoria: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Eliminar una convocatoria
     */
    public function eliminar(int $id): bool
    {
        DB::beginTransaction();

        try {
            $convocatoria = Convocatoria::findOrFail($id);

            if ($convocatoria->postulaciones()->count() > 0) {
                throw new \Exception('No se puede eliminar una convocatoria con postulaciones');
            }

            if ($convocatoria->estado === 'ACTIVA') {
                throw new \Exception('No se puede eliminar una convocatoria activa');
            }

            $convocatoria->delete();

            Log::info('Convocatoria eliminada', [
                'convocatoria_id' => $id,
            ]);

            DB::commit();

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar convocatoria: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Activar una convocatoria
     */
    public function activar(int $id): Convocatoria
    {
        DB::beginTransaction();

        try {
            $convocatoria = Convocatoria::findOrFail($id);

            if ($convocatoria->becas()->count() === 0) {
                throw new \Exception('La convocatoria debe tener al menos una beca para activarse');
            }

            Convocatoria::where('estado', 'ACTIVA')
                ->where('id', '!=', $id)
                ->update(['estado' => 'FINALIZADA']);

            $convocatoria->update(['estado' => 'ACTIVA']);

            Log::info('Convocatoria activada', [
                'convocatoria_id' => $id,
            ]);

            DB::commit();

            return $convocatoria->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al activar convocatoria: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Finalizar una convocatoria
     */
    public function finalizar(int $id): Convocatoria
    {
        DB::beginTransaction();

        try {
            $convocatoria = Convocatoria::findOrFail($id);

            $convocatoria->update(['estado' => 'FINALIZADA']);

            Log::info('Convocatoria finalizada', [
                'convocatoria_id' => $id,
            ]);

            DB::commit();

            return $convocatoria->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al finalizar convocatoria: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validar que las fechas no se solapen con otras convocatorias activas
     */
    private function validarFechas(string $fechaInicio, string $fechaFin, ?int $convocatoriaId = null): void
    {
        if ($fechaFin <= $fechaInicio) {
            throw new \Exception('La fecha de fin debe ser posterior a la fecha de inicio');
        }

        $query = Convocatoria::where('estado', 'ACTIVA')
            ->where(function ($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
                  ->orWhereBetween('fecha_fin', [$fechaInicio, $fechaFin])
                  ->orWhere(function ($q2) use ($fechaInicio, $fechaFin) {
                      $q2->where('fecha_inicio', '<=', $fechaInicio)
                         ->where('fecha_fin', '>=', $fechaFin);
                  });
            });

        if ($convocatoriaId) {
            $query->where('id', '!=', $convocatoriaId);
        }

        if ($query->exists()) {
            throw new \Exception('Las fechas se solapan con otra convocatoria activa');
        }
    }

    public function obtenerEstadisticas(int $id): array
    {
        $convocatoria = Convocatoria::query()
            ->where('id', $id)
            ->withCount([
                'postulaciones', // Total de postulaciones
                'postulaciones as aprobadas_count' => function ($query) {
                    $query->where('estado_postulado', 'APROBADO');
                },
                'postulaciones as denegadas_count' => function ($query) {
                    $query->where('estado_postulado', 'DENEGADO');
                }
            ])
            ->withSum('becas', 'cupos_disponibles') // Suma de cupos disponibles
            ->withCasts(['presupuesto_total' => 'float'])
            ->addSelect([
                'presupuesto_total' => Beca::selectRaw('COALESCE(SUM(monto * cupos_disponibles), 0)')
                    ->whereColumn('convocatoria_id', 'convocatorias.id')
                    ->limit(1)
            ])
            ->firstOrFail();

        $convocatoria->loadCount('becas');
        $pendientes = $convocatoria->postulaciones_count - $convocatoria->aprobadas_count - $convocatoria->denegadas_count;
        return [
            // Resultados basados en withCount y withSum
            'total_postulaciones' => $convocatoria->postulaciones_count,
            'aprobadas' => $convocatoria->aprobadas_count,
            'denegadas' => $convocatoria->denegadas_count,
            'pendientes' => $pendientes,
            'total_becas' => $convocatoria->becas_count,
            'cupos_totales' => (int) $convocatoria->becas_sum_cupos_disponibles,
            'presupuesto_total' => (float) $convocatoria->presupuesto_total,
        ];
    }
}
