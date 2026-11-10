<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShopRequest;
use App\Models\Shop;
use App\Models\User;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Shop::class);

        return Inertia::render('Admin/Shops/Index', [
            'shops' => Shop::query()
                ->with('owners:id,name,email')
                ->withCount('offers')
                ->orderBy('name')
                ->paginate(15),
            'owners' => User::role('shop-owner')->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function store(ShopRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $ownerIds = $this->validatedOwnerIds($data['owner_ids'] ?? []);
        unset($data['owner_ids']);
        $data['slug'] = UniqueSlug::make($data['slug'] ?? $data['name'], Shop::class);

        DB::transaction(function () use ($data, $ownerIds): void {
            $shop = Shop::create($data);
            $shop->owners()->sync($ownerIds);
        });

        return back()->with('success', 'Shop created successfully.');
    }

    public function update(ShopRequest $request, Shop $shop): RedirectResponse
    {
        $data = $request->validated();
        $syncOwners = array_key_exists('owner_ids', $data);
        $ownerIds = $syncOwners ? $this->validatedOwnerIds($data['owner_ids']) : [];
        unset($data['owner_ids']);
        $data['slug'] = UniqueSlug::make($data['slug'] ?? $data['name'], Shop::class, $shop->id);

        DB::transaction(function () use ($shop, $data, $ownerIds, $syncOwners): void {
            $shop->update($data);

            if ($syncOwners) {
                $shop->owners()->sync($ownerIds);
            }
        });

        return back()->with('success', 'Shop updated successfully.');
    }

    public function destroy(Shop $shop): RedirectResponse
    {
        $this->authorize('delete', $shop);
        $shop->delete();

        return back()->with('success', 'Shop deleted successfully.');
    }

    /**
     * @param  array<int, int|string>  $ownerIds
     * @return array<int, int>
     */
    private function validatedOwnerIds(array $ownerIds): array
    {
        $validIds = User::role('shop-owner')->whereIn('id', $ownerIds)->pluck('id')->all();

        if (count($validIds) !== count(array_unique($ownerIds))) {
            throw ValidationException::withMessages([
                'owner_ids' => 'Only users with the shop-owner role can be assigned to a shop.',
            ]);
        }

        return $validIds;
    }
}
