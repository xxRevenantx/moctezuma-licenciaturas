<?php

namespace App\Exports\ListasGeneracion;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ListaGeneralSheet implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(private readonly array $reporte)
    {
    }

    public function array(): array
    {
        $filas = [
            ['CENTRO UNIVERSITARIO MOCTEZUMA'],
            ['LISTA GENERAL DE ALUMNOS DE LA GENERACIÓN'],
            ['Generación', $this->reporte['generacion']->generacion],
            ['Ciclo escolar', $this->reporte['cicloEscolar']],
            ['Periodo escolar', $this->reporte['periodoEscolar']],
            ['Procedencia', $this->reporte['procedenciaTexto']],
            ['Fecha de emisión', $this->reporte['fechaEmision']->format('d/m/Y')],
            [],
            ['N.º', 'Matrícula', 'Nombre completo', 'Licenciatura', 'Generación', 'Procedencia'],
        ];

        foreach ($this->reporte['listas'] as $lista) {
            $filas[] = [
                '',
                '',
                'LICENCIATURA EN '.mb_strtoupper($lista['licenciatura']->nombre),
                '',
                '',
                'TOTAL: '.$lista['total'],
            ];

            if ($lista['alumnos']->isEmpty()) {
                $filas[] = ['', '', 'SIN ALUMNOS REGISTRADOS PARA EL FILTRO SELECCIONADO', '', '', ''];
                continue;
            }

            foreach ($lista['alumnos'] as $indice => $alumno) {
                $filas[] = [
                    $indice + 1,
                    $alumno->matricula,
                    trim("{$alumno->apellido_paterno} {$alumno->apellido_materno} {$alumno->nombre}"),
                    $lista['licenciatura']->nombre,
                    $this->reporte['generacion']->generacion,
                    $alumno->foraneo === 'true' ? 'FORÁNEO' : 'LOCAL',
                ];
            }
        }

        $filas[] = [];
        $filas[] = ['', '', '', '', 'HOMBRES', $this->reporte['totalHombres']];
        $filas[] = ['', '', '', '', 'MUJERES', $this->reporte['totalMujeres']];
        $filas[] = ['', '', '', '', 'LOCALES', $this->reporte['totalLocales']];
        $filas[] = ['', '', '', '', 'FORÁNEOS', $this->reporte['totalForaneos']];
        $filas[] = ['', '', '', '', 'TOTAL GENERAL', $this->reporte['totalGeneral']];

        return $filas;
    }

    public function title(): string
    {
        return 'LISTA GENERAL';
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A1:F2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A9:F9')->getFont()->setBold(true);
        $sheet->freezePane('A10');

        $ultimaFila = $sheet->getHighestRow();
        $sheet->getStyle("E".($ultimaFila - 4).":F{$ultimaFila}")->getFont()->setBold(true);

        for ($fila = 10; $fila <= $ultimaFila - 6; $fila++) {
            $valor = (string) $sheet->getCell("C{$fila}")->getValue();

            if (str_starts_with($valor, 'LICENCIATURA EN ')) {
                $sheet->getStyle("A{$fila}:F{$fila}")->getFont()->setBold(true);
            }
        }

        return [];
    }
}
