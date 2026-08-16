import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import defaultMktLogo from '../images/mkt_logo.png';

const appName = import.meta.env.VITE_APP_NAME || 'Yayasan MKT Indonesia';

const updateFavicon = (logoUrl) => {
    if (typeof document === 'undefined') return;
    const url = logoUrl || defaultMktLogo;
    let link = document.querySelector("link[rel~='icon']");
    if (!link) {
        link = document.createElement('link');
        link.rel = 'icon';
        document.head.appendChild(link);
    }
    link.href = url;

    let appleLink = document.querySelector("link[rel='apple-touch-icon']");
    if (appleLink) {
        appleLink.href = url;
    }
};

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        if (props?.initialPage?.props?.mktProfile?.logo) {
            updateFavicon(props.initialPage.props.mktProfile.logo);
        } else {
            updateFavicon(defaultMktLogo);
        }
        
        router.on('navigate', (event) => {
            if (event.detail?.page?.props?.mktProfile?.logo) {
                updateFavicon(event.detail.page.props.mktProfile.logo);
            }
        });

        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#EA580C', // Brand Soft Orange
    },
});
