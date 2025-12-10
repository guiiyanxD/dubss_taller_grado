<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: Array,
});

// Función para limpiar la etiqueta "Previous" y "Next" de los iconos HTML
const getLabel = (label) => {
    // Reemplaza las flechas de HTML &laquo; (<<) y &raquo; (>>) por texto legible o símbolos simples
    if (label.includes('&laquo;')) {
        return 'Anterior';
    }
    if (label.includes('&raquo;')) {
        return 'Siguiente';
    }
    return label;
};
</script>

<template>
    <div v-if="links.length > 3" class="flex flex-wrap items-center mt-6">
        <template v-for="(link, key) in links" :key="key">

            <div v-if="link.url === null"
                 class="mr-1 mb-1 px-4 py-2 text-sm leading-4 text-gray-400 border rounded-lg"
            >
                 {{ getLabel(link.label) }}
            </div>

            <Link v-else
                  class="mr-1 mb-1 px-4 py-2 text-sm leading-4 border rounded-lg hover:bg-blue-50 focus:border-blue-500 focus:text-blue-500 transition duration-150 ease-in-out"
                  :class="{ 'bg-blue-600 text-white border-blue-600': link.active, 'bg-white border-gray-300': !link.active }"
                  :href="link.url"
            >
                {{ getLabel(link.label) }}
            </Link>
        </template>
    </div>
</template>
