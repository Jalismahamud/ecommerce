<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShopOwnerRequest;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ShopOwnerController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/ShopOwners/Index', [
            'owners' => User::role('shop-owner')
                ->with('shops:id,name')
                ->orderBy('name')
                ->paginate(15),
            'shops' => Shop::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(ShopOwnerRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $shopIds = $data['shop_ids'] ?? [];
        unset($data['shop_ids']);

        DB::transaction(function () use ($data, $shopIds): void {
            $owner = User::create($data);
            $owner->assignRole('shop-owner');
            $owner->shops()->sync($shopIds);
        });

        return back()->with('success', 'Shop owner created successfully.');
    }
}
