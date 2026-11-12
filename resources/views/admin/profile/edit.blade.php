<x-layouts.admin title="Profile">
    <x-admin.page-header title="Your profile" description="Update your name, login email or password." />

    <form method="POST" action="{{ route('admin.profile.update') }}" class="a-card flex max-w-xl flex-col gap-5 p-6">
        @csrf
        @method('PUT')

        <x-admin.input name="name" label="Name" :value="auth()->user()->name" autocomplete="name" required />
        <x-admin.input name="email" type="email" label="Email" :value="auth()->user()->email" autocomplete="username" required />

        <div class="border-t border-line pt-5">
            <h2 class="font-semibold">Change password</h2>
            <p class="a-hint">Leave empty to keep your current password. At least 12 characters with letters and numbers.</p>
        </div>
        <x-admin.input name="password" type="password" label="New password" autocomplete="new-password" />
        <x-admin.input name="password_confirmation" type="password" label="Confirm new password" autocomplete="new-password" />

        <div class="border-t border-line pt-5">
            <x-admin.input name="current_password" type="password" label="Current password" autocomplete="current-password" hint="Required to save any change." required />
        </div>

        <button type="submit" class="a-btn a-btn--primary self-start">Save profile</button>
    </form>
</x-layouts.admin>
