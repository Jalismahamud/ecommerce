import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { createVuetify } from 'vuetify';
import 'bootstrap/dist/js/bootstrap.bundle';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const vuetify = createVuetify({
    theme: {
        defaultTheme: 'light',
        themes: {
            light: {
                colors: {
                    primary: '#5e35b1',
                    secondary: '#ede7f6',
                    success: '#2e7d32',
                    error: '#d32f2f',
                },
            },
        },
    },
});

createInertiaApp({
    title: (title) => (title ? `${title} - Fresh Food` : 'Fresh Food'),
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue');
        const page = pages[`./Pages/${name}.vue`];

        if (!page) {
            throw new Error(`Inertia page "${name}" was not found.`);
        }

        return resolvePageComponent(`./Pages/${name}.vue`, pages);
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(vuetify)
            .mount(el);
    },
    progress: {
        color: '#0aad0a',
    },
});
