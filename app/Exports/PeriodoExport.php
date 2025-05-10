<?php

namespace App\Exports;

use App\Models\Periodo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Events\AfterSheet;

class PeriodoExport implements  FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected $periodos;

    public function __construct($periodos)
    {
        $this->periodos = $periodos;
    }

    public function collection()
    {
        return $this->periodos->map(function ($periodo) {
            return [
                'ciclo_escolar'   => $periodo->ciclo_escolar,
                'cuatrimestre_id' => $periodo->cuatrimestre->nombre_cuatrimestre,
                'generacion_id'   => $periodo->generacion->generacion,
                'mes_id'         => $periodo->mes->meses,
               'inicio_periodo'  => $periodo->inicio_periodo ? \Carbon\Carbon::parse($periodo->inicio_periodo)->format('d-m-Y') : null,
                'termino_periodo' => $periodo->termino_periodo ? \Carbon\Carbon::parse($periodo->termino_periodo)->format('d-m-Y') : null,


            ];
        });
    }


    public function headings(): array
    {
        return [
            'Ciclo Escolar',
            'Cuatrimestre',
            'Generación',
            'Mes',
            'Inicio Periodo',
            'Termino Periodo',
        ];
    }

       public function styles(Worksheet $sheet)
    {
        // Style for the header row
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4CAF50'],
            ],
        ]);

        return [
            // Set the font size for the entire sheet
            1 => ['font' => ['size' => 12]],
        ];

    }


    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $rowCount = count($this->periodos) + 1;
                $range = "A1:F{$rowCount}";
                $event->sheet->getDelegate()->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
            },
        ];

    }

}
