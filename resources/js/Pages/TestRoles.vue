<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    user: Object
});
</script>

<template>
    <Head title="Test de Roles" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Prueba de Roles y Permisos
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Información del Usuario -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold mb-2">👤 Información del Usuario</h3>
                        <p><strong>Nombre:</strong> {{ user.name }}</p>
                        <p><strong>Email:</strong> {{ user.email }}</p>
                    </div>
                </div>

                <!-- Roles -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold mb-4">🎭 Roles Asignados</h3>
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="role in user.roles"
                                :key="role"
                                class="px-4 py-2 bg-blue-100 text-blue-800 rounded-full font-semibold"
                            >
                                {{ role }}
                            </span>
                        </div>
                        <p v-if="user.roles.length === 0" class="text-gray-500 italic">
                            No tiene roles asignados
                        </p>
                    </div>
                </div>

                <!-- Permisos -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-bold mb-4">🔐 Permisos ({{ user.permissions.length }})</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <div
                                v-for="permission in user.permissions"
                                :key="permission"
                                class="p-3 bg-green-50 border border-green-200 rounded-lg"
                            >
                                <span class="text-green-700">✓ {{ permission }}</span>
                            </div>
                        </div>
                        <p v-if="user.permissions.length === 0" class="text-gray-500 italic">
                            No tiene permisos asignados
                        </p>
                    </div>
                </div>

                <!-- Instrucciones -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mt-6">
                    <h4 class="font-bold text-yellow-800 mb-2">💡 Prueba el sistema:</h4>
                    <ol class="list-decimal list-inside space-y-1 text-yellow-800">
                        <li>Cierra sesión (Log Out)</li>
                        <li>Inicia sesión con otro usuario (ver credenciales en terminal)</li>
                        <li>Vuelve a esta página y observa los cambios en roles/permisos</li>
                    </ol>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
