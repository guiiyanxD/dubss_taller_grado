<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\FormularioSocioeconomicoRequest;
use App\Models\FormularioSocioEconomico;
use App\Models\GrupoFamiliar;
use App\Models\MiembroFamiliar;
use App\Models\DependenciaEconomica;
use App\Models\IngresoEconomico;
use App\Models\Residencia;
use App\Models\TenenciaVivienda;
use App\Models\Estudiante;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormularioSocioeconomicoController extends Controller
{
    /**
     * Obtener el formulario del estudiante autenticado
     *
     * GET /api/formularios/mi-formulario
     */
    public function miFormulario(Request $request): JsonResponse
    {
        $user = $request->user();

        $estudiante = Estudiante::where('id_usuario', $user->id)->firstOrFail();

        $formulario = FormularioSocioEconomico::with([
            'grupoFamiliar.miembrosFamiliares',
            'dependenciaEconomica.ingresosEconomicos',
            'dependenciaEconomica.tipoOcupacionDependiente',
            'residencia',
            'tenenciaVivienda.tipoTenenciaVivienda',
        ])->where('id_estudiante', $estudiante->id_usuario)->first();

        if (!$formulario) {
            return response()->json([
                'message' => 'No tienes un formulario creado aún',
                'formulario' => null
            ], 404);
        }

        return response()->json([
            'formulario' => $this->formatearFormulario($formulario)
        ], 200);
    }

    /**
     * Crear o actualizar el formulario socioeconómico (guardar progreso)
     *
     * POST /api/formularios
     * PUT /api/formularios/{id}
     */
    public function guardar(FormularioSocioeconomicoRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            $estudiante = Estudiante::where('id_usuario', $user->id)->firstOrFail();

            // Buscar formulario existente o crear uno nuevo
            $formulario = FormularioSocioEconomico::firstOrCreate(
                ['id_estudiante' => $estudiante->id_usuario],
                [
                    'estado_civil' => $request->estado_civil,
                    'tiene_hijos' => $request->tiene_hijos ?? false,
                    'cantidad_hijos' => $request->cantidad_hijos ?? 0,
                    'personas_a_cargo' => $request->personas_a_cargo ?? 0,
                    'recibe_bono' => $request->recibe_bono ?? false,
                    'tipo_bono' => $request->tipo_bono,
                    'certificado_discapacidad' => $request->certificado_discapacidad ?? false,
                    'carnet_discapacidad' => $request->carnet_discapacidad,
                    'observaciones' => $request->observaciones,
                    'completado' => $request->boolean('completado', false),
                ]
            );

            // Actualizar datos básicos si ya existe
            if (!$formulario->wasRecentlyCreated) {
                $formulario->update($request->only([
                    'estado_civil', 'tiene_hijos', 'cantidad_hijos', 'personas_a_cargo',
                    'recibe_bono', 'tipo_bono', 'certificado_discapacidad',
                    'carnet_discapacidad', 'observaciones', 'completado'
                ]));
            }

            // Guardar Grupo Familiar
            if ($request->has('grupo_familiar')) {
                $this->guardarGrupoFamiliar($formulario, $request->grupo_familiar);
            }

            // Guardar Dependencia Económica
            if ($request->has('dependencia_economica')) {
                $this->guardarDependenciaEconomica($formulario, $request->dependencia_economica);
            }

            // Guardar Residencia
            if ($request->has('residencia')) {
                $this->guardarResidencia($formulario, $request->residencia);
            }

            // Guardar Tenencia de Vivienda
            if ($request->has('tenencia_vivienda')) {
                $this->guardarTenenciaVivienda($formulario, $request->tenencia_vivienda);
            }

            DB::commit();

            // Recargar con relaciones
            $formulario->load([
                'grupoFamiliar.miembrosFamiliares',
                'dependenciaEconomica.ingresosEconomicos',
                'residencia',
                'tenenciaVivienda'
            ]);

            return response()->json([
                'message' => $formulario->completado
                    ? 'Formulario completado exitosamente'
                    : 'Progreso guardado exitosamente',
                'formulario' => $this->formatearFormulario($formulario)
            ], $formulario->wasRecentlyCreated ? 201 : 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al guardar formulario',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Guardar Grupo Familiar
     */
    private function guardarGrupoFamiliar(FormularioSocioEconomico $formulario, array $data): void
    {
        $grupoFamiliar = GrupoFamiliar::updateOrCreate(
            ['id_formulario' => $formulario->id],
            [
                'cantidad_familiares' => $data['cantidad_familiares'] ?? 0,
                'cantidad_hijos' => $data['cantidad_hijos'] ?? 0,
                'puntaje_grupo_familiar' => 0, // Se calculará después
                'puntaje_hijos' => 0,
            ]
        );

        // Eliminar miembros anteriores
        $grupoFamiliar->miembrosFamiliares()->delete();

        // Guardar miembros familiares
        if (isset($data['miembros']) && is_array($data['miembros'])) {
            foreach ($data['miembros'] as $miembro) {
                MiembroFamiliar::create([
                    'id_grupo_familiar' => $grupoFamiliar->id,
                    'nombre' => $miembro['nombre'],
                    'apellido' => $miembro['apellido'],
                    'parentesco' => $miembro['parentesco'],
                    'edad' => $miembro['edad'] ?? null,
                    'ocupacion' => $miembro['ocupacion'] ?? null,
                ]);
            }
        }
    }

    /**
     * Guardar Dependencia Económica
     */
    private function guardarDependenciaEconomica(FormularioSocioEconomico $formulario, array $data): void
    {
        $dependencia = DependenciaEconomica::updateOrCreate(
            ['id_formulario' => $formulario->id],
            [
                'tipo_dependencia' => $data['tipo_dependencia'],
                'puntaje_dependencia' => 0, // Se calculará después
            ]
        );

        // Eliminar ingresos anteriores
        $dependencia->ingresosEconomicos()->delete();

        // Guardar ingresos económicos
        if (isset($data['ingresos']) && is_array($data['ingresos'])) {
            foreach ($data['ingresos'] as $ingreso) {
                IngresoEconomico::create([
                    'id_dependencia_economica' => $dependencia->id,
                    'fuente_ingreso' => $ingreso['fuente_ingreso'],
                    'monto_mensual' => $ingreso['monto_mensual'],
                    'rango_monto' => $this->calcularRangoMonto($ingreso['monto_mensual']),
                ]);
            }
        }
    }

    /**
     * Guardar Residencia
     */
    private function guardarResidencia(FormularioSocioEconomico $formulario, array $data): void
    {
        Residencia::updateOrCreate(
            ['id_formulario' => $formulario->id],
            [
                'zona' => $data['zona'] ?? null,
                'direccion' => $data['direccion'] ?? null,
                'tipo_vivienda' => $data['tipo_vivienda'] ?? null,
                'material_construccion' => $data['material_construccion'] ?? null,
                'cant_dormitorios' => $data['cant_dormitorios'] ?? 0,
                'cant_banhos' => $data['cant_banhos'] ?? 0,
                'tiene_agua_potable' => $data['tiene_agua_potable'] ?? false,
                'tiene_luz_electrica' => $data['tiene_luz_electrica'] ?? false,
                'tiene_alcantarillado' => $data['tiene_alcantarillado'] ?? false,
                'tiene_internet' => $data['tiene_internet'] ?? false,
                'puntaje_residencia' => 0, // Se calculará después
            ]
        );
    }

    /**
     * Guardar Tenencia de Vivienda
     */
    private function guardarTenenciaVivienda(FormularioSocioEconomico $formulario, array $data): void
    {
        TenenciaVivienda::updateOrCreate(
            ['id_formulario' => $formulario->id],
            [
                'tipo_tenencia' => $data['tipo_tenencia'],
                'puntaje_tenencia' => 0, // Se calculará después
            ]
        );
    }

    /**
     * Calcular rango de monto
     */
    private function calcularRangoMonto(float $monto): string
    {
        if ($monto < 1000) return '0-1000';
        if ($monto < 2000) return '1000-2000';
        if ($monto < 3000) return '2000-3000';
        return '3000+';
    }

    /**
     * Formatear formulario para respuesta JSON
     */
    private function formatearFormulario(FormularioSocioEconomico $formulario): array
    {
        return [
            'id' => $formulario->id,
            'estado_civil' => $formulario->estado_civil,
            'tiene_hijos' => $formulario->tiene_hijos,
            'cantidad_hijos' => $formulario->cantidad_hijos,
            'personas_a_cargo' => $formulario->personas_a_cargo,
            'recibe_bono' => $formulario->recibe_bono,
            'tipo_bono' => $formulario->tipo_bono,
            'certificado_discapacidad' => $formulario->certificado_discapacidad,
            'carnet_discapacidad' => $formulario->carnet_discapacidad,
            'observaciones' => $formulario->observaciones,
            'completado' => $formulario->completado,
            'validado' => $formulario->validado_por !== null,
            'grupo_familiar' => $formulario->grupoFamiliar ? [
                'cantidad_familiares' => $formulario->grupoFamiliar->cantidad_familiares,
                'cantidad_hijos' => $formulario->grupoFamiliar->cantidad_hijos,
                'miembros' => $formulario->grupoFamiliar->miembrosFamiliares->map(fn($m) => [
                    'nombre' => $m->nombre,
                    'apellido' => $m->apellido,
                    'parentesco' => $m->parentesco,
                    'edad' => $m->edad,
                    'ocupacion' => $m->ocupacion,
                ]),
            ] : null,
            'dependencia_economica' => $formulario->dependenciaEconomica ? [
                'tipo_dependencia' => $formulario->dependenciaEconomica->tipo_dependencia,
                'ingresos' => $formulario->dependenciaEconomica->ingresosEconomicos->map(fn($i) => [
                    'fuente_ingreso' => $i->fuente_ingreso,
                    'monto_mensual' => $i->monto_mensual,
                    'rango_monto' => $i->rango_monto,
                ]),
            ] : null,
            'residencia' => $formulario->residencia ? [
                'zona' => $formulario->residencia->zona,
                'direccion' => $formulario->residencia->direccion,
                'tipo_vivienda' => $formulario->residencia->tipo_vivienda,
                'material_construccion' => $formulario->residencia->material_construccion,
                'cant_dormitorios' => $formulario->residencia->cant_dormitorios,
                'cant_banhos' => $formulario->residencia->cant_banhos,
                'tiene_agua_potable' => $formulario->residencia->tiene_agua_potable,
                'tiene_luz_electrica' => $formulario->residencia->tiene_luz_electrica,
                'tiene_alcantarillado' => $formulario->residencia->tiene_alcantarillado,
                'tiene_internet' => $formulario->residencia->tiene_internet,
            ] : null,
            'tenencia_vivienda' => $formulario->tenenciaVivienda ? [
                'tipo_tenencia' => $formulario->tenenciaVivienda->tipo_tenencia,
            ] : null,
        ];
    }
}
