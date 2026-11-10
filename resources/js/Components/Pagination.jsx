import { Link } from '@inertiajs/react';

export default function Pagination({ links = [] }) {
    if (links.length <= 3) {
        return null;
    }

    return (
        <nav className="mt-6 flex flex-wrap justify-center gap-1">
            {links.map((link, i) => {
                const cls = `rounded-md border px-3 py-1 text-sm ${
                    link.active ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-100'
                } ${link.url ? '' : 'pointer-events-none opacity-40'}`;

                return link.url ? (
                    <Link key={i} href={link.url} className={cls} preserveScroll dangerouslySetInnerHTML={{ __html: link.label }} />
                ) : (
                    <span key={i} className={cls} dangerouslySetInnerHTML={{ __html: link.label }} />
                );
            })}
        </nav>
    );
}
