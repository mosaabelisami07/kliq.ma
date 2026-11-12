<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingsRequest;
use App\Support\Media;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(SiteSettings $settings): View
    {
        return view('admin.settings.edit', [
            'settings' => $settings,
            'textKeys' => SettingsRequest::TEXT_KEYS,
        ]);
    }

    public function update(SettingsRequest $request, SiteSettings $settings): RedirectResponse
    {
        $values = $request->settingValues();

        foreach (config('kliq.settings.images') as $key) {
            if ($request->hasFile($key)) {
                $values[$key] = Media::replace($settings->get($key), $request->file($key), 'branding');
            } elseif ($request->boolean("remove_{$key}")) {
                Media::delete($settings->get($key));
                $values[$key] = null;
            }
        }

        $settings->save($values);

        return back()->with('success', 'Settings saved.');
    }
}
