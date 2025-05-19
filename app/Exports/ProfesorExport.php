<?php

namespace App\Exports;

use App\Models\Profesor;
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

class ProfesorExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected $profesores;

    public function __construct(Collection $profesores)
    {
        $this->profesores = $profesores;
    }

    public function collection()
    {
        return $this->profesores->map(function ($profesor) {
            return [
               'apellido_paterno' => $profesor->apellido_paterno,
              'apellido_materno' => $profesor->apellido_materno,
              'nombre' => $profesor->nombre,
              'CURP'=> $profesor->user->CURP,
              'email' => $profesor->user->email,
              'telefono' => $profesor->telefono,
              'perfil' => $profesor->perfil,
              'color' => $profesor->color,
              'status' => $profesor->user->status,

            ];
        });
    }

    public function headings(): array
    {
        return [
            'Apellido Paterno',
            'Apellido Materno',
            'Nombre',
            'CURP',
            'Email',
            'Teléfono',
            'Perfil',
            'Color',
            'Status',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo del encabezado
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
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
                $sheet = $event->sheet;
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                // Aplicar bordes a todas las celdas
                $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);

                // Colorear la columna "Color" según el valor de la propiedad color
                // La columna "Color" es la H (índice 6)
                for ($row = 2; $row <= $highestRow; $row++) {
                    $colorValue = $sheet->getCell('H' . $row)->getValue();
                    // Limpiar el valor y asegurarse que sea un color hexadecimal válido
                    $hexColor = ltrim($colorValue, '#');
                    if (preg_match('/^[A-Fa-f0-9]{6}$/', $hexColor)) {
                        $sheet->getStyle('H' . $row)->getFill()->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB($hexColor);
                    }
                }


                for ($row = 2; $row <= $highestRow; $row++) {
                    $cellValue = $event->sheet->getCell("I{$row}")->getValue();
                    if ($cellValue === 'true') {
                        $event->sheet->setCellValue("I{$row}", 'Activo');
                        $color = '00FF00';
                    } else {
                         $event->sheet->setCellValue("I{$row}", 'Inactivo');
                        $color = 'ff6464';
                    }

                    $event->sheet->getStyle("I{$row}")->applyFromArray([
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
