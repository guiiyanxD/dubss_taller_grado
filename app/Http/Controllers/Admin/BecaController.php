<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BecaService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Beca;
use Inertia\Response;


class BecaController extends Controller
{
    public function __construct(private BecaService $becaService) {}

    public function index(Request $request): Response
    {
        $filtros = [
            'convocatoria_id' => $request->input('convocatoria_id'),
            'busqueda' => $request->input('busqueda'),
        ];

        return Inertia::render('Admin/Becas/Index', [
            'becas' => $this->becaService->listar($filtros),
            'filtros' => $filtros,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Becas/Create');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'id_convocatoria' => 'required|exists:convocatoria,id',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'monto' => 'required|numeric|min:0',
            'cupos_disponibles' => 'required|integer|min:1',
            'requisitos' => 'nullable|array',
            'requisitos.*' => 'exists:requisito,id',
        ]);

        try {
            $beca = $this->becaService->crear($validated);
            return redirect()->route('admin.becas.show', $beca->id)
                ->with('success', 'Beca creada exitosamente');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(int $id): Response
    {
        return Inertia::render('Admin/Becas/Show', [
            'beca' => $this->becaService->obtener($id),
            'estadisticas' => $this->becaService->obtenerEstadisticas($id),
        ]);
    }

    public function edit(int $id): Response
    {
        return Inertia::render('Admin/Becas/Edit', [
            'beca' => $this->becaService->obtener($id),
        ]);
    }

    public function update(Request $request, int $id): Response
    {
        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'monto' => 'sometimes|required|numeric|min:0',
            'cupos_disponibles' => 'sometimes|required|integer|min:1',
            'requisitos' => 'nullable|array',
            'requisitos.*' => 'exists:requisito,id',
        ]);

        try {
            $this->becaService->actualizar($id, $validated);
            return Inertia::location(route('admin.becas.show', $id));
        } catch (\Exception $e) {
            return Inertia::render('Admin/Becas/Edit', [
                'beca' => $this->becaService->obtener($id),
            ])->with('error', $e->getMessage());
        }
    }

    public function destroy(int $id): \Illuminate\Http\RedirectResponse
    {
        try {
            $this->becaService->eliminar($id);
            return redirect()->route('admin.becas.index')
                ->with('success', 'Beca eliminada exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
