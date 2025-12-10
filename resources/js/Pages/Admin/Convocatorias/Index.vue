<script setup>
import { ref, watch } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    // La prop 'convocatorias' ahora es el objeto paginado (Object)
    convocatorias: Object,
    filtros: Object,
});

// Usamos los filtros iniciales del backend para mantener el estado
const busqueda = ref(props.filtros?.busqueda || '');
const estadoFiltro = ref(props.filtros?.estado || '');

// Función debounced para enviar la petición de búsqueda/filtro al servidor
const applyFilters = debounce(() => {
    router.get(route('admin.convocatorias.index'), {
        busqueda: busqueda.value,
        estado: estadoFiltro.value,
    }, {
        preserveState: true, // Mantiene el valor de 'busqueda' y 'estadoFiltro'
        preserveScroll: true,
        only: ['convocatorias', 'filtros'], // Solo pide estos datos de vuelta
    });
}, 300); // 300ms de retraso

// Monitoreamos ambos filtros (busqueda y estado)
watch([busqueda, estadoFiltro], applyFilters);

const eliminar = (id) => {
    if (confirm('¿Eliminar esta convocatoria? Si tiene postulaciones, no será posible.')) {
        router.delete(route('admin.convocatorias.destroy', id), {
            preserveScroll: true,
        });
    }
};

const estadoBadgeClass = (estado) => {
    return {
        'ACTIVA': 'bg-green-100 text-green-800 border-green-200',
        'BORRADOR': 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'FINALIZADA': 'bg-gray-100 text-gray-800 border-gray-200',
    }[estado] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Convocatorias" />

    <div class="p-6 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Convocatorias</h1>
                <p class="text-sm text-gray-600 mt-1">Gestión de períodos de postulación</p>
            </div>
            <a
                :href="route('admin.convocatorias.create')"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
            >
                + Nueva
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
            <div class="flex gap-3">
                <input
                    v-model="busqueda"
                    type="text"
                    placeholder="Buscar por nombre..."
                    class="flex-1 px-3 py-2 border rounded-lg text-sm"

                    />

                <select
                    v-model="estadoFiltro"
                    class="px-3 py-2 border rounded-lg text-sm"

                    >
                    <option value="">Todos</option>
                    <option value="ACTIVA">Activas</option>
                    <option value="BORRADOR">Borradores</option>
                    <option value="FINALIZADA">Finalizadas</option>
                </select>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fechas</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Becas</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr
                        v-for="conv in convocatorias.data"
                        :key="conv.id"
                        class="hover:bg-gray-50 transition"
                    >
                        <td class="px-4 py-3">
                            <a
                                :href="route('admin.convocatorias.show', conv.id)"
                                class="font-medium text-blue-600 hover:underline"
                            >
                                {{ conv.nombre }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                            {{ conv.fecha_inicio }} → {{ conv.fecha_fin }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span
                                :class="estadoBadgeClass(conv.estado)"
                                class="px-2 py-1 text-xs font-medium rounded-full border"
                            >
                                {{ conv.estado }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm text-gray-900">
                            {{ conv.total_becas }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm">
                            <a
                                :href="route('admin.convocatorias.edit', conv.id)"
                                class="text-blue-600 hover:text-blue-800 mr-3"
                            >
                                Editar
                            </a>
                            <button
                                @click="eliminar(conv.id)"
                                class="text-red-600 hover:text-red-800"
                            >
                                Eliminar
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="convocatorias.data.length === 0" class="text-center py-12">
                <p class="text-gray-500">No se encontraron convocatorias</p>
            </div>
        </div>

        <div class="mt-6">
            <Pagination :links="convocatorias.links" />
        </div>
    </div>
</template>
