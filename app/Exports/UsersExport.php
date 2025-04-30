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
                $user->username,
                $user->email,
                $user->matricula,
                $user->status ? 'Activo' : 'Inactivo',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Usuario',
            'Correo electrónico',
            'Matrícula',
            'Estado',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo del encabezado
        $sheet->getStyle('A1:D1')->applyFromArray([
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
                $rowCount = $this->usuarios->count();
                $estadoCol = 'D';

                // Bordes generales
                $event->sheet->getStyle("A1:D" . ($rowCount + 1))->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Estilos condicionales para estado (columna D)
                for ($row = 2; $row <= $rowCount + 1; $row++) {
                    $cell = $estadoCol . $row;
                    $estado = $event->sheet->getDelegate()->getCell($cell)->getValue();

                    if ($estado === 'Activo') {
                        $event->sheet->getStyle($cell)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'C8E6C9'], // verde claro
                            ],
                        ]);
                    } elseif ($estado === 'Inactivo') {
                        $event->sheet->getStyle($cell)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'FFCDD2'], // rojo claro
                            ],
                        ]);
                    }
                }
            },
        ];
    }
}
