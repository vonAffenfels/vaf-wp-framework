export class VisibleElement {
    static fromElement(element) {
        const onVisible = new VisibleElement();

        onVisible.element = element;

        return onVisible;
    }

    onVisible(callback) {
        if (this.isInViewport()) {
            callback();
            return;
        }

        visibilityTracker.onChange(() => {
            if (this.isInViewport()) {
                callback();
                return true;
            }
        });
    }

    isInViewport() {
        const rect = this.element.getBoundingClientRect();

        const verticalBounds = Bounds.fromMinMax(0, window.innerHeight || document.documentElement.clientHeight);
        const horizontalBounds = Bounds.fromMinMax(0, window.innerWidth || document.documentElement.clientWidth);

        return (
            (
                verticalBounds.within(rect.top)
                || verticalBounds.within(rect.bottom))
            && (
                horizontalBounds.within(rect.left)
                || horizontalBounds.within(rect.right)
            )
        );
    }
}

class Bounds {
    static fromMinMax(min, max) {
        const bounds = new Bounds;

        bounds.min = min;
        bounds.max = max;

        return bounds;
    }

    within(value) {
        return this.min <= value && value <= this.max;
    }
}

class VisibilityTracker {
    callbacks = [];

    constructor() {
        if (this.attachListeners()) {
            return;
        }

        if (this.attachInternetExplorer9Listeners()) {
            return;
        }

        console.log('Failed to register visibility listeners. Old Browser?');
    }

    onChange(callback) {
        this.callbacks.push(callback);
    }

    remove(callback) {
        this.callbacks = this.callbacks.filter(cb => cb !== callback);
    }

    attachListeners() {
        if (!window.addEventListener) {
            return false;
        }

        addEventListener('DOMContentLoaded', () => this.fireCallbacks(), false);
        addEventListener('load', () => this.fireCallbacks(), false);
        addEventListener('scroll', () => this.fireCallbacks(), false);
        addEventListener('resize', () => this.fireCallbacks(), false);
        return true;
    }

    attachInternetExplorer9Listeners() {
        if (!window.attachEvent) {
            return false;
        }

        attachEvent('onDOMContentLoaded',); // Internet Explorer 9+ :(
        attachEvent('onload', () => this.fireCallbacks());
        attachEvent('onscroll', () => this.fireCallbacks());
        attachEvent('onresize', () => this.fireCallbacks());
        return true;
    }

    fireCallbacks() {
        this.callbacks
            .map(callback => ({shouldRemove: callback(), callback}))
            .forEach(({shouldRemove, callback}) => {
                if (shouldRemove) {
                    this.remove(callback);
                }
            })
        ;
    }
}

const visibilityTracker = new VisibilityTracker;
