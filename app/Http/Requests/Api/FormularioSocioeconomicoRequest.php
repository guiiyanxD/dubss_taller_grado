<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class FormularioSocioeconomicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Datos básicos del formulario
            'estado_civil' => ['required', 'in:SOLTERO,CASADO,DIVORCIADO,VIUDO,UNION_LIBRE'],
            'tiene_hijos' => ['required', 'boolean'],
            'cantidad_hijos' => ['nullable', 'integer', 'min:0', 'max:20'],
            'personas_a_cargo' => ['nullable', 'integer', 'min:0', 'max:50'],
            'recibe_bono' => ['nullable', 'boolean'],
            'tipo_bono' => ['nullable', 'string', 'max:100'],
            'certificado_discapacidad' => ['nullable', 'boolean'],
            'carnet_discapacidad' => ['nullable', 'string', 'max:50'],
            'observaciones' => ['nullable', 'string', 'max:500'],
            'completado' => ['nullable', 'boolean'],

            // Grupo Familiar
            'grupo_familiar' => ['nullable', 'array'],
            'grupo_familiar.cantidad_familiares' => ['required_with:grupo_familiar', 'integer', 'min:0', 'max:50'],
            'grupo_familiar.cantidad_hijos' => ['nullable', 'integer', 'min:0', 'max:20'],
            'grupo_familiar.miembros' => ['nullable', 'array'],
            'grupo_familiar.miembros.*.nombre' => ['required', 'string', 'max:100'],
            'grupo_familiar.miembros.*.apellido' => ['required', 'string', 'max:100'],
            'grupo_familiar.miembros.*.parentesco' => ['required', 'string', 'max:50'],
            'grupo_familiar.miembros.*.edad' => ['nullable', 'integer', 'min:0', 'max:150'],
            'grupo_familiar.miembros.*.ocupacion' => ['nullable', 'string', 'max:100'],

            // Dependencia Económica
            'dependencia_economica' => ['nullable', 'array'],
            'dependencia_economica.tipo_dependencia' => ['required_with:dependencia_economica', 'in:DEPENDIENTE,INDEPENDIENTE'],
            'dependencia_economica.ingresos' => ['nullable', 'array'],
            'dependencia_economica.ingresos.*.fuente_ingreso' => ['required', 'string', 'max:100'],
            'dependencia_economica.ingresos.*.monto_mensual' => ['required', 'numeric', 'min:0', 'max:999999.99'],

            // Residencia
            'residencia' => ['nullable', 'array'],
            'residencia.zona' => ['nullable', 'string', 'max:100'],
            'residencia.direccion' => ['nullable', 'string', 'max:200'],
            'residencia.tipo_vivienda' => ['nullable', 'string', 'max:50'],
            'residencia.material_construccion' => ['nullable', 'string', 'max:50'],
            'residencia.cant_dormitorios' => ['nullable', 'integer', 'min:0', 'max:20'],
            'residencia.cant_banhos' => ['nullable', 'integer', 'min:0', 'max:20'],
            'residencia.tiene_agua_potable' => ['nullable', 'boolean'],
            'residencia.tiene_luz_electrica' => ['nullable', 'boolean'],
            'residencia.tiene_alcantarillado' => ['nullable', 'boolean'],
            'residencia.tiene_internet' => ['nullable', 'boolean'],

            // Tenencia de Vivienda
            'tenencia_vivienda' => ['nullable', 'array'],
            'tenencia_vivienda.tipo_tenencia' => ['required_with:tenencia_vivienda', 'in:PROPIA,ALQUILADA,PRESTADA,ANTICRESIS'],
        ];
    }

    public function messages(): array
    {
        return [
            'estado_civil.required' => 'El estado civil es obligatorio.',
            'estado_civil.in' => 'Estado civil no válido.',
            'tiene_hijos.required' => 'Debe indicar si tiene hijos.',
            'cantidad_hijos.max' => 'Cantidad de hijos no puede ser mayor a 20.',

            'grupo_familiar.cantidad_familiares.required_with' => 'Debe indicar la cantidad de familiares.',
            'grupo_familiar.miembros.*.nombre.required' => 'El nombre del miembro familiar es obligatorio.',
            'grupo_familiar.miembros.*.apellido.required' => 'El apellido del miembro familiar es obligatorio.',
            'grupo_familiar.miembros.*.parentesco.required' => 'El parentesco es obligatorio.',

            'dependencia_economica.tipo_dependencia.required_with' => 'Debe indicar el tipo de dependencia.',
            'dependencia_economica.ingresos.*.fuente_ingreso.required' => 'La fuente de ingreso es obligatoria.',
            'dependencia_economica.ingresos.*.monto_mensual.required' => 'El monto mensual es obligatorio.',

            'tenencia_vivienda.tipo_tenencia.required_with' => 'Debe indicar el tipo de tenencia.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Errores de validación en el formulario',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
