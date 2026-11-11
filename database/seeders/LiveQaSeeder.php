<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LiveQaSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException('Live QA fixtures may only be seeded in the local environment.');
        }

        $password = config('app.qa_seed_password');

        if (! is_string($password) || strlen($password) < 16) {
            throw new RuntimeException('Set LIVE_QA_SEED_PASSWORD to a password of at least 16 characters.');
        }

        DB::transaction(function (): void {
            $admin = User::updateOrCreate(
                ['email' => 'admin@freshfood.test'],
                ['name' => 'Fresh Food QA Admin', 'password' => config('app.qa_seed_password')]
            );
            $admin->assignRole('admin');

            $owner = User::updateOrCreate(
                ['email' => 'owner@freshfood.test'],
                ['name' => 'Fresh Food QA Owner', 'password' => config('app.qa_seed_password')]
            );
            $owner->assignRole('shop-owner');

            $shop = Shop::firstOrCreate(
                ['slug' => 'fresh-food-qa-market'],
                [
                    'name' => 'Fresh Food QA Market',
                    'description' => 'A sample neighborhood grocery shop for local live testing.',
                    'contact_email' => 'market@freshfood.test',
                    'phone' => '+1-555-010-2026',
                    'address' => '12 Market Street',
                    'is_active' => true,
                ]
            );
            $shop->owners()->syncWithoutDetaching([$owner->id]);

            $categoryData = [
                'fruits-vegetables' => 'Fresh Fruits & Vegetables',
                'dairy-bread-eggs' => 'Dairy, Bread & Eggs',
                'bakery-biscuits' => 'Bakery & Biscuits',
                'cold-drinks-juices' => 'Cold Drinks & Juices',
                'snack-munchies' => 'Snacks & Munchies',
            ];
            $categories = [];

            foreach ($categoryData as $slug => $name) {
                $categories[$slug] = Category::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $name, 'is_active' => true]
                );
            }

            $productData = [
                ['Fresh Strawberries', 'strawberries-500g', 'FRUIT-001', 'Green Valley', 'fruits-vegetables', 5.49, 6, true],
                ['Baby Spinach', 'baby-spinach-200g', 'VEG-001', 'Farm Fresh', 'fruits-vegetables', 3.25, 14, false],
                ['Organic Whole Milk', 'organic-whole-milk-1l', 'DAIRY-001', 'Meadow Farm', 'dairy-bread-eggs', 4.15, 11, true],
                ['Free Range Eggs', 'free-range-eggs-12', 'DAIRY-002', 'Sunny Acres', 'dairy-bread-eggs', 5.99, 8, false],
                ['Sourdough Country Loaf', 'sourdough-country-loaf', 'BAKE-001', 'Daily Oven', 'bakery-biscuits', 6.50, 5, true],
                ['Orange Juice', 'fresh-orange-juice-1l', 'DRINK-001', 'Citrus Press', 'cold-drinks-juices', 4.75, 9, false],
                ['Sea Salt Potato Chips', 'sea-salt-potato-chips-150g', 'SNACK-001', 'Crisp Co.', 'snack-munchies', 2.85, 20, false],
                ['Blueberry Muffins', 'blueberry-muffins-4pk', 'BAKE-002', 'Daily Oven', 'bakery-biscuits', 5.25, 7, true],
            ];

            foreach ($productData as [$name, $slug, $sku, $brand, $categorySlug, $price, $stock, $featured]) {
                $product = Product::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'category_id' => $categories[$categorySlug]->id,
                        'name' => $name,
                        'sku' => $sku,
                        'brand' => $brand,
                        'short_description' => 'Fresh, quality '.$name.'.',
                        'description' => 'A locally stocked grocery favorite, selected for freshness and everyday value.',
                        'is_active' => true,
                    ]
                );

                ShopProduct::firstOrCreate(
                    ['shop_id' => $shop->id, 'product_id' => $product->id],
                    [
                        'price' => $price,
                        'compare_at_price' => round($price * 1.15, 2),
                        'stock' => $stock,
                        'description' => 'Available for pickup from Fresh Food QA Market.',
                        'is_active' => true,
                        'is_featured' => $featured,
                    ]
                );
            }
        });
    }
}
