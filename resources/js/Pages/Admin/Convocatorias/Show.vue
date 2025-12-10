<script setup>
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    convocatoria: Object,
    estadisticas: Object,
});

const activar = () => {
    if (confirm('¿Activar esta convocatoria?')) {
        router.post(route('admin.convocatorias.activar', props.convocatoria.id), {}, {
            preserveScroll: true,
        });
    }
};

const finalizar = () => {
    if (confirm('¿Finalizar esta convocatoria? Esta acción no se puede revertir.')) {
        router.post(route('admin.convocatorias.finalizar', props.convocatoria.id), {}, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head :title="convocatoria.nombre" />

    <div class="p-6 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <a
                :href="route('admin.convocatorias.index')"
                class="text-sm text-blue-600 hover:underline mb-2 inline-block"
            >
                ← Volver a convocatorias
            </a>
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ convocatoria.nombre }}</h1>
                    <p class="text-gray-600 mt-1">{{ convocatoria.fecha_inicio }} - {{ convocatoria.fecha_fin }}</p>
                </div>
                <div class="flex gap-2">
                    <a
                        :href="route('admin.convocatorias.edit', convocatoria.id)"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm"
                    >
                        Editar
                    </a>
                    <button
                        v-if="convocatoria.estado === 'BORRADOR'"
                        @click="activar"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm"
                    >
                        Activar
                    </button>
                    <button
                        v-if="convocatoria.estado === 'ACTIVA'"
                        @click="finalizar"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm"
                    >
                        Finalizar
                    </button>
                </div>
            </div>
        </div>

        <!-- Estadísticas compactas -->
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="text-2xl font-bold text-gray-900">{{ estadisticas.total_postulaciones }}</div>
                <div class="text-xs text-gray-600">Postulaciones</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="text-2xl font-bold text-green-600">{{ estadisticas.aprobadas }}</div>
                <div class="text-xs text-gray-600">Aprobadas</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="text-2xl font-bold text-gray-900">{{ estadisticas.total_becas }}</div>
                <div class="text-xs text-gray-600">Becas</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="text-2xl font-bold text-blue-600">{{ estadisticas.cupos_totales }}</div>
                <div class="text-xs text-gray-600">Cupos Totales</div>
            </div>
        </div>

        <!-- Lista de becas (solo nombres y datos básicos) -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Becas</h2>
                <a
                    :href="route('admin.becas.create', { convocatoria_id: convocatoria.id })"
                    class="text-sm text-blue-600 hover:underline"
                >
                    + Agregar beca
                </a>
            </div>

            <div v-if="convocatoria.becas.length > 0" class="space-y-2">
                <div
                    v-for="beca in convocatoria.becas"
                    :key="beca.id"
                    class="flex justify-between items-center p-3 border rounded-lg hover:bg-gray-50"
                >
                    <div>
                        <a
                            :href="route('admin.becas.show', beca.id)"
                            class="font-medium text-blue-600 hover:underline"
                        >
                            {{ beca.nombre }}
                        </a>
                        <div class="text-xs text-gray-600 mt-1">
                            {{ beca.cupos_disponibles }} cupos · {{ beca.requisitos_count }} requisitos
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-8 text-gray-500 text-sm">
                No hay becas agregadas
            </div>
        </div>
    </div>
</template>
