<?php

namespace App\Http\Controllers\ShopOwner;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShopOwner\ShopProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ShopProductController extends Controller
{
    public function index(Request $request): Response
    {
        $shops = $request->user()->shops()
            ->orderBy('name')
            ->get(['shops.id', 'shops.name', 'shops.slug']);

        $shop = $shops->firstWhere('id', (int) $request->query('shop_id')) ?? $shops->first();

        $offers = $shop
            ? $shop->offers()->with(['product.category', 'product.media', 'media'])->orderBy('created_at', 'desc')->paginate(15)
            : new LengthAwarePaginator([], 0, 15);

        $catalog = Product::query()
            ->where('is_active', true)
            ->when(
                $shop,
                fn ($query) => $query->whereDoesntHave('offers', fn ($offersQuery) => $offersQuery->where('shop_id', $shop->id)),
                fn ($query) => $query->whereRaw('1 = 0')
            )
            ->when($request->string('search')->trim()->value(), fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->with('category:id,name')
            ->orderBy('name')
            ->paginate(12, ['id', 'category_id', 'name', 'sku', 'short_description'])
            ->withQueryString();

        $offers->through(fn (ShopProduct $offer) => [
            'id' => $offer->id,
            'product_id' => $offer->product_id,
            'name' => $offer->product->name,
            'sku' => $offer->product->sku,
            'price' => $offer->price,
            'stock' => $offer->stock,
            'description' => $offer->description,
            'is_active' => $offer->is_active,
            'is_featured' => $offer->is_featured,
            'image' => $offer->thumbnail_url,
        ]);

        return Inertia::render('ShopOwner/Products/Index', [
            'shops' => $shops,
            'shop' => $shop ? ['id' => $shop->id, 'name' => $shop->name, 'slug' => $shop->slug] : null,
            'offers' => $offers,
            'catalog' => $catalog,
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'filters' => ['search' => $request->string('search')->value()],
        ]);
    }

    public function storeFromCatalog(ShopProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $shop = Shop::findOrFail($data['shop_id']);
        $product = Product::query()->where('is_active', true)->findOrFail($data['product_id']);
        $this->ensureOfferDoesNotExist($shop, $product);

        $offer = $shop->offers()->create(Arr::only($data, [
            'product_id',
            'price',
            'compare_at_price',
            'stock',
            'description',
            'is_active',
            'is_featured',
        ]));

        if ($request->hasFile('image')) {
            $offer->addMediaFromRequest('image')->toMediaCollection('thumbnail');
        }

        return back()->with('success', 'Product added to your shop.');
    }

    public function storeNewProduct(ShopProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $shop = Shop::findOrFail($data['shop_id']);

        DB::transaction(function () use ($request, $data, $shop): void {
            $product = Product::create([
                'category_id' => $data['category_id'] ?? null,
                'name' => $data['name'],
                'slug' => UniqueSlug::make($data['name'], Product::class),
                'sku' => $data['sku'] ?? null,
                'brand' => $data['brand'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'description' => $data['product_description'] ?? null,
                'is_active' => true,
            ]);

            $offer = $shop->offers()->create([
                'product_id' => $product->id,
                'price' => $data['price'],
                'compare_at_price' => $data['compare_at_price'] ?? null,
                'stock' => $data['stock'],
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'is_featured' => $data['is_featured'] ?? false,
            ]);

            if ($request->hasFile('image')) {
                $offer->addMediaFromRequest('image')->toMediaCollection('thumbnail');
            }
        });

        return back()->with('success', 'Product created and added to your shop.');
    }

    public function update(ShopProductRequest $request, ShopProduct $shopProduct): RedirectResponse
    {
        $data = Arr::only($request->validated(), [
            'price',
            'compare_at_price',
            'stock',
            'description',
            'is_active',
            'is_featured',
        ]);
        $shopProduct->update($data);

        if ($request->hasFile('image')) {
            $shopProduct->addMediaFromRequest('image')->toMediaCollection('thumbnail');
        }

        return back()->with('success', 'Shop product updated successfully.');
    }

    public function destroy(ShopProduct $shopProduct): RedirectResponse
    {
        $this->authorize('delete', $shopProduct);
        $shopProduct->delete();

        return back()->with('success', 'Product removed from your shop.');
    }

    private function ensureOfferDoesNotExist(Shop $shop, Product $product): void
    {
        if ($shop->offers()->where('product_id', $product->id)->exists()) {
            throw ValidationException::withMessages([
                'product_id' => 'This product has already been added to the selected shop.',
            ]);
        }
    }
}
