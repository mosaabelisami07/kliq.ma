import { prefersReducedMotion, root } from './utils';

/**
 * Full-screen mobile menu with focus handling and Escape to close.
 */
export function initMenu() {
    const toggle = document.querySelector('[data-menu-toggle]');
    const menu = document.getElementById('mobile-menu');

    if (!toggle || !menu) {
        return;
    }

    const setOpen = (open) => {
        root.classList.toggle('menu-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', open ? toggle.dataset.labelClose : toggle.dataset.labelOpen);
        menu.inert = !open;

        if (open) {
            menu.querySelector('a')?.focus({ preventScroll: true });
        }
    };

    menu.inert = true;
    toggle.addEventListener('click', () => setOpen(!root.classList.contains('menu-open')));

    // Section links scroll the page underneath, so close the menu first.
    menu.querySelectorAll('a[href*="#"]').forEach((link) => {
        link.addEventListener('click', () => setOpen(false));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && root.classList.contains('menu-open')) {
            setOpen(false);
            toggle.focus();
        }
    });

    window.matchMedia('(min-width: 1024px)').addEventListener('change', () => setOpen(false));
}

/**
 * Cycles the words of a [data-rotator] headline.
 */
export function initRotators() {
    document.querySelectorAll('[data-rotator]').forEach((rotator) => {
        const words = [...rotator.children];

        words[0]?.classList.add('is-active');

        if (words.length < 2 || prefersReducedMotion()) {
            return;
        }

        let index = 0;

        setInterval(() => {
            if (document.hidden) {
                return;
            }

            const current = words[index];
            index = (index + 1) % words.length;
            const next = words[index];

            current.classList.remove('is-active');
            current.classList.add('is-out');
            next.classList.remove('is-out');
            next.classList.add('is-active');

            setTimeout(() => current.classList.remove('is-out'), 900);
        }, 2600);
    });
}

/**
 * Animated client-side category filtering for the portfolio grid. Links keep
 * working without JavaScript through the ?category= query string.
 */
export function initFilters() {
    const filters = document.querySelector('[data-filters]');
    const grid = document.querySelector('[data-filter-grid]');

    if (!filters || !grid) {
        return;
    }

    const items = [...document.querySelectorAll('[data-filter-item]')];
    const empty = document.querySelector('[data-filter-empty]');
    const links = [...filters.querySelectorAll('[data-filter]')];

    const render = (category, reveal = true) => {
        grid.replaceChildren(...items.filter((item) => !category || item.dataset.filterItem === category));

        if (reveal) {
            items.forEach((item) => {
                item.classList.add('is-in');
                item.querySelectorAll('[data-reveal]').forEach((element) => element.classList.add('is-in'));
            });
        }

        empty?.toggleAttribute('hidden', grid.children.length > 0);
    };

    // The server hides non-matching items; take over by removing them instead,
    // so the :nth-child() layout pattern only counts visible projects.
    items.forEach((item) => {
        item.hidden = false;
    });
    render(filters.querySelector('[data-filter].is-active')?.dataset.filter || null, false);

    links.forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();

            const category = link.dataset.filter || null;

            links.forEach((other) => {
                const active = other === link;
                other.classList.toggle('is-active', active);
                other.setAttribute('aria-current', active ? 'true' : 'false');
            });

            const url = new URL(window.location.href);
            category ? url.searchParams.set('category', category) : url.searchParams.delete('category');
            history.replaceState(null, '', url);

            if (document.startViewTransition && !prefersReducedMotion()) {
                root.classList.add('filter-vt');
                document.startViewTransition(() => render(category)).finished.finally(() => root.classList.remove('filter-vt'));

                return;
            }

            grid.classList.add('is-filtering');
            setTimeout(() => {
                render(category);
                grid.classList.remove('is-filtering');
            }, prefersReducedMotion() ? 0 : 300);
        });
    });
}
