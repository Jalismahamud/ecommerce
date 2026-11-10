<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::query()
            ->with(['category:id,name', 'media'])
            ->withCount('offers')
            ->orderBy('name')
            ->paginate(20);

        $products->through(fn (Product $product) => [
            'id' => $product->id,
            'category_id' => $product->category_id,
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'brand' => $product->brand,
            'category' => $product->category?->name,
            'short_description' => $product->short_description,
            'description' => $product->description,
            'is_active' => $product->is_active,
            'offers_count' => $product->offers_count,
            'image' => $product->getFirstMediaUrl('catalog-images'),
        ]);

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = Arr::except($request->validated(), ['image']);
        $data['slug'] = UniqueSlug::make($data['slug'] ?? $data['name'], Product::class);
        $product = Product::create($data);

        if ($request->hasFile('image')) {
            $product->addMediaFromRequest('image')->toMediaCollection('catalog-images');
        }

        return back()->with('success', 'Catalog product created successfully.');
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $data = Arr::except($request->validated(), ['image']);
        $data['slug'] = UniqueSlug::make($data['slug'] ?? $data['name'], Product::class, $product->id);
        $product->update($data);

        if ($request->hasFile('image')) {
            $product->addMediaFromRequest('image')->toMediaCollection('catalog-images');
        }

        return back()->with('success', 'Catalog product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);
        $product->delete();

        return back()->with('success', 'Catalog product deleted successfully.');
    }
}
