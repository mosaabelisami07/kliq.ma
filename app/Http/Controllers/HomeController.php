<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Project;
use App\Models\Service;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Number of projects shown in the home page portfolio section.
     */
    private const PROJECT_LIMIT = 3;

    public function __invoke(): View
    {
        $projects = Project::query()->published()->with('category')->get();

        return view('pages.home', [
            'services' => Service::query()->active()->get(),
            'brands' => Brand::query()->active()->get(),
            'projects' => $projects->take(self::PROJECT_LIMIT),
            'heroVisuals' => $projects->filter(fn (Project $project): bool => filled($project->thumbnail))->take(6)->values(),
            'hasMoreProjects' => $projects->count() > self::PROJECT_LIMIT,
            'categories' => ProjectController::categoriesWithProjects(),
        ]);
    }
}
