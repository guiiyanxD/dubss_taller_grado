<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Validar todos los campos
        $validated = $request->validate([
            // Campos básicos
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],

            // Campos adicionales DUBSS
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'ci' => 'required|string|max:20|unique:users,ci',
            'telefono' => 'required|string|max:15',
            'ciudad' => 'required|string|max:50',
            'fecha_nacimiento' => 'required|date|before:today',
        ], [
            // Mensajes personalizados en español
            'nombres.required' => 'El campo nombres es obligatorio.',
            'apellidos.required' => 'El campo apellidos es obligatorio.',
            'ci.required' => 'La cédula de identidad es obligatoria.',
            'ci.unique' => 'Esta cédula de identidad ya está registrada.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'ciudad.required' => 'Debe seleccionar una ciudad.',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        // Crear el usuario con todos los campos
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),

            // Campos adicionales
            'nombres' => $validated['nombres'],
            'apellidos' => $validated['apellidos'],
            'ci' => $validated['ci'],
            'telefono' => $validated['telefono'],
            'ciudad' => $validated['ciudad'],
            'fecha_nacimiento' => $validated['fecha_nacimiento'],
        ]);


        $user->assignRole('Estudiante');
        event(new Registered($user));
        Auth::login($user);
        return redirect(route('dashboard', absolute: false));
    }
}
