<?php

namespace App\Exports\Estadistica;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ResumenEstadisticaSheet implements FromArray, ShouldAutoSize, WithEvents, WithStyles, WithTitle
{
    public function __construct(
        private readonly array $reporte,
        private readonly array $institucion = [],
    ) {
    }

    public function array(): array
    {
        $escuela = $this->institucion['escuela'] ?? [];
        $totales = $this->reporte['totales'];

        $filas = [
            [$escuela['nombre'] ?? 'CENTRO UNIVERSITARIO MOCTEZUMA A.C.'],
            ['C.C.T.', $escuela['CCT'] ?? '12PSU0173I'],
            ['ESTADÍSTICA COMPLETA DE LICENCIATURAS'],
            ['Fecha de generación', now()->format('d/m/Y H:i')],
            [],
            ['Ciclo escolar', 'Hombres', 'Mujeres', 'Activos', 'Bajas', 'Egresados', 'Total general'],
        ];

        foreach ($this->reporte['secciones'] as $seccion) {
            $ciclo = $seccion['totales'];
            $filas[] = [
                $seccion['ciclo_escolar'],
                $ciclo['hombres_total'],
                $ciclo['mujeres_total'],
                $ciclo['activos_total'],
                $ciclo['bajas_total'],
                $ciclo['egresados_total'],
                $ciclo['total_general'],
            ];
        }

        $filas[] = [];
        $filas[] = [
            'TOTAL GENERAL',
            $totales['hombres_total'],
            $totales['mujeres_total'],
            $totales['activos_total'],
            $totales['bajas_total'],
            $totales['egresados_total'],
            $totales['total_general'],
        ];

        return $filas;
    }

    public function title(): string
    {
        return 'Resumen general';
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A3:G3');

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 15, 'color' => ['rgb' => '006492']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            3 => [
                'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '006492']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            6 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '88AC2E']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $ultimaFila = $sheet->getHighestRow();

                $sheet->freezePane('A7');
                $sheet->setAutoFilter("A6:G{$ultimaFila}");
                $sheet->getStyle("A6:G{$ultimaFila}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D9E2E7'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle("B7:G{$ultimaFila}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("A{$ultimaFila}:G{$ultimaFila}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '006492']],
                ]);

                $sheet->getColumnDimension('A')->setWidth(25);
                foreach (range('B', 'G') as $columna) {
                    $sheet->getColumnDimension($columna)->setWidth(15);
                }
            },
        ];
    }
}
