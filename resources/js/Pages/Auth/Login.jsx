import { Link, useForm } from '@inertiajs/react';
import { Spinner } from '../../Components/Icons';
import AppLayout from '../../Layouts/AppLayout';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({ username: '', password: '', remember: false });

    const submit = (e) => {
        e.preventDefault();
        post('/login');
    };

    return (
        <AppLayout title="Masuk">
            <div className="mx-auto max-w-md rounded-xl border border-slate-200 bg-white p-8 shadow-sm">
                <h1 className="mb-1 text-2xl font-bold">Masuk</h1>
                <p className="mb-6 text-sm text-slate-500">Masuk untuk mengelola artikel Anda.</p>

                <form onSubmit={submit} className="space-y-4">
                    <div>
                        <label htmlFor="login-username" className="mb-1 block text-sm font-medium">Username</label>
                        <input
                            id="login-username"
                            type="text"
                            value={data.username}
                            onChange={(e) => setData('username', e.target.value)}
                            className="w-full rounded-md border border-slate-300 px-3 py-2"
                            autoFocus
                        />
                        {errors.username && <p className="mt-1 text-sm text-red-700">{errors.username}</p>}
                    </div>
                    <div>
                        <label htmlFor="login-password" className="mb-1 block text-sm font-medium">Password</label>
                        <input
                            id="login-password"
                            type="password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            className="w-full rounded-md border border-slate-300 px-3 py-2"
                        />
                        {errors.password && <p className="mt-1 text-sm text-red-700">{errors.password}</p>}
                    </div>
                    <label className="flex items-center gap-2 text-sm">
                        <input type="checkbox" checked={data.remember} onChange={(e) => setData('remember', e.target.checked)} />
                        Ingat saya
                    </label>
                    <button
                        id="login-submit"
                        type="submit"
                        disabled={processing}
                        className="flex w-full items-center justify-center gap-2 rounded-md bg-indigo-600 py-2 font-medium text-white hover:bg-indigo-700 disabled:opacity-60"
                    >
                        {processing && <Spinner />} Masuk
                    </button>
                </form>

                <p className="mt-6 text-center text-sm text-slate-500">
                    Belum punya akun? <Link href="/register" className="font-medium text-indigo-700">Daftar</Link>
                </p>
            </div>
        </AppLayout>
    );
}
