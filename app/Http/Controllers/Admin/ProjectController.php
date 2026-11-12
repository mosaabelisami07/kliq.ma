<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProjectRequest;
use App\Models\Category;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Support\Media;
use App\Support\Reorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        return view('admin.projects.index', [
            'projects' => Project::query()->with('category')->withCount('images')->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.projects.form', [
            'project' => new Project(['is_published' => true]),
            'categories' => Category::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        $project = Project::query()->create([
            ...$request->projectAttributes(),
            'sort_order' => Reorder::next(Project::class),
        ]);

        $this->storeFiles($request, $project);

        return redirect()->route('admin.projects.edit', $project)->with('success', 'Project created.');
    }

    public function edit(Project $project): View
    {
        return view('admin.projects.form', [
            'project' => $project->load('images'),
            'categories' => Category::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->projectAttributes());

        $this->storeFiles($request, $project);

        return redirect()->route('admin.projects.edit', $project)->with('success', 'Project saved.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $paths = $project->images()->pluck('path')->push($project->thumbnail);

        $project->delete();
        $paths->each(fn (?string $path) => Media::delete($path));

        return redirect()->route('admin.projects.index')->with('success', 'Project deleted.');
    }

    public function publish(Project $project): RedirectResponse
    {
        $project->update(['is_published' => ! $project->is_published]);

        return back()->with('success', $project->is_published ? 'Project published.' : 'Project unpublished.');
    }

    public function move(Request $request, Project $project): RedirectResponse
    {
        Reorder::move(Project::class, $project, $request->validate(['direction' => ['required', 'in:up,down']])['direction']);

        return back();
    }

    public function destroyImage(Project $project, ProjectImage $image): RedirectResponse
    {
        $image->delete();
        Media::delete($image->path);

        return back()->with('success', 'Image removed.');
    }

    private function storeFiles(ProjectRequest $request, Project $project): void
    {
        if ($request->hasFile('thumbnail')) {
            $project->update(['thumbnail' => Media::replace($project->thumbnail, $request->file('thumbnail'), 'projects')]);
        } elseif ($request->boolean('remove_thumbnail') && $project->thumbnail) {
            Media::delete($project->thumbnail);
            $project->update(['thumbnail' => null]);
        }

        $order = (int) $project->images()->max('sort_order');

        DB::transaction(function () use ($request, $project, $order): void {
            foreach ($request->file('images', []) as $file) {
                $project->images()->create([
                    'path' => Media::store($file, "projects/{$project->id}"),
                    'sort_order' => ++$order,
                ]);
            }
        });
    }
}
