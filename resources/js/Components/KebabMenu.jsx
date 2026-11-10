import { useEffect, useRef, useState } from 'react';
import { DotsIcon } from './Icons';

/** items: [{ label, icon, onClick, className }] */
export default function KebabMenu({ items }) {
    const [open, setOpen] = useState(false);
    const ref = useRef(null);

    useEffect(() => {
        const close = (e) => {
            if (ref.current && !ref.current.contains(e.target)) {
                setOpen(false);
            }
        };
        document.addEventListener('mousedown', close);
        return () => document.removeEventListener('mousedown', close);
    }, []);

    return (
        <div className="relative" ref={ref}>
            <button
                type="button"
                onClick={() => setOpen(!open)}
                className="rounded-full p-2 text-slate-500 hover:bg-slate-100"
                aria-label="Menu tindakan"
            >
                <DotsIcon />
            </button>
            {open && (
                <div className="absolute right-0 z-30 mt-1 w-44 rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                    {items.map((item) => (
                        <button
                            key={item.label}
                            type="button"
                            onClick={() => {
                                setOpen(false);
                                item.onClick();
                            }}
                            className={`flex w-full items-center gap-2 px-4 py-2 text-left text-sm hover:bg-slate-50 ${item.className ?? 'text-indigo-700'}`}
                        >
                            {item.icon}
                            {item.label}
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
}
