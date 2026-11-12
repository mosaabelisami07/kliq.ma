import { hasFinePointer, lerp, prefersReducedMotion, root } from './utils';

const INTERACTIVE = 'a, button, label, summary, [role="button"], [data-cursor]';

/**
 * An orange dot with a trailing ring that grows over links and shows a
 * label over elements carrying data-cursor="…". Desktop pointers only.
 */
export function initCursor() {
    const cursor = document.querySelector('[data-cursor-root]');

    if (!cursor || !hasFinePointer() || prefersReducedMotion()) {
        return;
    }

    const dot = cursor.querySelector('.cursor__dot');
    const ring = cursor.querySelector('.cursor__ring');
    const label = cursor.querySelector('.cursor__label');

    let x = innerWidth / 2;
    let y = innerHeight / 2;
    let ringX = x;
    let ringY = y;

    root.classList.add('has-cursor');

    window.addEventListener(
        'pointermove',
        (event) => {
            x = event.clientX;
            y = event.clientY;

            if (!cursor.classList.contains('is-visible')) {
                ringX = x;
                ringY = y;
                cursor.classList.add('is-visible');
            }
        },
        { passive: true },
    );

    document.addEventListener('pointerleave', () => cursor.classList.remove('is-visible'));
    window.addEventListener('pointerdown', () => cursor.classList.add('is-down'));
    window.addEventListener('pointerup', () => cursor.classList.remove('is-down'));

    document.addEventListener('pointerover', (event) => {
        const target = event.target instanceof Element ? event.target : null;
        const labelled = target?.closest('[data-cursor]');
        const interactive = target?.closest(INTERACTIVE);
        const typing = target?.closest('input:not([type=checkbox]):not([type=radio]), textarea');

        cursor.classList.toggle('is-text', Boolean(typing));
        cursor.classList.toggle('has-label', Boolean(labelled));
        cursor.classList.toggle('is-hover', Boolean(interactive) && !labelled);

        if (labelled) {
            label.textContent = labelled.dataset.cursor;
        }
    });

    const render = () => {
        ringX = lerp(ringX, x, 0.16);
        ringY = lerp(ringY, y, 0.16);

        dot.style.transform = `translate3d(${x}px, ${y}px, 0)`;
        ring.style.transform = `translate3d(${ringX}px, ${ringY}px, 0)`;

        requestAnimationFrame(render);
    };

    requestAnimationFrame(render);
}

/**
 * Elements with data-magnetic lean towards the pointer while hovered.
 */
export function initMagnetic() {
    if (!hasFinePointer() || prefersReducedMotion()) {
        return;
    }

    document.querySelectorAll('[data-magnetic]').forEach((element) => {
        const strength = parseFloat(element.dataset.magnetic) || 0.3;

        element.addEventListener('pointermove', (event) => {
            const rect = element.getBoundingClientRect();
            const dx = event.clientX - (rect.left + rect.width / 2);
            const dy = event.clientY - (rect.top + rect.height / 2);

            element.style.translate = `${dx * strength}px ${dy * strength}px`;
        });

        element.addEventListener('pointerleave', () => {
            element.style.translate = '0px 0px';
        });
    });
}
