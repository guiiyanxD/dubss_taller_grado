<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ExportService;
use App\Models\Convocatoria;
use App\Models\Beca;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Controller para gestionar exportación de reportes
 *
 * CU-A4: Exportar Reportes
 */
class AdminReportesController extends Controller
{
    public function __construct(
        private ExportService $exportService
    ) {}

    /**
     * Mostrar página principal de reportes
     */
    public function index(): Response
    {
        $convocatorias = Convocatoria::with('becas')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Admin/Reportes/Index', [
            'convocatorias' => $convocatorias,
        ]);
    }

    /**
     * Exportar ranking de beca a Excel
     */
    public function exportarRankingExcel(Request $request): BinaryFileResponse
    {
        $request->validate([
            'beca_id' => 'required|exists:beca,id',
        ]);

        try {
            $fileName = $this->exportService->exportarRankingExcel($request->input('beca_id'));
            $filePath = storage_path("app/public/exports/{$fileName}");

            return response()->download($filePath, $fileName)->deleteFileAfterSend();

        } catch (\Exception $e) {
            \Log::error('Error al exportar ranking Excel: ' . $e->getMessage());

            return back()->with('error', 'Error al generar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Exportar ranking de beca a PDF
     */
    public function exportarRankingPDF(Request $request): BinaryFileResponse
    {
        $request->validate([
            'beca_id' => 'required|exists:beca,id',
        ]);

        try {
            $fileName = $this->exportService->exportarRankingPDF($request->input('beca_id'));
            $filePath = storage_path("app/public/exports/{$fileName}");

            return response()->download($filePath, $fileName)->deleteFileAfterSend();

        } catch (\Exception $e) {
            \Log::error('Error al exportar ranking PDF: ' . $e->getMessage());

            return back()->with('error', 'Error al generar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Exportar estadísticas generales a Excel
     */
    public function exportarEstadisticasExcel(Request $request): BinaryFileResponse
    {
        $request->validate([
            'convocatoria_id' => 'required|exists:convocatoria,id',
        ]);

        try {
            $fileName = $this->exportService->exportarEstadisticasExcel($request->input('convocatoria_id'));
            $filePath = storage_path("app/public/exports/{$fileName}");

            return response()->download($filePath, $fileName)->deleteFileAfterSend();

        } catch (\Exception $e) {
            \Log::error('Error al exportar estadísticas Excel: ' . $e->getMessage());

            return back()->with('error', 'Error al generar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Exportar nómina de aprobados (para pagos)
     */
    public function exportarNominaAprobados(Request $request): BinaryFileResponse
    {
        $request->validate([
            'convocatoria_id' => 'required|exists:convocatoria,id',
        ]);

        try {
            $fileName = $this->exportService->exportarNominaAprobados($request->input('convocatoria_id'));
            $filePath = storage_path("app/public/exports/{$fileName}");

            return response()->download($filePath, $fileName)->deleteFileAfterSend();

        } catch (\Exception $e) {
            \Log::error('Error al exportar nómina: ' . $e->getMessage());

            return back()->with('error', 'Error al generar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Limpiar archivos antiguos (tarea de mantenimiento)
     */
    public function limpiarArchivosAntiguos(): \Illuminate\Http\JsonResponse
    {
        try {
            $eliminados = $this->exportService->limpiarExportacionesAntiguas();

            return response()->json([
                'success' => true,
                'message' => "Se eliminaron {$eliminados} archivo(s) antiguo(s)",
                'eliminados' => $eliminados,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al limpiar archivos: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar archivos: ' . $e->getMessage(),
            ], 500);
        }
    }
}
