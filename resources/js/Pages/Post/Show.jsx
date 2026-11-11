import { Link, router } from '@inertiajs/react';
import { lazy, Suspense } from 'react';
const MarkdownViewer = lazy(() => import('../../Components/MarkdownViewer'));
import AppLayout from '../../Layouts/AppLayout';

export default function Show({ post }) {
    return (
        <AppLayout mode="dashboard" title={post.title}>
            <Link href="/dashboard/posts" className="text-sm text-indigo-700">&larr; Kembali ke daftar</Link>
            <div className="mx-auto mt-4 max-w-3xl rounded-xl border border-slate-200 bg-white p-8">
                <div className="mb-2 flex items-center gap-2">
                    <span className="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700">{post.category}</span>
                    <span className={`rounded-full px-2 py-0.5 text-xs ${post.is_published ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'}`}>
                        {post.is_published ? 'Published' : 'Draft'}
                    </span>
                </div>
                <h1 className="text-3xl font-extrabold">{post.title}</h1>
                {post.excerpt && <p className="mt-2 text-slate-600">{post.excerpt}</p>}
                {post.cover_url && <img src={post.cover_url} alt={post.title} width={768} height={384} decoding="async" className="my-6 max-h-96 w-full rounded-lg object-cover" />}
                <div className="mt-6">
                    <Suspense fallback={<p className="text-slate-600">Memuat artikel...</p>}>
                        <MarkdownViewer content={post.content} />
                    </Suspense>
                </div>
                <div className="mt-8 flex gap-3 border-t pt-4">
                    <button onClick={() => router.patch(`/dashboard/posts/${post.id}/publish`)} className="rounded-md bg-indigo-600 px-4 py-2 text-sm text-white">
                        {post.is_published ? 'Jadikan Draf' : 'Publikasikan'}
                    </button>
                    {post.is_published && (
                        <Link href={`/articles/${post.slug}`} className="rounded-md border border-indigo-600 px-4 py-2 text-sm text-indigo-700">Lihat di halaman publik</Link>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
