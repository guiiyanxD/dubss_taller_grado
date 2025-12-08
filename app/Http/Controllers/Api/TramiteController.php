<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tramite;
use App\Models\Estudiante;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TramiteController extends Controller
{
    /**
     * Obtener el trámite del estudiante autenticado
     *
     * GET /api/tramites/mi-tramite
     */
    public function miTramite(Request $request): JsonResponse
    {
        $user = $request->user();
        $estudiante = Estudiante::where('id_usuario', $user->id)->firstOrFail();

        $tramite = Tramite::with([
            'postulacion.beca',
            'postulacion.convocatoria',
            'estadoActual',
            'historial' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'documentos'
        ])
            ->whereHas('postulacion', function ($query) use ($estudiante) {
                $query->where('id_estudiante', $estudiante->id_usuario);
            })
            ->latest()
            ->first();

        if (!$tramite) {
            return response()->json([
                'message' => 'No tienes trámites registrados',
                'tramite' => null
            ], 404);
        }

        return response()->json([
            'tramite' => $this->formatearTramite($tramite)
        ], 200);
    }

    /**
     * Obtener trámite por código
     *
     * GET /api/tramites/{codigo}
     */
    public function porCodigo(Request $request, string $codigo): JsonResponse
    {
        $user = $request->user();
        $estudiante = Estudiante::where('id_usuario', $user->id)->firstOrFail();

        $tramite = Tramite::with([
            'postulacion.beca',
            'postulacion.convocatoria',
            'estadoActual',
            'historial',
            'documentos'
        ])
            ->where('codigo', $codigo)
            ->whereHas('postulacion', function ($query) use ($estudiante) {
                $query->where('id_estudiante', $estudiante->id_usuario);
            })
            ->firstOrFail();

        return response()->json([
            'tramite' => $this->formatearTramite($tramite)
        ], 200);
    }

    /**
     * Formatear trámite para respuesta JSON
     */
    private function formatearTramite(Tramite $tramite): array
    {
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
            'fecha_clasificacion' => $tramite->fecha_clasificacion?->format('Y-m-d H:i:s'),
            'postulacion' => [
                'estado' => $tramite->postulacion->estado_postulado,
                'puntaje_final' => $tramite->postulacion->puntaje_final,
                'posicion_ranking' => $tramite->postulacion->posicion_ranking,
                'beca' => [
                    'nombre' => $tramite->postulacion->beca->nombre,
                    'monto' => $tramite->postulacion->beca->monto,
                    'codigo' => $tramite->postulacion->beca->codigo,
                ],
                'convocatoria' => [
                    'nombre' => $tramite->postulacion->convocatoria->nombre,
                ],
            ],
            'historial' => $tramite->historial->map(function ($h) {
                return [
                    'estado_anterior' => $h->estado_anterior,
                    'estado_nuevo' => $h->estado_nuevo,
                    'observaciones' => $h->observaciones,
                    'fecha' => $h->created_at->format('Y-m-d H:i:s'),
                    'revisador' => $h->revisador ? $h->revisador->name : 'Sistema',
                ];
            }),
            'documentos' => $tramite->documentos->map(function ($d) {
                return [
                    'tipo_documento' => $d->tipo_documento,
                    'nombre_archivo' => $d->nombre_archivo,
                    'validado' => $d->validado,
                    'fecha_subida' => $d->fecha_subida?->format('Y-m-d H:i:s'),
                ];
            }),
            'proximos_pasos' => $this->obtenerProximosPasos($tramite),
        ];
    }

    /**
     * Obtener próximos pasos según el estado del trámite
     */
    private function obtenerProximosPasos(Tramite $tramite): array
    {
        $estadoNombre = $tramite->estadoActual->nombre ?? 'PENDIENTE';

        $pasos = [
            'PENDIENTE' => [
                'mensaje' => 'Presenta tu documentación física',
                'acciones' => [
                    'Acércate a las oficinas de DUBSS',
                    'Lleva tu CI original',
                    'Lleva tu Kardex académico',
                    'Lleva comprobante de domicilio',
                ],
            ],
            'EN_VALIDACION' => [
                'mensaje' => 'Tu documentación está siendo validada',
                'acciones' => [
                    'Espera la notificación de validación',
                    'Revisa tu correo electrónico',
                ],
            ],
            'VALIDADO' => [
                'mensaje' => 'Documentación validada. Esperando clasificación',
                'acciones' => [
                    'Tu formulario será evaluado',
                    'El proceso de clasificación puede tardar 2-3 días',
                ],
            ],
            'RECHAZADO' => [
                'mensaje' => 'Documentación rechazada',
                'acciones' => [
                    'Revisa las observaciones en el historial',
                    'Corrige los documentos faltantes',
                    'Acércate nuevamente a DUBSS',
                ],
            ],
            'EN_CLASIFICACION' => [
                'mensaje' => 'Tu postulación está siendo clasificada',
                'acciones' => [
                    'El sistema está calculando tu puntaje',
                    'Este proceso es automático',
                ],
            ],
            'CLASIFICADO' => [
                'mensaje' => 'Clasificación completada',
                'acciones' => [
                    'Espera los resultados finales',
                    'Revisa tu puntaje en la sección de postulaciones',
                ],
            ],
            'APROBADO' => [
                'mensaje' => '¡Felicitaciones! Tu beca fue aprobada',
                'acciones' => [
                    'Acércate a DUBSS para firmar documentos',
                    'Revisa el cronograma de pagos',
                ],
            ],
            'DENEGADO' => [
                'mensaje' => 'Tu postulación no fue aprobada',
                'acciones' => [
                    'Puedes postular en próximas convocatorias',
                    'Revisa tu puntaje y posición en el ranking',
                ],
            ],
        ];

        return $pasos[$estadoNombre] ?? [
            'mensaje' => 'Estado desconocido',
            'acciones' => [],
        ];
    }
}
