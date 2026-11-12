const root = document.documentElement;

/**
 * Off-canvas sidebar on small screens.
 */
function initSidebar() {
    document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const open = root.classList.toggle('sidebar-open');
            document.querySelectorAll('[data-sidebar-toggle]').forEach((toggle) => toggle.setAttribute('aria-expanded', String(open)));
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            root.classList.remove('sidebar-open');
        }
    });
}

/**
 * Forms with data-confirm="…" ask for confirmation in a modal dialog first.
 */
function initConfirmations() {
    const dialog = document.querySelector('[data-confirm-dialog]');

    if (!dialog) {
        return;
    }

    const message = dialog.querySelector('[data-confirm-message]');
    let pendingForm = null;

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || !form.dataset.confirm || form.dataset.confirmed) {
            return;
        }

        event.preventDefault();
        pendingForm = form;
        message.textContent = form.dataset.confirm;
        dialog.showModal();
    });

    dialog.addEventListener('close', () => {
        if (dialog.returnValue === 'confirm' && pendingForm) {
            pendingForm.dataset.confirmed = '1';
            pendingForm.requestSubmit();
        }

        pendingForm = null;
    });
}

/**
 * Language tabs for translatable fields (EN / FR / AR).
 */
function initLanguageTabs() {
    document.querySelectorAll('[data-translatable]').forEach((group) => {
        const tabs = [...group.querySelectorAll('[data-lang-tab]')];
        const panels = [...group.querySelectorAll('[data-lang-panel]')];

        const markFilled = () => {
            tabs.forEach((tab) => {
                const panel = group.querySelector(`[data-lang-panel="${tab.dataset.langTab}"]`);
                tab.classList.toggle('has-value', panel.querySelector('input, textarea').value.trim() !== '');
            });
        };

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                tabs.forEach((other) => other.setAttribute('aria-selected', String(other === tab)));
                panels.forEach((panel) => {
                    panel.hidden = panel.dataset.langPanel !== tab.dataset.langTab;
                });
                group.querySelector(`[data-lang-panel="${tab.dataset.langTab}"] input, [data-lang-panel="${tab.dataset.langTab}"] textarea`)?.focus();
            });
        });

        group.addEventListener('input', markFilled);
        markFilled();
    });
}

/**
 * Live preview for image inputs.
 */
function initImagePreviews() {
    document.querySelectorAll('input[type=file][data-preview]').forEach((input) => {
        const preview = document.getElementById(input.dataset.preview);

        input.addEventListener('change', () => {
            const file = input.files?.[0];

            if (!file || !preview) {
                return;
            }

            preview.src = URL.createObjectURL(file);
            preview.hidden = false;
        });
    });
}

/**
 * Flash messages fade out after a few seconds.
 */
function initToasts() {
    document.querySelectorAll('[data-toast]').forEach((toast) => {
        const hide = () => toast.remove();
        toast.querySelector('[data-toast-close]')?.addEventListener('click', hide);
        setTimeout(hide, 6000);
    });
}

/**
 * Prevent double submissions of slow forms (uploads).
 */
function initSubmitGuard() {
    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (event.defaultPrevented || !(form instanceof HTMLFormElement)) {
            return;
        }

        form.querySelectorAll('button[type=submit][data-busy-label]').forEach((button) => {
            button.disabled = true;
            button.textContent = button.dataset.busyLabel;
        });
    });
}

initSidebar();
initConfirmations();
initLanguageTabs();
initImagePreviews();
initToasts();
initSubmitGuard();
