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
        if(!window.inlineEditPost) {
            return;
        }

        const wp_inline_edit_function = window.inlineEditPost.edit;

        let reactRoot = null;

        window.inlineEditPost.edit = function (postId, ...args) {
            if ( typeof( postId ) == 'object' ) { // if it is object, get the ID number
                postId = parseInt( this.getId( postId ) );
            }

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
