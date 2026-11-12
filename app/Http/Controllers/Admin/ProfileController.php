<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('admin.profile.edit');
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->safe()->only(['name', 'email']));

        if ($request->filled('password')) {
            $user->password = $request->validated('password');
        }

        $user->save();

        if ($request->filled('password')) {
            Auth::logoutOtherDevices($request->validated('password'));
            $request->session()->regenerate();
        }

        return back()->with('success', 'Profile updated.');
    }
}
