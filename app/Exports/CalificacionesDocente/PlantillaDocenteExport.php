<?php

namespace App\Exports\CalificacionesDocente;

use Closure;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PlantillaDocenteExport implements WithMultipleSheets
{
    use Exportable;

    /**
     * @param Closure(object): array{alumnos:Collection,calificaciones:array} $resolverDatos
     */
    public function __construct(
        private readonly Collection $contextos,
        private readonly Closure $resolverDatos,
    ) {
    }

    public function sheets(): array
    {
        $sheets = [new InstruccionesSheet($this->contextos)];

        foreach ($this->contextos as $contexto) {
            $datos = ($this->resolverDatos)($contexto);
            $sheets[] = new PlantillaMateriaSheet(
                $contexto,
                $datos['alumnos'],
                $datos['calificaciones']
            );
        }

        return $sheets;
    }
}
