<?php

namespace App\Observers;

use App\Models\Accion;

class AccionObserver
{
    public function creating(Accion $accion): void
    {
        $accion->order = Accion::max('order') + 1;
    }


    public function deleted(Accion $accion)
    {
        // Actualizar los estudiantes
        Accion::where('order', '>', $accion->order)
            ->decrement('order');

    }
}
