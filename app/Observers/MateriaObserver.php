<?php

namespace App\Observers;

use App\Models\Materia;

class MateriaObserver
{
    public function creating(Materia $materia): void
    {
        $materia->orden = Materia::max('orden') + 1;
    }


    public function deleted(Materia $materia)
    {
        // Actualizar los estudiantes
        Materia::where('orden', '>', $materia->orden)
            ->decrement('orden');

    }
}
