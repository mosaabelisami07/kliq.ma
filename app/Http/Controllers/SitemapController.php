<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Service;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Every public page, in every language.
     */
    public function __invoke(): Response
    {
        $projects = Project::query()->where('is_published', true)->get(['slug', 'updated_at']);
        $services = Service::query()->where('is_active', true)->get(['slug', 'updated_at']);
        $urls = [];

        foreach (array_keys(config('kliq.locales')) as $locale) {
            $urls[] = ['loc' => route('home', ['locale' => $locale]), 'lastmod' => null];
            $urls[] = ['loc' => route('projects.index', ['locale' => $locale]), 'lastmod' => $projects->max('updated_at')];

            foreach ($projects as $project) {
                $urls[] = ['loc' => route('projects.show', ['locale' => $locale, 'project' => $project]), 'lastmod' => $project->updated_at];
            }

            foreach ($services as $service) {
                $urls[] = ['loc' => route('services.show', ['locale' => $locale, 'service' => $service]), 'lastmod' => $service->updated_at];
            }
        }

        return response()->view('sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml');
    }
}
