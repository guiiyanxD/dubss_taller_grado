<script setup>
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
// ELIMINAMOS: import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    // CAMBIO CRÍTICO: La prop 'tramites' ahora es un Array simple
    tramites: Array,
});

// Función para determinar las clases CSS del badge de estado
const estadoBadgeClass = (estadoNombre) => {
    switch (estadoNombre) {
        case 'PENDIENTE':
            return 'bg-yellow-100 text-yellow-800 border-yellow-300';
        case 'EN_VALIDACION':
            return 'bg-blue-100 text-blue-800 border-blue-300';
        default:
            return 'bg-gray-100 text-gray-800 border-gray-300';
    }
};

// Función para redirigir a la vista de validación
const irAValidar = (tramiteId) => {
    router.get(route('operador.tramites.validar', tramiteId));
};
</script>

<template>
    <Head title="Trámites Pendientes" />

    <div class="p-6 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">🚨 Trámites Pendientes de Validación</h1>
                <p class="text-sm text-gray-600 mt-1">Lista de trámites listos o en proceso de revisión por el operador.</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-xl overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trámite ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Postulante</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Beca</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Fecha Ingreso</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr
                        v-for="tramite in tramites"
                        :key="tramite.id"
                        class="hover:bg-gray-50 transition"
                    >
                        <td class="px-4 py-3 text-sm font-semibold text-gray-700">
                            #{{ tramite.id }}
                        </td>

                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">
                                {{ tramite.postulacion.estudiante.usuario.nombres }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                Cédula: {{ tramite.postulacion.estudiante.usuario.ci }}
                            </div>
                        </td>

                        <td class="px-4 py-3 text-sm text-gray-600">
                            <div class="font-semibold">{{ tramite.postulacion.beca.nombre }}</div>
                        </td>

                        <td class="px-4 py-3 text-center">
                            <span
                                :class="estadoBadgeClass(tramite.estado_actual.nombre)"
                                class="px-3 py-1 text-xs font-bold rounded-full border shadow-sm"
                            >
                                {{ tramite.estado_actual.nombre.replace('_', ' ') }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-center text-sm text-gray-600 whitespace-nowrap">
                            {{ new Date(tramite.created_at).toLocaleDateString() }}
                        </td>

                        <td class="px-4 py-3 text-right text-sm">
                            <button
                                @click="irAValidar(tramite.id)"
                                class="px-3 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold"
                            >
                                Validar
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="tramites.length === 0" class="text-center py-12">
                <p class="text-gray-500 text-lg">🎉 ¡No hay trámites pendientes!</p>
                <p class="text-gray-400 mt-1">Revisa más tarde o contacta al administrador.</p>
            </div>
        </div>

        </div>
</template>
