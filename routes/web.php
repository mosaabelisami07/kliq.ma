<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SitemapController;
use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$locales = array_keys(config('kliq.locales'));

Route::get('/', function (Request $request) use ($locales) {
    $locale = $request->session()->get('locale')
        ?? $request->getPreferredLanguage(array_values(array_unique([config('app.locale'), ...$locales])));

    return redirect()->route('home', ['locale' => in_array($locale, $locales, true) ? $locale : config('app.locale')]);
})->name('root');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::prefix('{locale}')
    ->where(['locale' => implode('|', $locales)])
    ->middleware(SetLocale::class)
    ->group(function () {
        Route::get('/', HomeController::class)->name('home');
        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
        Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');
        Route::post('/contact', [InquiryController::class, 'store'])->middleware('throttle:contact')->name('contact.store');

        // The former standalone pages are now sections of the home page.
        foreach (['about' => 'about', 'services' => 'services', 'contact' => 'contact'] as $path => $section) {
            Route::get("/{$path}", fn () => redirect()->to(route('home')."#{$section}", 301));
        }
    });

require __DIR__.'/admin.php';
