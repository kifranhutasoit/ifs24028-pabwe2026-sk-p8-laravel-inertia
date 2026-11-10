export default function Modal({
    open,
    onClose,
    title,
    children,
    footer,
    fullScreen = false,
    headerClass = 'bg-indigo-600 text-white',
    footerClass = 'bg-slate-100',
}) {
    if (!open) {
        return null;
    }

    const size = fullScreen ? 'h-screen w-screen' : 'max-h-[90vh] w-full max-w-lg rounded-xl';

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div className={`flex flex-col overflow-hidden bg-white shadow-2xl ${size}`}>
                <div className={`flex items-center justify-between px-6 py-4 ${headerClass}`}>
                    <h2 className="text-lg font-semibold">{title}</h2>
                    <button type="button" onClick={onClose} className="text-2xl leading-none opacity-80 hover:opacity-100" aria-label="Tutup">
                        &times;
                    </button>
                </div>
                <div className="flex-1 overflow-y-auto p-6">{children}</div>
                {footer && <div className={`flex justify-end gap-2 px-6 py-4 ${footerClass}`}>{footer}</div>}
            </div>
        </div>
    );
}
