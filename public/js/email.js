/**
 * KLIQ contact form: email delivery through EmailJS (https://www.emailjs.com).
 *
 * How it fits with the existing form (resources/js/modules/contact.js):
 *   1. The form is validated and saved by Laravel first (spam checks, dashboard copy).
 *   2. contact.js then calls window.KliqEmail.send(form), defined below.
 *   3. The success panel only appears once EmailJS confirms the email was sent;
 *      otherwise the form's existing error alert is shown.
 *
 * Before anything is sent, validate() checks the fields and contact.js shows
 * any errors next to them, using the form's existing error styles.
 *
 * Loaded by resources/views/components/layouts/app.blade.php (EmailJS is the
 * default; KLIQ_EMAIL_DELIVERY=laravel in .env would switch it off).
 */
(function () {
    'use strict';

    /**
     * EmailJS credentials: replace the placeholders with the values from your
     * EmailJS dashboard. The Public Key is meant to be public (it is visible in
     * every browser either way), so it is safe in this file. Never put your
     * EmailJS Private Key here.
     */
    const EMAILJS_PUBLIC_KEY = 'YOUR_PUBLIC_KEY'; // Account → General → Public Key
    const EMAILJS_SERVICE_ID = 'YOUR_SERVICE_ID'; // Email Services → your Gmail service → Service ID
    const EMAILJS_TEMPLATE_ID = 'YOUR_TEMPLATE_ID'; // Email Templates → your template → Template ID

    // Official EmailJS Browser SDK (https://www.emailjs.com/docs/sdk/installation/), pinned version.
    const SDK_URL = 'https://cdn.jsdelivr.net/npm/@emailjs/browser@4.4.1/dist/email.min.js';
    const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const PHONE_PATTERN = /^\+?[\d\s().-]{6,25}$/;

    let sdkPromise = null;

    const isConfigured = () => [EMAILJS_PUBLIC_KEY, EMAILJS_SERVICE_ID, EMAILJS_TEMPLATE_ID].every((value) => value && !value.startsWith('YOUR_'));

    /**
     * Load the EmailJS SDK once and initialise it with the Public Key.
     */
    function loadSdk() {
        sdkPromise ??= new Promise((resolve, reject) => {
            if (window.emailjs) {
                resolve(window.emailjs);

                return;
            }

            const script = document.createElement('script');
            script.src = SDK_URL;
            script.async = true;
            script.onload = () => (window.emailjs ? resolve(window.emailjs) : reject(new Error('EmailJS SDK loaded but window.emailjs is missing.')));
            script.onerror = () => reject(new Error(`Could not load the EmailJS SDK from ${SDK_URL}.`));
            document.head.appendChild(script);
        }).then((emailjs) => {
            emailjs.init({ publicKey: EMAILJS_PUBLIC_KEY });

            return emailjs;
        });

        sdkPromise.catch(() => {
            sdkPromise = null; // allow a retry on the next submission
        });

        return sdkPromise;
    }

    /**
     * Read the existing form fields into the variables used by the EmailJS template.
     */
    function templateParams(form) {
        const data = new FormData(form);
        const value = (name) => String(data.get(name) ?? '').trim();
        const serviceSelect = form.querySelector('[name="service"]');
        const service = serviceSelect && serviceSelect.value ? serviceSelect.selectedOptions[0]?.textContent.trim() : '';
        const name = value('name');
        const company = value('company');

        return {
            name,
            email: value('email'),
            subject: `New inquiry from ${name}${company ? ` (${company})` : ''}`,
            message: value('message'),
            company: company || '—',
            phone: value('phone') || '—',
            service: service || '—',
            reply_to: value('email'),
            page_url: window.location.href,
        };
    }

    /**
     * Client-side validation, run before anything is sent. Returns errors keyed
     * by field name ({ email: ['…'] }), in the page's language: the messages
     * come from the form's data-validation attribute (lang/xx/site.php).
     */
    function validate(form) {
        let messages = {};

        try {
            messages = JSON.parse(form.dataset.validation || '{}');
        } catch {
            console.error('[EmailJS] The contact form data-validation attribute is not valid JSON.');
        }

        const data = new FormData(form);
        const value = (name) => String(data.get(name) ?? '').trim();
        const errors = {};
        const fail = (field, key, fallback) => {
            errors[field] ??= [messages[key] ?? fallback];
        };

        if (!value('name')) {
            fail('name', 'name_required', 'Please enter your name.');
        }

        if (!value('email')) {
            fail('email', 'email_required', 'Please enter your email address.');
        } else if (!EMAIL_PATTERN.test(value('email'))) {
            fail('email', 'email_invalid', 'Please enter a valid email address.');
        }

        if (value('phone') && !PHONE_PATTERN.test(value('phone'))) {
            fail('phone', 'phone_invalid', 'Please enter a valid phone number.');
        }

        if (!value('message')) {
            fail('message', 'message_required', 'Please enter a message.');
        } else if (value('message').length < 20) {
            fail('message', 'message_min', 'Please write at least 20 characters.');
        }

        return errors;
    }

    /**
     * Send the contact form through EmailJS. Resolves when EmailJS accepted the
     * email, rejects (and logs to the console) otherwise.
     */
    async function send(form) {
        if (!isConfigured()) {
            throw new Error('EmailJS is not configured: set EMAILJS_PUBLIC_KEY, EMAILJS_SERVICE_ID and EMAILJS_TEMPLATE_ID in public/js/email.js.');
        }

        const invalid = Object.keys(validate(form));

        if (invalid.length) {
            throw new Error(`Invalid fields: ${invalid.join(', ')}.`);
        }

        const emailjs = await loadSdk();

        try {
            return await emailjs.send(EMAILJS_SERVICE_ID, EMAILJS_TEMPLATE_ID, templateParams(form));
        } catch (error) {
            // EmailJS rejects with { status, text }, e.g. 400 "The template ID is invalid".
            throw new Error(`EmailJS send failed (${error?.status ?? 'network'}): ${error?.text ?? error?.message ?? error}`);
        }
    }

    window.KliqEmail = { send, validate, isConfigured };

    if (!isConfigured()) {
        console.warn('[EmailJS] Credentials are still placeholders in public/js/email.js; contact form emails will fail until they are set.');
    }

    // Warm up the SDK in the background so the first submission is fast.
    if (isConfigured() && document.querySelector('[data-contact-form]')) {
        loadSdk().catch((error) => console.error('[EmailJS]', error));
    }
})();
