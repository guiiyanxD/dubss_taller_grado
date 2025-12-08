<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    // Campos básicos de Laravel Breeze
    name: '',
    email: '',
    password: '',
    password_confirmation: '',

    // Campos adicionales DUBSS
    nombres: '',
    apellidos: '',
    ci: '',
    telefono: '',
    ciudad: '',
    fecha_nacimiento: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Registro" />

        <form @submit.prevent="submit">
            <!-- Título -->
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Crear Cuenta</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Complete el formulario para registrarse en el sistema DUBSS
                </p>
            </div>

            <!-- Sección: Información Personal -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3 border-b pb-2">
                    📋 Información Personal
                </h3>

                <!-- Nombres -->
                <div class="mb-4">
                    <InputLabel for="nombres" value="Nombres *" />
                    <TextInput
                        id="nombres"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.nombres"
                        required
                        autofocus
                        autocomplete="given-name"
                        placeholder="Ej: Juan Carlos"
                    />
                    <InputError class="mt-2" :message="form.errors.nombres" />
                </div>

                <!-- Apellidos -->
                <div class="mb-4">
                    <InputLabel for="apellidos" value="Apellidos *" />
                    <TextInput
                        id="apellidos"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.apellidos"
                        required
                        autocomplete="family-name"
                        placeholder="Ej: Pérez López"
                    />
                    <InputError class="mt-2" :message="form.errors.apellidos" />
                </div>

                <!-- CI (Cédula de Identidad) -->
                <div class="mb-4">
                    <InputLabel for="ci" value="Cédula de Identidad (CI) *" />
                    <TextInput
                        id="ci"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.ci"
                        required
                        placeholder="Ej: 12345678"
                        maxlength="20"
                    />
                    <InputError class="mt-2" :message="form.errors.ci" />
                    <p class="mt-1 text-xs text-gray-500">
                        Ingrese su número de cédula sin guiones ni espacios
                    </p>
                </div>

                <!-- Fecha de Nacimiento -->
                <div class="mb-4">
                    <InputLabel for="fecha_nacimiento" value="Fecha de Nacimiento *" />
                    <TextInput
                        id="fecha_nacimiento"
                        type="date"
                        class="mt-1 block w-full"
                        v-model="form.fecha_nacimiento"
                        required
                        :max="new Date().toISOString().split('T')[0]"
                    />
                    <InputError class="mt-2" :message="form.errors.fecha_nacimiento" />
                </div>
            </div>

            <!-- Sección: Información de Contacto -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3 border-b pb-2">
                    📞 Información de Contacto
                </h3>

                <!-- Email -->
                <div class="mb-4">
                    <InputLabel for="email" value="Correo Electrónico *" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="form.email"
                        required
                        autocomplete="username"
                        placeholder="ejemplo@correo.com"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <!-- Teléfono -->
                <div class="mb-4">
                    <InputLabel for="telefono" value="Teléfono *" />
                    <TextInput
                        id="telefono"
                        type="tel"
                        class="mt-1 block w-full"
                        v-model="form.telefono"
                        required
                        placeholder="Ej: 70123456"
                        maxlength="15"
                    />
                    <InputError class="mt-2" :message="form.errors.telefono" />
                    <p class="mt-1 text-xs text-gray-500">
                        Incluya código de área si corresponde
                    </p>
                </div>

                <!-- Ciudad -->
                <div class="mb-4">
                    <InputLabel for="ciudad" value="Ciudad *" />
                    <select
                        id="ciudad"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        v-model="form.ciudad"
                        required
                    >
                        <option value="">Seleccione una ciudad</option>
                        <option value="La Paz">La Paz</option>
                        <option value="Cochabamba">Cochabamba</option>
                        <option value="Santa Cruz">Santa Cruz</option>
                        <option value="Oruro">Oruro</option>
                        <option value="Potosí">Potosí</option>
                        <option value="Sucre">Sucre</option>
                        <option value="Tarija">Tarija</option>
                        <option value="Beni">Beni</option>
                        <option value="Pando">Pando</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.ciudad" />
                </div>
            </div>

            <!-- Sección: Nombre de Usuario (name - oculto) -->
            <input type="hidden" :value="form.name = `${form.nombres} ${form.apellidos}`" />

            <!-- Sección: Seguridad -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3 border-b pb-2">
                    🔒 Seguridad
                </h3>

                <!-- Password -->
                <div class="mb-4">
                    <InputLabel for="password" value="Contraseña *" />
                    <TextInput
                        id="password"
                        type="password"
                        class="mt-1 block w-full"
                        v-model="form.password"
                        required
                        autocomplete="new-password"
                        placeholder="Mínimo 8 caracteres"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <!-- Password Confirmation -->
                <div class="mb-4">
                    <InputLabel for="password_confirmation" value="Confirmar Contraseña *" />
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        class="mt-1 block w-full"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="Repita su contraseña"
                    />
                    <InputError class="mt-2" :message="form.errors.password_confirmation" />
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex items-center justify-between mt-6">
                <Link
                    :href="route('login')"
                    class="text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    ¿Ya tienes cuenta? Inicia sesión
                </Link>

                <PrimaryButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Registrarse
                </PrimaryButton>
            </div>

            <!-- Nota legal -->
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-600 text-center">
                    Al registrarte, aceptas los términos y condiciones del sistema DUBSS.
                    Tus datos serán utilizados únicamente para la gestión de becas socioeconómicas.
                </p>
            </div>
        </form>
    </GuestLayout>
</template>

<style scoped>
/* Estilos personalizados si son necesarios */
</style>
