<?php

namespace App\Services;

use App\Models\Requisito;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class RequisitoService
{
    /**
     * Obtener todos los requisitos con filtros y paginación
     * * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    // CAMBIO 1: Agregamos $perPage y cambiamos el tipo de retorno a LengthAwarePaginator
    public function listar(array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Requisito::query()->withCount('becas');

        // Filtro por tipo
        if (isset($filtros['tipo']) && $filtros['tipo'] !== '') {
            $query->where('tipo', $filtros['tipo']);
        }

        // Filtro por obligatorio (Aseguramos que solo se aplique si está presente y no es nulo)
        if (isset($filtros['obligatorio']) && $filtros['obligatorio'] !== null) {
            // El valor 1/0 o true/false debe pasarse directamente
            $query->where('obligatorio', (bool)$filtros['obligatorio']);
        }

        // Búsqueda por nombre
        if (isset($filtros['busqueda'])) {
            $query->where('nombre', 'ILIKE', '%' . $filtros['busqueda'] . '%');
        }

        // CAMBIO 2: Aplicamos paginación
        $paginator = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // CAMBIO 3: Usamos ->through() para mapear SOLO los datos de la página actual
        return $paginator->through(function ($requisito) {
            return [
                'id' => $requisito->id,
                'nombre' => $requisito->nombre,
                'descripcion' => $requisito->descripcion,
                'tipo' => $requisito->tipo,
                'obligatorio' => $requisito->obligatorio,
                'total_becas' => $requisito->becas_count, // Eficiente gracias a withCount
                'created_at' => $requisito->created_at->format('Y-m-d H:i:s'),
            ];
        });
    }

    /**
     * Obtener un requisito con sus becas asociadas
     */
    public function obtener(int $id): array
    {
        $requisito = Requisito::with('becas.convocatoria')->findOrFail($id);

        return [
            'id' => $requisito->id,
            'nombre' => $requisito->nombre,
            'descripcion' => $requisito->descripcion,
            'tipo' => $requisito->tipo,
            'obligatorio' => $requisito->obligatorio,
            'becas' => $requisito->becas->map(function ($beca) {
                return [
                    'id' => $beca->id,
                    'nombre' => $beca->nombre,
                    'convocatoria' => [
                        'id' => $beca->convocatoria->id,
                        'nombre' => $beca->convocatoria->nombre,
                        'estado' => $beca->convocatoria->estado,
                    ],
                ];
            }),
            'created_at' => $requisito->created_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Crear un nuevo requisito
     */
    public function crear(array $datos): Requisito
    {
        DB::beginTransaction();

        try {
            $requisito = Requisito::create([
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
                'tipo' => $datos['tipo'],
                'obligatorio' => $datos['obligatorio'] ?? true,
            ]);

            Log::info('Requisito creado', [
                'requisito_id' => $requisito->id,
                'nombre' => $requisito->nombre,
                'tipo' => $requisito->tipo,
            ]);

            DB::commit();

            return $requisito;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear requisito: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Actualizar un requisito
     */
    public function actualizar(int $id, array $datos): Requisito
    {
        DB::beginTransaction();

        try {
            $requisito = Requisito::findOrFail($id);

            // No permitir cambiar tipo si ya está asociado a becas activas
            if (isset($datos['tipo']) && $datos['tipo'] !== $requisito->tipo) {
                $becasActivas = $requisito->becas()
                    ->whereHas('convocatoria', function ($query) {
                        $query->where('estado', 'ACTIVA');
                    })
                    ->count();

                if ($becasActivas > 0) {
                    throw new \Exception('No se puede cambiar el tipo de un requisito asociado a becas activas');
                }
            }

            $requisito->update($datos);

            Log::info('Requisito actualizado', [
                'requisito_id' => $requisito->id,
                'cambios' => $datos,
            ]);

            DB::commit();

            return $requisito->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar requisito: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Eliminar un requisito
     */
    public function eliminar(int $id): bool
    {
        DB::beginTransaction();

        try {
            $requisito = Requisito::findOrFail($id);

            // No permitir eliminar si está asociado a becas
            if ($requisito->becas()->count() > 0) {
                throw new \Exception('No se puede eliminar un requisito asociado a becas');
            }

            $requisito->delete();

            Log::info('Requisito eliminado', [
                'requisito_id' => $id,
            ]);

            DB::commit();

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar requisito: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener tipos de requisitos disponibles
     */
    public function obtenerTipos(): array
    {
        return [
            'DOCUMENTO' => 'Documento (PDF, imagen)',
            'INFORMACION' => 'Información textual',
            'CERTIFICADO' => 'Certificado oficial',
            'DECLARACION_JURADA' => 'Declaración jurada',
        ];
    }
}
