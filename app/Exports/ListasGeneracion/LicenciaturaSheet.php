<?php

namespace App\Exports\ListasGeneracion;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LicenciaturaSheet implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(
        private readonly array $reporte,
        private readonly array $lista,
        private readonly string $titulo
    ) {}

    public function array(): array
    {
        $filas = [
            ['CENTRO UNIVERSITARIO MOCTEZUMA'],
            ['Licenciatura', $this->lista['licenciatura']->nombre],
            ['Generación', $this->reporte['generacion']->generacion],
            ['Procedencia', $this->reporte['procedenciaTexto']],
            [],
            ['N.º', 'Matrícula', 'Nombre', 'Apellido paterno', 'Apellido materno', 'Procedencia'],
        ];

        if ($this->lista['alumnos']->isEmpty()) {
            $filas[] = ['', '', 'SIN ALUMNOS REGISTRADOS PARA EL FILTRO SELECCIONADO', '', '', ''];
        } else {
            foreach ($this->lista['alumnos'] as $indice => $alumno) {
                $filas[] = [
                    $indice + 1,
                    $alumno->matricula,
                    trim((string) $alumno->nombre),
                    trim((string) $alumno->apellido_paterno),
                    trim((string) $alumno->apellido_materno),
                    $alumno->foraneo === 'true' ? 'FORÁNEO' : 'LOCAL',
                ];
            }
        }

        $filas[] = [];
        $filas[] = ['', '', '', '', 'TOTAL', $this->lista['total']];

        return $filas;
    }

    public function title(): string
    {
        return $this->titulo;
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1:F1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A6:F6')->getFont()->setBold(true);
        $sheet->freezePane('A7');

        return [];
    }
}
