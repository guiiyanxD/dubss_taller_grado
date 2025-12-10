<script setup>
import { useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    requisito: {
        type: Object,
        default: null,
    },
    tipos: Object,
});

const esEdicion = !!props.requisito;

const form = useForm({
    nombre: props.requisito?.nombre || '',
    descripcion: props.requisito?.descripcion || '',
    tipo: props.requisito?.tipo || 'DOCUMENTO',
    obligatorio: props.requisito?.obligatorio ?? true,
});

const submit = () => {
    if (esEdicion) {
        form.post(route('admin.requisitos.update', props.requisito.id), {
            _method: 'PUT',
            preserveScroll: true,
        });
    } else {
        form.post(route('admin.requisitos.store'));
    }
};
</script>

<template>
    <Head :title="esEdicion ? 'Editar Requisito' : 'Nuevo Requisito'" />

    <div class="p-6 max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <a
                :href="route('admin.requisitos.index')"
                class="text-sm text-blue-600 hover:underline mb-2 inline-block"
            >
                ← Volver
            </a>
            <h1 class="text-3xl font-bold text-gray-900">
                {{ esEdicion ? 'Editar Requisito' : 'Nuevo Requisito' }}
            </h1>
        </div>

        <!-- Formulario compacto -->
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
                    placeholder="Cédula de Identidad"
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
                    rows="2"
                    class="w-full px-3 py-2 border rounded-lg"
                    placeholder="Instrucciones o detalles del requisito..."
                />
            </div>

            <!-- Tipo y Obligatorio en grid -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Tipo <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.tipo"
                        required
                        class="w-full px-3 py-2 border rounded-lg"
                    >
                        <option
                            v-for="(label, valor) in tipos"
                            :key="valor"
                            :value="valor"
                        >
                            {{ label }}
                        </option>
                    </select>
                    <div v-if="form.errors.tipo" class="text-red-500 text-xs mt-1">
                        {{ form.errors.tipo }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Obligatorio
                    </label>
                    <div class="flex items-center h-10">
                        <label class="flex items-center cursor-pointer">
                            <input
                                v-model="form.obligatorio"
                                type="checkbox"
                                class="w-5 h-5 rounded"
                            />
                            <span class="ml-2 text-sm text-gray-700">
                                Es obligatorio
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-end gap-3 pt-4">
                <a
                    :href="route('admin.requisitos.index')"
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
