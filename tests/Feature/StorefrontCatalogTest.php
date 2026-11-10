<?php

namespace Tests\Feature;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StorefrontCatalogTest extends TestCase
{
    public function test_storefront_only_shows_offers_for_the_selected_active_shop(): void
    {
        $suffix = Str::uuid()->toString();
        $shop = Shop::create(['name' => 'Shop A '.$suffix, 'slug' => 'shop-a-'.$suffix, 'is_active' => true]);
        $otherShop = Shop::create(['name' => 'Shop B '.$suffix, 'slug' => 'shop-b-'.$suffix, 'is_active' => true]);
        $product = Product::create(['name' => 'Shared item '.$suffix, 'slug' => 'shared-item-'.$suffix, 'is_active' => true]);
        $otherProduct = Product::create(['name' => 'Other item '.$suffix, 'slug' => 'other-item-'.$suffix, 'is_active' => true]);

        ShopProduct::create([
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'price' => 12.50,
            'stock' => 4,
            'is_active' => true,
        ]);
        ShopProduct::create([
            'shop_id' => $otherShop->id,
            'product_id' => $otherProduct->id,
            'price' => 8.00,
            'stock' => 7,
            'is_active' => true,
        ]);

        $this->get('/?shop='.$shop->slug, $this->inertiaHeaders())
            ->assertOk()
            ->assertJsonPath('component', 'Storefront/Home')
            ->assertJsonPath('props.shop.slug', $shop->slug)
            ->assertJsonPath('props.offers.data.0.name', $product->name)
            ->assertJsonPath('props.offers.data.0.price', '12.50')
            ->assertJsonMissing(['name' => $otherProduct->name]);
    }

    public function test_customer_cart_uses_the_shop_specific_offer(): void
    {
        $suffix = Str::uuid()->toString();
        $shop = Shop::create(['name' => 'Cart shop '.$suffix, 'slug' => 'cart-shop-'.$suffix, 'is_active' => true]);
        $product = Product::create(['name' => 'Cart item '.$suffix, 'slug' => 'cart-item-'.$suffix, 'is_active' => true]);
        $offer = ShopProduct::create([
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'price' => 9.75,
            'stock' => 3,
            'is_active' => true,
        ]);

        $this->post('/cart', ['shop_product_id' => $offer->id, 'quantity' => 2])
            ->assertRedirect();
        $this->assertSame([$offer->id => 2], session('cart'));

        $this->get('/cart', $this->inertiaHeaders())
            ->assertOk()
            ->assertJsonPath('component', 'Storefront/Cart')
            ->assertJsonPath('props.items.0.price', '9.75')
            ->assertJsonPath('props.items.0.quantity', 2)
            ->assertJsonPath('props.total', 19.5);
    }

    public function test_shop_owner_cannot_manage_an_unassigned_shop_offer(): void
    {
        Role::findOrCreate('shop-owner', 'web');

        $suffix = Str::uuid()->toString();
        $assignedShop = Shop::create(['name' => 'Assigned '.$suffix, 'slug' => 'assigned-'.$suffix, 'is_active' => true]);
        $otherShop = Shop::create(['name' => 'Unassigned '.$suffix, 'slug' => 'unassigned-'.$suffix, 'is_active' => true]);
        $product = Product::create(['name' => 'Restricted '.$suffix, 'slug' => 'restricted-'.$suffix, 'is_active' => true]);
        $offer = ShopProduct::create([
            'shop_id' => $otherShop->id,
            'product_id' => $product->id,
            'price' => 10,
            'stock' => 5,
            'is_active' => true,
        ]);
        $owner = User::factory()->create();
        $owner->assignRole('shop-owner');
        $owner->shops()->attach($assignedShop);

        $this->actingAs($owner)
            ->patch("/shop/products/{$offer->id}", [
                'shop_id' => $otherShop->id,
                'price' => 1,
                'stock' => 99,
            ])
            ->assertForbidden();

        $this->get('/admin/shops')->assertForbidden();

        $assignedOffer = ShopProduct::create([
            'shop_id' => $assignedShop->id,
            'product_id' => $product->id,
            'price' => 10,
            'stock' => 5,
            'is_active' => true,
        ]);

        $this->patch("/shop/products/{$assignedOffer->id}", [
            'price' => 12.50,
            'stock' => 8,
            'description' => 'Updated by the assigned owner',
        ])->assertRedirect();

        $this->assertDatabaseHas('shop_products', [
            'id' => $assignedOffer->id,
            'price' => 12.50,
            'stock' => 8,
        ]);
    }

    public function test_admin_can_create_shops_and_shared_catalog_products(): void
    {
        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('shop-owner', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $suffix = Str::uuid()->toString();

        $this->actingAs($admin)
            ->post('/admin/shops', ['name' => 'Admin shop '.$suffix, 'owner_ids' => []])
            ->assertRedirect();

        $this->assertDatabaseHas('shops', [
            'name' => 'Admin shop '.$suffix,
            'slug' => 'admin-shop-'.$suffix,
        ]);
        $shop = Shop::where('name', 'Admin shop '.$suffix)->firstOrFail();
        $this->put("/admin/shops/{$shop->id}", [
            'name' => $shop->name,
            'owner_ids' => [],
            'is_active' => true,
        ])->assertRedirect();

        $this->post('/admin/products', [
            'name' => 'Admin catalog item '.$suffix,
            'is_active' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('products', [
            'name' => 'Admin catalog item '.$suffix,
            'slug' => 'admin-catalog-item-'.$suffix,
        ]);
        $product = Product::where('name', 'Admin catalog item '.$suffix)->firstOrFail();
        $this->put("/admin/products/{$product->id}", [
            'name' => $product->name,
            'is_active' => true,
        ])->assertRedirect();
    }

    /**
     * @return array<string, string|null>
     */
    private function inertiaHeaders(): array
    {
        return [
            'X-Inertia' => 'true',
            'X-Inertia-Version' => app(HandleInertiaRequests::class)->version(request()),
        ];
    }
}
