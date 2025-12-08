<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Operador\TramiteOperadorController;
use App\Http\Controllers\Operador\DocumentoOperadorController;
use App\Http\Controllers\Admin\AdminResultadosController;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/test-roles', [App\Http\Controllers\TestRoleController::class, 'index'])->name('test.roles');
});

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Rutas del Panel Web Operativo
|--------------------------------------------------------------------------
*/

// Rutas para Operadores
Route::middleware(['auth', 'verified'])->prefix('operador')->name('operador.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [TramiteOperadorController::class, 'dashboard'])
        ->middleware(['auth', 'role:Operador|Dpto. Sistema'])
        ->name('dashboard');

    // Búsqueda de trámites
    Route::get('/tramites/buscar', [TramiteOperadorController::class, 'mostrarBusqueda'])
        ->name('tramites.buscar');
    Route::get('/tramites/buscar/resultados', [TramiteOperadorController::class, 'buscarPorCI'])
        ->name('tramites.buscar.resultados');

    // Trámites pendientes
    Route::get('/tramites/pendientes', [TramiteOperadorController::class, 'pendientes'])
        ->name('tramites.pendientes');

    // Validación de trámites
    Route::get('/tramites/{id}/validar', [TramiteOperadorController::class, 'mostrarValidacion'])
        ->name('tramites.validar');
    Route::put('/tramites/{id}/validar', [TramiteOperadorController::class, 'validar'])
        ->name('tramites.validar.submit');

    // Trámites validados (listos para digitalizar)
    Route::get('/tramites/validados', [TramiteOperadorController::class, 'validados'])
        ->name('tramites.validados');

    // Digitalización
    Route::get('/tramites/{id}/digitalizar', [DocumentoOperadorController::class, 'mostrarDigitalizacion'])
        ->name('tramites.digitalizar');
    Route::post('/documentos/upload', [DocumentoOperadorController::class, 'upload'])
        ->name('documentos.upload');
    Route::delete('/documentos/{id}', [DocumentoOperadorController::class, 'eliminar'])
        ->name('documentos.eliminar');
    Route::put('/tramites/{id}/completar-digitalizacion', [DocumentoOperadorController::class, 'completarDigitalizacion'])
        ->name('tramites.completar-digitalizacion');

    // Ver expediente digitalizado
    Route::get('/tramites/{id}/expediente', [DocumentoOperadorController::class, 'verExpediente'])
        ->name('tramites.expediente');

    // Descargar/Ver documentos
    Route::get('/documentos/{id}/descargar', [DocumentoOperadorController::class, 'descargar'])
        ->name('documentos.descargar');
    Route::get('/documentos/{id}/ver', [DocumentoOperadorController::class, 'ver'])
        ->name('documentos.ver');

    // Historial del operador
    Route::get('/historial', [TramiteOperadorController::class, 'historial'])
        ->name('historial');

    // Detalle de trámite
    Route::get('/tramites/{id}/detalle', [TramiteOperadorController::class, 'detalle'])
        ->name('tramites.detalle');
});

// Rutas para Administración (Dpto. Sistema y Dirección)
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard de resultados
    Route::get('/resultados/dashboard', [AdminResultadosController::class, 'dashboard'])
        ->name('resultados.dashboard');

    // Ranking por beca
    Route::get('/becas/{id}/ranking', [AdminResultadosController::class, 'rankingBeca'])
        ->name('becas.ranking');

    // Detalle de postulación
    Route::get('/postulaciones/{id}/detalle', [AdminResultadosController::class, 'detallePostulacion'])
        ->name('postulaciones.detalle');

    // Auditoría de trámite
    Route::get('/tramites/{id}/auditoria', [AdminResultadosController::class, 'auditoriaTramite'])
        ->name('tramites.auditoria');

    // Exportar reportes
    Route::post('/resultados/exportar', [AdminResultadosController::class, 'exportar'])
        ->name('resultados.exportar');

    // Comparación de convocatorias
    Route::get('/resultados/comparacion', [AdminResultadosController::class, 'comparacionConvocatorias'])
        ->name('resultados.comparacion');

    // Notificaciones masivas
    Route::post('/resultados/notificar', [AdminResultadosController::class, 'notificarResultados'])
        ->name('resultados.notificar');

    // Estadísticas filtradas (AJAX)
    Route::get('/resultados/estadisticas-filtradas', [AdminResultadosController::class, 'estadisticasFiltradas'])
        ->name('resultados.estadisticas-filtradas');
});
