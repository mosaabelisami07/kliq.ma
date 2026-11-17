/**
 * KLIQ contact form: delivery through WhatsApp.
 *
 * When a visitor submits a valid contact form, WhatsApp opens (app on phones,
 * WhatsApp Web / Desktop on computers) with a chat to the agency's number and
 * the form details already written. The visitor only has to press "Send".
 *
 * The number comes from KLIQ_WHATSAPP_NUMBER in .env, passed on this script's
 * data-number attribute by resources/views/components/layouts/app.blade.php.
 * Called by resources/js/modules/contact.js (window.KliqWhatsApp.open).
 */
(function () {
    'use strict';

    const script = document.currentScript;
    const number = String(script?.dataset.number ?? '').replace(/\D/g, '');

    /**
     * Build the WhatsApp message from the existing form fields.
     */
    function message(form) {
        const data = new FormData(form);
        const value = (name) => String(data.get(name) ?? '').trim();
        const select = form.querySelector('[name="service"]');
        const service = select && select.value ? select.selectedOptions[0]?.textContent.trim() : '';

        const lines = [
            '*New inquiry from the KLIQ website*',
            '',
            `*Full name:* ${value('name')}`,
            `*Business / company:* ${value('company') || '—'}`,
            `*Email:* ${value('email')}`,
            `*Phone:* ${value('phone') || '—'}`,
            `*Service of interest:* ${service || '—'}`,
            '',
            '*Project details:*',
            value('message'),
        ];

        return lines.join('\n');
    }

    /**
     * The wa.me link that opens a chat to the agency with the message filled in.
     */
    function url(form) {
        return `https://wa.me/${number}?text=${encodeURIComponent(message(form))}`;
    }

    /**
     * Open WhatsApp. Must run directly inside the submit event (before any
     * await), otherwise browsers block the new tab as a pop-up.
     * Returns false when no number is configured.
     */
    function open(form) {
        if (!number) {
            console.error('[WhatsApp] No number configured: set KLIQ_WHATSAPP_NUMBER in .env, then run php artisan config:clear.');

            return false;
        }

        const link = url(form);
        const tab = window.open(link, '_blank');

        if (tab) {
            tab.opener = null;
        } else {
            // Pop-up blocked: open WhatsApp in the current tab instead.
            window.location.href = link;
        }

        return true;
    }

    window.KliqWhatsApp = { open, url, message };

    if (!number) {
        console.warn('[WhatsApp] KLIQ_WHATSAPP_NUMBER is not set in .env; the contact form cannot open WhatsApp.');
    }
})();
