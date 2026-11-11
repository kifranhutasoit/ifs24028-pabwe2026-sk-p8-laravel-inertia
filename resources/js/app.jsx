import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';

// Setiap halaman dimuat saat dibutuhkan (code splitting) supaya bundle awal kecil.
const pages = import.meta.glob('./Pages/**/*.jsx');

createInertiaApp({
    title: (title) => (title ? `${title} - BlogVerse Pro` : 'BlogVerse Pro'),
    resolve: async (name) => {
        const loader = pages[`./Pages/${name}.jsx`];
        if (!loader) {
            throw new Error(`Halaman Inertia tidak ditemukan: ${name}`);
        }
        return await loader();
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
});
