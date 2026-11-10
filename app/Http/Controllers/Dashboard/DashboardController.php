<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): Response
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return Inertia::render('Dashboard/Index', [
                'stats' => [
                    'shops' => Shop::count(),
                    'products' => Product::count(),
                    'offers' => ShopProduct::count(),
                ],
            ]);
        }

        $shopIds = $user->shops()->pluck('shops.id');

        return Inertia::render('Dashboard/Index', [
            'stats' => [
                'shops' => $shopIds->count(),
                'products' => ShopProduct::whereIn('shop_id', $shopIds)->count(),
                'offers' => ShopProduct::whereIn('shop_id', $shopIds)->where('stock', '<=', 5)->count(),
            ],
        ]);
    }
}
