<?php

namespace App\Exports;

use App\Exports\Estadistica\DesgloseEstadisticaSheet;
use App\Exports\Estadistica\ResumenEstadisticaSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EstadisticaExport implements WithMultipleSheets
{
    public function __construct(
        private readonly array $reporte,
        private readonly array $institucion = [],
    ) {
    }

    public function sheets(): array
    {
        return [
            new ResumenEstadisticaSheet($this->reporte, $this->institucion),
            new DesgloseEstadisticaSheet($this->reporte),
        ];
    }
}
