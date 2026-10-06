<?php

namespace App\Exports\ListasGeneracion;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ListaGeneralSheet implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(private readonly array $reporte) {}

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
            ['Matrícula', 'Nombre', 'Apellido paterno', 'Apellido materno', 'CURP', 'Modalidad', 'Mes ingreso'],
        ];

        foreach ($this->reporte['listas'] as $lista) {
            $filas[] = [
                'LICENCIATURA EN ' . mb_strtoupper($lista['licenciatura']->nombre) . ' — TOTAL: ' . $lista['total'],
                '',
                '',
                '',
                '',
                '',
                '',
            ];

            if ($lista['alumnos']->isEmpty()) {
                $filas[] = ['SIN ALUMNOS REGISTRADOS PARA EL FILTRO SELECCIONADO', '', '', '', '', '', ''];
                continue;
            }

            foreach ($lista['alumnos'] as $alumno) {
                $filas[] = [
                    $alumno->matricula ?: $alumno->matricula_interna ?: '—',
                    trim((string) $alumno->nombre),
                    trim((string) $alumno->apellido_paterno),
                    trim((string) $alumno->apellido_materno),
                    trim((string) $alumno->CURP),
                    $this->nombreModalidad($alumno->modalidad?->nombre),
                    $this->reporte['mesIngreso'],
                ];
            }
        }

        $filas[] = [];
        $filas[] = ['', '', '', '', '', 'HOMBRES', $this->reporte['totalHombres']];
        $filas[] = ['', '', '', '', '', 'MUJERES', $this->reporte['totalMujeres']];
        $filas[] = ['', '', '', '', '', 'LOCALES', $this->reporte['totalLocales']];
        $filas[] = ['', '', '', '', '', 'FORÁNEOS', $this->reporte['totalForaneos']];
        $filas[] = ['', '', '', '', '', 'TOTAL GENERAL', $this->reporte['totalGeneral']];

        return $filas;
    }

    public function title(): string
    {
        return 'LISTA GENERAL';
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A2:G2');
        $sheet->getStyle('A1:G2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A9:G9')->getFont()->setBold(true);
        $sheet->freezePane('A10');

        $ultimaFila = $sheet->getHighestRow();
        $sheet->getStyle('F' . ($ultimaFila - 4) . ":G{$ultimaFila}")->getFont()->setBold(true);

        for ($fila = 10; $fila <= $ultimaFila - 6; $fila++) {
            $valor = (string) $sheet->getCell("A{$fila}")->getValue();

            if (str_starts_with($valor, 'LICENCIATURA EN ')) {
                $sheet->mergeCells("A{$fila}:G{$fila}");
                $sheet->getStyle("A{$fila}:G{$fila}")->getFont()->setBold(true);
            }
        }

        return [];
    }

    private function nombreModalidad(?string $modalidad): string
    {
        if (! $modalidad) {
            return 'No registrada';
        }

        return mb_convert_case(mb_strtolower(trim($modalidad), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }
}
