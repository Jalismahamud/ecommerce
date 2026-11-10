<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Categories/Index', [
            'categories' => Category::query()
                ->with('parent:id,name')
                ->withCount('products')
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString(),
            'parentCategories' => Category::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = UniqueSlug::make($data['slug'] ?? $data['name'], Category::class);
        Category::create($data);

        return back()->with('success', 'Category created successfully.');
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = UniqueSlug::make($data['slug'] ?? $data['name'], Category::class, $category->id);
        $category->update($data);

        return back()->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        $category->delete();

        return back()->with('success', 'Category deleted successfully.');
    }
}
