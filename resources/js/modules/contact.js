/**
 * Sends the contact form with fetch(): loading state, inline validation
 * errors and a success panel. Without JavaScript the form posts normally.
 */
export function initContactForm() {
    const container = document.querySelector('[data-contact]');
    const form = container?.querySelector('[data-contact-form]');

    if (!form) {
        return;
    }

    const success = container.querySelector('[data-contact-success]');
    const alert = form.querySelector('[data-contact-alert]');
    const button = form.querySelector('button[type=submit]');
    const label = button.querySelector('[data-label]');
    const idleLabel = label.textContent;

    const setBusy = (busy) => {
        button.setAttribute('aria-busy', String(busy));
        button.disabled = busy;
        label.textContent = busy ? button.dataset.sending : idleLabel;
    };

    const clearErrors = () => {
        alert.hidden = true;
        form.querySelectorAll('[data-error-for]').forEach((element) => {
            element.hidden = true;
            element.textContent = '';
        });
        form.querySelectorAll('[aria-invalid]').forEach((element) => element.removeAttribute('aria-invalid'));
    };

    const showErrors = (errors, fallback) => {
        let first = null;

        Object.entries(errors).forEach(([field, messages]) => {
            const slot = form.querySelector(`[data-error-for="${field}"]`);
            const input = form.querySelector(`[name="${field}"]`);

            if (slot) {
                slot.textContent = messages[0];
                slot.hidden = false;
            }

            if (input && input.type !== 'hidden') {
                input.setAttribute('aria-invalid', 'true');
                first ??= input;
            }
        });

        alert.textContent = errors.form?.[0] ?? fallback;
        alert.hidden = false;
        (first ?? alert).focus?.({ preventScroll: false });
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (button.disabled) {
            return;
        }

        clearErrors();
        setBusy(true);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            const data = await response.json().catch(() => ({}));

            if (response.ok) {
                container.querySelector('[data-contact-success-title]').textContent = data.title;
                container.querySelector('[data-contact-success-text]').textContent = data.message;
                form.hidden = true;
                success.hidden = false;
                success.focus?.();
                container.scrollIntoView({ behavior: 'smooth', block: 'center' });

                return;
            }

            if (response.status === 422) {
                showErrors(data.errors ?? {}, alert.dataset.summary);
            } else {
                showErrors({}, response.status === 429 ? form.dataset.throttled : form.dataset.failed);
            }
        } catch {
            showErrors({}, form.dataset.failed);
        } finally {
            setBusy(false);
        }
    });

    // "Send another message" resets the form in place instead of reloading.
    container.querySelector('[data-contact-reset]')?.addEventListener('click', (event) => {
        event.preventDefault();
        form.reset();
        form.querySelector('[data-contact-token]').value = crypto.randomUUID?.() ?? form.querySelector('[data-contact-token]').value;
        success.hidden = true;
        form.hidden = false;
        form.querySelector('input:not([type=hidden])')?.focus();
    });
}

/**
 * Highlights the navigation link of the home page section in view.
 */
export function initScrollSpy() {
    const links = [...document.querySelectorAll('[data-spy]')];
    const sections = links.map((link) => document.getElementById(link.dataset.spy)).filter((section) => section && section !== document.body);

    if (!sections.length) {
        return;
    }

    const visible = new Map();

    const update = () => {
        const current = [...visible.entries()].sort((a, b) => b[1] - a[1])[0]?.[0] ?? 'top';

        links.forEach((link) => {
            const active = link.dataset.spy === current;
            link.classList.toggle('is-current', active);
            active ? link.setAttribute('aria-current', 'location') : link.removeAttribute('aria-current');
        });
    };

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                entry.isIntersecting ? visible.set(entry.target.id, entry.intersectionRatio) : visible.delete(entry.target.id);
            });
            update();
        },
        { rootMargin: '-35% 0px -55% 0px', threshold: [0, 0.25, 0.5, 1] },
    );

    sections.forEach((section) => observer.observe(section));
}

/**
 * mailto: links do nothing on computers without a mail app set up, so on
 * mouse-driven devices they open a Gmail compose window in a new tab instead.
 * Phones keep the native mailto: behaviour, which opens their mail app.
 */
export function initMailLinks() {
    document.addEventListener('click', (event) => {
        const link = event.target.closest?.('a[href^="mailto:"]');

        if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        if (!window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
            return;
        }

        const mailto = new URL(link.href);
        const compose = new URL('https://mail.google.com/mail/');

        compose.search = new URLSearchParams({
            view: 'cm',
            fs: '1',
            to: decodeURIComponent(mailto.pathname),
            su: mailto.searchParams.get('subject') ?? '',
            body: mailto.searchParams.get('body') ?? '',
        }).toString();

        event.preventDefault();
        window.open(compose.toString(), '_blank', 'noopener');
    });
}
