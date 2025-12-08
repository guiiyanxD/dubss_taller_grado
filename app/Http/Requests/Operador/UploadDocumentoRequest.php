<?php

namespace App\Http\Requests\Operador;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UploadDocumentoRequest extends FormRequest
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
            'tramite_id' => 'required|integer|exists:tramite,id',
            'tipo_documento' => 'required|in:CI,KARDEX,COMPROBANTE_DOMICILIO,CERTIFICADO_INGRESOS,OTRO',
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB máximo
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'tramite_id.required' => 'El ID del trámite es requerido',
            'tramite_id.exists' => 'El trámite especificado no existe',
            'tipo_documento.required' => 'El tipo de documento es requerido',
            'tipo_documento.in' => 'El tipo de documento no es válido',
            'archivo.required' => 'Debe seleccionar un archivo',
            'archivo.file' => 'El archivo no es válido',
            'archivo.mimes' => 'Solo se permiten archivos PDF, JPG, JPEG o PNG',
            'archivo.max' => 'El archivo no puede superar 5 MB',
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
                'message' => 'Error de validación en el archivo',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
