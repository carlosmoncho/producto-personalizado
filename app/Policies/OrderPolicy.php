<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Todos los usuarios autenticados pueden ver pedidos
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        // Todos los usuarios autenticados pueden ver un pedido
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Todos los usuarios autenticados pueden crear pedidos
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): bool
    {
        // Todos los usuarios autenticados pueden actualizar pedidos
        // TODO: Restringir a roles específicos cuando se implemente RBAC
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        // Solo se pueden eliminar pedidos en estado draft o pending
        // TODO: Restringir a roles específicos cuando se implemente RBAC
        return in_array($order->status, ['draft', 'pending']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Order $order): bool
    {
        // Todos los usuarios autenticados pueden restaurar pedidos
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        // Solo super admins pueden eliminar permanentemente
        // TODO: Implementar cuando se configure RBAC
        return false;
    }

    /**
     * Determine whether the user can update the order status.
     */
    public function updateStatus(User $user, Order $order): bool
    {
        // Todos los usuarios autenticados pueden actualizar el estado
        // TODO: Restringir a roles específicos cuando se implemente RBAC
        return true;
    }
}
