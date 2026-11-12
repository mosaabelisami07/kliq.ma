<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ContentRequest;
use App\Support\Icons;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContentController extends Controller
{
    public function edit(SiteSettings $settings): View
    {
        return view('admin.content.edit', [
            'sections' => ContentRequest::SECTIONS,
            'lists' => ContentRequest::LISTS,
            'settings' => $settings,
            'icons' => Icons::choices(),
        ]);
    }

    public function update(ContentRequest $request, SiteSettings $settings): RedirectResponse
    {
        $settings->save($request->settingValues());

        return back()->with('success', 'Website content saved.');
    }
}
