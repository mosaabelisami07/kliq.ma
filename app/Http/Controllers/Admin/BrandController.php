<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BrandRequest;
use App\Models\Brand;
use App\Support\Media;
use App\Support\Reorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(): View
    {
        return view('admin.brands.index', [
            'brands' => Brand::query()->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function store(BrandRequest $request): RedirectResponse
    {
        $brand = Brand::query()->create([...$request->brandAttributes(), 'sort_order' => Reorder::next(Brand::class)]);

        $this->storeLogo($request, $brand);

        return back()->with('success', "{$brand->name} added.");
    }

    public function update(BrandRequest $request, Brand $brand): RedirectResponse
    {
        $brand->update($request->brandAttributes());

        $this->storeLogo($request, $brand);

        return back()->with('success', "{$brand->name} saved.");
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $brand->delete();
        Media::delete($brand->logo);

        return back()->with('success', "{$brand->name} deleted.");
    }

    public function toggle(Brand $brand): RedirectResponse
    {
        $brand->update(['is_active' => ! $brand->is_active]);

        return back()->with('success', $brand->is_active ? "{$brand->name} is now shown." : "{$brand->name} is now hidden.");
    }

    public function move(Request $request, Brand $brand): RedirectResponse
    {
        Reorder::move(Brand::class, $brand, $request->validate(['direction' => ['required', 'in:up,down']])['direction']);

        return back();
    }

    private function storeLogo(BrandRequest $request, Brand $brand): void
    {
        if ($request->hasFile('logo')) {
            $brand->update(['logo' => Media::replace($brand->logo, $request->file('logo'), 'brands')]);
        } elseif ($request->boolean('remove_logo') && $brand->logo) {
            Media::delete($brand->logo);
            $brand->update(['logo' => null]);
        }
    }
}
