<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use App\Models\Estudiante;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    /**
     * Listar notificaciones del estudiante autenticado
     *
     * GET /api/notificaciones
     * Query params: ?solo_no_leidas=true&limit=10
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $estudiante = Estudiante::where('id_usuario', $user->id)->firstOrFail();

        $query = Notificacion::where('id_estudiante', $estudiante->id_usuario);

        // Filtrar solo no leídas si se solicita
        if ($request->boolean('solo_no_leidas')) {
            $query->where('leido', false);
        }

        // Limitar resultados
        $limit = min($request->input('limit', 50), 100); // Max 100

        $notificaciones = $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($notif) {
                return [
                    'id' => $notif->id,
                    'tipo' => $notif->tipo,
                    'titulo' => $notif->titulo,
                    'mensaje' => $notif->mensaje,
                    'leido' => $notif->leido,
                    'fecha_creacion' => $notif->created_at->format('Y-m-d H:i:s'),
                    'fecha_lectura' => $notif->fecha_lectura?->format('Y-m-d H:i:s'),
                    'tramite_codigo' => $notif->tramite?->codigo,
                    'canal' => $notif->canal,
                ];
            });

        // Contar no leídas
        $noLeidas = Notificacion::where('id_estudiante', $estudiante->id_usuario)
            ->where('leido', false)
            ->count();

        return response()->json([
            'notificaciones' => $notificaciones,
            'total' => $notificaciones->count(),
            'no_leidas' => $noLeidas,
        ], 200);
    }

    /**
     * Marcar una notificación como leída
     *
     * PUT /api/notificaciones/{id}/leer
     */
    public function marcarComoLeida(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $estudiante = Estudiante::where('id_usuario', $user->id)->firstOrFail();

        $notificacion = Notificacion::where('id_estudiante', $estudiante->id_usuario)
            ->findOrFail($id);

        if (!$notificacion->leido) {
            $notificacion->update([
                'leido' => true,
                'fecha_lectura' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Notificación marcada como leída',
            'notificacion' => [
                'id' => $notificacion->id,
                'leido' => $notificacion->leido,
                'fecha_lectura' => $notificacion->fecha_lectura->format('Y-m-d H:i:s'),
            ]
        ], 200);
    }

    /**
     * Marcar todas las notificaciones como leídas
     *
     * PUT /api/notificaciones/leer-todas
     */
    public function marcarTodasComoLeidas(Request $request): JsonResponse
    {
        $user = $request->user();
        $estudiante = Estudiante::where('id_usuario', $user->id)->firstOrFail();

        $actualizadas = Notificacion::where('id_estudiante', $estudiante->id_usuario)
            ->where('leido', false)
            ->update([
                'leido' => true,
                'fecha_lectura' => now(),
            ]);

        return response()->json([
            'message' => 'Todas las notificaciones marcadas como leídas',
            'total_actualizadas' => $actualizadas,
        ], 200);
    }

    /**
     * Eliminar una notificación
     *
     * DELETE /api/notificaciones/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $estudiante = Estudiante::where('id_usuario', $user->id)->firstOrFail();

        $notificacion = Notificacion::where('id_estudiante', $estudiante->id_usuario)
            ->findOrFail($id);

        $notificacion->delete();

        return response()->json([
            'message' => 'Notificación eliminada exitosamente'
        ], 200);
    }

    /**
     * Obtener estadísticas de notificaciones
     *
     * GET /api/notificaciones/estadisticas
     */
    public function estadisticas(Request $request): JsonResponse
    {
        $user = $request->user();
        $estudiante = Estudiante::where('id_usuario', $user->id)->firstOrFail();

        $total = Notificacion::where('id_estudiante', $estudiante->id_usuario)->count();
        $leidas = Notificacion::where('id_estudiante', $estudiante->id_usuario)
            ->where('leido', true)
            ->count();
        $noLeidas = $total - $leidas;

        $porTipo = Notificacion::where('id_estudiante', $estudiante->id_usuario)
            ->selectRaw('tipo, COUNT(*) as total')
            ->groupBy('tipo')
            ->get()
            ->pluck('total', 'tipo');

        return response()->json([
            'estadisticas' => [
                'total' => $total,
                'leidas' => $leidas,
                'no_leidas' => $noLeidas,
                'porcentaje_leidas' => $total > 0 ? round(($leidas / $total) * 100, 2) : 0,
                'por_tipo' => $porTipo,
            ]
        ], 200);
    }
}
