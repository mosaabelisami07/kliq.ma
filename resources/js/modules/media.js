import { prefersReducedMotion, root } from './utils';

/**
 * Autoplaying, muted reels with a custom play / pause control. A reel only
 * plays while visible, and starts paused when reduced motion is requested.
 */
export function initReels() {
    document.querySelectorAll('[data-reel]').forEach((reel) => {
        const toggle = reel.querySelector('[data-reel-toggle]');
        const video = reel.querySelector('video');

        let pausedByVisitor = prefersReducedMotion();
        let visible = false;

        const sync = () => {
            const playing = visible && !pausedByVisitor;

            reel.classList.toggle('is-paused', !playing);

            if (video) {
                if (playing) {
                    video.play().catch(() => {});
                } else {
                    video.pause();
                }
            }

            if (toggle) {
                toggle.setAttribute('aria-pressed', String(!pausedByVisitor));
                toggle.setAttribute('aria-label', pausedByVisitor ? toggle.dataset.labelPlay : toggle.dataset.labelPause);
            }
        };

        new IntersectionObserver(
            ([entry]) => {
                visible = entry.isIntersecting;
                sync();
            },
            { threshold: 0.1 },
        ).observe(reel);

        toggle?.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            pausedByVisitor = !pausedByVisitor;
            sync();
        });

        sync();
    });
}

/**
 * Cinematic image-to-image slider with wipe transitions, autoplay, buttons,
 * arrow keys and swipe support.
 */
export function initSliders() {
    document.querySelectorAll('[data-slider]').forEach((slider) => {
        const slides = [...slider.querySelectorAll('[data-slide]')];
        const counter = slider.querySelector('[data-slide-index]');
        const duration = Number(slider.dataset.interval || 5000);
        const autoplay = slider.hasAttribute('data-autoplay') && !prefersReducedMotion();

        if (slides.length < 2) {
            return;
        }

        let index = 0;
        let timer = null;
        let visible = false;

        slider.style.setProperty('--slide-duration', `${duration}ms`);

        const go = (next, direction = 'next') => {
            const previous = slides[index];

            index = (next + slides.length) % slides.length;
            slider.dataset.direction = direction;
            slider.classList.add('is-ready');

            slides.forEach((slide) => slide.classList.remove('is-leaving'));
            previous.classList.remove('is-active');
            previous.classList.add('is-leaving');
            previous.setAttribute('aria-hidden', 'true');

            const current = slides[index];
            current.classList.remove('is-active');
            void current.offsetWidth;
            current.classList.add('is-active');
            current.removeAttribute('aria-hidden');

            if (counter) {
                counter.textContent = String(index + 1).padStart(2, '0');
            }

            restart();
        };

        const restart = () => {
            clearTimeout(timer);
            slider.classList.remove('is-playing');

            if (!autoplay || !visible) {
                return;
            }

            void slider.offsetWidth;
            slider.classList.add('is-playing');
            timer = setTimeout(() => go(index + 1), duration);
        };

        slider.querySelector('[data-slide-next]')?.addEventListener('click', () => go(index + 1));
        slider.querySelector('[data-slide-prev]')?.addEventListener('click', () => go(index - 1, 'prev'));

        slider.addEventListener('keydown', (event) => {
            const forward = root.dir === 'rtl' ? 'ArrowLeft' : 'ArrowRight';
            const backward = root.dir === 'rtl' ? 'ArrowRight' : 'ArrowLeft';

            if (event.key === forward) {
                go(index + 1);
            } else if (event.key === backward) {
                go(index - 1, 'prev');
            }
        });

        let startX = null;

        slider.addEventListener('pointerdown', (event) => {
            startX = event.clientX;
        });

        slider.addEventListener('pointerup', (event) => {
            if (startX === null) {
                return;
            }

            const delta = (event.clientX - startX) * (root.dir === 'rtl' ? -1 : 1);
            startX = null;

            if (Math.abs(delta) > 50) {
                delta < 0 ? go(index + 1) : go(index - 1, 'prev');
            }
        });

        new IntersectionObserver(([entry]) => {
            visible = entry.isIntersecting;
            restart();
        }).observe(slider);
    });
}

/**
 * Gives the clicked project's artwork a view-transition-name so it morphs
 * into the hero of the project page during navigation.
 */
export function initProjectMorph() {
    document.addEventListener('click', (event) => {
        const link = event.target instanceof Element ? event.target.closest('a[data-morph]') : null;
        const media = link?.querySelector('[data-vt]') ?? link?.closest('.project-card')?.querySelector('[data-vt]');

        if (media) {
            media.style.viewTransitionName = media.dataset.vt;
        }
    });

    // Clear the name again when the page is restored from the back/forward cache.
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('[data-vt]').forEach((media) => {
            media.style.viewTransitionName = '';
        });
    });
}
