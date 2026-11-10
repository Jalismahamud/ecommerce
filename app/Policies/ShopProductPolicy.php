<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;

class ShopProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'shop-owner']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ShopProduct $shopProduct): bool
    {
        return $user->hasRole('admin')
            || ($user->hasRole('shop-owner') && $user->shops()->whereKey($shopProduct->shop_id)->exists());
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Shop $shop): bool
    {
        return $user->hasRole('admin')
            || ($user->hasRole('shop-owner') && $user->shops()->whereKey($shop->id)->exists());
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ShopProduct $shopProduct): bool
    {
        return $user->hasRole('admin')
            || ($user->hasRole('shop-owner') && $user->shops()->whereKey($shopProduct->shop_id)->exists());
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ShopProduct $shopProduct): bool
    {
        return $this->update($user, $shopProduct);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ShopProduct $shopProduct): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ShopProduct $shopProduct): bool
    {
        return false;
    }
}
