<?php

namespace App\Observers;

use App\Models\Inscripcion;

class InscripcionObserver
{
    public function creating(Inscripcion $inscripcion): void
    {
        $inscripcion->orden = Inscripcion::max('orden') + 1;
    }


    public function deleted(Inscripcion $inscripcion)
    {
        // Actualizar los estudiantes
        Inscripcion::where('orden', '>', $inscripcion->orden)
            ->decrement('orden');

    }
}
