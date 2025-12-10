<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CustomerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Todos los usuarios autenticados pueden ver clientes
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Customer $customer): bool
    {
        // Todos los usuarios autenticados pueden ver un cliente
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Todos los usuarios autenticados pueden crear clientes
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Customer $customer): bool
    {
        // Todos los usuarios autenticados pueden actualizar clientes
        // TODO: Restringir a roles específicos cuando se implemente RBAC
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Customer $customer): bool
    {
        // No se pueden eliminar clientes con pedidos
        // TODO: Restringir a roles específicos cuando se implemente RBAC
        return $customer->orders()->count() === 0;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Customer $customer): bool
    {
        // Todos los usuarios autenticados pueden restaurar clientes
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Customer $customer): bool
    {
        // Solo super admins pueden eliminar permanentemente
        // TODO: Implementar cuando se configure RBAC
        return false;
    }
}
