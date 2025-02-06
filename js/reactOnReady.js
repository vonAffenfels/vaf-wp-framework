import {createRoot} from 'react-dom';
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

export function reactOnQuickEdit(id, fn) {
    ready(() => {
        const wp_inline_edit_function = inlineEditPost.edit;
        let reactRoot = null;

        inlineEditPost.edit = function (postId, ...args) {
            wp_inline_edit_function.apply(this, [postId, ...args]);

            if (!document.getElementById(id)) {
                return;
            }

            const initialData = JSON.parse(
                document.getElementById(id).dataset['initialData']
            );

            if(reactRoot) {
                reactRoot.unmount();
            }

            reactRoot = createRoot(
                document.getElementById(id)
            );
            reactRoot.render(fn({postId, initialData}));
        };
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
