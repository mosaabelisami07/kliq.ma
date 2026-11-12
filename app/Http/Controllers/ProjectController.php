<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $projects = Project::query()->published()->with('category')->get();
        $categories = $this->categoriesWithProjects();
        $category = $categories->firstWhere('slug', $request->query('category'))?->slug;

        return view('pages.projects.index', [
            'projects' => $projects,
            'categories' => $categories,
            'category' => $category,
        ]);
    }

    public function show(Project $project): View
    {
        abort_unless($project->is_published, 404);

        $project->load(['category', 'images']);

        $published = Project::query()->published()->pluck('id');
        $nextId = $published->get(($published->search($project->id) + 1) % $published->count());

        return view('pages.projects.show', [
            'project' => $project,
            'next' => $nextId !== $project->id ? Project::query()->find($nextId) : null,
        ]);
    }

    /**
     * Categories that have at least one published project, for the filters.
     *
     * @return Collection<int, Category>
     */
    public static function categoriesWithProjects(): Collection
    {
        return Category::query()
            ->whereHas('projects', fn ($query) => $query->where('is_published', true))
            ->withCount(['projects' => fn ($query) => $query->where('is_published', true)])
            ->orderBy('sort_order')
            ->get();
    }
}
