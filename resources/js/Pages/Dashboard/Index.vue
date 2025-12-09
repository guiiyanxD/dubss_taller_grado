<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    UserCircleIcon,
    BellIcon,
    ClockIcon,
    CheckCircleIcon,
    DocumentTextIcon,
    MagnifyingGlassIcon,
    CloudArrowUpIcon,
    DocumentDuplicateIcon,
    ChartBarIcon,
    MegaphoneIcon,
    AcademicCapIcon,
    CpuChipIcon,
    DocumentArrowDownIcon,
    Cog6ToothIcon,
    PresentationChartLineIcon,
    TrophyIcon,
    ChartPieIcon,
    ExclamationTriangleIcon,
    CheckBadgeIcon,
    UsersIcon,
    BanknotesIcon,
    ClipboardDocumentListIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    usuario: Object,
    estadisticas: Array,
    acciones_rapidas: Array,
    actividad_reciente: Array,
});

// Mapeo de iconos (nombre string → componente)
const iconComponents = {
    UserCircleIcon,
    BellIcon,
    ClockIcon,
    CheckCircleIcon,
    DocumentTextIcon,
    MagnifyingGlassIcon,
    CloudArrowUpIcon,
    DocumentDuplicateIcon,
    ChartBarIcon,
    MegaphoneIcon,
    AcademicCapIcon,
    CpuChipIcon,
    DocumentArrowDownIcon,
    Cog6ToothIcon,
    PresentationChartLineIcon,
    TrophyIcon,
    ChartPieIcon,
    ExclamationTriangleIcon,
    CheckBadgeIcon,
    UsersIcon,
    BanknotesIcon,
    ClipboardDocumentListIcon,
};

// Colores para los cards
const colorClasses = {
    blue: {
        bg: 'from-blue-500 to-blue-600',
        text: 'text-blue-600',
        light: 'bg-blue-50',
        border: 'border-blue-200',
    },
    green: {
        bg: 'from-green-500 to-green-600',
        text: 'text-green-600',
        light: 'bg-green-50',
        border: 'border-green-200',
    },
    yellow: {
        bg: 'from-yellow-500 to-yellow-600',
        text: 'text-yellow-600',
        light: 'bg-yellow-50',
        border: 'border-yellow-200',
    },
    red: {
        bg: 'from-red-500 to-red-600',
        text: 'text-red-600',
        light: 'bg-red-50',
        border: 'border-red-200',
    },
    purple: {
        bg: 'from-purple-500 to-purple-600',
        text: 'text-purple-600',
        light: 'bg-purple-50',
        border: 'border-purple-200',
    },
    orange: {
        bg: 'from-orange-500 to-orange-600',
        text: 'text-orange-600',
        light: 'bg-orange-50',
        border: 'border-orange-200',
    },
    indigo: {
        bg: 'from-indigo-500 to-indigo-600',
        text: 'text-indigo-600',
        light: 'bg-indigo-50',
        border: 'border-indigo-200',
    },
    gray: {
        bg: 'from-gray-500 to-gray-600',
        text: 'text-gray-600',
        light: 'bg-gray-50',
        border: 'border-gray-200',
    },
};

// Saludo según la hora
const saludo = computed(() => {
    const hora = new Date().getHours();
    if (hora < 12) return '¡Buenos días';
    if (hora < 19) return '¡Buenas tardes';
    return '¡Buenas noches';
});

// Badge de rol con color
const rolBadgeColor = computed(() => {
    const colores = {
        'Estudiante': 'blue',
        'Operador': 'green',
        'Dpto. Sistema': 'purple',
        'Dirección': 'red',
    };
    return colores[props.usuario.rol] || 'gray';
});
</script>

<template>
    <Head title="Dashboard Principal" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

                <!-- Header con saludo personalizado -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-4xl font-bold text-slate-900 mb-2">
                                {{ saludo }}, {{ usuario.nombre }}! 👋
                            </h1>
                            <div class="flex items-center gap-3">
                                <span
                                    class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold"
                                    :class="`bg-${rolBadgeColor}-100 text-${rolBadgeColor}-800`"
                                >
                                    {{ usuario.rol }}
                                </span>
                                <span class="text-slate-600">{{ usuario.email }}</span>
                            </div>
                        </div>

                        <!-- Logo DUBSS -->
                        <div class="hidden md:block">
                            <div class="text-right">
                                <div class="text-2xl font-bold text-blue-600">DUBSS</div>
                                <div class="text-sm text-slate-600">Sistema de Becas</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPIs / Estadísticas -->
                <div v-if="estadisticas.length > 0" class="mb-8">
                    <h2 class="text-xl font-bold text-slate-900 mb-4">📊 Estadísticas</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div
                            v-for="stat in estadisticas"
                            :key="stat.label"
                            class="bg-white rounded-2xl shadow-lg p-6 border-l-4 transition-all hover:shadow-xl hover:-translate-y-1"
                            :class="colorClasses[stat.color]?.border"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <component
                                    :is="iconComponents[stat.icono]"
                                    class="w-10 h-10"
                                    :class="colorClasses[stat.color]?.text"
                                />
                            </div>
                            <div class="text-3xl font-bold text-slate-900 mb-1">
                                {{ stat.valor }}
                            </div>
                            <div class="text-sm font-medium text-slate-600">
                                {{ stat.label }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-slate-900 mb-4">⚡ Acciones Rápidas</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <Link
                            v-for="accion in acciones_rapidas"
                            :key="accion.titulo"
                            :href="accion.ruta"
                            :class="[
                                'group relative bg-white rounded-2xl shadow-lg p-6 transition-all',
                                accion.habilitado
                                    ? 'hover:shadow-2xl hover:-translate-y-2 cursor-pointer'
                                    : 'opacity-50 cursor-not-allowed'
                            ]"
                            :disabled="!accion.habilitado"
                        >
                            <!-- Badge si existe -->
                            <div
                                v-if="accion.badge && accion.badge > 0"
                                class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-8 h-8 flex items-center justify-center shadow-lg animate-pulse"
                            >
                                {{ accion.badge }}
                            </div>

                            <!-- Gradiente de fondo decorativo -->
                            <div
                                class="absolute inset-0 bg-gradient-to-br opacity-0 group-hover:opacity-10 rounded-2xl transition-opacity"
                                :class="colorClasses[accion.color]?.bg"
                            ></div>

                            <div class="relative z-10">
                                <!-- Icono -->
                                <div
                                    class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4 transition-transform group-hover:scale-110"
                                    :class="colorClasses[accion.color]?.light"
                                >
                                    <component
                                        :is="iconComponents[accion.icono]"
                                        class="w-8 h-8"
                                        :class="colorClasses[accion.color]?.text"
                                    />
                                </div>

                                <!-- Título -->
                                <h3 class="text-lg font-bold text-slate-900 mb-2 group-hover:text-blue-600 transition-colors">
                                    {{ accion.titulo }}
                                </h3>

                                <!-- Descripción -->
                                <p class="text-sm text-slate-600">
                                    {{ accion.descripcion }}
                                </p>

                                <!-- Indicador de deshabilitado -->
                                <div v-if="!accion.habilitado" class="mt-3">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        No disponible
                                    </span>
                                </div>
                            </div>

                            <!-- Flecha decorativa -->
                            <div
                                v-if="accion.habilitado"
                                class="absolute bottom-4 right-4 opacity-0 group-hover:opacity-100 transition-all transform translate-x-2 group-hover:translate-x-0"
                            >
                                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Actividad Reciente -->
                <div v-if="actividad_reciente.length > 0" class="mb-8">
                    <h2 class="text-xl font-bold text-slate-900 mb-4">🕒 Actividad Reciente</h2>
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <div class="space-y-4">
                            <div
                                v-for="(item, index) in actividad_reciente"
                                :key="index"
                                class="flex items-start gap-4 p-4 rounded-xl hover:bg-slate-50 transition-colors"
                            >
                                <!-- Icono con color según tipo -->
                                <div
                                    class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0"
                                    :class="{
                                        'bg-green-100': item.tipo === 'VALIDADO' || item.tipo === 'APROBADO',
                                        'bg-blue-100': item.tipo === 'DIGITALIZADO' || item.tipo === 'CLASIFICADO',
                                        'bg-yellow-100': item.tipo === 'PENDIENTE' || item.tipo === 'ALERTA',
                                        'bg-red-100': item.tipo === 'RECHAZADO' || item.tipo === 'DENEGADO',
                                        'bg-purple-100': !['VALIDADO', 'APROBADO', 'DIGITALIZADO', 'CLASIFICADO', 'PENDIENTE', 'ALERTA', 'RECHAZADO', 'DENEGADO'].includes(item.tipo),
                                    }"
                                >
                                    <span class="text-xl">
                                        {{ item.tipo === 'VALIDADO' || item.tipo === 'APROBADO' ? '✅' : '' }}
                                        {{ item.tipo === 'DIGITALIZADO' ? '📄' : '' }}
                                        {{ item.tipo === 'CLASIFICADO' ? '🎯' : '' }}
                                        {{ item.tipo === 'PENDIENTE' || item.tipo === 'ALERTA' ? '⏳' : '' }}
                                        {{ item.tipo === 'RECHAZADO' || item.tipo === 'DENEGADO' ? '❌' : '' }}
                                    </span>
                                </div>

                                <!-- Contenido -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <h4 class="text-sm font-bold text-slate-900">
                                                {{ item.titulo }}
                                            </h4>
                                            <p class="text-sm text-slate-600 mt-1">
                                                {{ item.mensaje }}
                                            </p>
                                        </div>

                                        <!-- Badge de no leído -->
                                        <div v-if="item.leido === false">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Nuevo
                                            </span>
                                        </div>
                                    </div>

                                    <div class="text-xs text-slate-500 mt-2">
                                        {{ item.fecha }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ver más -->
                        <div class="mt-6 pt-4 border-t border-slate-200 text-center">
                            <Link
                                :href="usuario.rol === 'Estudiante' ? route('estudiante.notificaciones') : route('operador.historial')"
                                class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors"
                            >
                                Ver toda la actividad →
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Footer con ayuda -->
                <div class="bg-white rounded-2xl shadow-lg p-6 border-t-4 border-blue-500">
                    <div class="flex items-start gap-4">
                        <div class="text-4xl">💡</div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-slate-900 mb-2">
                                ¿Necesitas ayuda?
                            </h3>
                            <p class="text-sm text-slate-600 mb-4">
                                Si tienes dudas sobre cómo usar el sistema, consulta nuestra guía de usuario o contacta con soporte técnico.
                            </p>
                            <div class="flex gap-3">
                                <a
                                    href="#"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors"
                                >
                                    📖 Guía de Usuario
                                </a>
                                <a
                                    href="#"
                                    class="inline-flex items-center px-4 py-2 bg-slate-100 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-200 transition-colors"
                                >
                                    📧 Contactar Soporte
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Animación suave para los cards */
.group {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Pulso animado para badges */
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
