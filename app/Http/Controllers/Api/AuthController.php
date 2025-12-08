<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @group Autenticación
 *
 * Endpoints para autenticación de usuarios en la API.
 */
class AuthController extends Controller
{
    /**
     * Login
     *
     * Inicia sesión y obtiene un token de acceso para la API.
     *
     * @bodyParam email string required Email del usuario. Example: estudiante@dubss.edu
     * @bodyParam password string required Contraseña del usuario. Example: password
     *
     * @response 200 {
     *   "user": {
     *     "id": 1,
     *     "name": "Juan Pérez",
     *     "email": "estudiante@dubss.edu",
     *     "roles": ["Estudiante"]
     *   },
     *   "token": "1|abc123def456...",
     *   "token_type": "Bearer"
     * }
     *
     * @response 422 {
     *   "message": "The provided credentials are incorrect."
     * }
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Crear token
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Logout
     *
     * Cierra la sesión y revoca el token actual.
     *
     * @authenticated
     *
     * @response 200 {
     *   "message": "Logged out successfully"
     * }
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Usuario Actual
     *
     * Obtiene la información del usuario autenticado.
     *
     * @authenticated
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "Juan Pérez",
     *   "email": "estudiante@dubss.edu",
     *   "roles": ["Estudiante"],
     *   "permissions": ["crear_postulacion", "llenar_formulario"]
     * }
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }
}
