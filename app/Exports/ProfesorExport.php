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
              'telefono' => $profesor->telefono,
              'perfil' => $profesor->perfil,
              'color' => $profesor->color,


            ];
        });
    }

    public function headings(): array
    {
        return [
            'Apellido Paterno',
            'Apellido Materno',
            'Nombre',
            'Teléfono',
            'Perfil',
            'Color',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo del encabezado
        $sheet->getStyle('A1:F1')->applyFromArray([
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
                // La columna "Color" es la F (índice 6)
                for ($row = 2; $row <= $highestRow; $row++) {
                    $colorValue = $sheet->getCell('F' . $row)->getValue();
                    // Limpiar el valor y asegurarse que sea un color hexadecimal válido
                    $hexColor = ltrim($colorValue, '#');
                    if (preg_match('/^[A-Fa-f0-9]{6}$/', $hexColor)) {
                        $sheet->getStyle('F' . $row)->getFill()->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB($hexColor);
                    }
                }
            },
        ];
    }

}
