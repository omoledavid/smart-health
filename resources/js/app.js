import { createApp, h } from 'vue';
import { createInertiaApp, Link, Head } from '@inertiajs/vue3';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import VueApexCharts from 'vue3-apexcharts';

createInertiaApp({
    title: (title) => (title ? `${title} · SmartHealth` : 'SmartHealth'),
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(VueApexCharts)
            .component('Link', Link)
            .component('Head', Head)
            .component('apexchart', VueApexCharts)
            .mount(el);
    },
    progress: {
        color: '#2e37a4',
    },
});
