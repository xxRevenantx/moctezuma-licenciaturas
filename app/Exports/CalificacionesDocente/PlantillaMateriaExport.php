<?php

namespace App\Exports\CalificacionesDocente;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PlantillaMateriaExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        private readonly object $contexto,
        private readonly Collection $alumnos,
        private readonly array $calificaciones = [],
    ) {
    }

    public function sheets(): array
    {
        return [
            new InstruccionesSheet([$this->contexto]),
            new PlantillaMateriaSheet($this->contexto, $this->alumnos, $this->calificaciones),
        ];
    }
}
