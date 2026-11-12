export const root = document.documentElement;

export const prefersReducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

export const hasFinePointer = () => window.matchMedia('(hover: hover) and (pointer: fine)').matches;

export const isRtl = () => root.dir === 'rtl';

export const clamp = (value, min, max) => Math.min(max, Math.max(min, value));

export const lerp = (from, to, amount) => from + (to - from) * amount;

/**
 * Resolves once the intro loader has finished (immediately if it never ran).
 */
export const whenLoaded = () =>
    root.classList.contains('is-loading')
        ? new Promise((resolve) => window.addEventListener('kliq:loaded', resolve, { once: true }))
        : Promise.resolve();

const frameCallbacks = new Set();
let frameScheduled = false;

const runFrame = () => {
    frameScheduled = false;
    frameCallbacks.forEach((callback) => callback());
};

const scheduleFrame = () => {
    if (!frameScheduled) {
        frameScheduled = true;
        requestAnimationFrame(runFrame);
    }
};

/**
 * Runs the callback at most once per frame whenever the page scrolls or resizes.
 */
export function onScroll(callback) {
    frameCallbacks.add(callback);

    if (frameCallbacks.size === 1) {
        window.addEventListener('scroll', scheduleFrame, { passive: true });
        window.addEventListener('resize', scheduleFrame);
    }

    scheduleFrame();
}

/**
 * Wraps every word of an element's text in spans, leaving child elements intact.
 * Words are split on whitespace only, so Arabic letters stay joined.
 */
export function wrapWords(element, build) {
    let index = 0;

    const walk = (node) => {
        [...node.childNodes].forEach((child) => {
            if (child.nodeType === Node.TEXT_NODE) {
                const fragment = document.createDocumentFragment();

                child.textContent.split(/(\s+)/).forEach((part) => {
                    if (part === '') {
                        return;
                    }

                    fragment.append(/^\s+$/.test(part) ? document.createTextNode(part) : build(part, index++));
                });

                child.replaceWith(fragment);
            } else if (child.nodeType === Node.ELEMENT_NODE && !child.matches('br, [data-split-skip]')) {
                if (child.matches('.inline-pill')) {
                    const wrapper = build('', index++);
                    child.replaceWith(wrapper);
                    (wrapper.querySelector('.split-i') ?? wrapper).append(child);
                } else {
                    walk(child);
                }
            }
        });
    };

    walk(element);

    return index;
}
