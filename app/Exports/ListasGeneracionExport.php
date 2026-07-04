<?php

namespace App\Exports;

use App\Exports\ListasGeneracion\LicenciaturaSheet;
use App\Exports\ListasGeneracion\ListaGeneralSheet;
use App\Exports\ListasGeneracion\ResumenSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ListasGeneracionExport implements WithMultipleSheets
{
    public function __construct(private readonly array $reporte)
    {
    }

    public function sheets(): array
    {
        $sheets = [new ResumenSheet($this->reporte)];
        $usados = ['RESUMEN'];

        foreach ($this->reporte['listas'] as $lista) {
            $nombre = mb_substr($lista['licenciatura']->nombre_corto ?: $lista['licenciatura']->nombre, 0, 25);
            $base = preg_replace('/[\\\/\?\*\[\]:]/u', '', $nombre) ?: 'LICENCIATURA';
            $titulo = $base;
            $contador = 2;

            while (in_array(mb_strtoupper($titulo), $usados, true)) {
                $titulo = mb_substr($base, 0, 22) . " {$contador}";
                $contador++;
            }

            $usados[] = mb_strtoupper($titulo);
            $sheets[] = new LicenciaturaSheet($this->reporte, $lista, $titulo);
        }

        $sheets[] = new ListaGeneralSheet($this->reporte);

        return $sheets;
    }
}
