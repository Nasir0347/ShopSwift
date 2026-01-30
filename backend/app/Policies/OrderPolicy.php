<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     * Admins can view all orders, customers only their own
     */
    public function viewAny(User $user): bool
    {
        return true; // Can attempt to view, filtering handled in controller
    }

    /**
     * Determine whether the user can view the model.
     * Admins can view any order, customers only their own
     */
    public function view(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $order->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     * Any authenticated user can create orders
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     * Only admins can update orders (for status changes, etc.)
     */
    public function update(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     * Only admins can delete orders
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     * Only admins can restore soft-deleted orders
     */
    public function restore(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Only super admins can force delete orders
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return $user->role === 'super_admin';
    }

    /**
     * Determine if user can cancel an order
     * Admins always can, customers can only cancel their pending orders
     */
    public function cancel(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $order->user_id === $user->id && $order->status === 'pending';
    }
}
