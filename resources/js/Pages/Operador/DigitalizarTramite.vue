<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';

const props = defineProps({
    tramite: Object,
});

const uploadingFiles = ref({});
const completando = ref(false);

const documentosRequeridos = computed(() => {
    return props.tramite.documentos_requeridos.map(req => {
        const digitalizado = props.tramite.documentos_digitalizados.find(
            d => d.tipo === req.tipo
        );
        return {
            ...req,
            digitalizado: digitalizado || null,
            uploading: uploadingFiles.value[req.tipo] || false,
        };
    });
});

const documentosObligatoriosCompletos = computed(() => {
    return documentosRequeridos.value
        .filter(d => d.obligatorio)
        .every(d => d.digitalizado);
});

const progreso = computed(() => {
    const total = documentosRequeridos.value.filter(d => d.obligatorio).length;
    const completados = documentosRequeridos.value.filter(
        d => d.obligatorio && d.digitalizado
    ).length;
    return {
        completados,
        total,
        porcentaje: (completados / total) * 100,
    };
});

const handleFileUpload = async (event, tipo) => {
    const file = event.target.files[0];
    if (!file) return;

    // Validar tamaño (5 MB)
    if (file.size > 5 * 1024 * 1024) {
        alert('El archivo no puede superar 5 MB');
        return;
    }

    // Validar formato
    const formatosPermitidos = ['application/pdf', 'image/jpeg', 'image/png'];
    if (!formatosPermitidos.includes(file.type)) {
        alert('Solo se permiten archivos PDF, JPG o PNG');
        return;
    }

    uploadingFiles.value[tipo] = true;

    const formData = new FormData();
    formData.append('tramite_id', props.tramite.id);
    formData.append('tipo_documento', tipo);
    formData.append('archivo', file);

    try {
        const response = await axios.post(route('operador.documentos.upload'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        // Recargar la página para actualizar la lista
        router.reload({ only: ['tramite'] });

    } catch (error) {
        console.error('Error al subir archivo:', error);
        alert(error.response?.data?.message || 'Error al subir el archivo');
    } finally {
        uploadingFiles.value[tipo] = false;
        event.target.value = ''; // Reset input
    }
};

const eliminarDocumento = async (documentoId) => {
    if (!confirm('¿Está seguro de eliminar este documento?')) return;

    try {
        await axios.delete(route('operador.documentos.eliminar', documentoId));
        router.reload({ only: ['tramite'] });
    } catch (error) {
        alert('Error al eliminar el documento');
    }
};

const completarDigitalizacion = async () => {
    if (!documentosObligatoriosCompletos.value) {
        alert('Debe digitalizar todos los documentos obligatorios');
        return;
    }

    if (!confirm('¿Confirmar que la digitalización está completa?')) return;

    completando.value = true;

    try {
        await axios.put(
            route('operador.tramites.completar-digitalizacion', props.tramite.id)
        );

        router.visit(route('operador.dashboard'), {
            onFinish: () => {
                alert('✅ Digitalización completada exitosamente');
            },
        });
    } catch (error) {
        alert(error.response?.data?.message || 'Error al completar la digitalización');
        completando.value = false;
    }
};

const formatBytes = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};
</script>

<template>
    <Head title="Digitalizar Documentos" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gradient-to-br from-slate-50 via-green-50 to-slate-100">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-slate-900 mb-2">
                        📄 Digitalización de Documentos
                    </h1>
                    <p class="text-slate-600">
                        Trámite: <span class="font-mono font-bold">{{ tramite.codigo }}</span> -
                        {{ tramite.estudiante.nombre }} (CI: {{ tramite.estudiante.ci }})
                    </p>
                </div>

                <!-- Barra de progreso -->
                <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-lg font-bold text-slate-900">
                            Progreso de Digitalización
                        </h2>
                        <span class="text-2xl font-bold text-green-600">
                            {{ progreso.completados }}/{{ progreso.total }}
                        </span>
                    </div>

                    <div class="w-full h-4 bg-slate-200 rounded-full overflow-hidden">
                        <div
                            class="h-full bg-gradient-to-r from-green-500 to-green-600 transition-all duration-500"
                            :style="{ width: progreso.porcentaje + '%' }"
                        ></div>
                    </div>

                    <p class="text-sm text-slate-600 mt-2">
                        {{ Math.round(progreso.porcentaje) }}% completado
                    </p>
                </div>

                <!-- Lista de documentos -->
                <div class="space-y-4 mb-6">
                    <div
                        v-for="doc in documentosRequeridos"
                        :key="doc.tipo"
                        class="bg-white rounded-2xl shadow-lg p-6 transition-all"
                        :class="{
                            'ring-2 ring-green-500': doc.digitalizado,
                        }"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-3">
                                    <span class="text-3xl">
                                        {{ doc.digitalizado ? '✅' : '📄' }}
                                    </span>
                                    <div>
                                        <h3 class="text-lg font-bold text-slate-900">
                                            {{ doc.nombre }}
                                        </h3>
                                        <span
                                            v-if="!doc.obligatorio"
                                            class="inline-block px-2 py-1 bg-slate-100 text-slate-600 text-xs rounded-full"
                                        >
                                            Opcional
                                        </span>
                                    </div>
                                </div>

                                <!-- Archivo digitalizado -->
                                <div v-if="doc.digitalizado" class="bg-green-50 rounded-xl p-4 mb-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-mono text-sm text-green-900 mb-1">
                                                📎 {{ doc.digitalizado.nombre_archivo }}
                                            </p>
                                            <p class="text-xs text-green-700">
                                                Tamaño: {{ formatBytes(doc.digitalizado.tamanho_bytes) }} •
                                                Subido: {{ doc.digitalizado.fecha_subida }}
                                            </p>
                                        </div>
                                        <div class="flex gap-2">
                                            <a
                                                :href="route('operador.documentos.ver', doc.digitalizado.id)"
                                                target="_blank"
                                                class="px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-lg transition-all"
                                            >
                                                👁️ Ver
                                            </a>
                                            <button
                                                @click="eliminarDocumento(doc.digitalizado.id)"
                                                class="px-3 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-semibold rounded-lg transition-all"
                                            >
                                                🗑️ Eliminar
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Botón de subida -->
                                <div v-else>
                                    <label
                                        class="flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold rounded-xl cursor-pointer transition-all transform hover:scale-105 active:scale-95"
                                        :class="{ 'opacity-50 cursor-not-allowed': doc.uploading }"
                                    >
                                        <span v-if="!doc.uploading">📤 Subir Documento</span>
                                        <span v-else>
                                            <svg class="animate-spin h-5 w-5 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Subiendo...
                                        </span>
                                        <input
                                            type="file"
                                            class="hidden"
                                            accept=".pdf,.jpg,.jpeg,.png"
                                            @change="handleFileUpload($event, doc.tipo)"
                                            :disabled="doc.uploading"
                                        />
                                    </label>
                                    <p class="text-xs text-slate-500 mt-2 text-center">
                                        PDF, JPG o PNG • Máximo 5 MB
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex gap-4">
                    <button
                        @click="$inertia.visit(route('operador.tramites.validados'))"
                        class="px-6 py-3 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold rounded-xl transition-all"
                    >
                        ← Volver
                    </button>

                    <button
                        @click="completarDigitalizacion"
                        :disabled="!documentosObligatoriosCompletos || completando"
                        class="flex-1 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-4 px-6 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed transition-all transform hover:scale-105 active:scale-95 shadow-lg"
                    >
                        <span v-if="!completando">✅ Completar Digitalización</span>
                        <span v-else>Procesando...</span>
                    </button>
                </div>

                <!-- Información adicional -->
                <div class="mt-6 bg-blue-50 border-2 border-blue-200 rounded-2xl p-6">
                    <h3 class="font-bold text-blue-900 mb-3 flex items-center gap-2">
                        <span>💡</span>
                        <span>Instrucciones</span>
                    </h3>
                    <ul class="space-y-2 text-sm text-blue-800">
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500">•</span>
                            <span>Escanee cada documento en alta calidad (mínimo 300 DPI)</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500">•</span>
                            <span>Los archivos deben ser legibles y sin manchas</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500">•</span>
                            <span>Puede reemplazar un documento eliminándolo y subiéndolo nuevamente</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500">•</span>
                            <span>Al completar, el sistema iniciará automáticamente la clasificación</span>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
