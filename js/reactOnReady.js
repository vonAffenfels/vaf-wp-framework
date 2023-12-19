import {createRoot} from '@wordpress/element';
import {ready} from './ready.js';

export function reactOnReady(id, fn) {
    ready(() => {
        if (!document.getElementById(id)) {
            return;
        }

        const initialData = JSON.parse(
            document.getElementById(id).dataset['initialData']
        );

        createRoot(
            document.getElementById(id)
        ).render(fn({initialData}));
    });
}
