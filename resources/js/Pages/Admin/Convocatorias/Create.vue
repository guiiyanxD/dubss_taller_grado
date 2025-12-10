<script setup>
import { useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    convocatoria: {
        type: Object,
        default: null,
    },
    flash: {
        type: Object,
        default: () => ({ success: null, error: null }),
    },
});

const esEdicion = !!props.convocatoria;

const form = useForm({
    nombre: props.convocatoria?.nombre || '',
    descripcion: props.convocatoria?.descripcion || '',
    fecha_inicio: props.convocatoria?.fecha_inicio || '',
    fecha_fin: props.convocatoria?.fecha_fin || '',
    estado: props.convocatoria?.estado || 'BORRADOR',
});

const submit = () => {
    if (esEdicion) {
        form.post(route('admin.convocatorias.update', props.convocatoria.id), {
            _method: 'PUT',
            preserveScroll: true,
        });
    } else {
        form.post(route('admin.convocatorias.store'));
    }
};
</script>

<template>
    <Head :title="esEdicion ? 'Editar Convocatoria' : 'Nueva Convocatoria'" />


    <div class="p-6 max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <a
                :href="route('admin.convocatorias.index')"
                class="text-sm text-blue-600 hover:underline mb-2 inline-block"
            >
                ← Volver
            </a>
            <h1 class="text-3xl font-bold text-gray-900">
                {{ esEdicion ? 'Editar Convocatoria' : 'Nueva Convocatoria' }}
            </h1>
        </div>

        <div v-if="flash.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            <span class="block sm:inline">{{ flash.error }}</span>
        </div>
        <form @submit.prevent="submit" class="bg-white rounded-lg shadow-sm p-6 space-y-4">
            <!-- Nombre -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nombre <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="form.nombre"
                    type="text"
                    required
                    class="w-full px-3 py-2 border rounded-lg"
                    placeholder="Convocatoria 2025-1"
                />
                <div v-if="form.errors.nombre" class="text-red-500 text-xs mt-1">
                    {{ form.errors.nombre }}
                </div>
            </div>

            <!-- Descripción -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Descripción
                </label>
                <textarea
                    v-model="form.descripcion"
                    rows="3"
                    class="w-full px-3 py-2 border rounded-lg"
                    placeholder="Descripción opcional..."
                />
            </div>

            <!-- Fechas en grid -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Fecha Inicio <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.fecha_inicio"
                        type="date"
                        required
                        class="w-full px-3 py-2 border rounded-lg"
                    />
                    <div v-if="form.errors.fecha_inicio" class="text-red-500 text-xs mt-1">
                        {{ form.errors.fecha_inicio }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Fecha Fin <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.fecha_fin"
                        type="date"
                        required
                        class="w-full px-3 py-2 border rounded-lg"
                    />
                    <div v-if="form.errors.fecha_fin" class="text-red-500 text-xs mt-1">
                        {{ form.errors.fecha_fin }}
                    </div>
                </div>
            </div>

            <!-- Estado -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Estado
                </label>
                <select
                    v-model="form.estado"
                    class="w-full px-3 py-2 border rounded-lg"
                >
                    <option value="BORRADOR">Borrador</option>
                    <option value="ACTIVA">Activa</option>
                    <option value="FINALIZADA">Finalizada</option>
                </select>
            </div>

            <!-- Botones -->
            <div class="flex justify-end gap-3 pt-4">
                <a
                    :href="route('admin.convocatorias.index')"
                    class="px-4 py-2 text-gray-700 border rounded-lg hover:bg-gray-50"
                >
                    Cancelar
                </a>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                >
                    {{ form.processing ? 'Guardando...' : 'Guardar' }}
                </button>
            </div>
        </form>
    </div>
</template>
