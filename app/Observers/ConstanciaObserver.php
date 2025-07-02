<?php

namespace App\Observers;

use App\Models\Constancia;

class ConstanciaObserver
{
     public function creating(Constancia $constancia): void
    {
        $constancia->no_constancia = Constancia::max('no_constancia') + 1;
    }


    public function deleted(Constancia $constancia)
    {
        // Actualizar los estudiantes
        Constancia::where('no_constancia', '>', $constancia->no_constancia)
            ->decrement('no_constancia');

    }
}
