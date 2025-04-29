<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{


    public function creating(User $user): void
    {
        $user->order = User::max('order') + 1;
    }


    public function deleted(User $user)
    {
        // Actualizar los estudiantes
        User::where('order', '>', $user->order)
            ->decrement('order');

    }
}
