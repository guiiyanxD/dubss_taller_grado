<script setup>
import { useForm, Head } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const currentStep = ref(1);
const totalSteps = 4;
const props = defineProps({
    convocatorias: {
        type: Array,
        default: () => []
    }
});
const listaBecas = ref([]);
const form = useForm({

    ci_estudiante: '',
    id_convocatoria: '',
    id_beca: '',
    fecha_llenado: new Date().toISOString().split('T')[0],
    telefono_referencia: '',
    lugar_procedencia: '',
    comentario_personal: '',

    discapacidad: false,
    comentario_discapacidad: '',
    otro_beneficio: false,
    comentario_otro_beneficio: '',
    observaciones: '',

    grupo_familiar: {
        tiene_hijos: false,
        cantidad_hijos: 0,
        cantidad_familiares: 0,
        miembros: []
    },

    residencia: {
        provincia: '',
        zona: '',
        barrio: '',
        calle: '',
        cant_dormitorios: 0,
        cant_banhos: 0,
        cant_salas: 0,
        cantt_comedor: 0,
        cant_patios: 0,
    },
    tenencia: {
        tipo_tenencia: 'PROPIA',
        detalle_tenencia: ''
    },

    economica: {
        tipo_dependencia: 'PADRES',
        nota_ocupacion: '',
        rango_ingreso: '',
        ocupacion_nombre: ''
    }
});

const nextStep = () => {
    if (currentStep.value < totalSteps) currentStep.value++;
};
const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const nuevoMiembro = ref({
    nombre_completo: '',
    parentesco: '',
    edad: '',
    ocupacion: ''
});

const agregarMiembro = () => {
    if(nuevoMiembro.value.nombre_completo && nuevoMiembro.value.parentesco) {
        form.grupo_familiar.miembros.push({ ...nuevoMiembro.value });
        nuevoMiembro.value = { nombre_completo: '', parentesco: '', edad: '', ocupacion: '' };
        form.grupo_familiar.cantidad_familiares = form.grupo_familiar.miembros.length;
    }
};

const eliminarMiembro = (index) => {
    form.grupo_familiar.miembros.splice(index, 1);
    form.grupo_familiar.cantidad_familiares = form.grupo_familiar.miembros.length;
};

const submit = () => {
    form.post(route('admin.formularios.store'), {
        preserveScroll: true,
        onSuccess: () => {
            alert('¡Formulario, Postulación y Trámite creados con éxito!');
        },
        onError: (errors) => {
            console.error('Errores de validación:', errors);
            alert('Hubo un error al guardar. Revisa los campos marcados en rojo.');
        }
    });
};

// Calculo de progreso para la barra
const progressWidth = computed(() => `${(currentStep.value / totalSteps) * 100}%`);

const fetchBecas = async (convocatoriaId) => {
    listaBecas.value = [];
    form.id_beca = '';

    if (!convocatoriaId) return;

    try {
        const response = await axios.get(route('admin.convocatorias.becas', { id: convocatoriaId }));

        if (response.data.success) {
            listaBecas.value = response.data.becas;
        } else {
            console.error('Error al cargar becas:', response.data.message);
        }
    } catch (error) {
        console.error('Error de red al cargar becas:', error);
    }
};

watch(() => form.id_convocatoria, (newId) => {
    fetchBecas(newId);
});
</script>

<template>
    <Head title="Nuevo Formulario Socioeconómico" />

    <div class="p-6 max-w-5xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Formulario Socioeconómico</h1>

        </div>

        <div class="w-full bg-gray-200 rounded-full h-2.5 mb-6">
            <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" :style="{ width: progressWidth }"></div>
        </div>

        <form @submit.prevent="submit" class="bg-white shadow-lg rounded-lg p-6 border border-gray-100">

            <div v-show="currentStep === 1">
                <h2 class="text-xl font-semibold mb-4 text-blue-800 border-b pb-2">1. Datos Generales del Estudiante</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">CI Estudiante</label>
                        <input v-model="form.ci_estudiante" type="text" required placeholder="Ej: 8345678" class="w-full rounded-md border-gray-300" />
                        <div v-if="form.errors.ci_estudiante" class="text-red-500 text-xs">{{ form.errors.ci_estudiante }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Convocatoria</label>
                        <select v-model="form.id_convocatoria" required class="w-full rounded-md border-gray-300">
                            <option value="" disabled>Seleccione Convocatoria</option>
                            <option v-for="conv in convocatorias" :key="conv.id" :value="conv.id">
                                {{ conv.nombre }}
                            </option>
                        </select>
                        <div v-if="form.errors.id_convocatoria" class="text-red-500 text-xs">{{ form.errors.id_convocatoria }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Beca Disponible</label>
                        <select
                            v-model="form.id_beca"
                            required
                            :disabled="!form.id_convocatoria || listaBecas.length === 0"
                            class="w-full rounded-md border-gray-300 disabled:bg-gray-100"
                        >
                            <option value="" disabled>
                                {{ form.id_convocatoria ? 'Seleccione Beca' : 'Elija Convocatoria primero' }}
                            </option>
                            <option v-for="beca in listaBecas" :key="beca.id" :value="beca.id">
                                {{ beca.nombre }}
                            </option>
                        </select>
                        <div v-if="form.errors.id_beca" class="text-red-500 text-xs">{{ form.errors.id_beca }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha Llenado</label>
                        <input v-model="form.fecha_llenado" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Teléfono Referencia</label>
                        <input v-model="form.telefono_referencia" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Lugar de Procedencia</label>
                        <input v-model="form.lugar_procedencia" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input v-model="form.discapacidad" id="discapacidad" type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded" />
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="discapacidad" class="font-medium text-gray-700">¿Tiene alguna discapacidad?</label>
                        </div>
                    </div>
                    <div v-if="form.discapacidad" class="ml-7">
                        <input v-model="form.comentario_discapacidad" type="text" placeholder="Detalle la discapacidad..." class="block w-full rounded-md border-gray-300 shadow-sm text-sm" />
                    </div>

                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input v-model="form.otro_beneficio" id="beneficio" type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded" />
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="beneficio" class="font-medium text-gray-700">¿Recibe otro beneficio/beca externa?</label>
                        </div>
                    </div>
                    <div v-if="form.otro_beneficio" class="ml-7">
                        <input v-model="form.comentario_otro_beneficio" type="text" placeholder="Detalle el beneficio..." class="block w-full rounded-md border-gray-300 shadow-sm text-sm" />
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Comentario Personal / Situación</label>
                    <textarea v-model="form.comentario_personal" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                </div>
            </div>

            <div v-show="currentStep === 2">
                <h2 class="text-xl font-semibold mb-4 text-blue-800 border-b pb-2">2. Grupo Familiar</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 bg-gray-50 p-4 rounded-lg">
                    <div class="flex items-center">
                         <input v-model="form.grupo_familiar.tiene_hijos" id="hijos" type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded mr-2" />
                         <label for="hijos" class="text-sm font-medium text-gray-700">¿Tiene hijos propios?</label>
                    </div>
                    <div v-if="form.grupo_familiar.tiene_hijos">
                        <label class="block text-sm font-medium text-gray-700">Cantidad de Hijos</label>
                        <input v-model="form.grupo_familiar.cantidad_hijos" type="number" min="0" class="mt-1 block w-24 rounded-md border-gray-300 shadow-sm" />
                    </div>
                </div>

                <div class="mb-2">
                    <h3 class="font-medium text-gray-800">Agregar Miembros (Padres, Hermanos, etc.)</h3>
                    <p class="text-xs text-gray-500 mb-2">Total registrados: {{ form.grupo_familiar.cantidad_familiares }}</p>

                    <div class="grid grid-cols-1 md:grid-cols-5 gap-2 items-end bg-blue-50 p-3 rounded-md">
                        <div class="md:col-span-2">
                            <label class="text-xs text-gray-600">Nombre Completo</label>
                            <input v-model="nuevoMiembro.nombre_completo" type="text" class="w-full rounded-md border-gray-300 text-sm" />
                        </div>
                        <div>
                            <label class="text-xs text-gray-600">Parentesco</label>
                            <select v-model="nuevoMiembro.parentesco" class="w-full rounded-md border-gray-300 text-sm">
                                <option value="">Sel...</option>
                                <option value="PADRE">Padre</option>
                                <option value="MADRE">Madre</option>
                                <option value="HERMANO/A">Hermano/a</option>
                                <option value="ABUELO/A">Abuelo/a</option>
                                <option value="TIO/A">Tío/a</option>
                                <option value="OTRO">Otro</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-600">Edad</label>
                            <input v-model="nuevoMiembro.edad" type="number" class="w-full rounded-md border-gray-300 text-sm" />
                        </div>
                        <div>
                            <button @click.prevent="agregarMiembro" type="button" class="w-full bg-blue-600 text-white px-3 py-2 rounded-md text-sm hover:bg-blue-700">
                                + Agregar
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto border rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Parentesco</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Edad</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(miembro, index) in form.grupo_familiar.miembros" :key="index">
                                <td class="px-3 py-2 text-sm text-gray-900">{{ miembro.nombre_completo }}</td>
                                <td class="px-3 py-2 text-sm text-gray-500">{{ miembro.parentesco }}</td>
                                <td class="px-3 py-2 text-sm text-gray-500">{{ miembro.edad }}</td>
                                <td class="px-3 py-2 text-right text-sm">
                                    <button @click.prevent="eliminarMiembro(index)" class="text-red-600 hover:text-red-900">Eliminar</button>
                                </td>
                            </tr>
                            <tr v-if="form.grupo_familiar.miembros.length === 0">
                                <td colspan="4" class="px-3 py-4 text-center text-sm text-gray-400">No hay miembros agregados aún.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-show="currentStep === 3">
                <h2 class="text-xl font-semibold mb-4 text-blue-800 border-b pb-2">3. Vivienda y Residencia</h2>

                <h3 class="font-medium text-gray-700 mb-2">Ubicación</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <input v-model="form.residencia.provincia" type="text" placeholder="Provincia" class="rounded-md border-gray-300 shadow-sm" />
                    <input v-model="form.residencia.zona" type="text" placeholder="Zona" class="rounded-md border-gray-300 shadow-sm" />
                    <input v-model="form.residencia.barrio" type="text" placeholder="Barrio" class="rounded-md border-gray-300 shadow-sm" />
                    <input v-model="form.residencia.calle" type="text" placeholder="Calle / Av" class="rounded-md border-gray-300 shadow-sm" />
                </div>

                <h3 class="font-medium text-gray-700 mb-2 mt-6">Características del Inmueble</h3>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-4">
                    <div>
                        <label class="block text-xs text-gray-500">Dormitorios</label>
                        <input v-model="form.residencia.cant_dormitorios" type="number" class="w-full rounded-md border-gray-300" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500">Baños</label>
                        <input v-model="form.residencia.cant_banhos" type="number" class="w-full rounded-md border-gray-300" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500">Salas</label>
                        <input v-model="form.residencia.cant_salas" type="number" class="w-full rounded-md border-gray-300" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500">Comedores</label>
                        <input v-model="form.residencia.cantt_comedor" type="number" class="w-full rounded-md border-gray-300" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500">Patios</label>
                        <input v-model="form.residencia.cant_patios" type="number" class="w-full rounded-md border-gray-300" />
                    </div>
                </div>

                <h3 class="font-medium text-gray-700 mb-2 mt-6">Tenencia</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tipo Tenencia</label>
                        <select v-model="form.tenencia.tipo_tenencia" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="PROPIA">Propia</option>
                            <option value="ALQUILADA">Alquilada</option>
                            <option value="ANTICRETICO">Anticrético</option>
                            <option value="CEDIDA">Cedida / Prestada</option>
                            <option value="PAGANDO">Pagando al banco/crédito</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Detalle Adicional</label>
                        <input v-model="form.tenencia.detalle_tenencia" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                    </div>
                </div>
            </div>

            <div v-show="currentStep === 4">
                <h2 class="text-xl font-semibold mb-4 text-blue-800 border-b pb-2">4. Situación Económica</h2>

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dependencia Económica (¿De quién depende?)</label>
                        <select v-model="form.economica.tipo_dependencia" class="block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="PADRES">Ambos Padres</option>
                            <option value="SOLO_PADRE">Solo Padre</option>
                            <option value="SOLO_MADRE">Solo Madre</option>
                            <option value="TUTOR">Tutor / Familiar</option>
                            <option value="INDEPENDIENTE">Autosustento (Trabaja)</option>
                            <option value="ESPOSO">Esposo/a</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ocupación del Sostén Económico</label>
                        <input v-model="form.economica.ocupacion_nombre" type="text" placeholder="Ej: Comerciante, Albañil, Profesor..." class="block w-full rounded-md border-gray-300 shadow-sm" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notas sobre la ocupación (Opcional)</label>
                        <textarea v-model="form.economica.nota_ocupacion" rows="2" class="block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                    </div>

                    <div class="bg-yellow-50 p-4 rounded-md border border-yellow-200">
                        <label class="block text-sm font-bold text-gray-800 mb-2">Rango de Ingreso Mensual Familiar (Bs.)</label>
                        <select v-model="form.economica.rango_ingreso" class="block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Seleccione...</option>
                            <option value="MENOS_2000">Menos de 2.000 Bs</option>
                            <option value="2000_3000">2.000 - 3.000 Bs</option>
                            <option value="3000_4500">3.000 - 4.500 Bs</option>
                            <option value="4500_6000">4.500 - 6.000 Bs</option>
                            <option value="MAS_6000">Más de 6.000 Bs</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Sumar ingresos de todos los miembros que aportan.</p>
                    </div>
                </div>

                <div class="mt-8 border-t pt-4">
                    <label class="block text-sm font-medium text-gray-700">Observaciones Generales (Admin)</label>
                    <textarea v-model="form.observaciones" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                </div>
            </div>

            <div class="flex justify-between mt-8 pt-4 border-t border-gray-100">
                <button
                    v-if="currentStep > 1"
                    type="button"
                    @click="prevStep"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                >
                    Atrás
                </button>
                <div v-else></div> <button
                    v-if="currentStep < totalSteps"
                    type="button"
                    @click="nextStep"
                    class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium"
                >
                    Siguiente
                </button>

                <div class="flex justify-between mt-8 pt-4 border-t border-gray-100">
                    <button
                        v-if="currentStep === totalSteps"
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-bold disabled:opacity-50"
                    >
                        {{ form.processing ? 'Guardando...' : 'Finalizar y Guardar' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>
