<script setup>
import { ref, watch } from 'vue'; // Quitamos 'computed'
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import Pagination from '@/Components/Pagination.vue'; // Necesario para la paginación

const props = defineProps({
    // La prop 'requisitos' ahora es el objeto paginado (Object)
    requisitos: Object,
    tipos: Object,
    filtros: Object,
});

const busqueda = ref(props.filtros?.busqueda || '');
const tipoFiltro = ref(props.filtros?.tipo || '');
const obligatorioFiltro = ref(props.filtros?.obligatorio ?? ''); // Nuevo filtro

// --- ELIMINAMOS el código de `requisitosFiltrados` y `computed` ---

// Función debounced para enviar la petición de búsqueda/filtro al servidor
const applyFilters = debounce(() => {
    // Convertimos el valor del select de obligatorio a un valor que el backend entienda (true/false/null)
    let obligatorioValue = obligatorioFiltro.value === '' ? null : obligatorioFiltro.value;

    router.get(route('admin.requisitos.index'), {
        busqueda: busqueda.value,
        tipo: tipoFiltro.value,
        obligatorio: obligatorioValue,
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['requisitos', 'filtros'],
    });
}, 300);

// Monitoreamos todos los campos de filtro
watch([busqueda, tipoFiltro, obligatorioFiltro], applyFilters);

const eliminar = (id) => {
    if (confirm('¿Eliminar este requisito?')) {
        // ... (código delete) ...
    }
};

const tipoLabel = (tipo) => {
    return props.tipos[tipo] || tipo;
};
</script>

<template>
    <Head title="Requisitos" />

    <div class="p-6 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Requisitos</h1>
                <p class="text-sm text-gray-600 mt-1">Documentos necesarios para postular</p>
            </div>
            <a
                :href="route('admin.requisitos.create')"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
            >
                + Nuevo
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
                    v-model="tipoFiltro"
                    class="px-3 py-2 border rounded-lg text-sm"
                >
                    <option value="">Todos los tipos</option>
                    <option
                        v-for="(label, valor) in tipos"
                        :key="valor"
                        :value="valor"
                    >
                        {{ label }}
                    </option>
                </select>

                <select
                    v-model="obligatorioFiltro"
                    class="px-3 py-2 border rounded-lg text-sm"
                >
                    <option value="">Todos</option>
                    <option :value="true">Obligatorios</option>
                    <option :value="false">Opcionales</option>
                </select>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Obligatorio</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Becas</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr
                        v-for="req in requisitos.data" :key="req.id"
                        class="hover:bg-gray-50 transition"
                    >
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">{{ req.nombre }}</div>
                            <div v-if="req.descripcion" class="text-xs text-gray-500 mt-1 truncate max-w-md">
                                {{ req.descripcion }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-gray-600">{{ tipoLabel(req.tipo) }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span
                                :class="req.obligatorio ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'"
                                class="px-2 py-1 text-xs rounded-full"
                            >
                                {{ req.obligatorio ? 'Sí' : 'No' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm text-gray-900">
                            {{ req.total_becas }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm">
                            <a
                                :href="route('admin.requisitos.edit', req.id)"
                                class="text-blue-600 hover:text-blue-800 mr-3"
                            >
                                Editar
                            </a>
                            <button
                                @click="eliminar(req.id)"
                                class="text-red-600 hover:text-red-800"
                            >
                                Eliminar
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="requisitos.data.length === 0" class="text-center py-12">
                <p class="text-gray-500">No se encontraron requisitos</p>
            </div>
        </div>

        <div class="mt-6">
            <Pagination :links="requisitos.links" />
        </div>
    </div>
</template>
