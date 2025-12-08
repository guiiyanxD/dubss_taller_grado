<?php

namespace App\Http\Requests\Operador;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ValidarTramiteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole(['Operador', 'Dpto. Sistema']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'accion' => 'required|in:APROBAR,RECHAZAR',
            'documentos_validados' => 'required_if:accion,APROBAR|array',
            'documentos_validados.*.tipo' => 'required_with:documentos_validados|string',
            'documentos_validados.*.valido' => 'required_with:documentos_validados|boolean',
            'observaciones' => 'required_if:accion,RECHAZAR|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'accion.required' => 'Debe especificar una acción (APROBAR o RECHAZAR)',
            'accion.in' => 'La acción debe ser APROBAR o RECHAZAR',
            'documentos_validados.required_if' => 'Debe validar los documentos al aprobar',
            'observaciones.required_if' => 'Debe especificar el motivo del rechazo',
            'observaciones.max' => 'Las observaciones no pueden exceder 500 caracteres',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
