import { prefersReducedMotion, wrapWords } from './utils';

function splitText(element) {
    wrapWords(element, (word, index) => {
        const outer = document.createElement('span');
        const inner = document.createElement('span');

        outer.className = 'split-w';
        inner.className = 'split-i';
        inner.style.setProperty('--i', index);
        inner.textContent = word;
        outer.append(inner);

        return outer;
    });

    element.classList.add('is-split');
}

function animateCount(element) {
    const target = Number(element.dataset.count);
    const suffix = element.dataset.suffix ?? '';

    if (prefersReducedMotion()) {
        element.textContent = `${target}${suffix}`;

        return;
    }

    const start = performance.now();
    const duration = 1800;

    const tick = (now) => {
        const progress = Math.min(1, (now - start) / duration);
        const eased = 1 - (1 - progress) ** 4;

        element.textContent = `${Math.round(target * eased)}${suffix}`;

        if (progress < 1) {
            requestAnimationFrame(tick);
        }
    };

    requestAnimationFrame(tick);
}

/**
 * Splits [data-split] headings into words before the first paint so they can
 * be revealed without a flash of unstyled text.
 */
export function prepareReveals() {
    document.querySelectorAll('[data-split]').forEach(splitText);
}

/**
 * Adds .is-in to reveal targets as they enter the viewport, starts counters,
 * and pauses generative artwork that is off screen.
 */
export function initReveals() {
    const targets = document.querySelectorAll('[data-reveal], [data-split], [data-count], [data-reveal-group]');

    if (prefersReducedMotion() || !('IntersectionObserver' in window)) {
        targets.forEach((element) => {
            element.classList.add('is-in');

            if (element.dataset.count) {
                animateCount(element);
            }
        });
    } else {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    entry.target.classList.add('is-in');

                    if (entry.target.dataset.count) {
                        animateCount(entry.target);
                    }

                    observer.unobserve(entry.target);
                });
            },
            { rootMargin: '0px 0px -8% 0px', threshold: 0.12 },
        );

        targets.forEach((element) => observer.observe(element));
    }

    const artObserver = new IntersectionObserver(
        (entries) => entries.forEach((entry) => entry.target.classList.toggle('is-idle', !entry.isIntersecting)),
        { rootMargin: '10% 0px' },
    );

    document.querySelectorAll('.art').forEach((art) => artObserver.observe(art));
}
