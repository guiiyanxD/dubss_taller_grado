<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Datos de usuario
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
            ],
            'ci' => ['required', 'string', 'max:20', 'unique:users,ci'],
            'telefono' => ['required', 'string', 'min:8', 'max:15'],
            'ciudad' => ['required', 'string', 'max:100'],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],

            // Datos de estudiante
            'nro_registro' => ['required', 'string', 'max:50', 'unique:estudiante,nro_registro'],
            'carrera' => ['required', 'string', 'max:100'],
            'semestre' => ['required', 'integer', 'min:1', 'max:12'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            // Usuario
            'nombres.required' => 'Los nombres son obligatorios.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'ci.required' => 'El CI es obligatorio.',
            'ci.unique' => 'Este CI ya está registrado.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.min' => 'El teléfono debe tener al menos 8 dígitos.',
            'ciudad.required' => 'La ciudad es obligatoria.',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',

            // Estudiante
            'nro_registro.required' => 'El número de registro es obligatorio.',
            'nro_registro.unique' => 'Este número de registro ya está registrado.',
            'carrera.required' => 'La carrera es obligatoria.',
            'semestre.required' => 'El semestre es obligatorio.',
            'semestre.min' => 'El semestre debe ser al menos 1.',
            'semestre.max' => 'El semestre no puede ser mayor a 12.',
        ];
    }

    /**
     * Handle a failed validation attempt (para API).
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
