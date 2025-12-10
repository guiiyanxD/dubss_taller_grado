<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Requisito;
use App\Services\RequisitoService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RequisitoController extends Controller
{
    public function __construct(private RequisitoService $requisitoService) {}

    // CAMBIO 1: Obtener filtros, incluyendo 'obligatorio' como booleano o nulo.
    public function index(Request $request): Response
    {
        $filtros = [
            'tipo' => $request->input('tipo'),
            // Transformamos 'obligatorio' a booleano si existe, sino null
            'obligatorio' => $request->has('obligatorio')
                            ? (filter_var($request->input('obligatorio'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $request->input('obligatorio'))
                            : null,
            'busqueda' => $request->input('busqueda'),
        ];

        $perPage = 15; // Límite de paginación por defecto

        return Inertia::render('Admin/Requisitos/Index', [
            // CAMBIO 2: Pasamos los filtros y la paginación al servicio
            'requisitos' => $this->requisitoService->listar($filtros, $perPage),
            'tipos' => $this->requisitoService->obtenerTipos(),
            'filtros' => $filtros,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Requisitos/Create', [
            'tipos' => $this->requisitoService->obtenerTipos(),
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|in:DOCUMENTO,INFORMACION,CERTIFICADO,DECLARACION_JURADA',
            'obligatorio' => 'required|boolean',
        ]);

        try {
            $requisito = $this->requisitoService->crear($validated);
            return redirect()->route('admin.requisitos.index')
                ->with('success', 'Requisito creado exitosamente');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(int $id): Response
    {
        return Inertia::render('Admin/Requisitos/Show', [
            'requisito' => $this->requisitoService->obtener($id),
        ]);
    }

    public function edit(int $id): Response
    {
        return Inertia::render('Admin/Requisitos/Edit', [
            'requisito' => $this->requisitoService->obtener($id),
            'tipos' => $this->requisitoService->obtenerTipos(),
        ]);
    }

    public function update(Request $request, int $id): Response
    {
        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo' => 'sometimes|required|in:DOCUMENTO,INFORMACION,CERTIFICADO,DECLARACION_JURADA',
            'obligatorio' => 'sometimes|required|boolean',
        ]);

        try {
            $this->requisitoService->actualizar($id, $validated);
            return Inertia::location(route('admin.requisitos.index'));
        } catch (\Exception $e) {
            return Inertia::render('Admin/Requisitos/Edit', [
                'requisito' => $this->requisitoService->obtener($id),
                'tipos' => $this->requisitoService->obtenerTipos(),
            ])->with('error', $e->getMessage());
        }
    }

    public function destroy(int $id): \Illuminate\Http\RedirectResponse
    {
        try {
            $this->requisitoService->eliminar($id);
            return redirect()->route('admin.requisitos.index')
                ->with('success', 'Requisito eliminado exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
