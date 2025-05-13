<?php

namespace App\Exports;

use App\Models\Inscripcion;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Events\AfterSheet;

class MatriculaExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected $matricula;

    public function __construct(Collection $matricula)
    {
        $this->matricula = $matricula;
    }

    public function collection()
    {
        return $this->matricula->map(function ($estudiante) {
            return [
                "#" => $estudiante->orden,
                "Matrícula" => $estudiante->matricula,
                "CURP" => $estudiante->CURP,
                "Nombre" => $estudiante->nombre,
                "Apellido Paterno" => $estudiante->apellido_paterno,
                "Apellido Materno" => $estudiante->apellido_materno,
                'Género' => $estudiante->sexo,
                'Cuatrimestre' => $estudiante->cuatrimestre->nombre_cuatrimestre,
                'Generación' => $estudiante->generacion->generacion,
                'status'=> $estudiante->status == 'true' ? 'Activo' : 'Inactivo',
                "Fecha de creación" => $estudiante->created_at->format('d-m-Y'),

            ];
        });
    }

    public function headings(): array
    {
        return [
            '#',
            'Matrícula',
            'CURP',
            'Nombre',
            'Apellido Paterno',
            'Apellido Materno',
            'Género',
            'Cuatrimestre',
            'Generación',
            'Status',
            'Fecha de creación',
        ];
    }


     public function styles(Worksheet $sheet)
    {
        // Estilo del encabezado
        $sheet->getStyle('A1:K1')->applyFromArray([
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
                $rowCount = count($this->matricula) + 1;
                $range = "A1:K{$rowCount}";

                // Apply border styles
                $event->sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Apply conditional formatting for the "Estatus" column (J9:J$rowCount)
                for ($row = 9; $row <= $rowCount; $row++) {
                    $cellValue = $event->sheet->getCell("J{$row}")->getValue();
                    $color = $cellValue === 'Activo' ? '00FF00' : 'FF0000'; // Green for "Activa", Red for "Inactivo"

                    $event->sheet->getStyle("J{$row}")->applyFromArray([
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
