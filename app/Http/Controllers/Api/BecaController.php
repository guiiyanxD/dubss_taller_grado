<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Beca;
use App\Models\Convocatoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BecaController extends Controller
{
    /**
     * Listar becas disponibles de convocatorias activas
     *
     * GET /api/becas
     * Query params: ?convocatoria_id=1
     */
    public function index(Request $request): JsonResponse
    {
        $query = Beca::with(['convocatoria', 'requisitos'])
            ->whereHas('convocatoria', function ($q) {
                $q->where('fecha_inicio', '<=', now())
                  ->where('fecha_fin', '>=', now());
            });

        // Filtrar por convocatoria si se especifica
        if ($request->has('convocatoria_id')) {
            $query->where('id_convocatoria', $request->convocatoria_id);
        }

        $becas = $query->get()->map(function ($beca) {
            return [
                'id' => $beca->id,
                'codigo' => $beca->codigo,
                'nombre' => $beca->nombre,
                'descripcion' => $beca->descripcion,
                'cupos_disponibles' => $beca->cupos_disponibles,
                'monto' => $beca->monto,
                'duracion_meses' => $beca->duracion_meses,
                'tipo_beca' => $beca->tipo_beca,
                'convocatoria' => [
                    'id' => $beca->convocatoria->id,
                    'nombre' => $beca->convocatoria->nombre,
                    'fecha_inicio' => $beca->convocatoria->fecha_inicio->format('Y-m-d'),
                    'fecha_fin' => $beca->convocatoria->fecha_fin->format('Y-m-d'),
                ],
                'requisitos' => $beca->requisitos->map(function ($req) {
                    return [
                        'id' => $req->id,
                        'nombre' => $req->nombre,
                        'descripcion' => $req->descripcion,
                    ];
                }),
                'cupos_restantes' => $this->calcularCuposRestantes($beca),
            ];
        });

        return response()->json([
            'becas' => $becas,
            'total' => $becas->count(),
        ], 200);
    }

    /**
     * Obtener detalle de una beca específica
     *
     * GET /api/becas/{id}
     */
    public function show(int $id): JsonResponse
    {
        $beca = Beca::with(['convocatoria', 'requisitos', 'postulaciones'])
            ->findOrFail($id);

        // Verificar que la convocatoria esté activa
        if (!$this->convocatoriaActiva($beca->convocatoria)) {
            return response()->json([
                'message' => 'Esta beca no está disponible actualmente'
            ], 404);
        }

        return response()->json([
            'beca' => [
                'id' => $beca->id,
                'codigo' => $beca->codigo,
                'nombre' => $beca->nombre,
                'descripcion' => $beca->descripcion,
                'cupos_disponibles' => $beca->cupos_disponibles,
                'monto' => $beca->monto,
                'duracion_meses' => $beca->duracion_meses,
                'tipo_beca' => $beca->tipo_beca,
                'convocatoria' => [
                    'id' => $beca->convocatoria->id,
                    'nombre' => $beca->convocatoria->nombre,
                    'descripcion' => $beca->convocatoria->descripcion,
                    'fecha_inicio' => $beca->convocatoria->fecha_inicio->format('Y-m-d'),
                    'fecha_fin' => $beca->convocatoria->fecha_fin->format('Y-m-d'),
                    'dias_restantes' => now()->diffInDays($beca->convocatoria->fecha_fin),
                ],
                'requisitos' => $beca->requisitos->map(function ($req) {
                    return [
                        'id' => $req->id,
                        'nombre' => $req->nombre,
                        'descripcion' => $req->descripcion,
                    ];
                }),
                'estadisticas' => [
                    'cupos_totales' => $beca->cupos_disponibles,
                    'cupos_restantes' => $this->calcularCuposRestantes($beca),
                    'total_postulaciones' => $beca->postulaciones->count(),
                    'porcentaje_ocupacion' => $this->calcularPorcentajeOcupacion($beca),
                ],
            ]
        ], 200);
    }

    /**
     * Calcular cupos restantes
     */
    private function calcularCuposRestantes(Beca $beca): int
    {
        $postulacionesAprobadas = $beca->postulaciones()
            ->where('estado_postulado', 'APROBADO')
            ->count();

        return max(0, $beca->cupos_disponibles - $postulacionesAprobadas);
    }

    /**
     * Calcular porcentaje de ocupación
     */
    private function calcularPorcentajeOcupacion(Beca $beca): float
    {
        if ($beca->cupos_disponibles == 0) return 0;

        $postulacionesAprobadas = $beca->postulaciones()
            ->where('estado_postulado', 'APROBADO')
            ->count();

        return round(($postulacionesAprobadas / $beca->cupos_disponibles) * 100, 2);
    }

    /**
     * Verificar si una convocatoria está activa
     */
    private function convocatoriaActiva(Convocatoria $convocatoria): bool
    {
        return $convocatoria->fecha_inicio <= now()
            && $convocatoria->fecha_fin >= now();
    }
}
