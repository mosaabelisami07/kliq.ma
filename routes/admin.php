<?php

use App\Http\Controllers\Admin\AuthenticatedSessionController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InquiryController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:20,1')->name('login.store');
    });

    Route::middleware(['auth', EnsureUserIsAdmin::class])->group(function () {
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('/inquiries/export', [InquiryController::class, 'export'])->name('inquiries.export');
        Route::resource('inquiries', InquiryController::class)->only(['index', 'show', 'update', 'destroy']);

        Route::patch('/projects/{project}/publish', [ProjectController::class, 'publish'])->name('projects.publish');
        Route::patch('/projects/{project}/move', [ProjectController::class, 'move'])->name('projects.move');
        Route::delete('/projects/{project}/images/{image}', [ProjectController::class, 'destroyImage'])->scopeBindings()->name('projects.images.destroy');
        Route::resource('projects', ProjectController::class)->except('show');

        Route::patch('/services/{service}/toggle', [ServiceController::class, 'toggle'])->name('services.toggle');
        Route::patch('/services/{service}/move', [ServiceController::class, 'move'])->name('services.move');
        Route::resource('services', ServiceController::class)->except('show');

        Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::patch('/brands/{brand}/toggle', [BrandController::class, 'toggle'])->name('brands.toggle');
        Route::patch('/brands/{brand}/move', [BrandController::class, 'move'])->name('brands.move');
        Route::resource('brands', BrandController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::get('/content', [ContentController::class, 'edit'])->name('content.edit');
        Route::put('/content', [ContentController::class, 'update'])->name('content.update');

        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    });
});
