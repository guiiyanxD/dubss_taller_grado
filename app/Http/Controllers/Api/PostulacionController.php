<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PostulacionRequest;
use App\Models\Postulacion;
use App\Models\Tramite;
use App\Models\Estudiante;
use App\Models\Beca;
use App\Models\FormularioSocioEconomico;
use App\Models\EstadoTramite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostulacionController extends Controller
{
    /**
     * Listar postulaciones del estudiante autenticado
     *
     * GET /api/postulaciones
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $estudiante = Estudiante::where('id_usuario', $user->id)->firstOrFail();

        $postulaciones = Postulacion::with(['beca', 'convocatoria', 'tramite.estadoActual'])
            ->where('id_estudiante', $estudiante->id_usuario)
            ->orderBy('fecha_postulacion', 'desc')
            ->get()
            ->map(function ($postulacion) {
                return [
                    'id' => $postulacion->id,
                    'fecha_postulacion' => $postulacion->fecha_postulacion->format('Y-m-d H:i:s'),
                    'estado' => $postulacion->estado_postulado,
                    'puntaje_final' => $postulacion->puntaje_final,
                    'posicion_ranking' => $postulacion->posicion_ranking,
                    'beca' => [
                        'id' => $postulacion->beca->id,
                        'codigo' => $postulacion->beca->codigo,
                        'nombre' => $postulacion->beca->nombre,
                        'monto' => $postulacion->beca->monto,
                    ],
                    'convocatoria' => [
                        'id' => $postulacion->convocatoria->id,
                        'nombre' => $postulacion->convocatoria->nombre,
                    ],
                    'tramite' => $postulacion->tramite ? [
                        'codigo' => $postulacion->tramite->codigo,
                        'estado_actual' => $postulacion->tramite->estadoActual->nombre ?? 'PENDIENTE',
                        'clasificado' => $postulacion->tramite->clasificado === 'SI',
                    ] : null,
                ];
            });

        return response()->json([
            'postulaciones' => $postulaciones,
            'total' => $postulaciones->count(),
        ], 200);
    }

    /**
     * Obtener detalle de una postulación
     *
     * GET /api/postulaciones/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $estudiante = Estudiante::where('id_usuario', $user->id)->firstOrFail();

        $postulacion = Postulacion::with([
            'beca.requisitos',
            'convocatoria',
            'tramite.estadoActual',
            'tramite.historial.revisador'
        ])
            ->where('id_estudiante', $estudiante->id_usuario)
            ->findOrFail($id);

        return response()->json([
            'postulacion' => [
                'id' => $postulacion->id,
                'fecha_postulacion' => $postulacion->fecha_postulacion->format('Y-m-d H:i:s'),
                'estado' => $postulacion->estado_postulado,
                'puntaje_final' => $postulacion->puntaje_final,
                'posicion_ranking' => $postulacion->posicion_ranking,
                'fecha_clasificacion' => $postulacion->fecha_clasificacion?->format('Y-m-d H:i:s'),
                'beca' => [
                    'id' => $postulacion->beca->id,
                    'codigo' => $postulacion->beca->codigo,
                    'nombre' => $postulacion->beca->nombre,
                    'descripcion' => $postulacion->beca->descripcion,
                    'monto' => $postulacion->beca->monto,
                    'duracion_meses' => $postulacion->beca->duracion_meses,
                    'requisitos' => $postulacion->beca->requisitos->map(fn($r) => [
                        'nombre' => $r->nombre,
                        'descripcion' => $r->descripcion,
                    ]),
                ],
                'convocatoria' => [
                    'id' => $postulacion->convocatoria->id,
                    'nombre' => $postulacion->convocatoria->nombre,
                    'fecha_inicio' => $postulacion->convocatoria->fecha_inicio->format('Y-m-d'),
                    'fecha_fin' => $postulacion->convocatoria->fecha_fin->format('Y-m-d'),
                ],
                'tramite' => $postulacion->tramite ? [
                    'codigo' => $postulacion->tramite->codigo,
                    'estado_actual' => $postulacion->tramite->estadoActual->nombre ?? 'PENDIENTE',
                    'clasificado' => $postulacion->tramite->clasificado === 'SI',
                    'fecha_creacion' => $postulacion->tramite->created_at->format('Y-m-d H:i:s'),
                    'historial' => $postulacion->tramite->historial->map(fn($h) => [
                        'estado_anterior' => $h->estado_anterior,
                        'estado_nuevo' => $h->estado_nuevo,
                        'observaciones' => $h->observaciones,
                        'fecha' => $h->created_at->format('Y-m-d H:i:s'),
                        'revisador' => $h->revisador ? $h->revisador->name : 'Sistema',
                    ]),
                ] : null,
            ]
        ], 200);
    }

    /**
     * Crear una nueva postulación
     *
     * POST /api/postulaciones
     */
    public function store(PostulacionRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            $estudiante = Estudiante::where('id_usuario', $user->id)->firstOrFail();

            // Verificar que el formulario esté completo
            $formulario = FormularioSocioEconomico::where('id_estudiante', $estudiante->id_usuario)
                ->where('completado', true)
                ->firstOrFail();

            // Verificar que la beca exista y tenga cupos
            $beca = Beca::findOrFail($request->id_beca);

            if ($this->calcularCuposRestantes($beca) <= 0) {
                return response()->json([
                    'message' => 'Esta beca ya no tiene cupos disponibles'
                ], 400);
            }

            // Verificar que no tenga una postulación previa a la misma beca
            $postulacionExistente = Postulacion::where('id_estudiante', $estudiante->id_usuario)
                ->where('id_beca', $request->id_beca)
                ->first();

            if ($postulacionExistente) {
                return response()->json([
                    'message' => 'Ya tienes una postulación a esta beca'
                ], 400);
            }

            // Crear postulación
            $postulacion = Postulacion::create([
                'id_estudiante' => $estudiante->id_usuario,
                'id_beca' => $request->id_beca,
                'id_convocatoria' => $beca->id_convocatoria,
                'id_formulario' => $formulario->id,
                'estado_postulado' => 'PENDIENTE',
                'fecha_postulacion' => now(),
                'creado_por' => $user->id,
            ]);

            // Crear trámite asociado
            $estadoPendiente = EstadoTramite::where('nombre', 'PENDIENTE')->first();

            $tramite = Tramite::create([
                'id_postulacion' => $postulacion->id,
                'codigo' => $this->generarCodigoTramite(),
                'estado_actual' => $estadoPendiente->id,
                'clasificado' => 'NO',
            ]);

            // Registrar en historial
            $tramite->historial()->create([
                'estado_anterior' => null,
                'estado_nuevo' => 'PENDIENTE',
                'observaciones' => 'Trámite creado automáticamente',
                'revisador_por' => $user->id,
                'fecha_revision' => now(),
            ]);

            DB::commit();

            // Recargar con relaciones
            $postulacion->load(['beca', 'convocatoria', 'tramite']);

            return response()->json([
                'message' => 'Postulación creada exitosamente',
                'postulacion' => [
                    'id' => $postulacion->id,
                    'fecha_postulacion' => $postulacion->fecha_postulacion->format('Y-m-d H:i:s'),
                    'estado' => $postulacion->estado_postulado,
                    'beca' => [
                        'nombre' => $postulacion->beca->nombre,
                        'monto' => $postulacion->beca->monto,
                    ],
                    'tramite' => [
                        'codigo' => $tramite->codigo,
                        'mensaje' => 'Presenta tu documentación física en las oficinas de DUBSS'
                    ]
                ]
            ], 201);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Debes completar tu formulario socioeconómico antes de postular'
            ], 400);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al crear postulación',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Generar código único de trámite
     */
    private function generarCodigoTramite(): string
    {
        do {
            $codigo = 'TRM-' . strtoupper(Str::random(8));
        } while (Tramite::where('codigo', $codigo)->exists());

        return $codigo;
    }

    /**
     * Calcular cupos restantes
     */
    private function calcularCuposRestantes(Beca $beca): int
    {
        $postulacionesAprobadas = Postulacion::where('id_beca', $beca->id)
            ->where('estado_postulado', 'APROBADO')
            ->count();

        return max(0, $beca->cupos_disponibles - $postulacionesAprobadas);
    }
}
