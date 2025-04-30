<?php

namespace App\Exports;

use App\Models\Licenciatura;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Events\AfterSheet;

class LicenciaturaExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected $licenciaturas;

    public function __construct($licenciaturas)
    {
        $this->licenciaturas = $licenciaturas;
    }

    public function collection()
    {
        return $this->licenciaturas->map(function ($lic) {
            return [
                'Nombre'        => $lic->nombre,
                'Nombre corto'  => $lic->nombre_corto,
                'RVOE'          => $lic->RVOE,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Nombre corto',
            'RVOE',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Encabezado estilizado: fondo verde y texto blanco
        $sheet->getStyle('A1:C1')->applyFromArray([
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
                $rowCount = $this->licenciaturas->count();
                $cellRange = 'A1:C' . ($rowCount + 1); // Desde encabezado hasta la última fila

                // Bordes para toda la tabla
                $event->sheet->getStyle($cellRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
