<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Mantiene las columnas históricas y distingue disponibilidad docente de acceso. */
class ProfesorExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    public function __construct(protected Collection $profesores) {}

    public function collection()
    {
        return $this->profesores->map(fn ($profesor) => [
            'apellido_paterno' => $profesor->apellido_paterno,
            'apellido_materno' => $profesor->apellido_materno,
            'nombre' => $profesor->nombre,
            'CURP' => $profesor->user?->CURP,
            'email' => $profesor->user?->email,
            'telefono' => $profesor->telefono,
            'perfil' => $profesor->perfil,
            'color' => $profesor->color,
            'disponibilidad' => $profesor->status === 'true' ? 'Disponible' : 'No disponible',
            'acceso' => $profesor->user?->status === 'true' ? 'Activo' : 'Pendiente / inactivo',
            'asignaciones' => $profesor->asignacion_materias_count ?? $profesor->asignacionMaterias()->count(),
        ]);
    }

    public function headings(): array
    {
        return [
            'Apellido Paterno', 'Apellido Materno', 'Nombre', 'CURP', 'Email',
            'Teléfono', 'Perfil', 'Color', 'Disponibilidad docente',
            'Acceso al sistema', 'Materias asignadas',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '08749C'],
            ],
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $max = $sheet->getHighestRow();
                $sheet->getStyle("A1:K{$max}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'E5E7EB'],
                        ],
                    ],
                ]);
                $sheet->freezePane('A2');
                if ($max < 2) {
                    return;
                }
                $sheet->getStyle("D2:D{$max}")->getNumberFormat()->setFormatCode('@');
                $sheet->getStyle("F2:F{$max}")->getNumberFormat()->setFormatCode('@');

                for ($row = 2; $row <= $max; $row++) {
                    $colorValue = $sheet->getCell("H{$row}")->getValue();
                    $color = ltrim((string) $colorValue, '#');
                    if (preg_match('/^[0-9a-fA-F]{6}$/', $color)) {
                        $sheet->getStyle("H{$row}")->getFill()->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB($color);
                    }
                    foreach (['I' => 'Disponible', 'J' => 'Activo'] as $columna => $positivo) {
                        $valor = $sheet->getCell("{$columna}{$row}")->getValue();
                        $sheet->getStyle("{$columna}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB($valor === $positivo ? 'DCFCE7' : 'FEF3C7');
                    }
                }
            },
        ];
    }
}
