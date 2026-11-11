import { Link, usePage } from '@inertiajs/react';
import { lazy, Suspense } from 'react';
const MarkdownViewer = lazy(() => import('../../Components/MarkdownViewer'));
import AppLayout from '../../Layouts/AppLayout';

export default function PostShow({ post }) {
    const { auth } = usePage().props;

    return (
        <AppLayout title={post.title}>
            <article className="mx-auto max-w-3xl">
                <Link href="/articles" className="text-sm text-indigo-700">&larr; Kembali ke daftar artikel</Link>
                <span className="mt-4 inline-block rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700">{post.category}</span>
                <h1 className="mt-2 text-4xl font-extrabold leading-tight">{post.title}</h1>
                <p className="mt-2 text-sm text-slate-500">
                    Oleh {post.user?.name} &middot; {post.published_at ? new Date(post.published_at).toLocaleDateString('id-ID', { dateStyle: 'long' }) : ''}
                </p>

                {post.cover_url && <img src={post.cover_url} alt={post.title} width={768} height={384} decoding="async" className="my-6 max-h-96 w-full rounded-xl object-cover" />}

                <div className="mt-6">
                    <Suspense fallback={<p className="text-slate-600">Memuat artikel...</p>}>
                        <MarkdownViewer content={post.content} />
                    </Suspense>
                </div>

                {!auth.user && (
                    <div className="mt-12 rounded-xl bg-indigo-50 p-6 text-center">
                        <h3 className="text-lg font-semibold">Suka tulisan ini? Bergabunglah dengan komunitas.</h3>
                        <p className="mt-1 text-sm text-slate-600">Masuk atau daftar untuk mulai menulis artikelmu sendiri.</p>
                        <div className="mt-4 flex justify-center gap-3">
                            <Link href="/login" className="rounded-md bg-indigo-600 px-5 py-2 font-medium text-white hover:bg-indigo-700">Halaman Login</Link>
                            <Link href="/register" className="rounded-md border border-indigo-600 px-5 py-2 font-medium text-indigo-700 hover:bg-white">Halaman Register</Link>
                        </div>
                    </div>
                )}
            </article>
        </AppLayout>
    );
}
