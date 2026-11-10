<?php

namespace Tests\Feature;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
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

    public function test_unassigned_products_are_never_visible_to_customers(): void
    {
        $suffix = Str::uuid()->toString();
        $shop = Shop::create(['name' => 'Visible Shop '.$suffix, 'slug' => 'visible-shop-'.$suffix, 'is_active' => true]);
        $unassigned = Product::create(['name' => 'Unassigned Item '.$suffix, 'slug' => 'unassigned-item-'.$suffix, 'is_active' => true]);

        $this->get('/?shop='.$shop->slug, $this->inertiaHeaders())
            ->assertOk()
            ->assertJsonMissing(['name' => $unassigned->name]);

        $this->get("/shops/{$shop->slug}/{$unassigned->slug}")
            ->assertNotFound();
    }

    public function test_admin_can_create_update_and_delete_shop_owners(): void
    {
        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('shop-owner', 'web');

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $shopA = Shop::create(['name' => 'Owner Shop A', 'slug' => 'owner-shop-a', 'is_active' => true]);
        $shopB = Shop::create(['name' => 'Owner Shop B', 'slug' => 'owner-shop-b', 'is_active' => true]);

        $this->actingAs($admin)
            ->post('/admin/shop-owners', [
                'name' => 'First Owner',
                'email' => 'owner1@example.test',
                'password' => 'StrongPass1234',
                'password_confirmation' => 'StrongPass1234',
                'shop_ids' => [$shopA->id],
            ])
            ->assertRedirect();

        $owner = User::where('email', 'owner1@example.test')->firstOrFail();
        $this->assertTrue($owner->hasRole('shop-owner'));
        $this->assertTrue($owner->shops()->whereKey($shopA->id)->exists());

        $this->put("/admin/shop-owners/{$owner->id}", [
            'name' => 'Updated Owner',
            'email' => 'owner1@example.test',
            'password' => '',
            'password_confirmation' => '',
            'shop_ids' => [$shopB->id],
        ])->assertRedirect();

        $owner->refresh();
        $this->assertSame('Updated Owner', $owner->name);
        $this->assertFalse($owner->shops()->whereKey($shopA->id)->exists());
        $this->assertTrue($owner->shops()->whereKey($shopB->id)->exists());

        // Cannot edit or delete an admin through the shop-owners endpoint
        $this->put("/admin/shop-owners/{$admin->id}", [
            'name' => 'Hacked',
            'email' => $admin->email,
        ])->assertNotFound();
        $this->delete("/admin/shop-owners/{$admin->id}")->assertNotFound();

        $this->delete("/admin/shop-owners/{$owner->id}")->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $owner->id]);
    }

    public function test_admin_product_image_update_replaces_previous_catalog_image(): void
    {
        Storage::fake('public');
        Role::findOrCreate('admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post('/admin/products', [
                'name' => 'Image Replace Test',
                'is_active' => true,
                'image' => UploadedFile::fake()->image('first.jpg', 100, 100),
            ])
            ->assertRedirect();

        $product = Product::where('name', 'Image Replace Test')->firstOrFail();
        $this->assertSame(1, $product->getMedia('catalog-images')->count());
        $this->assertStringContainsString('first.jpg', $product->getFirstMediaUrl('catalog-images'));

        $this->put("/admin/products/{$product->id}", [
            'name' => 'Image Replace Test',
            'is_active' => true,
            'image' => UploadedFile::fake()->image('second.jpg', 100, 100),
        ])->assertRedirect();

        $product->refresh();
        $this->assertSame(1, $product->getMedia('catalog-images')->count());
        $this->assertStringContainsString('second.jpg', $product->getFirstMediaUrl('catalog-images'));
    }

    public function test_shared_catalog_flow_and_shop_specific_offers_with_media_cleanup(): void
    {
        Storage::fake('public');
        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('shop-owner', 'web');

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $ownerA = User::factory()->create();
        $ownerA->assignRole('shop-owner');
        $ownerB = User::factory()->create();
        $ownerB->assignRole('shop-owner');

        $shopA = Shop::create(['name' => 'Shop Alpha', 'slug' => 'shop-alpha', 'is_active' => true]);
        $shopB = Shop::create(['name' => 'Shop Beta', 'slug' => 'shop-beta', 'is_active' => true]);
        $shopA->owners()->attach($ownerA);
        $shopB->owners()->attach($ownerB);

        $adminProduct = Product::create(['name' => 'Admin Honey', 'slug' => 'admin-honey', 'is_active' => true]);

        // Owner A creates a new product with a thumbnail -> available in shared catalog for Owner B
        $this->actingAs($ownerA)
            ->post('/shop/products', [
                'shop_id' => $shopA->id,
                'name' => 'Owner Mango',
                'price' => 10.00,
                'compare_at_price' => 14.00,
                'stock' => 6,
                'description' => 'Alpha sweet mango',
                'is_active' => true,
                'image' => UploadedFile::fake()->image('alpha-mango.jpg', 100, 100),
            ])
            ->assertRedirect();

        $mango = Product::where('name', 'Owner Mango')->firstOrFail();
        $this->assertStringStartsWith('/storage/', $mango->getFirstMediaUrl('catalog-images'));

        // Owner A index returns compare_at_price so editing does not lose it
        $this->get("/shop/products?shop_id={$shopA->id}", $this->inertiaHeaders())
            ->assertOk()
            ->assertJsonPath('props.offers.data.0.compare_at_price', '14.00');

        // Owner B browses shared catalog and sees both Admin and Owner A products
        $catalogResponse = $this->actingAs($ownerB)
            ->get("/shop/products?shop_id={$shopB->id}", $this->inertiaHeaders())
            ->assertOk();

        $catalogNames = collect($catalogResponse->json('props.catalog.data'))->pluck('name')->all();
        $this->assertContains('Admin Honey', $catalogNames);
        $this->assertContains('Owner Mango', $catalogNames);

        // Owner B lists Owner Mango in Shop Beta with a different price, description, and thumbnail
        $this->post('/shop/products/catalog', [
            'shop_id' => $shopB->id,
            'product_id' => $mango->id,
            'price' => 15.50,
            'compare_at_price' => 18.00,
            'stock' => 9,
            'description' => 'Beta organic mango',
            'is_active' => true,
            'image' => UploadedFile::fake()->image('beta-mango.jpg', 100, 100),
        ])->assertRedirect();

        // Customer sees shop-specific price, description, and thumbnail per shop
        $this->get("/?shop={$shopA->slug}", $this->inertiaHeaders())
            ->assertOk()
            ->assertJsonPath('props.offers.data.0.price', '10.00')
            ->assertJsonPath('props.offers.data.0.description', 'Alpha sweet mango');

        $betaHome = $this->get("/?shop={$shopB->slug}", $this->inertiaHeaders())
            ->assertOk()
            ->assertJsonPath('props.offers.data.0.price', '15.50')
            ->assertJsonPath('props.offers.data.0.description', 'Beta organic mango');

        $this->assertStringContainsString('beta-mango.jpg', $betaHome->json('props.offers.data.0.image'));

        // Deleting Shop Beta cleans up ShopProduct media records
        $betaOffer = ShopProduct::where('shop_id', $shopB->id)->where('product_id', $mango->id)->firstOrFail();
        $this->assertGreaterThan(0, Media::where('model_type', ShopProduct::class)->where('model_id', $betaOffer->id)->count());

        $this->actingAs($admin)->delete("/admin/shops/{$shopB->id}")->assertRedirect();
        $this->assertSame(0, Media::where('model_type', ShopProduct::class)->where('model_id', $betaOffer->id)->count());
    }

    public function test_logout_triggers_full_storefront_shell_reload(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/logout', [], $this->inertiaHeaders());

        $response->assertStatus(409)
            ->assertHeader('X-Inertia-Location', route('storefront.home'));

        $this->assertGuest();
    }

    /**
     * @return array<string, string|null>
     */
    private function inertiaHeaders(): array
    {
        return [
            'X-Inertia' => 'true',
            'X-Inertia-Version' => app(HandleInertiaRequests::class)->version(request()) ?? '',
        ];
    }
}
