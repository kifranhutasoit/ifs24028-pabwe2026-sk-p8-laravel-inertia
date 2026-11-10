import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';

const pages = import.meta.glob('./Pages/**/*.jsx', { eager: true });

createInertiaApp({
    title: (title) => (title ? `${title} - BlogVerse Pro` : 'BlogVerse Pro'),
    resolve: (name) => {
        const page = pages[`./Pages/${name}.jsx`];
        if (!page) {
            throw new Error(`Halaman Inertia tidak ditemukan: ${name}`);
        }
        return page;
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
});
