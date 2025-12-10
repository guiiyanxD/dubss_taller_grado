
//import '../css/app.css';
//import './bootstrap';
//
//import { createInertiaApp } from '@inertiajs/vue3';
//import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
//import { createApp, h } from 'vue';
//import { ZiggyVue } from '../../vendor/tightenco/ziggy';
//
//const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
//
//createInertiaApp({
//    title: (title) => `${title} - ${appName}`,
//    resolve: (name) =>
//        resolvePageComponent(
//            `./Pages/${name}.vue`,
//            import.meta.glob('./Pages/**/*.vue'),
//        ),
//    setup({ el, App, props, plugin }) {
//        return createApp({ render: () => h(App, props) })
//            .use(plugin)
//            .use(ZiggyVue)
//            .mount(el);
//    },
//    progress: {
//        color: '#4B5563',
//    },
//});
// resources/js/app.js - OPTIMIZADO PARA CODE SPLITTING

import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
// 1. ELIMINAR ESTA LÍNEA: import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,

    // 2. MODIFICACIÓN CRÍTICA: Implementación directa de import.meta.glob
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue');
        return pages[`./Pages/${name}.vue`]();
    },

    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
