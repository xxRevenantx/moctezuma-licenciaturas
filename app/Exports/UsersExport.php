<?php

namespace App\Exports;

use App\Models\User;
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

class UsersExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected $usuarios;

    public function __construct(Collection $usuarios)
    {
        $this->usuarios = $usuarios;
    }

    public function collection()
    {
        return $this->usuarios->map(function ($user) {
            return [
                "Usuario" => $user->username,
                "Correo electrónico" => $user->email,
                "Status" => $user->status == 'true' ? 'Activo' : 'Inactivo',
                "Rol" => $user->roles->pluck('name')->implode(', '),
                "Fecha de creación" => $user->created_at->format('d-m-Y'),

            ];
        });
    }

    public function headings(): array
    {
        return [
            'Usuario',
            'Correo electrónico',
            'Status',
            'Rol',
            'Fecha de creación',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo del encabezado
        $sheet->getStyle('A1:E1')->applyFromArray([
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
                $rowCount = count($this->usuarios) + 1;
                $range = "A1:E{$rowCount}";

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
                    $color = $cellValue === 'Activo' ? '00FF00' : 'FF0000'; // Green for "Activa", Red for "Inactivo"

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
