<script setup>
import { ref, onMounted } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    beca: {
        type: Object,
        default: null,
    },
});

const esEdicion = !!props.beca;


const convocatorias = ref([]);
const requisitos = ref([]);
const cargando = ref(true);

const form = useForm({
    id_convocatoria: props.beca?.convocatoria?.id || '',
    nombre: props.beca?.nombre || '',
    descripcion: props.beca?.descripcion || '',
    cupos_disponibles: props.beca?.cupos_disponibles || 10,
    requisitos: props.beca?.requisitos?.map(r => r.id) || [],
});

onMounted(async () => {
    try {
        const [convRes, reqRes] = await Promise.all([
            fetch(route('admin.convocatorias.index') ),
            fetch(route('admin.requisitos.index') ),
        ]);

        convocatorias.value = await convRes.json();
        requisitos.value = await reqRes.json();
    } catch (error) {
        console.error('Error cargando datos:', error);
    } finally {
        cargando.value = false;
    }
});

const submit = () => {
    if (esEdicion) {
        form.post(route('admin.becas.update', props.beca.id), {
            _method: 'PUT',
            preserveScroll: true,
        });
    } else {
        form.post(route('admin.becas.store'));
    }
};

const toggleRequisito = (id) => {
    const index = form.requisitos.indexOf(id);
    if (index > -1) {
        form.requisitos.splice(index, 1);
    } else {
        form.requisitos.push(id);
    }
};
</script>

<template>
    <Head :title="esEdicion ? 'Editar Beca' : 'Nueva Beca'" />

    <div class="p-6 max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <a
                :href="route('admin.becas.index')"
                class="text-sm text-blue-600 hover:underline mb-2 inline-block"
            >
                ← Volver
            </a>
            <h1 class="text-3xl font-bold text-gray-900">
                {{ esEdicion ? 'Editar Beca' : 'Nueva Beca' }}
            </h1>
        </div>

        <!-- Loader -->
        <div v-if="cargando" class="bg-white rounded-lg shadow-sm p-12 text-center">
            <div class="animate-spin h-8 w-8 border-4 border-blue-600 border-t-transparent rounded-full mx-auto"></div>
            <p class="text-gray-600 mt-4">Cargando...</p>
        </div>

        <!-- Formulario -->
        <form v-else @submit.prevent="submit" class="bg-white rounded-lg shadow-sm p-6 space-y-4">
            <!-- Convocatoria -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Convocatoria <span class="text-red-500">*</span>
                </label>
                <select
                    v-model="form.id_convocatoria"
                    required
                    class="w-full px-3 py-2 border rounded-lg"
                >
                    <option value="">Seleccionar...</option>
                    <option
                        v-for="conv in convocatorias"
                        :key="conv.id"
                        :value="conv.id"
                    >
                        {{ conv.nombre }}
                    </option>
                </select>
                <div v-if="form.errors.id_convocatoria" class="text-red-500 text-xs mt-1">
                    {{ form.errors.id_convocatoria }}
                </div>
            </div>

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
                    placeholder="Beca de Alimentación"
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
                    placeholder="Descripción opcional..."
                />
            </div>

            <!-- Cupos -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Cupos Disponibles <span class="text-red-500">*</span>
                </label>
                <input
                    v-model.number="form.cupos_disponibles"
                    type="number"
                    min="1"
                    required
                    class="w-full px-3 py-2 border rounded-lg"
                />
                <div v-if="form.errors.cupos_disponibles" class="text-red-500 text-xs mt-1">
                    {{ form.errors.cupos_disponibles }}
                </div>
            </div>

            <!-- Requisitos (checkboxes compactos) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Requisitos
                </label>
                <div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto border rounded-lg p-3">
                    <label
                        v-for="req in requisitos"
                        :key="req.id"
                        class="flex items-center gap-2 text-sm"
                    >
                        <input
                            type="checkbox"
                            :checked="form.requisitos.includes(req.id)"
                            @change="toggleRequisito(req.id)"
                            class="rounded"
                        />
                        <span>{{ req.nombre }}</span>
                    </label>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-end gap-3 pt-4">
                <a
                    :href="route('admin.becas.index')"
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
