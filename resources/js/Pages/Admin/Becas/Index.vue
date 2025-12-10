<script setup>
import { ref, watch } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import { debounce } from 'lodash';

// Importa el componente de paginación (ajusta la ruta si es necesario)
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    // La prop 'becas' ahora es el objeto paginado (data, links, current_page, etc.)
    becas: Object,
    filtros: Object,
});

const busqueda = ref(props.filtros?.busqueda || '');

// Hacemos la llamada al servidor (Backend) cuando cambia la búsqueda
// ELIMINAMOS EL CÓDIGO DE FILTRADO LOCAL (becasFiltradas)
watch(busqueda, debounce(function (value) {
    router.get(route('admin.becas.index'),
    {
        // Parámetros que se envían a Laravel
        busqueda: value,
        // Mantener otros filtros si existen, por ejemplo: convocatoria_id: props.filtros?.convocatoria_id
    },
    {
        preserveState: true, // Mantiene el valor de 'busqueda' en el input
        preserveScroll: true,
    });
}, 300)); // 300ms de retraso para evitar inundar el servidor

const eliminar = (id) => {
    if (confirm('¿Eliminar esta beca? Esta acción es irreversible si no hay postulaciones.')) {
        router.delete(route('admin.becas.destroy', id), {
            preserveScroll: true,
            preserveState: true,
        });
    }
};
</script>

<template>
    <Head title="Becas" />

    <div class="p-6 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Becas</h1>
                <p class="text-sm text-gray-600 mt-1">Administración de becas por convocatoria</p>
            </div>
            <a
                :href="route('admin.becas.create')"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
            >
                + Nueva
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
            <input
                v-model="busqueda"
                type="text"
                placeholder="Buscar por nombre de beca o convocatoria..."
                class="w-full px-3 py-2 border rounded-lg text-sm"
            />
        </div>

        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Beca</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Convocatoria</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Cupos</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Requisitos</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr
                        v-for="beca in becas.data"
                        :key="beca.id"
                        class="hover:bg-gray-50 transition"
                    >
                        <td class="px-4 py-3">
                            <a
                                :href="route('admin.becas.show', beca.id)"
                                class="font-medium text-blue-600 hover:underline"
                            >
                                {{ beca.nombre }}
                            </a>
                            <div v-if="beca.descripcion" class="text-xs text-gray-500 mt-1 truncate max-w-xs">
                                {{ beca.descripcion }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-gray-900">{{ beca.convocatoria.nombre }}</div>
                            <span
                                :class="{
                                    'bg-green-100 text-green-800': beca.convocatoria.estado === 'ACTIVA',
                                    'bg-yellow-100 text-yellow-800': beca.convocatoria.estado === 'BORRADOR',
                                    'bg-gray-100 text-gray-800': beca.convocatoria.estado === 'FINALIZADA',
                                }"
                                class="text-xs px-2 py-0.5 rounded-full"
                            >
                                {{ beca.convocatoria.estado }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm text-gray-900">
                            {{ beca.cupos_disponibles }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm text-gray-900">
                            {{ beca.total_requisitos }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm">
                            <a
                                :href="route('admin.becas.edit', beca.id)"
                                class="text-blue-600 hover:text-blue-800 mr-3"
                            >
                                Editar
                            </a>
                            <button
                                @click="eliminar(beca.id)"
                                class="text-red-600 hover:text-red-800"
                            >
                                Eliminar
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="becas.data.length === 0" class="text-center py-12">
                <p class="text-gray-500">No se encontraron becas que coincidan con la búsqueda.</p>
            </div>
        </div>

        <div class="mt-6">
            <Pagination :links="becas.links" />
        </div>
    </div>
</template>
