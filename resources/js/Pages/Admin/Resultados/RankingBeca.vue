<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    ranking: Object,
    filtros: Object,
});

const filtrosActuales = ref({
    estado: props.filtros?.estado || '',
    carrera: props.filtros?.carrera || '',
    ciudad: props.filtros?.ciudad || '',
    puntaje_min: props.filtros?.puntaje_min || '',
    puntaje_max: props.filtros?.puntaje_max || '',
});

const aplicarFiltros = () => {
    router.get(
        route('admin.becas.ranking', props.ranking.beca.id),
        filtrosActuales.value,
        { preserveState: true }
    );
};

const limpiarFiltros = () => {
    filtrosActuales.value = {
        estado: '',
        carrera: '',
        ciudad: '',
        puntaje_min: '',
        puntaje_max: '',
    };
    aplicarFiltros();
};

const exportarExcel = () => {
    window.location.href = route('admin.resultados.exportar', {
        tipo: 'ranking_completo',
        beca_id: props.ranking.beca.id,
        formato: 'xlsx',
        incluir_detalles: true,
    });
};

const getEstadoBadge = (estado) => {
    const badges = {
        APROBADO: 'bg-green-100 text-green-800 border-green-300',
        DENEGADO: 'bg-red-100 text-red-800 border-red-300',
        PENDIENTE: 'bg-amber-100 text-amber-800 border-amber-300',
    };
    return badges[estado] || 'bg-gray-100 text-gray-800 border-gray-300';
};

const getPuntajeColor = (puntaje) => {
    if (puntaje >= 80) return 'text-green-600 font-bold';
    if (puntaje >= 60) return 'text-blue-600 font-semibold';
    if (puntaje >= 40) return 'text-amber-600';
    return 'text-slate-600';
};
</script>

<template>
    <Head :title="`Ranking - ${ranking.beca.nombre}`" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-slate-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

                <!-- Header -->
                <div class="mb-6">
                    <div class="flex items-center gap-3 mb-2">
                        <button
                            @click="$inertia.visit(route('admin.resultados.dashboard'))"
                            class="px-4 py-2 bg-white rounded-lg hover:bg-slate-100 transition-all"
                        >
                            ← Volver
                        </button>
                        <h1 class="text-3xl font-bold text-slate-900">
                            🏆 {{ ranking.beca.nombre }}
                        </h1>
                    </div>
                    <p class="text-slate-600">
                        Código: {{ ranking.beca.codigo }} •
                        Cupos disponibles: <span class="font-bold">{{ ranking.beca.cupos_disponibles }}</span>
                    </p>
                </div>

                <!-- Filtros -->
                <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-slate-900">🔍 Filtros</h2>
                        <button
                            @click="limpiarFiltros"
                            class="text-sm text-blue-600 hover:text-blue-800 font-semibold"
                        >
                            Limpiar filtros
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Estado</label>
                            <select
                                v-model="filtrosActuales.estado"
                                class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            >
                                <option value="">Todos</option>
                                <option value="APROBADO">Aprobados</option>
                                <option value="DENEGADO">Denegados</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Carrera</label>
                            <input
                                v-model="filtrosActuales.carrera"
                                type="text"
                                placeholder="Ej: Ingeniería"
                                class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Ciudad</label>
                            <input
                                v-model="filtrosActuales.ciudad"
                                type="text"
                                placeholder="Ej: La Paz"
                                class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Puntaje Mín</label>
                            <input
                                v-model.number="filtrosActuales.puntaje_min"
                                type="number"
                                min="0"
                                max="100"
                                placeholder="0"
                                class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Puntaje Máx</label>
                            <input
                                v-model.number="filtrosActuales.puntaje_max"
                                type="number"
                                min="0"
                                max="100"
                                placeholder="100"
                                class="w-full px-3 py-2 border-2 border-slate-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            />
                        </div>
                    </div>

                    <div class="flex gap-3 mt-4">
                        <button
                            @click="aplicarFiltros"
                            class="px-6 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold rounded-lg transition-all"
                        >
                            Aplicar Filtros
                        </button>
                        <button
                            @click="exportarExcel"
                            class="px-6 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg transition-all"
                        >
                            📥 Exportar Excel
                        </button>
                    </div>
                </div>

                <!-- Tabla de ranking -->
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-bold">Pos.</th>
                                    <th class="px-6 py-4 text-left text-sm font-bold">Estudiante</th>
                                    <th class="px-6 py-4 text-left text-sm font-bold">CI</th>
                                    <th class="px-6 py-4 text-left text-sm font-bold">Carrera</th>
                                    <th class="px-6 py-4 text-center text-sm font-bold">Puntaje</th>
                                    <th class="px-6 py-4 text-center text-sm font-bold">Estado</th>
                                    <th class="px-6 py-4 text-center text-sm font-bold">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <tr
                                    v-for="(postulacion, index) in ranking.ranking"
                                    :key="postulacion.id"
                                    class="hover:bg-slate-50 transition-colors"
                                    :class="{
                                        'bg-green-50': postulacion.posicion_ranking <= ranking.beca.cupos_disponibles,
                                        'border-t-4 border-red-500': postulacion.posicion_ranking === ranking.beca.cupos_disponibles + 1,
                                    }"
                                >
                                    <td class="px-6 py-4">
                                        <span class="text-2xl font-bold text-slate-700">
                                            {{ postulacion.posicion_ranking || (index + 1) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-slate-900">
                                            {{ postulacion.estudiante.user.name }}
                                        </p>
                                        <p class="text-xs text-slate-500">
                                            {{ postulacion.estudiante.user.email }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-slate-700">
                                        {{ postulacion.estudiante.user.ci }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-700">
                                        {{ postulacion.estudiante.carrera }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="text-3xl font-bold"
                                            :class="getPuntajeColor(postulacion.puntaje_final)"
                                        >
                                            {{ postulacion.puntaje_final }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="inline-block px-3 py-1 rounded-full text-sm font-bold border-2"
                                            :class="getEstadoBadge(postulacion.estado_postulado)"
                                        >
                                            {{ postulacion.estado_postulado === 'APROBADO' ? '✅' : '❌' }}
                                            {{ postulacion.estado_postulado }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button
                                            @click="$inertia.visit(route('admin.postulaciones.detalle', postulacion.id))"
                                            class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-lg transition-all"
                                        >
                                            Ver Detalle
                                        </button>
                                    </td>
                                </tr>

                                <!-- Línea de corte de cupos -->
                                <tr v-if="ranking.ranking.length > ranking.beca.cupos_disponibles">
                                    <td colspan="7" class="px-6 py-3 bg-red-50">
                                        <div class="flex items-center justify-center gap-2 text-red-700 font-bold">
                                            <span>⚠️</span>
                                            <span>CORTE DE CUPOS - Por debajo de esta línea: DENEGADOS</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="bg-slate-50 px-6 py-4 flex items-center justify-between border-t border-slate-200">
                        <div class="text-sm text-slate-600">
                            Mostrando {{ ranking.ranking.length }} de {{ ranking.pagination.total }} resultados
                        </div>
                        <div class="flex gap-2">
                            <button
                                v-if="ranking.pagination.current_page > 1"
                                @click="router.get(route('admin.becas.ranking', { id: ranking.beca.id, page: ranking.pagination.current_page - 1 }))"
                                class="px-4 py-2 bg-white border border-slate-300 rounded-lg hover:bg-slate-100 transition-all"
                            >
                                ← Anterior
                            </button>
                            <span class="px-4 py-2 bg-blue-500 text-white rounded-lg font-semibold">
                                {{ ranking.pagination.current_page }} / {{ ranking.pagination.last_page }}
                            </span>
                            <button
                                v-if="ranking.pagination.current_page < ranking.pagination.last_page"
                                @click="router.get(route('admin.becas.ranking', { id: ranking.beca.id, page: ranking.pagination.current_page + 1 }))"
                                class="px-4 py-2 bg-white border border-slate-300 rounded-lg hover:bg-slate-100 transition-all"
                            >
                                Siguiente →
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Animación para el corte de cupos */
@keyframes pulse-red {
    0%, 100% {
        background-color: rgb(254 242 242);
    }
    50% {
        background-color: rgb(254 226 226);
    }
}

.border-t-4.border-red-500 {
    animation: pulse-red 2s ease-in-out infinite;
}
</style>
