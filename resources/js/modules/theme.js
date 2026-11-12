import { prefersReducedMotion, root } from './utils';

const STORAGE_KEY = 'kliq-theme';

const currentTheme = () => (root.dataset.theme === 'dark' ? 'dark' : 'light');

function syncControls() {
    const isDark = currentTheme() === 'dark';

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-pressed', String(isDark));
    });

    document.querySelector('meta[name="theme-color"]')?.setAttribute('content', isDark ? '#0A0A0A' : '#F4F4F1');
}

function applyTheme(theme) {
    root.dataset.theme = theme;

    try {
        localStorage.setItem(STORAGE_KEY, theme);
    } catch {
        // Storage can be unavailable (private mode); the theme still applies.
    }

    syncControls();
}

/**
 * Reveals the new theme as a circle expanding from the toggle that was pressed.
 */
function switchTheme(button) {
    const next = currentTheme() === 'dark' ? 'light' : 'dark';

    if (!document.startViewTransition || prefersReducedMotion()) {
        root.classList.add('theme-fade');
        applyTheme(next);
        setTimeout(() => root.classList.remove('theme-fade'), 600);

        return;
    }

    const rect = button.getBoundingClientRect();
    const x = rect.left + rect.width / 2;
    const y = rect.top + rect.height / 2;
    const radius = Math.hypot(Math.max(x, innerWidth - x), Math.max(y, innerHeight - y));

    root.classList.add('theme-vt');

    const transition = document.startViewTransition(() => applyTheme(next));

    transition.ready.then(() => {
        root.animate(
            { clipPath: [`circle(0px at ${x}px ${y}px)`, `circle(${radius}px at ${x}px ${y}px)`] },
            { duration: 750, easing: 'cubic-bezier(0.76, 0, 0.24, 1)', pseudoElement: '::view-transition-new(root)' },
        );
    });

    transition.finished.finally(() => root.classList.remove('theme-vt'));
}

export function initTheme() {
    syncControls();

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => switchTheme(button));
    });
}
