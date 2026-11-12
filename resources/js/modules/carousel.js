/**
 * Scroll-snap carousels: arrow buttons, page dots and mouse dragging on top
 * of a native horizontal scroller, so touch swiping and keyboard scrolling
 * keep working. Handles right-to-left pages, where scrollLeft runs negative.
 */
export function initCarousels() {
    document.querySelectorAll('[data-carousel]').forEach(setUpCarousel);
}

function setUpCarousel(carousel) {
    const track = carousel.querySelector('[data-carousel-track]');
    const slides = [...track.children];
    const prev = carousel.querySelector('[data-carousel-prev]');
    const next = carousel.querySelector('[data-carousel-next]');
    const dotsContainer = carousel.querySelector('[data-carousel-dots]');
    const direction = () => (getComputedStyle(track).direction === 'rtl' ? -1 : 1);

    if (slides.length === 0) {
        return;
    }

    const step = () => {
        const gap = parseFloat(getComputedStyle(track).columnGap) || 0;

        return slides[0].getBoundingClientRect().width + gap;
    };

    const visibleCount = () => Math.max(1, Math.round((track.clientWidth + 1) / step()));
    const pageCount = () => Math.max(1, slides.length - visibleCount() + 1);
    const currentIndex = () => Math.round(Math.abs(track.scrollLeft) / step());

    const goTo = (index) => {
        const target = Math.min(Math.max(index, 0), pageCount() - 1);

        track.scrollTo({ left: target * step() * direction() });
    };

    let dots = [];

    const renderDots = () => {
        if (!dotsContainer) {
            return;
        }

        dotsContainer.replaceChildren();
        dots = Array.from({ length: pageCount() }, (_, index) => {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'carousel-dot';
            dot.setAttribute('aria-label', `${index + 1} / ${pageCount()}`);
            dot.addEventListener('click', () => goTo(index));
            dotsContainer.append(dot);

            return dot;
        });
        dotsContainer.hidden = dots.length < 2;
    };

    const update = () => {
        const index = Math.min(currentIndex(), pageCount() - 1);
        const atEnd = Math.abs(track.scrollLeft) + track.clientWidth >= track.scrollWidth - 2;

        if (prev) {
            prev.disabled = index <= 0 && Math.abs(track.scrollLeft) < 2;
        }

        if (next) {
            next.disabled = atEnd;
        }

        dots.forEach((dot, dotIndex) => dot.setAttribute('aria-current', String(dotIndex === (atEnd ? dots.length - 1 : index))));
    };

    prev?.addEventListener('click', () => goTo(currentIndex() - 1));
    next?.addEventListener('click', () => goTo(currentIndex() + 1));

    let frame = 0;
    track.addEventListener('scroll', () => {
        cancelAnimationFrame(frame);
        frame = requestAnimationFrame(update);
    }, { passive: true });

    new ResizeObserver(() => {
        renderDots();
        update();
    }).observe(track);

    // Mouse drag; touch devices already swipe the native scroller.
    let dragStart = null;

    track.addEventListener('pointerdown', (event) => {
        if (event.pointerType !== 'mouse' || event.button !== 0) {
            return;
        }

        dragStart = { x: event.clientX, scroll: track.scrollLeft, moved: false };
    });

    window.addEventListener('pointermove', (event) => {
        if (!dragStart) {
            return;
        }

        const distance = event.clientX - dragStart.x;

        if (!dragStart.moved && Math.abs(distance) > 5) {
            dragStart.moved = true;
            track.classList.add('is-dragging');
        }

        if (dragStart.moved) {
            track.scrollLeft = dragStart.scroll - distance;
        }
    });

    window.addEventListener('pointerup', () => {
        if (!dragStart) {
            return;
        }

        const moved = dragStart.moved;
        const startIndex = Math.round(Math.abs(dragStart.scroll) / step());
        dragStart = null;

        if (!moved) {
            return;
        }

        track.classList.remove('is-dragging');

        // Snap to the nearest card in the direction of the drag.
        const travelled = (Math.abs(track.scrollLeft) / step()) - startIndex;
        goTo(startIndex + (travelled > 0.15 ? Math.ceil(travelled) : travelled < -0.15 ? Math.floor(travelled) : 0));

        // Don't treat the end of a drag as a click on the card under the cursor.
        track.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
        }, { capture: true, once: true });
    });

    track.addEventListener('dragstart', (event) => event.preventDefault());
}
