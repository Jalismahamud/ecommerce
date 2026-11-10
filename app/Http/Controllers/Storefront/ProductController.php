<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $shop, string $product): Response
    {
        $shops = Shop::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug']);
        $shop = $shops->firstWhere('slug', $shop);

        abort_unless($shop, 404);
        $offer = $shop->offers()
            ->where('is_active', true)
            ->whereHas('product', fn ($query) => $query->where('slug', $product)->where('is_active', true))
            ->with(['product.category', 'product.media', 'media'])
            ->firstOrFail();

        return Inertia::render('Storefront/Product', [
            'shops' => $shops,
            'shop' => ['id' => $shop->id, 'name' => $shop->name, 'slug' => $shop->slug],
            'offer' => [
                'id' => $offer->id,
                'price' => $offer->price,
                'compare_at_price' => $offer->compare_at_price,
                'stock' => $offer->stock,
                'description' => $offer->description ?: $offer->product->description,
                'thumbnail' => $offer->thumbnail_url,
                'name' => $offer->product->name,
                'slug' => $offer->product->slug,
                'category' => $offer->product->category?->name,
            ],
        ]);
    }
}
