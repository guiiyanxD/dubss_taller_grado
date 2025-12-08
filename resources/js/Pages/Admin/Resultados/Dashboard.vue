<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Chart as ChartJS, ArcElement, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend } from 'chart.js';
import { Doughnut, Bar } from 'vue-chartjs';

ChartJS.register(ArcElement, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

const props = defineProps({
    estadisticas: Object,
    convocatorias: Array,
    convocatoria_seleccionada: Number,
});

const convocatoriaSeleccionada = ref(props.convocatoria_seleccionada);

// Datos para gráfico de dona (distribución de resultados)
const distribucionData = computed(() => ({
    labels: ['Aprobadas', 'Denegadas', 'En Proceso'],
    datasets: [{
        data: [
            props.estadisticas.aprobadas,
            props.estadisticas.denegadas,
            props.estadisticas.en_proceso
        ],
        backgroundColor: ['#10b981', '#ef4444', '#f59e0b'],
        borderWidth: 0,
    }]
}));

// Datos para gráfico de barras (distribución de puntajes)
const puntajesData = computed(() => ({
    labels: ['0-20', '21-40', '41-60', '61-80', '81-100'],
    datasets: [{
        label: 'Cantidad de Postulaciones',
        data: [
            props.estadisticas.distribucion_puntajes['0-20'] || 0,
            props.estadisticas.distribucion_puntajes['21-40'] || 0,
            props.estadisticas.distribucion_puntajes['41-60'] || 0,
            props.estadisticas.distribucion_puntajes['61-80'] || 0,
            props.estadisticas.distribucion_puntajes['81-100'] || 0,
        ],
        backgroundColor: 'rgba(59, 130, 246, 0.8)',
        borderRadius: 8,
    }]
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
        }
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 0,
    }).format(value);
};

const cambiarConvocatoria = () => {
    window.location.href = route('admin.resultados.dashboard', {
        convocatoria_id: convocatoriaSeleccionada.value
    });
};
</script>

<template>
    <Head title="Dashboard de Resultados" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h1 class="text-4xl font-bold text-white mb-2">
                                📊 Dashboard de Resultados
                            </h1>
                            <p class="text-blue-200">
                                Análisis completo de postulaciones y clasificaciones
                            </p>
                        </div>

                        <!-- Selector de convocatoria -->
                        <div class="bg-white/10 backdrop-blur-lg rounded-xl p-4">
                            <label class="block text-white text-sm font-semibold mb-2">
                                Convocatoria:
                            </label>
                            <select
                                v-model="convocatoriaSeleccionada"
                                @change="cambiarConvocatoria"
                                class="px-4 py-2 bg-white/20 text-white border border-white/30 rounded-lg focus:ring-2 focus:ring-blue-500"
                            >
                                <option :value="null" class="text-slate-900">Todas</option>
                                <option
                                    v-for="conv in convocatorias"
                                    :key="conv.id"
                                    :value="conv.id"
                                    class="text-slate-900"
                                >
                                    {{ conv.nombre }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- KPIs principales -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-2xl">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-5xl">📝</span>
                            <div class="text-right">
                                <p class="text-blue-100 text-sm font-semibold">Total</p>
                                <p class="text-4xl font-bold">{{ estadisticas.total_postulaciones }}</p>
                            </div>
                        </div>
                        <p class="text-sm text-blue-100">Postulaciones</p>
                    </div>

                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-6 text-white shadow-2xl">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-5xl">✅</span>
                            <div class="text-right">
                                <p class="text-green-100 text-sm font-semibold">Aprobadas</p>
                                <p class="text-4xl font-bold">{{ estadisticas.aprobadas }}</p>
                            </div>
                        </div>
                        <p class="text-sm text-green-100">
                            Tasa: {{ estadisticas.tasa_aprobacion }}%
                        </p>
                    </div>

                    <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl p-6 text-white shadow-2xl">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-5xl">📊</span>
                            <div class="text-right">
                                <p class="text-amber-100 text-sm font-semibold">Promedio</p>
                                <p class="text-4xl font-bold">{{ estadisticas.promedio_puntaje }}</p>
                            </div>
                        </div>
                        <p class="text-sm text-amber-100">Puntaje Promedio</p>
                    </div>

                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 text-white shadow-2xl">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-5xl">💰</span>
                            <div class="text-right">
                                <p class="text-purple-100 text-sm font-semibold">Ejecutado</p>
                                <p class="text-2xl font-bold">{{ formatCurrency(estadisticas.presupuesto_utilizado) }}</p>
                            </div>
                        </div>
                        <p class="text-sm text-purple-100">
                            De {{ formatCurrency(estadisticas.presupuesto_total) }}
                        </p>
                    </div>
                </div>

                <!-- Gráficos -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Distribución de resultados -->
                    <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 shadow-2xl">
                        <h2 class="text-xl font-bold text-white mb-6">
                            Distribución de Resultados
                        </h2>
                        <div class="h-64">
                            <Doughnut :data="distribucionData" :options="chartOptions" />
                        </div>
                    </div>

                    <!-- Distribución de puntajes -->
                    <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 shadow-2xl">
                        <h2 class="text-xl font-bold text-white mb-6">
                            Distribución de Puntajes
                        </h2>
                        <div class="h-64">
                            <Bar :data="puntajesData" :options="chartOptions" />
                        </div>
                    </div>
                </div>

                <!-- Rankings por Beca -->
                <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 shadow-2xl mb-8">
                    <h2 class="text-2xl font-bold text-white mb-6">
                        🎯 Rankings por Beca
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Link
                            v-for="beca in estadisticas.becas"
                            :key="beca.id"
                            :href="route('admin.becas.ranking', beca.id)"
                            class="group bg-white/5 hover:bg-white/10 border border-white/20 rounded-xl p-5 transition-all hover:scale-105"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-lg font-bold text-white group-hover:text-blue-300 transition-colors">
                                    {{ beca.nombre }}
                                </h3>
                                <span class="text-2xl">🏆</span>
                            </div>

                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div>
                                    <p class="text-2xl font-bold text-blue-300">{{ beca.cupos }}</p>
                                    <p class="text-xs text-white/70">Cupos</p>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-green-300">{{ beca.aprobadas }}</p>
                                    <p class="text-xs text-white/70">Aprobadas</p>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-amber-300">{{ beca.tasa_ocupacion }}%</p>
                                    <p class="text-xs text-white/70">Ocupación</p>
                                </div>
                            </div>

                            <!-- Barra de progreso -->
                            <div class="mt-4 w-full h-2 bg-white/20 rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-gradient-to-r from-green-400 to-blue-500 transition-all"
                                    :style="{ width: beca.tasa_ocupacion + '%' }"
                                ></div>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Acciones rápidas -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Link
                        :href="route('admin.resultados.comparacion')"
                        class="bg-gradient-to-br from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 rounded-2xl p-6 text-white shadow-2xl transition-all transform hover:scale-105"
                    >
                        <span class="text-4xl mb-3 block">📈</span>
                        <h3 class="text-xl font-bold mb-2">Comparar Convocatorias</h3>
                        <p class="text-sm text-blue-100">Ver tendencias históricas</p>
                    </Link>

                    <button
                        @click="$inertia.post(route('admin.resultados.exportar', { tipo: 'ranking_completo', formato: 'xlsx' }))"
                        class="bg-gradient-to-br from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 rounded-2xl p-6 text-white shadow-2xl transition-all transform hover:scale-105 text-left"
                    >
                        <span class="text-4xl mb-3 block">📥</span>
                        <h3 class="text-xl font-bold mb-2">Exportar Reportes</h3>
                        <p class="text-sm text-green-100">Descargar en Excel/PDF</p>
                    </button>

                    <button
                        @click="$inertia.post(route('admin.resultados.notificar', { convocatoria_id: convocatoriaSeleccionada }))"
                        class="bg-gradient-to-br from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 rounded-2xl p-6 text-white shadow-2xl transition-all transform hover:scale-105 text-left"
                    >
                        <span class="text-4xl mb-3 block">📧</span>
                        <h3 class="text-xl font-bold mb-2">Notificar Resultados</h3>
                        <p class="text-sm text-purple-100">Envío masivo de emails</p>
                    </button>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Animaciones para las tarjetas */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.grid > * {
    animation: fadeInUp 0.5s ease-out;
}
</style>
