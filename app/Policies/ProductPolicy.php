<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Todos los usuarios autenticados pueden ver productos
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product $product): bool
    {
        // Todos los usuarios autenticados pueden ver un producto
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Todos los usuarios autenticados pueden crear productos
        // TODO: Restringir a roles específicos cuando se implemente RBAC
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product): bool
    {
        // Todos los usuarios autenticados pueden actualizar productos
        // TODO: Restringir a roles específicos cuando se implemente RBAC
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product): bool
    {
        // Todos los usuarios autenticados pueden eliminar productos
        // TODO: Restringir a roles específicos cuando se implemente RBAC
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Product $product): bool
    {
        // Todos los usuarios autenticados pueden restaurar productos
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        // Solo super admins pueden eliminar permanentemente
        // TODO: Implementar cuando se configure RBAC
        return true;
    }
}
