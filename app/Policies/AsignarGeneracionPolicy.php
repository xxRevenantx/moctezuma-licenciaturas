<?php

namespace App\Policies;

use App\Models\User;
use App\Models\asignar_generacion;
use Illuminate\Auth\Access\Response;

class AsignarGeneracionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, asignar_generacion $asignarGeneracion): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, asignar_generacion $asignarGeneracion): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, asignar_generacion $asignarGeneracion): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, asignar_generacion $asignarGeneracion): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, asignar_generacion $asignarGeneracion): bool
    {
        return false;
    }
}
