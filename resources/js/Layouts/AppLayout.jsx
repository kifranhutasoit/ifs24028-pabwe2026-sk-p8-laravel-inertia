import { Head, Link, router, usePage } from '@inertiajs/react';
import LoadingIndicator from '../Components/LoadingIndicator';

function Flash() {
    const { flash } = usePage().props;

    if (!flash?.success && !flash?.error) {
        return null;
    }

    const ok = Boolean(flash.success);

    return (
        <div className={`mb-4 rounded-lg border px-4 py-3 text-sm ${ok ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-800'}`}>
            {flash.success ?? flash.error}
        </div>
    );
}

function Avatar({ user }) {
    return user.profile_photo_url ? (
        <img src={user.profile_photo_url} alt={user.name} className="h-8 w-8 rounded-full object-cover" />
    ) : (
        <span className="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 text-sm font-semibold text-white">
            {user.name.charAt(0).toUpperCase()}
        </span>
    );
}

const logout = () => router.post('/logout');

function PublicShell({ children }) {
    const { auth, appName } = usePage().props;
    const user = auth.user;

    return (
        <div className="flex min-h-screen flex-col">
            <header className="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
                <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
                    <Link href="/" className="text-xl font-bold text-indigo-700">
                        {appName ?? 'BlogVerse Pro'}
                    </Link>
                    <nav className="flex items-center gap-3 text-sm">
                        <Link href="/articles" className="text-slate-600 hover:text-indigo-700">Artikel</Link>
                        {user ? (
                            <>
                                <Link href={user.role === 'admin' ? '/admin/users' : '/dashboard/posts'} className="rounded-md bg-indigo-600 px-3 py-1.5 font-medium text-white hover:bg-indigo-700">
                                    {user.role === 'admin' ? 'Panel Admin' : 'Dashboard Penulis'}
                                </Link>
                                <Avatar user={user} />
                                <button type="button" onClick={logout} className="text-slate-500 hover:text-red-600">Keluar</button>
                            </>
                        ) : (
                            <>
                                <Link href="/login" className="rounded-md border border-indigo-600 px-3 py-1.5 font-medium text-indigo-700 hover:bg-indigo-50">Masuk</Link>
                                <Link href="/register" className="rounded-md bg-indigo-600 px-3 py-1.5 font-medium text-white hover:bg-indigo-700">Daftar</Link>
                            </>
                        )}
                    </nav>
                </div>
            </header>

            <main className="mx-auto w-full max-w-6xl flex-1 px-4 py-8">
                <Flash />
                {children}
            </main>

            <footer className="border-t border-slate-200 bg-white py-6 text-center text-sm text-slate-500">
                <div className="mb-2 flex justify-center gap-4">
                    <Link href="/" className="hover:text-indigo-700">Beranda Publik</Link>
                    <Link href="/login" className="hover:text-indigo-700">Halaman Login</Link>
                    <Link href="/register" className="hover:text-indigo-700">Halaman Register</Link>
                </div>
                &copy; {new Date().getFullYear()} {appName ?? 'BlogVerse Pro'}
            </footer>
        </div>
    );
}

function DashboardShell({ children }) {
    const page = usePage();
    const user = page.props.auth.user;
    const url = page.url;

    const menu =
        user.role === 'admin'
            ? [
                  { href: '/admin/users', label: 'Pengguna' },
                  { href: '/admin/posts', label: 'Semua Artikel' },
                  { href: '/profile', label: 'Profil' },
                  { href: '/', label: 'Lihat Blog' },
              ]
            : [
                  { href: '/dashboard/posts', label: 'Artikel Saya' },
                  { href: '/profile', label: 'Profil' },
                  { href: '/', label: 'Lihat Blog' },
              ];

    return (
        <div className="flex min-h-screen">
            <aside className="hidden w-60 flex-col bg-slate-900 p-4 text-slate-200 md:flex">
                <Link href="/" className="mb-6 text-lg font-bold text-white">BlogVerse Pro</Link>
                <nav className="flex flex-1 flex-col gap-1">
                    {menu.map((item) => (
                        <Link
                            key={item.href}
                            href={item.href}
                            className={`rounded-md px-3 py-2 text-sm hover:bg-slate-800 ${url.startsWith(item.href) && item.href !== '/' ? 'bg-slate-800 text-white' : ''}`}
                        >
                            {item.label}
                        </Link>
                    ))}
                </nav>
            </aside>
            <div className="flex min-w-0 flex-1 flex-col">
                <header className="flex items-center justify-between border-b border-slate-200 bg-white px-6 py-3">
                    <div className="flex flex-wrap gap-3 text-sm md:hidden">
                        {menu.map((item) => (
                            <Link key={item.href} href={item.href} className="text-indigo-700">{item.label}</Link>
                        ))}
                    </div>
                    <div className="ml-auto flex items-center gap-3 text-sm">
                        <Avatar user={user} />
                        <span className="font-medium">{user.name}</span>
                        <span className="rounded-full bg-indigo-100 px-2 py-0.5 text-xs text-indigo-700">{user.role}</span>
                        <button type="button" onClick={logout} className="text-slate-500 hover:text-red-600">Keluar</button>
                    </div>
                </header>
                <main className="flex-1 p-6">
                    <Flash />
                    {children}
                </main>
            </div>
        </div>
    );
}

export default function AppLayout({ children, mode = 'public', title }) {
    const Shell = mode === 'dashboard' ? DashboardShell : PublicShell;

    return (
        <>
            <Head title={title} />
            <LoadingIndicator />
            <Shell>{children}</Shell>
        </>
    );
}
