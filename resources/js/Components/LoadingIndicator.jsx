import { router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { Spinner } from './Icons';

export default function LoadingIndicator() {
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        const offStart = router.on('start', () => setLoading(true));
        const offFinish = router.on('finish', () => setLoading(false));

        return () => {
            offStart();
            offFinish();
        };
    }, []);

    if (!loading) {
        return null;
    }

    return (
        <>
            <div className="fixed inset-x-0 top-0 z-[100] h-1 overflow-hidden bg-indigo-100">
                <div className="h-full w-1/3 animate-pulse bg-indigo-600" style={{ animation: 'bar 1s ease-in-out infinite' }} />
            </div>
            <style>{`@keyframes bar { 0% { margin-left: -33%; } 100% { margin-left: 100%; } }`}</style>
            <div className="fixed bottom-5 left-1/2 z-[100] flex -translate-x-1/2 items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-sm text-white shadow-lg">
                <Spinner />
                <em>Memproses tindakan...</em>
            </div>
        </>
    );
}
