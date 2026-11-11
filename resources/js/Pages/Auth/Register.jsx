import { Link, useForm } from '@inertiajs/react';
import { Spinner } from '../../Components/Icons';
import AppLayout from '../../Layouts/AppLayout';

const fields = [
    { name: 'name', label: 'Nama Lengkap', type: 'text' },
    { name: 'username', label: 'Username', type: 'text' },
    { name: 'email', label: 'Email (opsional)', type: 'email' },
    { name: 'password', label: 'Password', type: 'password' },
    { name: 'password_confirmation', label: 'Konfirmasi Password', type: 'password' },
];

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        username: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post('/register');
    };

    return (
        <AppLayout title="Daftar">
            <div className="mx-auto max-w-md rounded-xl border border-slate-200 bg-white p-8 shadow-sm">
                <h1 className="mb-1 text-2xl font-bold">Daftar Penulis</h1>
                <p className="mb-6 text-sm text-slate-500">Buat akun untuk mulai menerbitkan artikel.</p>

                <form onSubmit={submit} className="space-y-4">
                    {fields.map((f) => (
                        <div key={f.name}>
                            <label htmlFor={`register-${f.name}`} className="mb-1 block text-sm font-medium">{f.label}</label>
                            <input
                                id={`register-${f.name}`}
                                type={f.type}
                                value={data[f.name]}
                                onChange={(e) => setData(f.name, e.target.value)}
                                className="w-full rounded-md border border-slate-300 px-3 py-2"
                            />
                            {errors[f.name] && <p className="mt-1 text-sm text-red-700">{errors[f.name]}</p>}
                        </div>
                    ))}
                    <button
                        id="register-submit"
                        type="submit"
                        disabled={processing}
                        className="flex w-full items-center justify-center gap-2 rounded-md bg-indigo-600 py-2 font-medium text-white hover:bg-indigo-700 disabled:opacity-60"
                    >
                        {processing && <Spinner />} Daftar
                    </button>
                </form>

                <p className="mt-6 text-center text-sm text-slate-500">
                    Sudah punya akun? <Link href="/login" className="font-medium text-indigo-700">Masuk</Link>
                </p>
            </div>
        </AppLayout>
    );
}
