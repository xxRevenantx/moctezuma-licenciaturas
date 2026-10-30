<?php

namespace App\Exports;

use App\Exports\ListaNominalGrados\ResumenNominalSheet;
use App\Exports\ListaNominalGrados\ListaNominalSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ListaNominalGradosExport implements WithMultipleSheets
{
    public function __construct(private readonly array $reporte, private readonly array $escuela) {}

    public function sheets(): array
    {
        $hojas = [new ResumenNominalSheet($this->reporte, $this->escuela)];
        foreach ($this->reporte['grupos'] as $indice => $grupo) {
            $hojas[] = new ListaNominalSheet($grupo, $this->reporte, $this->escuela, $indice + 1);
        }
        return $hojas;
    }
}
