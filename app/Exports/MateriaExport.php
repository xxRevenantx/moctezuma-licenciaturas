<?php

namespace App\Exports;

use App\Models\Materia;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Events\AfterSheet;

class MateriaExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected $materia;

    public function __construct(Collection $materia)
    {
        $this->materia = $materia;
    }

    public function collection()
    {
        return $this->materia->map(function ($mat) {
            return [
                "#" => $mat->id,
                "Nombre" => $mat->nombre,
                "Slug" => $mat->slug,
                "Clave" => $mat->clave,
                "Créditos" => $mat->creditos,
                "Cuatrimestre" => $mat->cuatrimestre->nombre_cuatrimestre,
                "Licenciatura" => $mat->licenciatura->nombre,
                "Calificable" => $mat->calificable == 'true' ? 'Sí' : 'No',
                "Fecha de creación" => $mat->created_at->format('d-m-Y'),

            ];
        });
    }

    public function headings(): array
    {
        return [
            '#',
            'Nombre',
            'Slug',
            'Clave',
            'Créditos',
            'Cuatrimestre',
            'Licenciatura',
            'Calificable',
            'Fecha de creación',
        ];
    }

     public function styles(Worksheet $sheet)
    {
        // Estilo del encabezado
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => '4F81BD'],
            ],
        ]);
    }

      public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $rowCount = count($this->materia) + 1;
                $range = "A1:I{$rowCount}";

                // Apply border styles
                $event->sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);


                for ($row = 2; $row <= $rowCount; $row++) {
                    $cellValue = $event->sheet->getCell("H{$row}")->getValue();


                     if ($cellValue === 'Sí') {
                        $color = '00FF00';
                    } else {
                        $color = 'ff6464';
                    }

                    $event->sheet->getStyle("H{$row}")->applyFromArray([
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
