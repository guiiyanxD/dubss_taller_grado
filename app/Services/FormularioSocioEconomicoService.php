<?php

namespace App\Services;

use App\Models\Estudiante;
use App\Models\FormularioSocioEconomico;
use App\Models\Postulacion;
use App\Models\Tramite;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FormularioSocioEconomicoService
{
    /**
     * Guarda el formulario completo y genera la postulación y el trámite.
     */
    public function registrarFormularioCompleto(array $datos): array
    {
        return DB::transaction(function () use ($datos) {


            $estudiante = Estudiante::whereHas('usuario', function ($query) use ($datos) {
                $query->where('ci', $datos['ci_estudiante']);
            })->first();

            if (!$estudiante) {
                throw ValidationException::withMessages([
                    'ci_estudiante' => "No se encontró un estudiante registrado con el CI: {$datos['ci_estudiante']}"
                ]);
            }

            // 2. Guardar Formulario Base
            $formulario = FormularioSocioEconomico::create([
                'id_estudiante' => $estudiante->id_usuario, // Usamos el ID real encontrado
                'fecha_llenado' => $datos['fecha_llenado'],
                'telefono_referencia' => $datos['telefono_referencia'],
                'lugar_procedencia' => $datos['lugar_procedencia'],
                'comentario_personal' => $datos['comentario_personal'] ?? null,
                'observaciones' => $datos['observaciones'] ?? null,
                // Booleanos
                'discapacidad' => $datos['discapacidad'] ?? false,
                'comentario_discapacidad' => $datos['comentario_discapacidad'] ?? null,
                'otro_beneficio' => $datos['otro_beneficio'] ?? false,
                'comentario_otro_beneficio' => $datos['comentario_otro_beneficio'] ?? null,
                'completado' => true,
                'validado_por' => false
            ]);

            // 3. Guardar Grupo Familiar
            $grupoFamiliar = $formulario->grupoFamiliar()->create([
                'tiene_hijos' => $datos['grupo_familiar']['tiene_hijos'] ?? false,
                'cantidad_hijos' => $datos['grupo_familiar']['cantidad_hijos'] ?? 0,
                'cantidad_familiares' => count($datos['grupo_familiar']['miembros']),
            ]);

            // 3.1 Miembros Familiares
            if (!empty($datos['grupo_familiar']['miembros'])) {
                $grupoFamiliar->miembros()->createMany($datos['grupo_familiar']['miembros']);
            }

            // 4. Guardar Residencia
            $formulario->residencia()->create($datos['residencia']);

            // 5. Guardar Tenencia Vivienda
            $formulario->tenenciaVivienda()->create([
                'tipo_tenencia' => $datos['tenencia']['tipo_tenencia'],
                'detalle_tenencia' => $datos['tenencia']['detalle_tenencia'] ?? null,
            ]);
            // Nota: Si usas la tabla 'tipo_tenencia_vivienda' separada, aquí deberías insertarla,
            // pero con los datos que tenemos, guardar en 'tenencia_vivienda' suele bastar.

            // 6. Guardar Dependencia Económica
            $dependencia = $formulario->dependenciaEconomica()->create([
                'tipo_dependencia' => $datos['economica']['tipo_dependencia'],
                'nota_ocupacion_dependiente' => $datos['economica']['nota_ocupacion'] ?? null,
            ]);

            // 6.1 Guardar Tipo Ocupación (Relacionada a Dependencia)
            if (!empty($datos['economica']['ocupacion_nombre'])) {
                $dependencia->tipoOcupacion()->create([
                    'nombre' => $datos['economica']['ocupacion_nombre'],
                ]);
            }

            // 6.2 Guardar Ingreso Económico (Relacionado a Dependencia)
            if (!empty($datos['economica']['rango_ingreso'])) {
                $dependencia->ingresoEconomico()->create([
                    'rango_monto' => $datos['economica']['rango_ingreso'],
                ]);
            }

            // ---------------------------------------------------------
            // 7. CREAR POSTULACIÓN AUTOMÁTICA
            // ---------------------------------------------------------

            // Verificamos si ya existe una postulación para esta convocatoria para no duplicar
            $existePostulacion = Postulacion::where('id_estudiante', $estudiante->id_usuario)
                ->where('id_convocatoria', $datos['id_convocatoria'])
                ->exists();

            if ($existePostulacion) {
                 throw ValidationException::withMessages([
                    'general' => "El estudiante ya tiene una postulación registrada para esta convocatoria."
                ]);
            }

            $postulacion = Postulacion::create([
                'id_estudiante' => $estudiante->id_usuario,
                'id_convocatoria' => $datos['id_convocatoria'],
                'id_formulario' => $formulario->id,
                'id_beca' => $datos['id_beca'],
                'fecha_postulacion' => now(),
                'estado_postulado' => 'PENDIENTE', // Estado inicial
            ]);

            // ---------------------------------------------------------
            // 8. CREAR TRÁMITE (Para que el operador lo vea)
            // ---------------------------------------------------------
            // Asumimos estado 1 = PENDIENTE según tus ejemplos anteriores
            $tramite = Tramite::create([
                'id_postulacion' => $postulacion->id,
                'codigo' => 'TRA-' . str_pad($postulacion->id, 6, '0', STR_PAD_LEFT), // Generador simple de código
                'fecha_creacion' => now(),
                'clasificado' => 'NO',
                'estado_actual' => 1, // 1: ID del estado PENDIENTE
            ]);

            Log::info("Formulario, Postulación y Trámite creados exitosamente para CI: {$datos['ci_estudiante']}");

            return [
                'formulario_id' => $formulario->id,
                'postulacion_id' => $postulacion->id,
                'tramite_id' => $tramite->id
            ];
        });
    }
}
