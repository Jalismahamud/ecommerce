<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function index(Request $request): Response
    {
        $cart = $request->session()->get('cart', []);
        $offers = ShopProduct::query()
            ->whereIn('id', array_keys($cart))
            ->where('is_active', true)
            ->whereHas('shop', fn ($query) => $query->where('is_active', true))
            ->whereHas('product', fn ($query) => $query->where('is_active', true))
            ->with(['shop:id,name,slug', 'product:id,name,slug', 'product.media', 'media'])
            ->get()
            ->map(fn (ShopProduct $offer) => [
                'id' => $offer->id,
                'name' => $offer->product->name,
                'shop' => $offer->shop->name,
                'shop_slug' => $offer->shop->slug,
                'slug' => $offer->product->slug,
                'price' => $offer->price,
                'stock' => $offer->stock,
                'quantity' => min((int) ($cart[$offer->id] ?? 1), $offer->stock),
                'image' => $offer->thumbnail_url,
            ])
            ->filter(fn (array $item) => $item['quantity'] > 0)
            ->values();

        $validCart = $offers->mapWithKeys(fn (array $item) => [$item['id'] => $item['quantity']])->all();
        $request->session()->put('cart', $validCart);

        return Inertia::render('Storefront/Cart', [
            'shops' => Shop::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug']),
            'items' => $offers,
            'total' => $offers->sum(fn (array $item) => (float) $item['price'] * $item['quantity']),
            'itemCount' => $offers->sum('quantity'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'shop_product_id' => ['required', 'integer', 'exists:shop_products,id'],
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:99'],
        ]);
        $offer = ShopProduct::query()
            ->where('is_active', true)
            ->whereHas('shop', fn ($query) => $query->where('is_active', true))
            ->whereHas('product', fn ($query) => $query->where('is_active', true))
            ->findOrFail($data['shop_product_id']);
        $cart = $request->session()->get('cart', []);
        $quantity = (int) ($cart[$offer->id] ?? 0) + (int) ($data['quantity'] ?? 1);

        if ($quantity > $offer->stock) {
            throw ValidationException::withMessages([
                'quantity' => 'The requested quantity is not available in stock.',
            ]);
        }

        $cart[$offer->id] = $quantity;
        $request->session()->put('cart', $cart);

        return back()->with('success', 'Product added to your cart.');
    }

    public function update(Request $request, ShopProduct $shopProduct): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:99']]);
        abort_unless($shopProduct->is_active && $shopProduct->product->is_active && $shopProduct->shop->is_active, 404);

        if ($data['quantity'] > $shopProduct->stock) {
            throw ValidationException::withMessages([
                'quantity' => 'The requested quantity is not available in stock.',
            ]);
        }

        $cart = $request->session()->get('cart', []);
        abort_unless(array_key_exists($shopProduct->id, $cart), 404);
        $cart[$shopProduct->id] = (int) $data['quantity'];
        $request->session()->put('cart', $cart);

        return back()->with('success', 'Cart updated.');
    }

    public function destroy(Request $request, ShopProduct $shopProduct): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$shopProduct->id]);
        $request->session()->put('cart', $cart);

        return back()->with('success', 'Product removed from your cart.');
    }
}
