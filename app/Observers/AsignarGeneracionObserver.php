<?php

namespace App\Observers;

use App\Models\AsignarGeneracion;

class AsignarGeneracionObserver
{
    public function creating(AsignarGeneracion $asignar): void
    {
        $asignar->order = AsignarGeneracion::max('order') + 1;
    }


    public function deleted(AsignarGeneracion $asignar)
    {
        // Actualizar
        AsignarGeneracion::where('order', '>', $asignar->order)
            ->decrement('order');

    }
}
