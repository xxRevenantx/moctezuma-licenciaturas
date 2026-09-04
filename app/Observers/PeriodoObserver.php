<?php

namespace App\Observers;

use App\Models\Periodo;

class PeriodoObserver
{
    public function creating(Periodo $periodo): void
    {
        $periodo->order = Periodo::max('order') + 1;
    }


    public function saving(Periodo $periodo): void
    {
        try {
            app(\App\Services\AcademicPeriodResolver::class)->assertConsistent($periodo);
        } catch (\App\Exceptions\AcademicPeriodException $e) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'ciclo_escolar' => $e->getMessage(),
            ]);
        }
    }

    public function deleted(Periodo $periodo)
    {
        // Actualizar los estudiantes
        Periodo::where('order', '>', $periodo->order)
            ->decrement('order');

    }
}
