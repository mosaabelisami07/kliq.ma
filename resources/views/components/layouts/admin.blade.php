@props(['title' => 'Dashboard'])

@php
    $unread = \App\Models\Inquiry::query()->unread()->count();
    $nav = [
        ['admin.dashboard', 'admin.dashboard', 'layout-dashboard', 'Dashboard'],
        ['admin.inquiries.index', 'admin.inquiries.*', 'inbox', 'Inquiries'],
        ['admin.projects.index', 'admin.projects.*', 'briefcase', 'Projects'],
        ['admin.categories.index', 'admin.categories.*', 'tag', 'Categories'],
        ['admin.services.index', 'admin.services.*', 'layers', 'Services'],
        ['admin.brands.index', 'admin.brands.*', 'badge-check', 'Brands'],
        ['admin.content.edit', 'admin.content.*', 'file-text', 'Website content'],
        ['admin.settings.edit', 'admin.settings.*', 'settings', 'Settings'],
    ];
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} — KLIQ Admin</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @fonts
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="min-h-screen font-sans antialiased">
    <a href="#admin-main" class="sr-only z-50 rounded-full bg-orange px-4 py-2 font-semibold focus:not-sr-only focus:fixed focus:start-4 focus:top-4">Skip to content</a>

    {{-- Sidebar --}}
    <aside class="a-sidebar fixed inset-y-0 start-0 z-40 flex w-68 flex-col bg-ink p-4 text-white lg:translate-x-0" aria-label="Admin navigation">
        <div class="flex items-center justify-between px-2 py-3">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 text-[1.6rem]">
                <x-logo />
                <span class="rounded-full bg-white/10 px-2 py-0.5 text-[0.65rem] font-semibold tracking-wider uppercase">Admin</span>
            </a>
            <button type="button" class="a-btn a-btn--icon text-white lg:hidden" data-sidebar-toggle aria-label="Close menu"><x-icon name="x" /></button>
        </div>

        <nav class="mt-6 flex-1 overflow-y-auto">
            <ul class="flex flex-col gap-1">
                @foreach ($nav as [$route, $pattern, $icon, $label])
                    @php($active = request()->routeIs($pattern))
                    <li>
                        <a href="{{ route($route) }}" @class(['a-nav-link', 'is-active' => $active]) @if ($active) aria-current="page" @endif>
                            <x-icon :name="$icon" class="size-[1.15rem]" />
                            <span class="flex-1">{{ $label }}</span>
                            @if ($route === 'admin.inquiries.index' && $unread)
                                <span @class(['grid min-w-6 place-items-center rounded-full px-1.5 text-xs font-bold', 'bg-black text-orange' => $active, 'bg-orange text-black' => ! $active]) aria-label="{{ $unread }} unread">{{ $unread }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="border-t border-white/10 pt-4">
            <a href="{{ route('root') }}" target="_blank" class="a-nav-link"><x-icon name="external-link" class="size-[1.15rem]" /> View website</a>
            <a href="{{ route('admin.profile.edit') }}" @class(['a-nav-link', 'is-active' => request()->routeIs('admin.profile.*')])>
                <x-icon name="user" class="size-[1.15rem]" />
                <span class="min-w-0 flex-1 truncate">{{ auth()->user()->name }}</span>
            </a>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="a-nav-link w-full"><x-icon name="log-out" class="size-[1.15rem]" /> Log out</button>
            </form>
        </div>
    </aside>

    <div class="fixed inset-0 z-30 bg-black/40 lg:hidden [html:not(.sidebar-open)_&]:hidden" data-sidebar-toggle aria-hidden="true"></div>

    <div class="lg:ps-68">
        <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-line bg-cream/85 px-4 backdrop-blur md:px-8 lg:hidden">
            <button type="button" class="a-btn a-btn--ghost a-btn--icon" data-sidebar-toggle aria-label="Open menu" aria-expanded="false"><x-icon name="menu" /></button>
            <span class="text-2xl"><x-logo /></span>
            @if ($unread)
                <a href="{{ route('admin.inquiries.index', ['status' => 'new']) }}" class="ms-auto rounded-full bg-orange px-2.5 py-1 text-xs font-bold">{{ $unread }} unread</a>
            @endif
        </header>

        <main id="admin-main" class="mx-auto max-w-7xl px-4 py-8 md:px-8 md:py-10">
            {{ $slot }}
        </main>
    </div>

    {{-- Flash messages --}}
    <div class="pointer-events-none fixed inset-x-4 bottom-4 z-50 flex flex-col items-end gap-2 sm:inset-x-auto sm:end-6" aria-live="polite">
        @foreach (['success' => 'bg-ink text-white', 'error' => 'bg-red-700 text-white'] as $type => $classes)
            @if (session($type))
                <div class="pointer-events-auto flex max-w-sm items-center gap-3 rounded-2xl px-4 py-3 text-sm shadow-xl {{ $classes }}" role="status" data-toast>
                    <x-icon :name="$type === 'success' ? 'check' : 'circle-alert'" class="size-4 shrink-0 text-orange" />
                    <span class="flex-1">{{ session($type) }}</span>
                    <button type="button" class="opacity-70 hover:opacity-100" data-toast-close aria-label="Dismiss"><x-icon name="x" class="size-4" /></button>
                </div>
            @endif
        @endforeach
    </div>

    {{-- Confirmation dialog used by forms with data-confirm --}}
    <dialog class="a-dialog m-auto w-[min(26rem,calc(100%-2rem))] rounded-3xl bg-white p-0 shadow-2xl" data-confirm-dialog aria-labelledby="confirm-title">
        <form method="dialog" class="p-6">
            <span class="grid size-11 place-items-center rounded-full bg-red-50 text-red-700"><x-icon name="trash" /></span>
            <h2 id="confirm-title" class="mt-4 text-lg font-semibold">Are you sure?</h2>
            <p class="mt-1 text-sm text-muted" data-confirm-message></p>
            <div class="mt-6 flex justify-end gap-2">
                <button value="cancel" class="a-btn a-btn--ghost" autofocus>Cancel</button>
                <button value="confirm" class="a-btn a-btn--danger">Yes, continue</button>
            </div>
        </form>
    </dialog>
</body>
</html>
