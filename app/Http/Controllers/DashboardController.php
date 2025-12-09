<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Dashboard principal unificado
     * Muestra acciones específicas según el rol del usuario
     *
     * GET /dashboard
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Obtener estadísticas según el rol
        $estadisticas = $this->getEstadisticasPorRol($user);

        // Obtener acciones rápidas según el rol
        $accionesRapidas = $this->getAccionesRapidasPorRol($user);

        // Obtener actividad reciente según el rol
        $actividadReciente = $this->getActividadRecientePorRol($user);

        return Inertia::render('Dashboard/Index', [
            'usuario' => [
                'id' => $user->id,
                'nombre' => $user->nombre_completo,
                'rol' => $user->rol,
                'email' => $user->email,
            ],
            'estadisticas' => $estadisticas,
            'acciones_rapidas' => $accionesRapidas,
            'actividad_reciente' => $actividadReciente,
        ]);
    }

    /**
     * Obtener estadísticas según el rol
     */
    private function getEstadisticasPorRol($user): array
    {
        switch ($user->rol) {
            case 'Estudiante':
                return $this->getEstadisticasEstudiante($user);

            case 'Operador':
                return $this->getEstadisticasOperador($user);

            case 'Dpto. Sistema':
            case 'Dirección':
                return $this->getEstadisticasAdmin($user);

            default:
                return [];
        }
    }

    /**
     * Obtener acciones rápidas según el rol
     */
    private function getAccionesRapidasPorRol($user): array
    {
        switch ($user->rol) {
            case 'Estudiante':
                return [
                    [
                        'titulo' => 'Postular a Beca',
                        'descripcion' => 'Completa tu formulario socioeconómico y postula',
                        'icono' => 'DocumentTextIcon',
                        'color' => 'blue',
                        'ruta' => route('estudiante.postular'),
                        'habilitado' => true,
                    ],
                    [
                        'titulo' => 'Mis Postulaciones',
                        'descripcion' => 'Ver estado de mis postulaciones',
                        'icono' => 'ClipboardDocumentListIcon',
                        'color' => 'green',
                        'ruta' => route('estudiante.postulaciones'),
                        'habilitado' => true,
                    ],
                    [
                        'titulo' => 'Notificaciones',
                        'descripcion' => 'Ver mis notificaciones y mensajes',
                        'icono' => 'BellIcon',
                        'color' => 'yellow',
                        'ruta' => route('estudiante.notificaciones'),
                        'habilitado' => true,
                        'badge' => $this->getNotificacionesNoLeidas($user),
                    ],
                    [
                        'titulo' => 'Mi Perfil',
                        'descripcion' => 'Actualizar mis datos personales',
                        'icono' => 'UserCircleIcon',
                        'color' => 'purple',
                        'ruta' => route('estudiante.perfil'),
                        'habilitado' => true,
                    ],
                ];

            case 'Operador':
                return [
                    [
                        'titulo' => 'Buscar Trámite',
                        'descripcion' => 'Buscar trámite por CI del estudiante',
                        'icono' => 'MagnifyingGlassIcon',
                        'color' => 'blue',
                        'ruta' => route('operador.tramites.buscar'),
                        'habilitado' => true,
                    ],
                    [
                        'titulo' => 'Trámites Pendientes',
                        'descripcion' => 'Ver trámites pendientes de validación',
                        'icono' => 'ClockIcon',
                        'color' => 'orange',
                        'ruta' => route('operador.tramites.pendientes'),
                        'habilitado' => true,
                        'badge' => $this->getTramitesPendientes(),
                    ],
                    [
                        'titulo' => 'Digitalizar Expedientes',
                        'descripcion' => 'Digitalizar documentos validados',
                        'icono' => 'CloudArrowUpIcon',
                        'color' => 'green',
                        'ruta' => route('operador.tramites.validados'),
                        'habilitado' => true,
                    ],
                    [
                        'titulo' => 'Mi Historial',
                        'descripcion' => 'Ver trámites que he procesado',
                        'icono' => 'DocumentDuplicateIcon',
                        'color' => 'purple',
                        'ruta' => route('operador.historial'),
                        'habilitado' => true,
                    ],
                ];

            case 'Dpto. Sistema':
                return [
                    [
                        'titulo' => 'Dashboard de Resultados',
                        'descripcion' => 'Ver estadísticas y resultados generales',
                        'icono' => 'ChartBarIcon',
                        'color' => 'blue',
                        'ruta' => route('admin.resultados.dashboard'),
                        'habilitado' => true,
                    ],
                    [
                        'titulo' => 'Gestionar Convocatorias',
                        'descripcion' => 'Crear y administrar convocatorias',
                        'icono' => 'MegaphoneIcon',
                        'color' => 'green',
                        'ruta' => route('admin.convocatorias.index'),
                        'habilitado' => true,
                    ],
                    [
                        'titulo' => 'Gestionar Becas',
                        'descripcion' => 'Configurar becas y cupos',
                        'icono' => 'AcademicCapIcon',
                        'color' => 'purple',
                        'ruta' => route('admin.becas.index'),
                        'habilitado' => true,
                    ],
                    [
                        'titulo' => 'Clasificación Automática',
                        'descripcion' => 'Ejecutar clasificación de postulaciones',
                        'icono' => 'CpuChipIcon',
                        'color' => 'orange',
                        'ruta' => route('admin.clasificacion.ejecutar'),
                        'habilitado' => $this->hayTramitesListosParaClasificar(),
                    ],
                    [
                        'titulo' => 'Exportar Reportes',
                        'descripcion' => 'Generar reportes en Excel/PDF',
                        'icono' => 'DocumentArrowDownIcon',
                        'color' => 'indigo',
                        'ruta' => route('admin.reportes.index'),
                        'habilitado' => true,
                    ],
                    [
                        'titulo' => 'Configuración',
                        'descripcion' => 'Gestionar catálogos y parámetros',
                        'icono' => 'Cog6ToothIcon',
                        'color' => 'gray',
                        'ruta' => route('admin.configuracion.index'),
                        'habilitado' => true,
                    ],
                ];

            case 'Dirección':
                return [
                    [
                        'titulo' => 'Dashboard Ejecutivo',
                        'descripcion' => 'Ver KPIs y métricas principales',
                        'icono' => 'PresentationChartLineIcon',
                        'color' => 'blue',
                        'ruta' => route('direccion.dashboard'),
                        'habilitado' => true,
                    ],
                    [
                        'titulo' => 'Resultados por Beca',
                        'descripcion' => 'Ver rankings y aprobados',
                        'icono' => 'TrophyIcon',
                        'color' => 'yellow',
                        'ruta' => route('admin.resultados.dashboard'),
                        'habilitado' => true,
                    ],
                    [
                        'titulo' => 'Análisis Comparativo',
                        'descripcion' => 'Comparar convocatorias históricas',
                        'icono' => 'ChartPieIcon',
                        'color' => 'green',
                        'ruta' => route('direccion.analisis'),
                        'habilitado' => true,
                    ],
                    [
                        'titulo' => 'Reportes Ejecutivos',
                        'descripcion' => 'Descargar reportes consolidados',
                        'icono' => 'DocumentChartBarIcon',
                        'color' => 'purple',
                        'ruta' => route('direccion.reportes'),
                        'habilitado' => true,
                    ],
                ];

            default:
                return [];
        }
    }

    /**
     * Obtener actividad reciente según el rol
     */
    private function getActividadRecientePorRol($user): array
    {
        switch ($user->rol) {
            case 'Estudiante':
                return $this->getActividadEstudiante($user);

            case 'Operador':
                return $this->getActividadOperador($user);

            case 'Dpto. Sistema':
            case 'Dirección':
                return $this->getActividadAdmin($user);

            default:
                return [];
        }
    }

    // ========================================
    // Métodos auxiliares para Estudiante
    // ========================================

    private function getEstadisticasEstudiante($user): array
    {
        $postulaciones = \App\Models\Postulacion::where('id_estudiante', $user->id)->get();

        return [
            [
                'label' => 'Postulaciones',
                'valor' => $postulaciones->count(),
                'icono' => 'DocumentTextIcon',
                'color' => 'blue',
            ],
            [
                'label' => 'Aprobadas',
                'valor' => $postulaciones->where('estado_postulado', 'APROBADO')->count(),
                'icono' => 'CheckCircleIcon',
                'color' => 'green',
            ],
            [
                'label' => 'En Proceso',
                'valor' => $postulaciones->whereIn('estado_postulado', ['PENDIENTE', 'EN_REVISION'])->count(),
                'icono' => 'ClockIcon',
                'color' => 'yellow',
            ],
            [
                'label' => 'Notificaciones',
                'valor' => $this->getNotificacionesNoLeidas($user),
                'icono' => 'BellIcon',
                'color' => 'red',
            ],
        ];
    }

    private function getActividadEstudiante($user): array
    {
        $notificaciones = \App\Models\Notificacion::where('id_estudiante', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return $notificaciones->map(function ($notif) {
            return [
                'tipo' => $notif->tipo,
                'titulo' => $notif->titulo,
                'mensaje' => $notif->mensaje,
                'fecha' => $notif->created_at->diffForHumans(),
                'leido' => $notif->leido,
            ];
        })->toArray();
    }

    private function getNotificacionesNoLeidas($user): int
    {
        return \App\Models\Notificacion::where('id_estudiante', $user->id)
            ->where('leido', false)
            ->count();
    }

    // ========================================
    // Métodos auxiliares para Operador
    // ========================================

    private function getEstadisticasOperador($user): array
    {
        $hoy = today();

        return [
            [
                'label' => 'Pendientes',
                'valor' => \App\Models\Tramite::where('estado_actual',
                    \App\Models\EstadoTramite::where('nombre', 'PENDIENTE')->value('id')
                )->count(),
                'icono' => 'ExclamationTriangleIcon',
                'color' => 'orange',
            ],
            [
                'label' => 'Validados Hoy',
                'valor' => \App\Models\TramiteHistorial::where('revisador_por', $user->id)
                    ->whereDate('fecha_revision', $hoy)
                    ->where('estado_nuevo', 'VALIDADO')
                    ->count(),
                'icono' => 'CheckBadgeIcon',
                'color' => 'green',
            ],
            [
                'label' => 'Por Digitalizar',
                'valor' => \App\Models\Tramite::where('estado_actual',
                    \App\Models\EstadoTramite::where('nombre', 'VALIDADO')->value('id')
                )->count(),
                'icono' => 'CloudArrowUpIcon',
                'color' => 'blue',
            ],
            [
                'label' => 'Total Procesados',
                'valor' => \App\Models\TramiteHistorial::where('revisador_por', $user->id)->count(),
                'icono' => 'DocumentDuplicateIcon',
                'color' => 'purple',
            ],
        ];
    }

    private function getActividadOperador($user): array
    {
        $historial = \App\Models\TramiteHistorial::where('revisador_por', $user->id)
            ->with(['tramite.postulacion.estudiante'])
            ->orderBy('fecha_revision', 'desc')
            ->limit(5)
            ->get();

        return $historial->map(function ($item) {
            return [
                'tipo' => $item->estado_nuevo,
                'titulo' => "Trámite {$item->estado_nuevo}",
                'mensaje' => "Estudiante: {$item->tramite->postulacion->estudiante->nombre_completo}",
                'fecha' => $item->fecha_revision->diffForHumans(),
            ];
        })->toArray();
    }

    private function getTramitesPendientes(): int
    {
        return \App\Models\Tramite::where('estado_actual',
            \App\Models\EstadoTramite::where('nombre', 'PENDIENTE')->value('id')
        )->count();
    }

    // ========================================
    // Métodos auxiliares para Admin
    // ========================================

    private function getEstadisticasAdmin($user): array
    {
        $convocatoriaActual = \App\Models\Convocatoria::where('estado', 'ACTIVA')->first();

        if (!$convocatoriaActual) {
            return [];
        }

        $postulaciones = \App\Models\Postulacion::where('id_convocatoria', $convocatoriaActual->id);

        return [
            [
                'label' => 'Total Postulaciones',
                'valor' => $postulaciones->count(),
                'icono' => 'UsersIcon',
                'color' => 'blue',
            ],
            [
                'label' => 'Aprobadas',
                'valor' => $postulaciones->where('estado_postulado', 'APROBADO')->count(),
                'icono' => 'CheckCircleIcon',
                'color' => 'green',
            ],
            [
                'label' => 'Presupuesto Ejecutado',
                'valor' => 'Bs ' . number_format($this->getPresupuestoEjecutado($convocatoriaActual->id), 0),
                'icono' => 'BanknotesIcon',
                'color' => 'yellow',
            ],
            [
                'label' => 'Tasa de Aprobación',
                'valor' => $this->getTasaAprobacion($convocatoriaActual->id) . '%',
                'icono' => 'ChartBarIcon',
                'color' => 'purple',
            ],
        ];
    }

    private function getActividadAdmin($user): array
    {
        $tramites = \App\Models\Tramite::with([
            'postulacion.estudiante',
            'estadoActual',
        ])
        ->orderBy('updated_at', 'desc')
        ->limit(5)
        ->get();

        return $tramites->map(function ($tramite) {
            return [
                'tipo' => $tramite->estadoActual->nombre,
                'titulo' => "Trámite {$tramite->codigo}",
                'mensaje' => "Estudiante: {$tramite->postulacion->estudiante->nombre_completo}",
                'fecha' => $tramite->updated_at->diffForHumans(),
            ];
        })->toArray();
    }

    private function hayTramitesListosParaClasificar(): bool
    {
        return \App\Models\Tramite::where('estado_actual',
            \App\Models\EstadoTramite::where('nombre', 'DIGITALIZADO')->value('id')
        )->exists();
    }

    private function getPresupuestoEjecutado(int $convocatoriaId): float
    {
        return \App\Models\Postulacion::where('id_convocatoria', $convocatoriaId)
            ->where('estado_postulado', 'APROBADO')
            ->join('beca', 'postulacion.id_beca', '=', 'beca.id')
            ->sum('beca.monto');
    }

    private function getTasaAprobacion(int $convocatoriaId): float
    {
        $total = \App\Models\Postulacion::where('id_convocatoria', $convocatoriaId)->count();
        $aprobadas = \App\Models\Postulacion::where('id_convocatoria', $convocatoriaId)
            ->where('estado_postulado', 'APROBADO')
            ->count();

        return $total > 0 ? round(($aprobadas / $total) * 100, 1) : 0;
    }
}
