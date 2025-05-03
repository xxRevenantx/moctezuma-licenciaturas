<?php

namespace App\Observers;

use App\Models\Periodo;

class PeriodoObserver
{
    public function creating(Periodo $periodo): void
    {
        $periodo->order = Periodo::max('order') + 1;
    }


    public function deleted(Periodo $periodo)
    {
        // Actualizar los estudiantes
        Periodo::where('order', '>', $periodo->order)
            ->decrement('order');

    }
}
