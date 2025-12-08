<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    estadisticas: Object,
});

const page = usePage();
const showSuccessMessage = ref(false);
const showErrorMessage = ref(false);

onMounted(() => {
    // Mostrar mensajes flash si existen
    if (page.props.flash?.success) {
        showSuccessMessage.value = true;
        setTimeout(() => {
            showSuccessMessage.value = false;
        }, 5000);
    }
    if (page.props.flash?.error) {
        showErrorMessage.value = true;
        setTimeout(() => {
            showErrorMessage.value = false;
        }, 5000);
    }
});

const stats = [
    {
        name: 'Total Procesados',
        value: props.estadisticas.total_procesados,
        icon: '📊',
        color: 'from-blue-500 to-blue-600',
    },
    {
        name: 'Pendientes',
        value: props.estadisticas.pendientes,
        icon: '⏳',
        color: 'from-amber-500 to-amber-600',
    },
    {
        name: 'Validados Hoy',
        value: props.estadisticas.validados_hoy,
        icon: '✅',
        color: 'from-green-500 to-green-600',
    },
];

const actions = [
    {
        title: 'Buscar Trámite',
        description: 'Buscar trámite por CI del estudiante',
        icon: '🔍',
        route: 'operador.tramites.buscar',
        color: 'bg-blue-500 hover:bg-blue-600',
    },
    {
        title: 'Trámites Pendientes',
        description: 'Ver lista de trámites pendientes de validación',
        icon: '📋',
        route: 'operador.tramites.pendientes',
        color: 'bg-amber-500 hover:bg-amber-600',
    },
    {
        title: 'Digitalizar Documentos',
        description: 'Trámites validados listos para digitalizar',
        icon: '📄',
        route: 'operador.tramites.validados',
        color: 'bg-green-500 hover:bg-green-600',
    },
    {
        title: 'Mi Historial',
        description: 'Ver historial de trámites procesados',
        icon: '📚',
        route: 'operador.historial',
        color: 'bg-purple-500 hover:bg-purple-600',
    },
];
</script>

<template>
    <Head title="Dashboard - Operador" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-slate-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

                <!-- Mensajes Flash -->
                <Transition name="slide-down">
                    <div
                        v-if="showSuccessMessage && page.props.flash?.success"
                        class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg shadow-lg"
                    >
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">✅</span>
                            <p class="text-green-800 font-semibold">{{ page.props.flash.success }}</p>
                        </div>
                    </div>
                </Transition>

                <Transition name="slide-down">
                    <div
                        v-if="showErrorMessage && page.props.flash?.error"
                        class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-lg"
                    >
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">❌</span>
                            <p class="text-red-800 font-semibold">{{ page.props.flash.error }}</p>
                        </div>
                    </div>
                </Transition>

                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-slate-900 mb-2">
                        Panel del Operador
                    </h1>
                    <p class="text-lg text-slate-600">
                        Sistema de validación y digitalización de trámites
                    </p>
                </div>

                <!-- Estadísticas -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div
                        v-for="stat in stats"
                        :key="stat.name"
                        class="relative overflow-hidden rounded-2xl bg-white shadow-xl shadow-slate-200/50 hover:shadow-2xl hover:shadow-slate-300/50 transition-all duration-300"
                    >
                        <div class="absolute inset-0 bg-gradient-to-br opacity-5" :class="stat.color"></div>
                        <div class="relative p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-600 uppercase tracking-wider">
                                        {{ stat.name }}
                                    </p>
                                    <p class="mt-2 text-4xl font-bold text-slate-900">
                                        {{ stat.value }}
                                    </p>
                                </div>
                                <div class="text-6xl opacity-20">
                                    {{ stat.icon }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-slate-900 mb-6">
                        Acciones Rápidas
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <Link
                            v-for="action in actions"
                            :key="action.title"
                            :href="route(action.route)"
                            class="group relative overflow-hidden rounded-2xl bg-white shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1"
                        >
                            <div class="absolute inset-0 bg-gradient-to-br opacity-0 group-hover:opacity-10 transition-opacity" :class="action.color"></div>
                            <div class="relative p-6">
                                <div class="text-5xl mb-4">
                                    {{ action.icon }}
                                </div>
                                <h3 class="text-lg font-bold text-slate-900 mb-2">
                                    {{ action.title }}
                                </h3>
                                <p class="text-sm text-slate-600">
                                    {{ action.description }}
                                </p>
                            </div>
                            <div
                                class="absolute bottom-0 left-0 right-0 h-1 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"
                                :class="action.color"
                            ></div>
                        </Link>
                    </div>
                </div>

                <!-- Flujo del Proceso -->
                <div class="bg-white rounded-2xl shadow-xl p-8">
                    <h2 class="text-2xl font-bold text-slate-900 mb-6">
                        Flujo del Proceso
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center text-2xl mb-3">
                                🔍
                            </div>
                            <h4 class="font-bold text-slate-900 mb-1">Búsqueda</h4>
                            <p class="text-xs text-slate-600">Buscar por CI</p>
                        </div>

                        <div class="hidden md:flex items-center justify-center">
                            <div class="w-full h-1 bg-gradient-to-r from-blue-500 to-amber-500"></div>
                        </div>

                        <div class="flex flex-col items-center text-center">
                            <div class="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center text-2xl mb-3">
                                ✅
                            </div>
                            <h4 class="font-bold text-slate-900 mb-1">Validación</h4>
                            <p class="text-xs text-slate-600">Revisar docs físicos</p>
                        </div>

                        <div class="hidden md:flex items-center justify-center">
                            <div class="w-full h-1 bg-gradient-to-r from-amber-500 to-green-500"></div>
                        </div>

                        <div class="flex flex-col items-center text-center">
                            <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center text-2xl mb-3">
                                📄
                            </div>
                            <h4 class="font-bold text-slate-900 mb-1">Digitalización</h4>
                            <p class="text-xs text-slate-600">Escanear documentos</p>
                        </div>

                        <div class="hidden md:flex items-center justify-center">
                            <div class="w-full h-1 bg-gradient-to-r from-green-500 to-purple-500"></div>
                        </div>

                        <div class="flex flex-col items-center text-center">
                            <div class="w-16 h-16 rounded-full bg-purple-100 flex items-center justify-center text-2xl mb-3">
                                🤖
                            </div>
                            <h4 class="font-bold text-slate-900 mb-1">Clasificación</h4>
                            <p class="text-xs text-slate-600">Automático</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Animaciones personalizadas */
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

.group:hover .text-5xl {
    animation: float 2s ease-in-out infinite;
}

/* Animación para mensajes flash */
.slide-down-enter-active {
    transition: all 0.3s ease-out;
}

.slide-down-leave-active {
    transition: all 0.3s ease-in;
}

.slide-down-enter-from {
    transform: translateY(-20px);
    opacity: 0;
}

.slide-down-leave-to {
    transform: translateY(-20px);
    opacity: 0;
}
</style>
