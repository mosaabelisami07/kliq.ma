<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Support\Reorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::query()->withCount('projects')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::query()->create([...$request->categoryAttributes(), 'sort_order' => Reorder::next(Category::class)]);

        return back()->with('success', 'Category added.');
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->categoryAttributes());

        return back()->with('success', 'Category saved.');
    }

    /**
     * Projects in the category stay, without a category.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return back()->with('success', 'Category deleted.');
    }
}
