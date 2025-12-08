<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ResultadosService;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\JsonResponse;

class AdminResultadosController extends Controller
{
    protected $resultadosService;
    protected $exportService;

    public function __construct(
        ResultadosService $resultadosService,
        ExportService $exportService
    ) {
        $this->resultadosService = $resultadosService;
        $this->exportService = $exportService;
        $this->middleware(['auth', 'role:Dpto. Sistema|Dirección']);
    }

    /**
     * Dashboard general de resultados
     *
     * GET /admin/resultados/dashboard
     */
    public function dashboard(Request $request): Response
    {
        $convocatoriaId = $request->input('convocatoria_id');

        $estadisticas = $this->resultadosService->obtenerEstadisticasGenerales($convocatoriaId);
        $convocatorias = $this->resultadosService->obtenerConvocatoriasDisponibles();

        return Inertia::render('Admin/Resultados/Dashboard', [
            'estadisticas' => $estadisticas,
            'convocatorias' => $convocatorias,
            'convocatoria_seleccionada' => $convocatoriaId,
        ]);
    }

    /**
     * Ver ranking completo de una beca
     *
     * GET /admin/becas/{id}/ranking
     */
    public function rankingBeca(int $becaId, Request $request): Response
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 50);
        $filtros = $request->only(['estado', 'carrera', 'ciudad', 'puntaje_min', 'puntaje_max']);

        $ranking = $this->resultadosService->obtenerRankingBeca($becaId, $page, $perPage, $filtros);

        return Inertia::render('Admin/Resultados/RankingBeca', [
            'ranking' => $ranking,
            'filtros' => $filtros,
        ]);
    }

    /**
     * Ver detalle completo de una postulación
     *
     * GET /admin/postulaciones/{id}/detalle
     */
    public function detallePostulacion(int $postulacionId): Response
    {
        $detalle = $this->resultadosService->obtenerDetalleCompleto($postulacionId);

        if (!$detalle) {
            return redirect()->route('admin.resultados.dashboard')
                ->with('error', 'Postulación no encontrada');
        }

        return Inertia::render('Admin/Resultados/DetallePostulacion', [
            'postulacion' => $detalle,
        ]);
    }

    /**
     * Ver auditoría completa de un trámite
     *
     * GET /admin/tramites/{id}/auditoria
     */
    public function auditoriaTramite(int $tramiteId): Response
    {
        $auditoria = $this->resultadosService->obtenerAuditoria($tramiteId);

        if (!$auditoria) {
            return redirect()->route('admin.resultados.dashboard')
                ->with('error', 'Trámite no encontrado');
        }

        return Inertia::render('Admin/Resultados/AuditoriaTramite', [
            'auditoria' => $auditoria,
        ]);
    }

    /**
     * Exportar reporte de resultados
     *
     * POST /admin/resultados/exportar
     */
    public function exportar(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:ranking_completo,aprobados,denegados,estadisticas',
            'beca_id' => 'required_if:tipo,ranking_completo,aprobados,denegados|integer',
            'formato' => 'required|in:xlsx,pdf,csv',
            'incluir_detalles' => 'boolean',
        ]);

        try {
            $archivo = $this->exportService->generarReporte(
                $request->tipo,
                $request->formato,
                [
                    'beca_id' => $request->beca_id,
                    'incluir_detalles' => $request->boolean('incluir_detalles'),
                ]
            );

            return response()->download($archivo['path'], $archivo['nombre'])->deleteFileAfterSend();

        } catch (\Exception $e) {
            return back()->with('error', 'Error al exportar reporte: ' . $e->getMessage());
        }
    }

    /**
     * Comparar múltiples convocatorias
     *
     * GET /admin/resultados/comparacion
     */
    public function comparacionConvocatorias(): Response
    {
        $comparacion = $this->resultadosService->compararConvocatorias();

        return Inertia::render('Admin/Resultados/ComparacionConvocatorias', [
            'comparacion' => $comparacion,
        ]);
    }

    /**
     * Enviar notificaciones masivas de resultados
     *
     * POST /admin/resultados/notificar
     */
    public function notificarResultados(Request $request): JsonResponse
    {
        $request->validate([
            'convocatoria_id' => 'required|integer|exists:convocatoria,id',
        ]);

        try {
            $resultado = $this->resultadosService->notificarResultadosMasivos($request->convocatoria_id);

            return response()->json([
                'success' => true,
                'message' => 'Notificaciones encoladas exitosamente',
                'emails_encolados' => $resultado['total'],
                'tiempo_estimado' => $resultado['tiempo_estimado'],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al encolar notificaciones: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener estadísticas filtradas (AJAX)
     *
     * GET /admin/resultados/estadisticas-filtradas
     */
    public function estadisticasFiltradas(Request $request): JsonResponse
    {
        $filtros = $request->only(['convocatoria_id', 'beca_id', 'fecha_inicio', 'fecha_fin']);

        $estadisticas = $this->resultadosService->obtenerEstadisticasFiltradas($filtros);

        return response()->json([
            'success' => true,
            'estadisticas' => $estadisticas,
        ], 200);
    }
}
