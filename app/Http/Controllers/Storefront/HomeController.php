<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Shop;
use App\Models\ShopProduct;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        $shops = Shop::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $shop = $shops->firstWhere('slug', $request->query('shop')) ?? $shops->first();

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(12)
            ->get(['id', 'name', 'slug'])
            ->map(fn (Category $category, int $index) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'image' => asset('frontend/fresh-food-theme/src/assets/images/category/'.(
                    [
                        'dairy-bread-eggs' => 'category-dairy-bread-eggs.jpg',
                        'snack-munchies' => 'category-snack-munchies.jpg',
                        'bakery-biscuits' => 'category-bakery-biscuits.jpg',
                        'fruits-vegetables' => 'category-fruits-vegetables.jpg',
                        'cold-drinks-juices' => 'category-cold-drinks-juices.jpg',
                        'atta-rice-dal' => 'category-atta-rice-dal.jpg',
                        'baby-care' => 'category-baby-care.jpg',
                        'chicken-meat-fish' => 'category-chicken-meat-fish.jpg',
                        'cleaning-essentials' => 'category-cleaning-essentials.jpg',
                        'instant-food' => 'category-instant-food.jpg',
                        'pet-care' => 'category-pet-care.jpg',
                        'tea-coffee-drinks' => 'category-tea-coffee-drinks.jpg',
                    ][$category->slug] ?? 'category-'.(($index % 5) + 1).'.jpg'
                )),
            ]);

        $offers = $shop
            ? $shop->offers()
                ->where('is_active', true)
                ->whereHas('product', fn ($query) => $query->where('is_active', true))
                ->when($request->filled('search'), fn ($query) => $query->whereHas(
                    'product',
                    fn ($productQuery) => $productQuery->where('name', 'like', '%'.$request->string('search')->trim()->value().'%')
                ))
                ->when($request->filled('category'), fn ($query) => $query->whereHas(
                    'product.category',
                    fn ($categoryQuery) => $categoryQuery->where('slug', $request->query('category'))
                ))
                ->with(['product.category', 'product.media', 'media'])
                ->orderByDesc('is_featured')
                ->latest()
                ->paginate(12)
                ->withQueryString()
            : new LengthAwarePaginator([], 0, 12);

        $offers->through(fn (ShopProduct $offer) => [
            'id' => $offer->id,
            'name' => $offer->product->name,
            'slug' => $offer->product->slug,
            'category' => $offer->product->category?->name,
            'category_slug' => $offer->product->category?->slug,
            'price' => $offer->price,
            'compare_at_price' => $offer->compare_at_price,
            'stock' => $offer->stock,
            'description' => $offer->description ?: $offer->product->short_description,
            'image' => $offer->thumbnail_url,
            'featured' => $offer->is_featured,
        ]);

        return Inertia::render('Storefront/Home', [
            'shops' => $shops,
            'shop' => $shop ? ['id' => $shop->id, 'name' => $shop->name, 'slug' => $shop->slug] : null,
            'categories' => $categories,
            'offers' => $offers,
            'filters' => [
                'search' => $request->string('search')->value(),
                'category' => $request->query('category'),
            ],
        ]);
    }
}
