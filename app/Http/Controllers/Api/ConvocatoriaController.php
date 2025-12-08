<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Convocatoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConvocatoriaController extends Controller
{
    /**
     * Listar convocatorias activas
     *
     * GET /api/convocatorias
     * Query params: ?incluir_cerradas=true
     */
    public function index(Request $request): JsonResponse
    {
        $query = Convocatoria::with(['becas']);

        // Por defecto, solo convocatorias activas
        if (!$request->boolean('incluir_cerradas')) {
            $query->where('fecha_inicio', '<=', now())
                  ->where('fecha_fin', '>=', now());
        }

        $convocatorias = $query->orderBy('fecha_inicio', 'desc')
            ->get()
            ->map(function ($convocatoria) {
                return [
                    'id' => $convocatoria->id,
                    'nombre' => $convocatoria->nombre,
                    'descripcion' => $convocatoria->descripcion,
                    'fecha_inicio' => $convocatoria->fecha_inicio->format('Y-m-d'),
                    'fecha_fin' => $convocatoria->fecha_fin->format('Y-m-d'),
                    'estado' => $this->obtenerEstado($convocatoria),
                    'dias_restantes' => $this->calcularDiasRestantes($convocatoria),
                    'total_becas' => $convocatoria->becas->count(),
                    'total_cupos' => $convocatoria->becas->sum('cupos_disponibles'),
                ];
            });

        return response()->json([
            'convocatorias' => $convocatorias,
            'total' => $convocatorias->count(),
        ], 200);
    }

    /**
     * Obtener detalle de una convocatoria
     *
     * GET /api/convocatorias/{id}
     */
    public function show(int $id): JsonResponse
    {
        $convocatoria = Convocatoria::with(['becas.requisitos'])
            ->findOrFail($id);

        return response()->json([
            'convocatoria' => [
                'id' => $convocatoria->id,
                'nombre' => $convocatoria->nombre,
                'descripcion' => $convocatoria->descripcion,
                'fecha_inicio' => $convocatoria->fecha_inicio->format('Y-m-d'),
                'fecha_fin' => $convocatoria->fecha_fin->format('Y-m-d'),
                'estado' => $this->obtenerEstado($convocatoria),
                'dias_restantes' => $this->calcularDiasRestantes($convocatoria),
                'becas' => $convocatoria->becas->map(function ($beca) {
                    return [
                        'id' => $beca->id,
                        'codigo' => $beca->codigo,
                        'nombre' => $beca->nombre,
                        'descripcion' => $beca->descripcion,
                        'cupos_disponibles' => $beca->cupos_disponibles,
                        'monto' => $beca->monto,
                        'duracion_meses' => $beca->duracion_meses,
                        'tipo_beca' => $beca->tipo_beca,
                        'requisitos_count' => $beca->requisitos->count(),
                    ];
                }),
                'estadisticas' => [
                    'total_becas' => $convocatoria->becas->count(),
                    'total_cupos' => $convocatoria->becas->sum('cupos_disponibles'),
                    'monto_total' => $convocatoria->becas->sum(function ($beca) {
                        return $beca->monto * $beca->cupos_disponibles;
                    }),
                ],
            ]
        ], 200);
    }

    /**
     * Obtener estado de la convocatoria
     */
    private function obtenerEstado(Convocatoria $convocatoria): string
    {
        $ahora = now();

        if ($ahora < $convocatoria->fecha_inicio) {
            return 'PROXIMA';
        } elseif ($ahora > $convocatoria->fecha_fin) {
            return 'CERRADA';
        } else {
            return 'ACTIVA';
        }
    }

    /**
     * Calcular días restantes
     */
    private function calcularDiasRestantes(Convocatoria $convocatoria): ?int
    {
        $ahora = now();

        if ($ahora > $convocatoria->fecha_fin) {
            return null; // Ya cerró
        }

        if ($ahora < $convocatoria->fecha_inicio) {
            return $ahora->diffInDays($convocatoria->fecha_inicio); // Días para que inicie
        }

        return $ahora->diffInDays($convocatoria->fecha_fin); // Días para que cierre
    }
}
