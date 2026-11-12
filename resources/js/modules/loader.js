import { root } from './utils';

const DURATION = 1400;

/**
 * Plays the Kliq intro once per browser session (the head script decides).
 */
export function initLoader() {
    if (!root.classList.contains('is-loading')) {
        return;
    }

    const counter = document.querySelector('[data-loader-count]');
    const start = performance.now();

    const tick = (now) => {
        const progress = Math.min(1, (now - start) / DURATION);
        const eased = 1 - (1 - progress) ** 3;

        if (counter) {
            counter.textContent = String(Math.round(eased * 100)).padStart(3, '0');
        }

        if (progress < 1) {
            requestAnimationFrame(tick);
        }
    };

    requestAnimationFrame(tick);

    try {
        sessionStorage.setItem('kliq-intro', '1');
    } catch {
        // The intro simply plays again next time.
    }

    setTimeout(() => {
        root.classList.add('is-leaving');
        root.classList.remove('is-loading');
        window.dispatchEvent(new Event('kliq:loaded'));

        setTimeout(() => root.classList.remove('is-leaving'), 1000);
    }, DURATION + 250);
}
