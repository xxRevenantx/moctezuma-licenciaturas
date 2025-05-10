<?php

namespace App\Exports;

use App\Models\AsignarGeneracion;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Events\AfterSheet;

class AsignarGeneracionExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected $asignaciones;

    public function __construct($asignaciones)
    {
        $this->asignaciones = $asignaciones;
    }

    public function collection()
    {
        return $this->asignaciones->map(function ($asig) {
            return [
                'licenciatura_id' => $asig->licenciatura->nombre,
                'generacion_id'   => $asig->generacion->generacion,
                'Estatus'         => $asig->generacion->activa == 'true' ? 'Activa' : 'Inactiva',
                'modalidad_id'    => $asig->modalidad->nombre,

            ];
        });


    }

    public function headings(): array
    {
        return [
            'Licenciatura',
            'Generación',
            'Estatus',
            'Modalidad',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style for the header row
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4CAF50'],
            ],
        ]);


    }


    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $rowCount = count($this->asignaciones) + 1;
                $range = "A1:D{$rowCount}";

                // Apply border styles
                $event->sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Apply conditional formatting for the "Estatus" column (C2:C$rowCount)
                for ($row = 2; $row <= $rowCount; $row++) {
                    $cellValue = $event->sheet->getCell("C{$row}")->getValue();
                    $color = $cellValue === 'Activa' ? '00FF00' : 'FF0000'; // Green for "Activa", Red for "Inactiva"

                    $event->sheet->getStyle("C{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $color],
                        ],
                    ]);
                }
            },
        ];
    }




}
