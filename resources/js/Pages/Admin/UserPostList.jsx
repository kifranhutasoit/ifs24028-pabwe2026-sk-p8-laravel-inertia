import { Link } from '@inertiajs/react';
import Pagination from '../../Components/Pagination';
import AppLayout from '../../Layouts/AppLayout';

export default function UserPostList({ owner, posts }) {
    return (
        <AppLayout mode="dashboard" title={`Artikel ${owner.name}`}>
            <Link href="/admin/users" className="text-sm text-indigo-700">&larr; Kembali ke pengguna</Link>
            <div className="my-4 flex flex-wrap items-center justify-between gap-3">
                <h1 className="text-2xl font-bold">Artikel milik {owner.name} <span className="text-base font-normal text-slate-500">(@{owner.username})</span></h1>
                <a href={`/admin/users/${owner.id}/posts/export`} className="rounded-md bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800">Ekspor ke Excel</a>
            </div>

            {posts.data.length === 0 ? (
                <p className="rounded-lg border border-dashed p-8 text-center text-slate-500">Pengguna ini belum punya artikel.</p>
            ) : (
                <ul className="divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white">
                    {posts.data.map((p) => (
                        <li key={p.id} className="flex items-center justify-between gap-4 px-4 py-3">
                            <div className="min-w-0">
                                <p className="truncate font-medium">{p.title}</p>
                                <p className="text-xs text-slate-500">{p.category}</p>
                            </div>
                            <span className={`shrink-0 rounded-full px-2 py-0.5 text-xs ${p.is_published ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'}`}>
                                {p.is_published ? 'Published' : 'Draft'}
                            </span>
                        </li>
                    ))}
                </ul>
            )}
            <Pagination links={posts.links} />
        </AppLayout>
    );
}
