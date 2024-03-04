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

export function reactBySelectorOnReady(selector, fn) {
    ready(() => {
        const elements = [...document.querySelectorAll(selector)];
        if (elements.length === 0) {
            return;
        }

        elements.forEach(element => {
            const initialData = JSON.parse(
                element.dataset['initialData']
            );

            createRoot(
                element
            ).render(fn({initialData}));
        });
    });
}
