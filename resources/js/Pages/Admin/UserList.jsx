import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import Pagination from '../../Components/Pagination';
import AppLayout from '../../Layouts/AppLayout';

export default function UserList({ users, filters }) {
    const [search, setSearch] = useState(filters.search);

    return (
        <AppLayout mode="dashboard" title="Pengguna">
            <h1 className="mb-4 text-2xl font-bold">Kelola Pengguna</h1>
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    router.get('/admin/users', search ? { search } : {}, { preserveState: true });
                }}
                className="mb-4 flex gap-2"
            >
                <input type="search" aria-label="Cari" value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Cari nama atau username..." className="w-72 rounded-md border border-slate-300 px-3 py-2" />
                <button className="rounded-md bg-indigo-600 px-4 py-2 text-white">Cari</button>
            </form>

            <div className="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table className="w-full text-left text-sm">
                    <thead className="bg-slate-100 text-slate-600">
                        <tr>
                            <th className="px-4 py-3">Nama</th>
                            <th className="px-4 py-3">Username</th>
                            <th className="px-4 py-3">Peran</th>
                            <th className="px-4 py-3">Artikel</th>
                            <th className="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {users.data.map((u) => (
                            <tr key={u.id} className="border-t border-slate-100">
                                <td className="px-4 py-3 font-medium">{u.name}</td>
                                <td className="px-4 py-3">{u.username}</td>
                                <td className="px-4 py-3">{u.role}</td>
                                <td className="px-4 py-3">{u.posts_count}</td>
                                <td className="space-x-3 px-4 py-3 text-right">
                                    <Link href={`/admin/users/${u.id}/posts`} className="text-indigo-700">Lihat Artikel</Link>
                                    <a href={`/admin/users/${u.id}/posts/export`} className="text-emerald-800">Ekspor Excel</a>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <Pagination links={users.links} />
        </AppLayout>
    );
}
