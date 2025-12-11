<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\FormularioSocioEconomicoService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FormularioSocioEconomicoController extends Controller
{
    public function __construct(private FormularioSocioEconomicoService $formularioService) {}

    public function create(): Response
    {
        // Aquí podrías pasar las convocatorias/becas activas si quisieras seleccionarlas en la vista
        // Por ahora, asumimos que se pasan o se seleccionan en el formulario
        return Inertia::render('FormularioSocioeconomico/Create');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        // 1. Validación de estructuras anidadas y datos requeridos
        $validated = $request->validate([
            // Datos Críticos para el flujo
            'ci_estudiante' => 'required|string|max:20', // Cambiado de ID a CI
            'id_convocatoria' => 'required|integer|exists:convocatoria,id',
            'id_beca' => 'required|integer|exists:beca,id',

            // Datos del Formulario Base
            'fecha_llenado' => 'required|date',
            'telefono_referencia' => 'nullable|string|max:15',
            'lugar_procedencia' => 'nullable|string|max:100',
            'comentario_personal' => 'nullable|string',
            'discapacidad' => 'boolean',
            'comentario_discapacidad' => 'nullable|string',
            'otro_beneficio' => 'boolean',
            'comentario_otro_beneficio' => 'nullable|string',
            'observaciones' => 'nullable|string',

            // Grupo Familiar
            'grupo_familiar.tiene_hijos' => 'boolean',
            'grupo_familiar.cantidad_hijos' => 'integer|min:0',
            'grupo_familiar.miembros' => 'nullable|array',
            'grupo_familiar.miembros.*.nombre_completo' => 'required_with:grupo_familiar.miembros|string',
            'grupo_familiar.miembros.*.parentesco' => 'required_with:grupo_familiar.miembros|string',
            'grupo_familiar.miembros.*.edad' => 'required_with:grupo_familiar.miembros|integer',
            'grupo_familiar.miembros.*.ocupacion' => 'nullable|string',

            // Residencia
            'residencia.provincia' => 'nullable|string',
            'residencia.zona' => 'nullable|string',
            'residencia.calle' => 'nullable|string',
            'residencia.barrio' => 'nullable|string',
            'residencia.cant_dormitorios' => 'nullable|integer',
            'residencia.cant_banhos' => 'nullable|integer',
            'residencia.cant_salas' => 'nullable|integer',
            'residencia.cantt_comedor' => 'nullable|integer',
            'residencia.cant_patios' => 'nullable|integer',

            // Tenencia
            'tenencia.tipo_tenencia' => 'required|string',
            'tenencia.detalle_tenencia' => 'nullable|string',

            // Económica
            'economica.tipo_dependencia' => 'required|string',
            'economica.ocupacion_nombre' => 'nullable|string',
            'economica.nota_ocupacion' => 'nullable|string',
            'economica.rango_ingreso' => 'required|string',
        ]);

        try {
            // Llamamos al servicio transaccional
            $resultado = $this->formularioService->registrarFormularioCompleto($validated);

            return redirect()->route('admin.formularios.index') // O a donde desees redirigir
                ->with('success', "Formulario guardado y trámite #{$resultado['tramite_id']} generado exitosamente.");

        } catch (\Exception $e) {
            // Si es un error de validación manual (como CI no encontrado), volvemos con los errores
            return back()
                ->withInput()
                ->with('error', 'Error al guardar el formulario: ' . $e->getMessage());
        }
    }
}
