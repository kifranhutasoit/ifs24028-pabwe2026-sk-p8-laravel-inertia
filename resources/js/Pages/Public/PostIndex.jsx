import { Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import Pagination from '../../Components/Pagination';
import AppLayout from '../../Layouts/AppLayout';

export default function PostIndex({ posts, categories, filters }) {
    const { auth } = usePage().props;
    const [search, setSearch] = useState(filters.search);
    const [category, setCategory] = useState(filters.category);

    const apply = (next = {}) => {
        const params = { search, category, ...next };
        Object.keys(params).forEach((k) => params[k] === '' && delete params[k]);
        router.get('/articles', params, { preserveState: true });
    };

    return (
        <AppLayout title="Beranda">
            <section className="mb-10 rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 p-10 text-white">
                <h1 className="mb-3 text-4xl font-extrabold">Baca, Tulis, dan Bagikan Ide</h1>
                <p className="mb-6 max-w-2xl text-indigo-100">
                    BlogVerse Pro adalah platform publikasi artikel. Jelajahi tulisan komunitas atau mulai menulis dengan bantuan AI.
                </p>
                {!auth.user && (
                    <div className="flex flex-wrap gap-3">
                        <Link href="/login" className="rounded-md bg-white px-5 py-2 font-semibold text-indigo-700 hover:bg-indigo-50">Halaman Login (Masuk)</Link>
                        <Link href="/register" className="rounded-md border border-white px-5 py-2 font-semibold hover:bg-white/10">Halaman Register (Daftar Penulis)</Link>
                    </div>
                )}
            </section>

            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    apply();
                }}
                className="mb-6 flex flex-wrap gap-3"
            >
                <input
                    type="search"
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    placeholder="Cari judul atau isi artikel..."
                    className="min-w-60 flex-1 rounded-md border border-slate-300 px-3 py-2"
                />
                <select
                    value={category}
                    onChange={(e) => {
                        setCategory(e.target.value);
                        apply({ category: e.target.value });
                    }}
                    className="rounded-md border border-slate-300 px-3 py-2"
                >
                    <option value="">Semua kategori</option>
                    {categories.map((c) => (
                        <option key={c.value} value={c.value}>{c.label}</option>
                    ))}
                </select>
                <button type="submit" className="rounded-md bg-indigo-600 px-5 py-2 font-medium text-white hover:bg-indigo-700">Cari</button>
            </form>

            {posts.data.length === 0 ? (
                <p className="rounded-lg border border-dashed border-slate-300 p-10 text-center text-slate-500">Belum ada artikel yang cocok.</p>
            ) : (
                <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {posts.data.map((post) => (
                        <Link key={post.id} href={`/articles/${post.slug}`} className="group overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
                            {post.cover_url ? (
                                <img src={post.cover_url} alt={post.title} className="h-44 w-full object-cover" />
                            ) : (
                                <div className="flex h-44 items-center justify-center bg-gradient-to-br from-indigo-100 to-violet-100 text-4xl">✍️</div>
                            )}
                            <div className="p-4">
                                <span className="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700">{post.category}</span>
                                <h2 className="mt-2 text-lg font-semibold group-hover:text-indigo-700">{post.title}</h2>
                                <p className="mt-1 line-clamp-3 text-sm text-slate-600">{post.excerpt}</p>
                                <p className="mt-3 text-xs text-slate-400">
                                    {post.user?.name} &middot; {post.published_at ? new Date(post.published_at).toLocaleDateString('id-ID') : ''}
                                </p>
                            </div>
                        </Link>
                    ))}
                </div>
            )}

            <Pagination links={posts.links} />
        </AppLayout>
    );
}
