<?php

namespace App\Exports\PlantillaDocente;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PlantillaDocenteExport implements WithMultipleSheets
{
    public function __construct(private readonly array $reporte, private readonly array $escuela) {}

    public function sheets(): array
    {
        $sheets = [new ResumenPlantillaSheet($this->reporte, $this->escuela)];
        $sheets[] = new DocentesPlantillaSheet(null, $this->reporte, $this->escuela, 0);
        foreach ($this->reporte['grupos'] as $indice => $grupo) {
            $sheets[] = new DocentesPlantillaSheet($grupo, $this->reporte, $this->escuela, $indice + 1);
        }
        return $sheets;
    }
}
