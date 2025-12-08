<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BecaController;
use App\Http\Controllers\Api\ConvocatoriaController;
use App\Http\Controllers\Api\FormularioSocioeconomicoController;
use App\Http\Controllers\Api\PostulacionController;
use App\Http\Controllers\Api\TramiteController;
use App\Http\Controllers\Api\NotificacionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí registramos las rutas de la API REST para el sistema DUBSS.
| Estas rutas están protegidas por Sanctum y serán documentadas por Scribe.
|
*/

// ============================================
// RUTAS PÚBLICAS (sin autenticación)
// ============================================

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('api.auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login');
});

// ============================================
// RUTAS PROTEGIDAS (requieren autenticación)
// ============================================

Route::middleware('auth:sanctum')->group(function () {

    // --------------------------------------------
    // AUTENTICACIÓN
    // --------------------------------------------
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me'])->name('api.auth.me');
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::post('/logout-all', [AuthController::class, 'logoutAll'])->name('api.auth.logout-all');
    });

    // --------------------------------------------
    // BECAS
    // --------------------------------------------
    Route::prefix('becas')->group(function () {
        Route::get('/', [BecaController::class, 'index'])->name('api.becas.index');
        Route::get('/{id}', [BecaController::class, 'show'])->name('api.becas.show');
    });

    // --------------------------------------------
    // CONVOCATORIAS
    // --------------------------------------------
    Route::prefix('convocatorias')->group(function () {
        Route::get('/', [ConvocatoriaController::class, 'index'])->name('api.convocatorias.index');
        Route::get('/{id}', [ConvocatoriaController::class, 'show'])->name('api.convocatorias.show');
    });

    // --------------------------------------------
    // FORMULARIO SOCIOECONÓMICO
    // --------------------------------------------
    Route::prefix('formularios')->group(function () {
        Route::get('/mi-formulario', [FormularioSocioeconomicoController::class, 'miFormulario'])
            ->name('api.formularios.mi-formulario');

        Route::post('/', [FormularioSocioeconomicoController::class, 'guardar'])
            ->name('api.formularios.guardar');
    });

    // --------------------------------------------
    // POSTULACIONES
    // --------------------------------------------
    Route::prefix('postulaciones')->group(function () {
        Route::get('/', [PostulacionController::class, 'index'])->name('api.postulaciones.index');
        Route::get('/{id}', [PostulacionController::class, 'show'])->name('api.postulaciones.show');
        Route::post('/', [PostulacionController::class, 'store'])->name('api.postulaciones.store');
    });

    // --------------------------------------------
    // TRÁMITES
    // --------------------------------------------
    Route::prefix('tramites')->group(function () {
        Route::get('/mi-tramite', [TramiteController::class, 'miTramite'])
            ->name('api.tramites.mi-tramite');

        Route::get('/{codigo}', [TramiteController::class, 'porCodigo'])
            ->name('api.tramites.por-codigo');
    });

    // --------------------------------------------
    // NOTIFICACIONES
    // --------------------------------------------
    Route::prefix('notificaciones')->group(function () {
        Route::get('/', [NotificacionController::class, 'index'])
            ->name('api.notificaciones.index');

        Route::get('/estadisticas', [NotificacionController::class, 'estadisticas'])
            ->name('api.notificaciones.estadisticas');

        Route::put('/{id}/leer', [NotificacionController::class, 'marcarComoLeida'])
            ->name('api.notificaciones.marcar-leida');

        Route::put('/leer-todas', [NotificacionController::class, 'marcarTodasComoLeidas'])
            ->name('api.notificaciones.leer-todas');

        Route::delete('/{id}', [NotificacionController::class, 'destroy'])
            ->name('api.notificaciones.destroy');
    });
});

// ============================================
// RUTA DE HEALTH CHECK
// ============================================

Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'service' => 'DUBSS API',
        'version' => '1.0.0',
        'timestamp' => now()->toIso8601String(),
    ]);
})->name('api.health');
