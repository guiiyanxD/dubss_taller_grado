<?php

namespace App\Http\Controllers\Operador;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operador\ValidarTramiteRequest;
use App\Services\TramiteOperadorService;
use Illuminate\Http\Request;
use App\Models\Tramite;
use Inertia\Inertia;
use Inertia\Response;
//use Illuminate\Http\Response;

class TramiteOperadorController extends Controller
{
    protected $tramiteService;

    public function __construct(TramiteOperadorService $tramiteService)
    {
        $this->tramiteService = $tramiteService;
        //$this->middleware(['auth', 'role:Operador|Dpto. Sistema']);
    }

    /**
     * Dashboard del operador
     *
     * GET /operador/dashboard
     */
    public function dashboard(): Response
    {
        $estadisticas = $this->tramiteService->obtenerEstadisticasOperador(auth()->id());

        return Inertia::render('Operador/Dashboard', [
            'estadisticas' => $estadisticas,
        ]);
    }

    /**
     * Buscar trámite por CI del estudiante
     *
     * GET /operador/tramites/buscar?ci=1234567
     */
    public function buscarPorCI(Request $request): Response
    {
        $request->validate([
            'ci' => 'required|string|max:20',
        ]);

        $tramite = $this->tramiteService->buscarPorCI($request->ci);

        if (!$tramite) {
            return Inertia::render('Operador/BuscarTramite', [
                'error' => 'No se encontró ningún trámite para el CI: ' . $request->ci,
                'ci' => $request->ci,
            ]);
        }

        return Inertia::render('Operador/ValidarTramite', [
            'tramite' => $tramite,
        ]);
    }

    /**
     * Mostrar formulario de búsqueda
     *
     * GET /operador/tramites/buscar
     */
    public function mostrarBusqueda(): Response
    {
        return Inertia::render('Operador/BuscarTramite');
    }

    /**
     * Listar trámites pendientes de validación
     *
     * GET /operador/tramites/pendientes
     */
    public function pendientes(Request $request): Response
    {
        $tramites = $this->tramiteService->obtenerTramitesPendientes();

        return Inertia::render('Operador/TramitesPendientes', [
            'tramites' => $tramites, // Ahora es un array directo, no un objeto paginado
        ]);
    }

    /**
     * Ver detalle de un trámite para validación
     *
     * GET /operador/tramites/{id}/validar
     */
    public function mostrarValidacion(int $id): Response
    {
        $tramiteDatos = $this->tramiteService->obtenerParaValidacion($id);

        return Inertia::render('Operador/ValidarTramite', [
            'tramite' => $tramiteDatos,
        ]);
    }

    /**
     * Validar documentación del trámite
     *
     * PUT /operador/tramites/{id}/validar
     */
    public function validar(ValidarTramiteRequest $request, int $id): Response
    {
        try {
            $resultado = $this->tramiteService->validarTramite(
                tramiteId: $id,
                accion: $request->input('accion'),
                documentosValidados: $request->input('documentos_validados', []),
                observaciones: $request->input('observaciones'),
                operadorId: auth()->id()
            );

            // Opción A: Usar Inertia::location() para redirect externo
            return Inertia::location(route('operador.dashboard'));

            // O Opción B: Renderizar directamente la vista con datos actualizados
            // $estadisticas = $this->tramiteService->obtenerEstadisticasOperador(auth()->id());
            // return Inertia::render('Operador/Dashboard', [
            //     'estadisticas' => $estadisticas,
            // ])->with('success', $resultado['message']);

        } catch (\Exception $e) {
            return Inertia::render('Operador/ValidarTramite', [
                'tramite' => Tramite::with([
                    'postulacion.estudiante',
                    'postulacion.beca',
                    'estadoActual',
                    'documentos',
                ])->findOrFail($id),
                'error' => 'Error al validar trámite: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Listar trámites validados (listos para digitalizar)
     *
     * GET /operador/tramites/validados
     */
    public function validados(Request $request): Response
    {
        $tramites = $this->tramiteService->obtenerTramitesValidados(
            $request->input('page', 1),
            $request->input('per_page', 15)
        );

        return Inertia::render('Operador/TramitesValidados', [
            'tramites' => $tramites,
        ]);
    }

    /**
     * Ver historial de trámites procesados por el operador
     *
     * GET /operador/tramites/historial
     */
    public function historial(Request $request): Response
    {
        $tramites = $this->tramiteService->obtenerHistorialOperador(
            auth()->id(),
            $request->input('page', 1),
            $request->input('per_page', 15)
        );

        return Inertia::render('Operador/Historial', [
            'tramites' => $tramites,
        ]);
    }

    /**
     * Ver detalle completo de un trámite
     *
     * GET /operador/tramites/{id}/detalle
     */
    public function detalle(int $id): Response
    {
        $tramite = $this->tramiteService->obtenerDetalleCompleto($id);

        if (!$tramite) {
            return redirect()->route('operador.dashboard')
                ->with('error', 'Trámite no encontrado');
        }

        return Inertia::render('Operador/DetalleTramite', [
            'tramite' => $tramite,
        ]);
    }
}
