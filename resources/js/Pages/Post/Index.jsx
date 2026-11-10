import { Link, router, useForm, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { EyeIcon, PencilIcon, SparkIcon, Spinner, TrashIcon } from '../../Components/Icons';
import KebabMenu from '../../Components/KebabMenu';
import MarkdownEditor from '../../Components/MarkdownEditor';
import Modal from '../../Components/Modal';
import Pagination from '../../Components/Pagination';
import AppLayout from '../../Layouts/AppLayout';

const emptyForm = { title: '', category: 'Umum', excerpt: '', content: '', is_published: false, cover: null };

function StatCard({ label, value, color }) {
    return (
        <div className={`rounded-xl border p-5 ${color}`}>
            <p className="text-sm opacity-80">{label}</p>
            <p className="text-3xl font-bold">{value}</p>
        </div>
    );
}

function PostFormModal({ open, onClose, post, categories }) {
    const form = useForm({ ...emptyForm });

    useEffect(() => {
        if (open) {
            form.setData(
                post
                    ? { title: post.title, category: post.category, excerpt: post.excerpt ?? '', content: post.content, is_published: post.is_published, cover: null }
                    : { ...emptyForm }
            );
            form.clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, post]);

    const submit = (e) => {
        e.preventDefault();
        const options = { forceFormData: true, preserveScroll: true, onSuccess: onClose };

        if (post) {
            form.transform((data) => ({ ...data, _method: 'put' }));
            form.post(`/dashboard/posts/${post.id}`, options);
        } else {
            form.transform((data) => data);
            form.post('/dashboard/posts', options);
        }
    };

    const { data, setData, errors, processing } = form;

    return (
        <Modal
            open={open}
            onClose={onClose}
            fullScreen
            title={post ? 'Ubah Artikel' : 'Tambah Artikel'}
            headerClass="bg-indigo-700 text-white"
            footerClass="bg-indigo-50 border-t border-indigo-200"
            footer={
                <>
                    <button type="button" onClick={onClose} className="rounded-md border border-slate-300 bg-white px-5 py-2 text-slate-700 hover:bg-slate-100">Batal</button>
                    <button type="submit" form="post-form" disabled={processing} className="flex items-center gap-2 rounded-md bg-indigo-600 px-5 py-2 font-medium text-white hover:bg-indigo-700 disabled:opacity-60">
                        {processing && <Spinner />} Simpan
                    </button>
                </>
            }
        >
            <form id="post-form" onSubmit={submit} className="mx-auto max-w-4xl space-y-4">
                <div>
                    <label className="mb-1 block text-sm font-medium">Judul</label>
                    <input value={data.title} onChange={(e) => setData('title', e.target.value)} className="w-full rounded-md border border-slate-300 px-3 py-2" />
                    {errors.title && <p className="mt-1 text-sm text-red-600">{errors.title}</p>}
                </div>
                <div className="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label className="mb-1 block text-sm font-medium">Kategori</label>
                        <select value={data.category} onChange={(e) => setData('category', e.target.value)} className="w-full rounded-md border border-slate-300 px-3 py-2">
                            {categories.map((c) => (
                                <option key={c.value} value={c.value}>{c.label}</option>
                            ))}
                        </select>
                        {errors.category && <p className="mt-1 text-sm text-red-600">{errors.category}</p>}
                    </div>
                    <div>
                        <label className="mb-1 block text-sm font-medium">Foto Sampul</label>
                        <input type="file" accept="image/*" onChange={(e) => setData('cover', e.target.files[0])} className="w-full" />
                        {errors.cover && <p className="mt-1 text-sm text-red-600">{errors.cover}</p>}
                        {post?.cover_url && (
                            <button type="button" onClick={() => router.delete(`/dashboard/posts/${post.id}/cover`, { preserveScroll: true })} className="mt-1 text-xs text-red-600">
                                Hapus sampul saat ini
                            </button>
                        )}
                    </div>
                </div>
                <div>
                    <label className="mb-1 block text-sm font-medium">Ringkasan</label>
                    <textarea value={data.excerpt} onChange={(e) => setData('excerpt', e.target.value)} rows={2} className="w-full rounded-md border border-slate-300 px-3 py-2" />
                    {errors.excerpt && <p className="mt-1 text-sm text-red-600">{errors.excerpt}</p>}
                </div>
                <div>
                    <label className="mb-1 block text-sm font-medium">Isi Artikel (Markdown)</label>
                    <MarkdownEditor key={post ? `edit-${post.id}` : 'new'} value={data.content} onChange={(v) => setData('content', v)} />
                    {errors.content && <p className="mt-1 text-sm text-red-600">{errors.content}</p>}
                </div>
                <label className="flex items-center gap-2 text-sm">
                    <input type="checkbox" checked={data.is_published} onChange={(e) => setData('is_published', e.target.checked)} />
                    Publikasikan ke halaman publik
                </label>
            </form>
        </Modal>
    );
}

function AiModal({ open, onClose }) {
    const { flash } = usePage().props;
    const [count, setCount] = useState(1);
    const [topic, setTopic] = useState('');
    const [generating, setGenerating] = useState(false);
    const [saving, setSaving] = useState(false);
    const [items, setItems] = useState([]);

    useEffect(() => {
        if (flash?.generated) {
            setItems(flash.generated);
        }
    }, [flash?.generated]);

    const generate = () => {
        setGenerating(true);
        router.post('/dashboard/posts/ai-generate', { count, topic }, { preserveScroll: true, onFinish: () => setGenerating(false) });
    };

    const save = () => {
        setSaving(true);
        router.post('/dashboard/posts/ai-save', { posts: items }, {
            preserveScroll: true,
            onSuccess: () => {
                setItems([]);
                onClose();
            },
            onFinish: () => setSaving(false),
        });
    };

    return (
        <Modal
            open={open}
            onClose={onClose}
            title="AI Generator Artikel"
            headerClass="bg-violet-700 text-white"
            footerClass="bg-violet-50"
            footer={
                <>
                    <button type="button" onClick={onClose} className="rounded-md border border-slate-300 bg-white px-4 py-2 text-slate-700">Batal</button>
                    {items.length > 0 && (
                        <button type="button" onClick={save} disabled={saving} className="flex items-center gap-2 rounded-md bg-emerald-600 px-4 py-2 font-medium text-white disabled:opacity-60">
                            {saving && <Spinner />} Simpan {items.length} Draf
                        </button>
                    )}
                </>
            }
        >
            <div className="space-y-4">
                <div className="grid grid-cols-3 gap-3">
                    <div>
                        <label className="mb-1 block text-sm font-medium">Jumlah</label>
                        <input type="number" min={1} max={5} value={count} onChange={(e) => setCount(Math.min(5, Math.max(1, Number(e.target.value) || 1)))} className="w-full rounded-md border border-slate-300 px-3 py-2" />
                    </div>
                    <div className="col-span-2">
                        <label className="mb-1 block text-sm font-medium">Topik (opsional)</label>
                        <input value={topic} onChange={(e) => setTopic(e.target.value)} placeholder="mis. produktivitas belajar" className="w-full rounded-md border border-slate-300 px-3 py-2" />
                    </div>
                </div>
                <button type="button" onClick={generate} disabled={generating} className="flex items-center gap-2 rounded-md bg-violet-600 px-4 py-2 font-medium text-white disabled:opacity-60">
                    {generating ? <Spinner /> : <SparkIcon />} Hasilkan Artikel
                </button>

                {items.map((item, i) => (
                    <div key={i} className="rounded-lg border border-slate-200 p-3">
                        <p className="text-xs text-violet-700">{item.category}</p>
                        <p className="font-semibold">{item.title}</p>
                        <p className="text-sm text-slate-600">{item.excerpt}</p>
                    </div>
                ))}
            </div>
        </Modal>
    );
}

export default function Index({ posts, stats, categories, filters }) {
    const [search, setSearch] = useState(filters.search);
    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] = useState(null);
    const [deleting, setDeleting] = useState(null);
    const [aiOpen, setAiOpen] = useState(false);
    const [busy, setBusy] = useState(false);
    const fileInput = useRef(null);
    const { flash } = usePage().props;

    useEffect(() => {
        if (flash?.generated) {
            setAiOpen(true);
        }
    }, [flash?.generated]);

    const openCreate = () => {
        setEditing(null);
        setFormOpen(true);
    };

    const openEdit = (post) => {
        setEditing(post);
        setFormOpen(true);
    };

    const confirmDelete = () => {
        setBusy(true);
        router.delete(`/dashboard/posts/${deleting.id}`, {
            preserveScroll: true,
            onFinish: () => {
                setBusy(false);
                setDeleting(null);
            },
        });
    };

    const importFile = (e) => {
        const file = e.target.files[0];
        if (file) {
            router.post('/dashboard/posts/import', { file }, { forceFormData: true, preserveScroll: true });
        }
        e.target.value = '';
    };

    return (
        <AppLayout mode="dashboard" title="Artikel Saya">
            <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                <h1 className="text-2xl font-bold">Artikel Saya</h1>
                <div className="flex flex-wrap gap-2">
                    <button type="button" onClick={() => setAiOpen(true)} className="flex items-center gap-1 rounded-md bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700"><SparkIcon /> AI Generator</button>
                    <a href="/dashboard/posts/export" className="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Ekspor Excel</a>
                    <button type="button" onClick={() => fileInput.current.click()} className="rounded-md bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-700">Impor Excel</button>
                    <input ref={fileInput} type="file" accept=".xlsx,.xls" className="hidden" onChange={importFile} />
                    <button type="button" onClick={openCreate} className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ Tambah Artikel</button>
                </div>
            </div>

            <div className="mb-6 grid gap-4 sm:grid-cols-3">
                <StatCard label="Total Artikel" value={stats.total} color="border-indigo-200 bg-indigo-50 text-indigo-900" />
                <StatCard label="Total Selesai" value={stats.published} color="border-emerald-200 bg-emerald-50 text-emerald-900" />
                <StatCard label="Belum Selesai" value={stats.draft} color="border-amber-200 bg-amber-50 text-amber-900" />
            </div>

            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    router.get('/dashboard/posts', search ? { search } : {}, { preserveState: true });
                }}
                className="mb-4 flex gap-2"
            >
                <input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Cari artikel saya..." className="w-72 rounded-md border border-slate-300 px-3 py-2" />
                <button className="rounded-md bg-slate-800 px-4 py-2 text-white">Cari</button>
            </form>

            {posts.data.length === 0 ? (
                <p className="rounded-lg border border-dashed border-slate-300 p-10 text-center text-slate-500">Belum ada artikel. Klik “Tambah Artikel” untuk mulai menulis.</p>
            ) : (
                <ul className="divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white">
                    {posts.data.map((post) => (
                        <li key={post.id} className="flex items-center gap-4 px-4 py-3">
                            {post.cover_url ? (
                                <img src={post.cover_url} alt="" className="h-12 w-16 rounded object-cover" />
                            ) : (
                                <div className="flex h-12 w-16 items-center justify-center rounded bg-slate-100 text-xl">📝</div>
                            )}
                            <div className="min-w-0 flex-1">
                                <Link href={`/dashboard/posts/${post.id}`} className="block truncate font-medium hover:text-indigo-700">{post.title}</Link>
                                <p className="text-xs text-slate-500">{post.category} &middot; {new Date(post.created_at).toLocaleDateString('id-ID')}</p>
                            </div>
                            <button
                                type="button"
                                onClick={() => router.patch(`/dashboard/posts/${post.id}/publish`, {}, { preserveScroll: true })}
                                className={`rounded-full px-3 py-1 text-xs font-medium ${post.is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'}`}
                                title={post.is_published ? 'Klik untuk jadikan draf' : 'Klik untuk publikasikan'}
                            >
                                {post.is_published ? 'Published' : 'Draft'}
                            </button>
                            <KebabMenu
                                items={[
                                    { label: 'Detail', icon: <EyeIcon />, onClick: () => router.visit(`/dashboard/posts/${post.id}`), className: 'text-indigo-700' },
                                    { label: 'Ubah Edit', icon: <PencilIcon />, onClick: () => openEdit(post), className: 'text-indigo-700' },
                                    { label: 'Hapus', icon: <TrashIcon />, onClick: () => setDeleting(post), className: 'text-red-600' },
                                ]}
                            />
                        </li>
                    ))}
                </ul>
            )}
            <Pagination links={posts.links} />

            <PostFormModal open={formOpen} onClose={() => setFormOpen(false)} post={editing} categories={categories} />
            <AiModal open={aiOpen} onClose={() => setAiOpen(false)} />

            <Modal
                open={deleting !== null}
                onClose={() => setDeleting(null)}
                title="Hapus Artikel"
                headerClass="bg-red-600 text-white"
                footerClass="bg-red-50"
                footer={
                    <>
                        <button type="button" onClick={() => setDeleting(null)} className="rounded-md border border-slate-300 bg-white px-4 py-2">Batal</button>
                        <button type="button" onClick={confirmDelete} disabled={busy} className="flex items-center gap-2 rounded-md bg-red-600 px-4 py-2 font-medium text-white disabled:opacity-60">
                            {busy && <Spinner />} Hapus
                        </button>
                    </>
                }
            >
                <p>Yakin ingin menghapus artikel <strong>{deleting?.title}</strong>? Tindakan ini tidak dapat dibatalkan.</p>
            </Modal>
        </AppLayout>
    );
}
