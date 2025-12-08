<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    error: String,
    ci: String,
});

const ciInput = ref(props.ci || '');
const buscando = ref(false);

const buscarTramite = () => {
    if (!ciInput.value.trim()) {
        return;
    }

    buscando.value = true;
    router.get(
        route('operador.tramites.buscar.resultados'),
        { ci: ciInput.value },
        {
            preserveState: true,
            onFinish: () => {
                buscando.value = false;
            },
        }
    );
};
</script>

<template>
    <Head title="Buscar Trámite" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-slate-100">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

                <!-- Header -->
                <div class="text-center mb-12">
                    <div class="inline-block p-4 bg-blue-100 rounded-full mb-4">
                        <span class="text-6xl">🔍</span>
                    </div>
                    <h1 class="text-4xl font-bold text-slate-900 mb-2">
                        Buscar Trámite
                    </h1>
                    <p class="text-lg text-slate-600">
                        Ingresa el CI del estudiante para ver su trámite
                    </p>
                </div>

                <!-- Formulario de búsqueda -->
                <div class="bg-white rounded-2xl shadow-2xl p-8 mb-8">
                    <form @submit.prevent="buscarTramite" class="space-y-6">
                        <div>
                            <label for="ci" class="block text-sm font-bold text-slate-900 mb-2">
                                Cédula de Identidad (CI)
                            </label>
                            <input
                                id="ci"
                                v-model="ciInput"
                                type="text"
                                placeholder="Ej: 1234567"
                                class="w-full px-4 py-4 text-lg border-2 border-slate-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all"
                                :disabled="buscando"
                                autofocus
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="!ciInput.trim() || buscando"
                            class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold py-4 px-6 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed transition-all transform hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl"
                        >
                            <span v-if="!buscando" class="flex items-center justify-center gap-2">
                                <span>🔍</span>
                                <span>Buscar Trámite</span>
                            </span>
                            <span v-else class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Buscando...</span>
                            </span>
                        </button>
                    </form>

                    <!-- Mensaje de error -->
                    <div
                        v-if="error"
                        class="mt-6 bg-red-50 border-2 border-red-200 rounded-xl p-4"
                    >
                        <div class="flex items-start gap-3">
                            <span class="text-2xl">⚠️</span>
                            <div>
                                <h3 class="font-bold text-red-900 mb-1">
                                    No se encontró el trámite
                                </h3>
                                <p class="text-sm text-red-700">
                                    {{ error }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Consejos -->
                <div class="bg-blue-50 border-2 border-blue-200 rounded-2xl p-6">
                    <h3 class="font-bold text-blue-900 mb-3 flex items-center gap-2">
                        <span>💡</span>
                        <span>Consejos de búsqueda</span>
                    </h3>
                    <ul class="space-y-2 text-sm text-blue-800">
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500">•</span>
                            <span>Ingresa el CI sin espacios ni guiones</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500">•</span>
                            <span>El CI debe corresponder al estudiante que presentó la documentación</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500">•</span>
                            <span>Solo se mostrarán trámites activos</span>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
