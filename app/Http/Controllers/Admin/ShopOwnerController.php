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
                ->with('shops:id,name,is_active')
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString(),
            'shops' => Shop::query()->orderBy('name')->get(['id', 'name', 'is_active']),
        ]);
    }

    public function store(ShopOwnerRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['email'] = strtolower($data['email']);
        $shopIds = $data['shop_ids'] ?? [];
        unset($data['shop_ids']);

        DB::transaction(function () use ($data, $shopIds): void {
            $owner = User::create($data);
            $owner->assignRole('shop-owner');
            $owner->shops()->sync($shopIds);
        });

        return back()->with('success', 'Shop owner created successfully.');
    }

    public function update(ShopOwnerRequest $request, User $shopOwner): RedirectResponse
    {
        abort_unless($shopOwner->hasRole('shop-owner'), 404);

        $data = $request->validated();
        $data['email'] = strtolower($data['email']);
        $syncShops = array_key_exists('shop_ids', $data);
        $shopIds = $syncShops ? $data['shop_ids'] : [];
        unset($data['shop_ids']);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        DB::transaction(function () use ($shopOwner, $data, $syncShops, $shopIds): void {
            $shopOwner->update($data);

            if ($syncShops) {
                $shopOwner->shops()->sync($shopIds);
            }
        });

        return back()->with('success', 'Shop owner updated successfully.');
    }

    public function destroy(User $shopOwner): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        abort_unless($shopOwner->hasRole('shop-owner'), 404);
        abort_if($shopOwner->is(auth()->user()) || $shopOwner->hasRole('admin'), 403);

        DB::transaction(function () use ($shopOwner): void {
            $shopOwner->shops()->detach();
            $shopOwner->delete();
        });

        return back()->with('success', 'Shop owner deleted successfully.');
    }
}
