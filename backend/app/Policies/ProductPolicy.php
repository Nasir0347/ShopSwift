<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     * Products are publicly viewable
     */
    public function viewAny(?User $user): bool
    {
        return true; // Public access to product listings
    }

    /**
     * Determine whether the user can view the model.
     * Products are publicly viewable
     */
    public function view(?User $user, Product $product): bool
    {
        return true; // Public access to product details
    }

    /**
     * Determine whether the user can create models.
     * Only admins can create products
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     * Only admins can update products
     */
    public function update(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     * Only admins can delete products
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     * Only admins can restore soft-deleted products
     */
    public function restore(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Only super admins can force delete products
     */
    public function forceDelete(User $user, Product $product): bool
    {
        return $user->role === 'super_admin';
    }
}
