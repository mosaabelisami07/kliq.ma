<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceRequest;
use App\Models\Service;
use App\Support\Icons;
use App\Support\Media;
use App\Support\Reorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('admin.services.index', [
            'services' => Service::query()->withCount('inquiries')->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.services.form', [
            'service' => new Service(['is_active' => true, 'icon' => 'sparkles']),
            'icons' => Icons::choices(),
        ]);
    }

    public function store(ServiceRequest $request): RedirectResponse
    {
        $service = Service::query()->create([
            ...$request->serviceAttributes(),
            'sort_order' => Reorder::next(Service::class),
        ]);

        $this->storeImage($request, $service);

        return redirect()->route('admin.services.edit', $service)->with('success', 'Service created.');
    }

    public function edit(Service $service): View
    {
        return view('admin.services.form', [
            'service' => $service,
            'icons' => Icons::choices(),
        ]);
    }

    public function update(ServiceRequest $request, Service $service): RedirectResponse
    {
        $service->update($request->serviceAttributes());

        $this->storeImage($request, $service);

        return redirect()->route('admin.services.edit', $service)->with('success', 'Service saved.');
    }

    /**
     * Past inquiries keep the service name they were submitted with.
     */
    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();
        Media::delete($service->image);

        return redirect()->route('admin.services.index')->with('success', 'Service deleted.');
    }

    public function toggle(Service $service): RedirectResponse
    {
        $service->update(['is_active' => ! $service->is_active]);

        return back()->with('success', $service->is_active ? 'Service activated.' : 'Service deactivated.');
    }

    public function move(Request $request, Service $service): RedirectResponse
    {
        Reorder::move(Service::class, $service, $request->validate(['direction' => ['required', 'in:up,down']])['direction']);

        return back();
    }

    private function storeImage(ServiceRequest $request, Service $service): void
    {
        if ($request->hasFile('image')) {
            $service->update(['image' => Media::replace($service->image, $request->file('image'), 'services')]);
        } elseif ($request->boolean('remove_image') && $service->image) {
            Media::delete($service->image);
            $service->update(['image' => null]);
        }
    }
}
