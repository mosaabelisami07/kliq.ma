import { clamp, isRtl, onScroll, prefersReducedMotion, wrapWords } from './utils';

/**
 * Gives the sticky header a frosted background once the page has scrolled.
 */
export function initHeader() {
    const header = document.querySelector('[data-header]');

    if (!header) {
        return;
    }

    onScroll(() => {
        header.classList.toggle('is-scrolled', scrollY > 24);
    });
}

/**
 * Moves [data-parallax="0.15"] layers against the scroll direction.
 */
export function initParallax() {
    const layers = [...document.querySelectorAll('[data-parallax]')];

    if (!layers.length || prefersReducedMotion()) {
        return;
    }

    onScroll(() => {
        layers.forEach((layer) => {
            const rect = layer.parentElement.getBoundingClientRect();

            if (rect.bottom < 0 || rect.top > innerHeight) {
                return;
            }

            const offset = (rect.top + rect.height / 2 - innerHeight / 2) / innerHeight;

            layer.style.translate = `0 ${(-offset * Number(layer.dataset.parallax) * 100).toFixed(2)}%`;
        });
    });
}

/**
 * Lights up the words of [data-wordlight] paragraphs as they scroll through.
 */
export function initWordlight() {
    document.querySelectorAll('[data-wordlight]').forEach((element) => {
        const words = [];

        wrapWords(element, (word) => {
            const span = document.createElement('span');
            span.className = 'wl';
            span.textContent = word;
            words.push(span);

            return span;
        });

        if (prefersReducedMotion()) {
            words.forEach((word) => word.classList.add('is-lit'));

            return;
        }

        let lit = -1;

        onScroll(() => {
            const rect = element.getBoundingClientRect();
            const progress = clamp((innerHeight * 0.85 - rect.top) / (rect.height + innerHeight * 0.3), 0, 1);
            const count = Math.round(progress * words.length);

            if (count === lit) {
                return;
            }

            lit = count;
            words.forEach((word, index) => word.classList.toggle('is-lit', index < count));
        });
    });
}

/**
 * Pins [data-hscroll] sections and turns vertical scrolling into horizontal
 * movement of their track. Falls back to native swiping on small screens.
 */
export function initHorizontalScroll() {
    document.querySelectorAll('[data-hscroll]').forEach((section) => {
        const sticky = section.querySelector('.hscroll__sticky');
        const track = section.querySelector('[data-hscroll-track]');
        const bar = section.querySelector('.hscroll__bar > span');
        const desktop = window.matchMedia('(min-width: 768px)');

        let distance = 0;
        let pinned = false;

        const measure = () => {
            pinned = desktop.matches && !prefersReducedMotion();
            section.classList.toggle('is-pinned', pinned);

            if (!pinned) {
                section.style.height = '';
                track.style.translate = '';

                return;
            }

            distance = Math.max(0, track.scrollWidth - sticky.clientWidth);
            section.style.height = `${distance + innerHeight}px`;
        };

        const update = () => {
            if (!pinned) {
                return;
            }

            const rect = section.getBoundingClientRect();
            const progress = clamp(-rect.top / Math.max(1, rect.height - innerHeight), 0, 1);

            track.style.translate = `${(progress * distance * (isRtl() ? 1 : -1)).toFixed(1)}px 0`;

            if (bar) {
                bar.style.scale = `${progress} 1`;
            }
        };

        measure();
        document.fonts?.ready.then(measure);
        window.addEventListener('resize', measure);
        onScroll(update);
    });
}

/**
 * Opens the showreel frame from an inset card to full bleed as it scrolls in.
 */
export function initShowreel() {
    const frames = document.querySelectorAll('[data-showreel]');

    if (!frames.length || prefersReducedMotion()) {
        return;
    }

    onScroll(() => {
        frames.forEach((frame) => {
            const rect = frame.getBoundingClientRect();
            const progress = clamp((innerHeight - rect.top) / (innerHeight * 0.9), 0, 1);

            frame.style.setProperty('--p', progress.toFixed(3));
        });
    });
}

/**
 * Lets the hero artwork drift gently with the pointer.
 */
export function initHeroPointer() {
    const hero = document.querySelector('[data-hero]');

    if (!hero || prefersReducedMotion()) {
        return;
    }

    hero.addEventListener('pointermove', (event) => {
        const rect = hero.getBoundingClientRect();

        hero.style.setProperty('--mx', (((event.clientX - rect.left) / rect.width) * 2 - 1).toFixed(3));
        hero.style.setProperty('--my', (((event.clientY - rect.top) / rect.height) * 2 - 1).toFixed(3));
    });
}
