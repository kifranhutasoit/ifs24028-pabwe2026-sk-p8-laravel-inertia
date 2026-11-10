import { useForm, usePage } from '@inertiajs/react';
import { Spinner } from '../../Components/Icons';
import AppLayout from '../../Layouts/AppLayout';

export default function Edit() {
    const user = usePage().props.auth.user;
    const { data, setData, post, processing, errors } = useForm({
        _method: 'put',
        name: user.name,
        username: user.username,
        email: user.email ?? '',
        password: '',
        password_confirmation: '',
        photo: null,
    });

    const submit = (e) => {
        e.preventDefault();
        post('/profile', { forceFormData: true, preserveScroll: true });
    };

    const input = (name, label, type = 'text') => (
        <div>
            <label className="mb-1 block text-sm font-medium">{label}</label>
            <input
                type={type}
                value={data[name]}
                onChange={(e) => setData(name, e.target.value)}
                className="w-full rounded-md border border-slate-300 px-3 py-2"
            />
            {errors[name] && <p className="mt-1 text-sm text-red-600">{errors[name]}</p>}
        </div>
    );

    return (
        <AppLayout mode="dashboard" title="Profil">
            <div className="max-w-xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h1 className="mb-4 text-2xl font-bold">Profil Saya</h1>
                <form onSubmit={submit} className="space-y-4">
                    {user.profile_photo_url && <img src={user.profile_photo_url} alt={user.name} className="h-20 w-20 rounded-full object-cover" />}
                    <div>
                        <label className="mb-1 block text-sm font-medium">Foto Profil</label>
                        <input type="file" accept="image/*" onChange={(e) => setData('photo', e.target.files[0])} />
                        {errors.photo && <p className="mt-1 text-sm text-red-600">{errors.photo}</p>}
                    </div>
                    {input('name', 'Nama')}
                    {input('username', 'Username')}
                    {input('email', 'Email', 'email')}
                    {input('password', 'Password Baru (kosongkan jika tidak diganti)', 'password')}
                    {input('password_confirmation', 'Konfirmasi Password Baru', 'password')}
                    <button type="submit" disabled={processing} className="flex items-center gap-2 rounded-md bg-indigo-600 px-5 py-2 font-medium text-white hover:bg-indigo-700 disabled:opacity-60">
                        {processing && <Spinner />} Simpan Perubahan
                    </button>
                </form>
            </div>
        </AppLayout>
    );
}
