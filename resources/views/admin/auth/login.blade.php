<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in — KLIQ Admin</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @fonts
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="grid min-h-screen font-sans antialiased lg:grid-cols-2">
    <div class="relative hidden overflow-hidden bg-ink p-12 text-white lg:flex lg:flex-col lg:justify-between">
        <div class="absolute -end-40 -top-40 size-[34rem] rounded-full bg-orange/30 blur-3xl"></div>
        <div class="absolute -bottom-32 -start-20 size-[24rem] rounded-full bg-orange/20 blur-3xl"></div>
        <span class="relative text-4xl"><x-logo /></span>
        <div class="relative">
            <p class="font-display text-5xl leading-[1.05] font-bold tracking-tight">Manage your website,<br><span class="text-orange">projects and clients.</span></p>
            <p class="mt-5 max-w-md text-white/60">The private dashboard for the KLIQ team. Authorised administrators only.</p>
        </div>
        <p class="relative text-sm text-white/40">© {{ now()->year }} KLIQ</p>
    </div>

    <main class="flex items-center justify-center px-5 py-12">
        <div class="w-full max-w-sm">
            <span class="text-3xl lg:hidden"><x-logo /></span>
            <h1 class="mt-6 font-display text-3xl font-bold tracking-tight lg:mt-0">Sign in</h1>
            <p class="mt-1 text-muted">Use your administrator account.</p>

            @if (session('status'))
                <p class="mt-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('admin.login.store') }}" class="mt-8 flex flex-col gap-5">
                @csrf

                <x-admin.input name="email" type="email" label="Email" autocomplete="username" required autofocus />
                <x-admin.input name="password" type="password" label="Password" autocomplete="current-password" required />

                @if ($accessCodeRequired)
                    <x-admin.input name="access_code" type="password" label="Access code" autocomplete="off" hint="The additional code provided to administrators." required />
                @endif

                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="remember" value="1" class="size-4 accent-orange"> Keep me signed in
                </label>

                <button type="submit" class="a-btn a-btn--primary mt-2 min-h-12 w-full text-base" data-busy-label="Signing in…">Sign in</button>
            </form>

            <a href="{{ route('root') }}" class="mt-8 inline-flex items-center gap-1.5 text-sm text-muted hover:text-ink"><x-icon name="chevron-left" class="size-4" /> Back to website</a>
        </div>
    </main>
</body>
</html>
