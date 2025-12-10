<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    tramite: Object,
});

const documentos = ref([
    { tipo: 'CI', nombre: 'Cédula de Identidad', validado: null, observacion: '' },
    { tipo: 'KARDEX', nombre: 'Kardex Académico', validado: null, observacion: '' },
    { tipo: 'COMPROBANTE_DOMICILIO', nombre: 'Comprobante de Domicilio', validado: null, observacion: '' },
    { tipo: 'CERTIFICADO_INGRESOS', nombre: 'Certificado de Ingresos', validado: null, observacion: '', opcional: true },
]);

const observacionesGenerales = ref('');
const procesando = ref(false);

const todosDocumentosValidados = computed(() => {
    return documentos.value
        .filter(d => !d.opcional)
        .every(d => d.validado !== null);
});

const hayDocumentosInvalidos = computed(() => {
    return documentos.value.some(d => d.validado === false);
});

const puedeAprobar = computed(() => {
    return todosDocumentosValidados.value && !hayDocumentosInvalidos.value;
});

const puedeRechazar = computed(() => {
    return hayDocumentosInvalidos.value;
});

const toggleDocumento = (index, valor) => {
    documentos.value[index].validado = documentos.value[index].validado === valor ? null : valor;
};

const aprobarTramite = () => {
    if (!puedeAprobar.value) return;

    if (!confirm('¿Confirmar aprobación de la documentación?')) return;

    procesando.value = true;

    // Usar router.put - el backend ahora retorna directamente la página Dashboard
    router.put(
        route('operador.tramites.validar.submit', props.tramite.id),
        {
            accion: 'APROBAR',
            documentos_validados: documentos.value
                .filter(d => d.validado === true)
                .map(d => ({ tipo: d.tipo, valido: true })),
            observaciones: 'Todos los documentos correctos',
        },
        {
            preserveScroll: false,
            onFinish: () => {
                procesando.value = false;
            },
        }
    );
};
const aprobar = () => {
    router.put(
        route('operador.tramites.validar.submit', props.tramite.id),
        {
            accion: 'APROBAR',
            documentos_validados: documentos.value
                .filter(d => d.validado === true)
                .map(d => ({ tipo: d.tipo, valido: true })),
            observaciones: 'Documentos correctos',
        },
        {
            preserveState: false,
            preserveScroll: false,
            onSuccess: () => {
                // Forzar navegación completa al dashboard
                router.visit(route('operador.dashboard'), {
                    method: 'get',
                    preserveState: false,
                });
            },
            onError: (errors) => {
                console.error('Error al validar:', errors);
            },
            onFinish: () => {
                procesando.value = false;
            }
        }
    );
};

const rechazarTramite = () => {
    if (!puedeRechazar.value) return;

    const motivos = documentos.value
        .filter(d => d.validado === false && d.observacion)
        .map(d => `${d.nombre}: ${d.observacion}`)
        .join('; ');

    if (!motivos && !observacionesGenerales.value) {
        alert('Debes especificar el motivo del rechazo en las observaciones');
        return;
    }

    if (!confirm('¿Confirmar rechazo de la documentación?')) return;

    procesando.value = true;

    router.put(
        route('operador.tramites.validar.submit', props.tramite.id),
        {
            accion: 'RECHAZAR',
            observaciones: observacionesGenerales.value || motivos,
        },
        {
            preserveScroll: false,
            onFinish: () => {
                procesando.value = false;
            },
        }
    );
};

const getEstadoColor = (estado) => {
    const colores = {
        PENDIENTE: 'bg-gray-100 text-gray-800',
        EN_VALIDACION: 'bg-blue-100 text-blue-800',
        VALIDADO: 'bg-green-100 text-green-800',
        RECHAZADO: 'bg-red-100 text-red-800',
    };
    return colores[estado] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Validar Trámite" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-slate-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-slate-900 mb-2">
                        Validación de Documentos
                    </h1>
                    <p class="text-slate-600">
                        Trámite: <span class="font-mono font-bold">{{ tramite.codigo }}</span>
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Panel izquierdo: Información del estudiante -->
                    <div class="lg:col-span-1 space-y-6">

                        <!-- Datos del estudiante -->
                        <div class="bg-white rounded-2xl shadow-lg p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="text-3xl">👤</span>
                                <h2 class="text-xl font-bold text-slate-900">Estudiante</h2>
                            </div>

                            <div class="space-y-3">
                                <div>
                                    <label class="text-xs font-bold text-slate-600 uppercase">Nombre</label>
                                    <p class="text-slate-900 font-semibold">{{ tramite.estudiante.nombre }}</p>
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-slate-600 uppercase">CI</label>
                                    <p class="text-slate-900 font-mono">{{ tramite.estudiante.ci }}</p>
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-slate-600 uppercase">Carrera</label>
                                    <p class="text-slate-900">{{ tramite.estudiante.carrera }}</p>
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-slate-600 uppercase">Semestre</label>
                                    <p class="text-slate-900">{{ tramite.estudiante.semestre }}º</p>
                                </div>
                            </div>
                        </div>

                        <!-- Beca postulada -->
                        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg p-6 text-white">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="text-3xl">🎓</span>
                                <h2 class="text-xl font-bold">Beca Postulada</h2>
                            </div>
                            <p class="text-lg font-bold mb-2">{{ tramite.beca.nombre }}</p>
                            <p class="text-blue-100">Monto: Bs {{ tramite.beca.monto }}</p>
                        </div>

                        <!-- Estado actual -->
                        <div class="bg-white rounded-2xl shadow-lg p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="text-3xl">📊</span>
                                <h2 class="text-xl font-bold text-slate-900">Estado</h2>
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="px-4 py-2 rounded-full text-sm font-bold"
                                    :class="getEstadoColor(tramite.estado_actual.nombre)"
                                >
                                    {{ tramite.estado_actual.nombre }}
                                </span>
                            </div>
                        </div>

                    </div>

                    <!-- Panel derecho: Validación de documentos -->
                    <div class="lg:col-span-2 space-y-6">

                        <!-- Checklist de documentos -->
                        <div class="bg-white rounded-2xl shadow-lg p-6">
                            <div class="flex items-center gap-3 mb-6">
                                <span class="text-3xl">📋</span>
                                <h2 class="text-xl font-bold text-slate-900">Checklist de Documentos</h2>
                            </div>

                            <div class="space-y-4">
                                <div
                                    v-for="(doc, index) in documentos"
                                    :key="doc.tipo"
                                    class="border-2 rounded-xl p-4 transition-all"
                                    :class="{
                                        'border-green-300 bg-green-50': doc.validado === true,
                                        'border-red-300 bg-red-50': doc.validado === false,
                                        'border-slate-200 bg-white': doc.validado === null,
                                    }"
                                >
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-2">
                                                <h3 class="font-bold text-slate-900">{{ doc.nombre }}</h3>
                                                <span
                                                    v-if="doc.opcional"
                                                    class="px-2 py-1 bg-slate-100 text-slate-600 text-xs rounded-full"
                                                >
                                                    Opcional
                                                </span>
                                            </div>

                                            <!-- Botones de validación -->
                                            <div class="flex gap-2 mb-3">
                                                <button
                                                    @click="toggleDocumento(index, true)"
                                                    class="px-4 py-2 rounded-lg font-semibold transition-all"
                                                    :class="
                                                        doc.validado === true
                                                            ? 'bg-green-500 text-white shadow-lg'
                                                            : 'bg-slate-100 text-slate-600 hover:bg-green-100'
                                                    "
                                                >
                                                    ✅ Válido
                                                </button>
                                                <button
                                                    @click="toggleDocumento(index, false)"
                                                    class="px-4 py-2 rounded-lg font-semibold transition-all"
                                                    :class="
                                                        doc.validado === false
                                                            ? 'bg-red-500 text-white shadow-lg'
                                                            : 'bg-slate-100 text-slate-600 hover:bg-red-100'
                                                    "
                                                >
                                                    ❌ Inválido
                                                </button>
                                            </div>

                                            <!-- Campo de observación (solo si es inválido) -->
                                            <div v-if="doc.validado === false">
                                                <input
                                                    v-model="doc.observacion"
                                                    type="text"
                                                    placeholder="Especificar motivo del rechazo..."
                                                    class="w-full px-3 py-2 border border-red-300 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 text-sm"
                                                />
                                            </div>
                                        </div>

                                        <!-- Indicador visual -->
                                        <div class="text-3xl">
                                            <span v-if="doc.validado === true">✅</span>
                                            <span v-else-if="doc.validado === false">❌</span>
                                            <span v-else>⬜</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Observaciones generales -->
                        <div class="bg-white rounded-2xl shadow-lg p-6">
                            <label class="block text-lg font-bold text-slate-900 mb-3">
                                Observaciones Generales
                            </label>
                            <textarea
                                v-model="observacionesGenerales"
                                rows="4"
                                placeholder="Escribir observaciones adicionales..."
                                class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all"
                            ></textarea>
                        </div>

                        <!-- Botones de acción -->
                        <div class="flex gap-4">
                            <button
                                @click="aprobar"
                                :disabled="!puedeAprobar || procesando"
                                class="flex-1 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-4 px-6 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed transition-all transform hover:scale-105 active:scale-95 shadow-lg"
                            >
                                <span v-if="!procesando">✅ Aprobar Documentación</span>
                                <span v-else>Procesando...</span>
                            </button>

                            <button
                                @click="rechazarTramite"
                                :disabled="!puedeRechazar || procesando"
                                class="flex-1 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold py-4 px-6 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed transition-all transform hover:scale-105 active:scale-95 shadow-lg"
                            >
                                <span v-if="!procesando">❌ Rechazar Documentación</span>
                                <span v-else>Procesando...</span>
                            </button>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
