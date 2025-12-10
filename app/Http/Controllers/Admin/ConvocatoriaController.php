<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\ConvocatoriaService;
use App\Models\Convocatoria;

class ConvocatoriaController extends Controller
{
    public function __construct(
        private ConvocatoriaService $convocatoriaService
    ) {}

    public function index(Request $request): Response
    {
        $filtros = $request->only(['busqueda', 'estado']);
        $perPage = 15;
        $convocatorias = $this->convocatoriaService->listar($filtros, $perPage);
        return Inertia::render('Admin/Convocatorias/Index', [

            'filtros' => $filtros,
            'convocatorias' => $convocatorias,
        ]);
    }

    /**
     * GET /admin/convocatorias/crear
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Convocatorias/Create');
    }

    /**
     * POST /admin/convocatorias
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'estado' => 'required|in:BORRADOR,ACTIVA,FINALIZADA',
        ]);

        try {
            $convocatoria = $this->convocatoriaService->crear($validated);

            return redirect()
                ->route('admin.convocatorias.show', $convocatoria->id)
                ->with('success', 'Convocatoria creada exitosamente');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * GET /admin/convocatorias/{id}
     */
    public function show(int $id): Response
    {
        $convocatoria = $this->convocatoriaService->obtener($id);
        $estadisticas = $this->convocatoriaService->obtenerEstadisticas($id);

        return Inertia::render('Admin/Convocatorias/Show', [
            'convocatoria' => $convocatoria,
            'estadisticas' => $estadisticas,
        ]);
    }

    /**
     * GET /admin/convocatorias/{id}/editar
     */
    public function edit(int $id): Response
    {
        $convocatoria = $this->convocatoriaService->obtener($id);

        return Inertia::render('Admin/Convocatorias/Edit', [
            'convocatoria' => $convocatoria,
        ]);
    }

    /**
     * PUT /admin/convocatorias/{id}
     */
    public function update(Request $request, int $id): Response
    {
        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'sometimes|required|date',
            'fecha_fin' => 'sometimes|required|date|after:fecha_inicio',
            'estado' => 'sometimes|required|in:BORRADOR,ACTIVA,FINALIZADA',
        ]);

        try {
            $this->convocatoriaService->actualizar($id, $validated);

            // Usar Inertia::location() para evitar error 303
            return Inertia::location(route('admin.convocatorias.show', $id));

        } catch (\Exception $e) {
            $convocatoria = $this->convocatoriaService->obtener($id);

            return Inertia::render('Admin/Convocatorias/Edit', [
                'convocatoria' => $convocatoria,
            ])->with('error', $e->getMessage());
        }
    }

    /**
     * DELETE /admin/convocatorias/{id}
     */
    public function destroy(int $id): \Illuminate\Http\RedirectResponse
    {
        try {
            $this->convocatoriaService->eliminar($id);

            return redirect()
                ->route('admin.convocatorias.index')
                ->with('success', 'Convocatoria eliminada exitosamente');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * POST /admin/convocatorias/{id}/activar
     */
    public function activar(int $id): Response
    {
        try {
            $this->convocatoriaService->activar($id);

            return Inertia::location(route('admin.convocatorias.show', $id));

        } catch (\Exception $e) {
            $convocatoria = $this->convocatoriaService->obtener($id);
            $estadisticas = $this->convocatoriaService->obtenerEstadisticas($id);

            return Inertia::render('Admin/Convocatorias/Show', [
                'convocatoria' => $convocatoria,
                'estadisticas' => $estadisticas,
            ])->with('error', $e->getMessage());
        }
    }

    /**
     * POST /admin/convocatorias/{id}/finalizar
     */
    public function finalizar(int $id): Response
    {
        try {
            $this->convocatoriaService->finalizar($id);

            return Inertia::location(route('admin.convocatorias.show', $id));

        } catch (\Exception $e) {
            $convocatoria = $this->convocatoriaService->obtener($id);
            $estadisticas = $this->convocatoriaService->obtenerEstadisticas($id);

            return Inertia::render('Admin/Convocatorias/Show', [
                'convocatoria' => $convocatoria,
                'estadisticas' => $estadisticas,
            ])->with('error', $e->getMessage());
        }
    }
}
