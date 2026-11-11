import { router } from '@inertiajs/react';
import { useState } from 'react';
import Pagination from '../../Components/Pagination';
import AppLayout from '../../Layouts/AppLayout';

export default function PostList({ posts, filters }) {
    const [search, setSearch] = useState(filters.search);

    return (
        <AppLayout mode="dashboard" title="Semua Artikel">
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h1 className="text-2xl font-bold">Semua Artikel (Read-only)</h1>
                <a href="/admin/posts/export" className="rounded-md bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800">Ekspor Semua ke Excel</a>
            </div>
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    router.get('/admin/posts', search ? { search } : {}, { preserveState: true });
                }}
                className="mb-4 flex gap-2"
            >
                <input type="search" aria-label="Cari" value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Cari artikel..." className="w-72 rounded-md border border-slate-300 px-3 py-2" />
                <button className="rounded-md bg-indigo-600 px-4 py-2 text-white">Cari</button>
            </form>

            <ul className="divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white">
                {posts.data.map((p) => (
                    <li key={p.id} className="flex items-center justify-between gap-4 px-4 py-3">
                        <div className="min-w-0">
                            <p className="truncate font-medium">{p.title}</p>
                            <p className="text-xs text-slate-500">{p.user?.name} &middot; {p.category}</p>
                        </div>
                        <span className={`shrink-0 rounded-full px-2 py-0.5 text-xs ${p.is_published ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'}`}>
                            {p.is_published ? 'Published' : 'Draft'}
                        </span>
                    </li>
                ))}
            </ul>
            <Pagination links={posts.links} />
        </AppLayout>
    );
}
