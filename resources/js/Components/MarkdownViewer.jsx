import Viewer from '@toast-ui/editor/dist/toastui-editor-viewer';
import '@toast-ui/editor/dist/toastui-editor-viewer.css';
import { useEffect, useRef } from 'react';

export default function MarkdownViewer({ content = '' }) {
    const el = useRef(null);

    useEffect(() => {
        const viewer = new Viewer({ el: el.current, initialValue: content });
        return () => viewer.destroy();
    }, [content]);

    return <div ref={el} />;
}
