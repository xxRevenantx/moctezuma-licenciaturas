<?php

namespace App\Exports\ListasGeneracion;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ResumenSheet implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(private readonly array $reporte)
    {
    }

    public function array(): array
    {
        $filas = [
            ['CENTRO UNIVERSITARIO MOCTEZUMA'],
            ['LISTAS POR GENERACIÓN'],
            ['Generación', $this->reporte['generacion']->generacion],
            ['Ciclo escolar', $this->reporte['cicloEscolar']],
            ['Periodo escolar', $this->reporte['periodoEscolar']],
            ['Procedencia', $this->reporte['procedenciaTexto']],
            ['Fecha de emisión', $this->reporte['fechaEmision']->format('d/m/Y')],
            [],
            ['ID', 'Licenciatura', 'Locales', 'Foráneos', 'Total'],
        ];

        foreach ($this->reporte['listas'] as $lista) {
            $filas[] = [
                $lista['licenciatura']->id,
                $lista['licenciatura']->nombre,
                $lista['locales'],
                $lista['foraneos'],
                $lista['total'],
            ];
        }

        $filas[] = [];
        $filas[] = ['', 'TOTAL GENERAL', $this->reporte['totalLocales'], $this->reporte['totalForaneos'], $this->reporte['totalGeneral']];

        return $filas;
    }

    public function title(): string
    {
        return 'RESUMEN';
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:E1');
        $sheet->mergeCells('A2:E2');
        $sheet->getStyle('A1:E2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A9:E9')->getFont()->setBold(true);
        $sheet->freezePane('A10');

        return [];
    }
}
