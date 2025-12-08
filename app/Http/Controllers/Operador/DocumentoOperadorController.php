<?php

namespace App\Http\Controllers\Operador;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operador\UploadDocumentoRequest;
use App\Services\DocumentoOperadorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class DocumentoOperadorController extends Controller
{
    protected $documentoService;

    public function __construct(DocumentoOperadorService $documentoService)
    {
        $this->documentoService = $documentoService;
        $this->middleware(['auth', 'role:Operador|Dpto. Sistema']);
    }

    /**
     * Mostrar pantalla de digitalización
     *
     * GET /operador/tramites/{id}/digitalizar
     */
    public function mostrarDigitalizacion(int $tramiteId): Response
    {
        $tramite = $this->documentoService->obtenerParaDigitalizacion($tramiteId);

        if (!$tramite) {
            return redirect()->route('operador.tramites.validados')
                ->with('error', 'Trámite no encontrado o no está validado');
        }

        return Inertia::render('Operador/DigitalizarTramite', [
            'tramite' => $tramite,
        ]);
    }

    /**
     * Subir un documento digitalizado
     *
     * POST /operador/documentos/upload
     */
    public function upload(UploadDocumentoRequest $request): JsonResponse
    {
        try {
            $documento = $this->documentoService->uploadDocumento(
                $request->tramite_id,
                $request->tipo_documento,
                $request->file('archivo'),
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Documento subido correctamente',
                'documento' => $documento,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir documento: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar un documento digitalizado
     *
     * DELETE /operador/documentos/{id}
     */
    public function eliminar(int $id): JsonResponse
    {
        try {
            $this->documentoService->eliminarDocumento($id, auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'Documento eliminado correctamente',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar documento: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Completar digitalización de un trámite
     *
     * PUT /operador/tramites/{id}/completar-digitalizacion
     */
    public function completarDigitalizacion(int $tramiteId): JsonResponse
    {
        try {
            $resultado = $this->documentoService->completarDigitalizacion(
                $tramiteId,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => $resultado['message'],
                'tramite' => $resultado['tramite'],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al completar digitalización: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ver expediente digitalizado completo
     *
     * GET /operador/tramites/{id}/expediente
     */
    public function verExpediente(int $tramiteId): Response
    {
        $expediente = $this->documentoService->obtenerExpedienteCompleto($tramiteId);

        if (!$expediente) {
            return redirect()->route('operador.dashboard')
                ->with('error', 'Expediente no encontrado');
        }

        return Inertia::render('Operador/VerExpediente', [
            'expediente' => $expediente,
        ]);
    }

    /**
     * Descargar un documento
     *
     * GET /operador/documentos/{id}/descargar
     */
    public function descargar(int $id)
    {
        try {
            return $this->documentoService->descargarDocumento($id);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al descargar documento: ' . $e->getMessage());
        }
    }

    /**
     * Ver documento (preview en navegador)
     *
     * GET /operador/documentos/{id}/ver
     */
    public function ver(int $id)
    {
        try {
            return $this->documentoService->verDocumento($id);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al visualizar documento: ' . $e->getMessage());
        }
    }
}
