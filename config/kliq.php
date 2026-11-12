<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Locales
    |--------------------------------------------------------------------------
    |
    | Every public page is served under one of these URL prefixes. The order
    | is the order shown in the language switcher. Content edited in the admin
    | dashboard can be translated into each of them; missing translations
    | fall back to app.fallback_locale.
    |
    */

    'locales' => [
        'ar' => ['name' => 'العربية', 'short' => 'ع', 'dir' => 'rtl'],
        'fr' => ['name' => 'Français', 'short' => 'FR', 'dir' => 'ltr'],
        'en' => ['name' => 'English', 'short' => 'EN', 'dir' => 'ltr'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin dashboard
    |--------------------------------------------------------------------------
    |
    | "access_code" is an optional second secret required at login, on top of
    | the administrator's email and password. Set ADMIN_ACCESS_CODE in .env to
    | either a plain value or a bcrypt hash (php artisan kliq:hash-code).
    | Leave it empty to disable the extra field.
    |
    */

    'admin' => [
        'access_code' => env('ADMIN_ACCESS_CODE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Media
    |--------------------------------------------------------------------------
    |
    | Disk that stores uploaded logos, project and service images. "public"
    | serves files from storage/app/public through the public/storage link
    | (php artisan storage:link). Any disk with public URLs (e.g. s3) works.
    |
    */

    'media_disk' => env('MEDIA_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Inquiry notifications
    |--------------------------------------------------------------------------
    |
    | Fallback recipient for "new inquiry" emails when no notification email
    | has been saved in the dashboard settings.
    |
    */

    'notification_email' => env('KLIQ_NOTIFICATION_EMAIL'),

    /*
    |--------------------------------------------------------------------------
    | Contact form options
    |--------------------------------------------------------------------------
    */

    'budgets' => ['under-20k', '20-50k', '50-150k', '150k-plus', 'undecided'],

    'contact_methods' => ['email', 'phone', 'whatsapp'],

    /*
    |--------------------------------------------------------------------------
    | Editable website settings
    |--------------------------------------------------------------------------
    |
    | Keys the dashboard can edit. "text" keys are translatable; their default
    | copy lives in lang/{locale}/site.php under "defaults". "lists" are
    | translatable repeating items (icon, title, text) with a fixed number of
    | rows. Contact details and social links have no defaults: they are only
    | shown once an administrator has entered them.
    |
    */

    'settings' => [
        'text' => [
            'hero_eyebrow', 'hero_title', 'hero_text',
            'brands_title',
            'about_title', 'about_text', 'about_mission', 'about_approach',
            'services_title', 'services_text',
            'work_title', 'work_text',
            'process_title', 'process_text',
            'why_title', 'why_text',
            'contact_title', 'contact_text',
            'footer_text', 'address',
            'seo_title', 'seo_description',
        ],

        'lists' => [
            'values' => ['rows' => 4, 'icons' => ['lightbulb', 'handshake', 'badge-check', 'trending-up']],
            'process_steps' => ['rows' => 5, 'icons' => ['search', 'compass', 'pen-tool', 'rocket', 'refresh-cw']],
            'why_items' => ['rows' => 4, 'icons' => ['target', 'sparkles', 'messages-square', 'chart-line']],
        ],

        'strings' => ['contact_email', 'contact_phone', 'whatsapp', 'map_url', 'map_location', 'notification_email'],

        'socials' => [
            'instagram' => 'Instagram',
            'facebook' => 'Facebook',
            'linkedin' => 'LinkedIn',
            'tiktok' => 'TikTok',
            'behance' => 'Behance',
            'youtube' => 'YouTube',
            'x' => 'X',
        ],

        'images' => ['logo', 'favicon', 'og_image'],

        'defaults' => [
            // The Google Maps listing for Kliq.ma supplied with the brief.
            'map_url' => 'https://maps.app.goo.gl/pbXGCTEyDuNiCNpT9',
            // Where that listing sits; shown as the embedded map on the contact section.
            'map_location' => '33.5274554,-7.6500933',
            'notify_on_inquiry' => true,
        ],
    ],

];
