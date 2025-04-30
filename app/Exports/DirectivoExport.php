<?php

namespace App\Exports;

use App\Models\Directivo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Events\AfterSheet;

class DirectivoExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    public function collection()
    {
        return Directivo::select(
            'id',
            'titulo',
            'nombre',
            'apellido_paterno',
            'apellido_materno',
            'telefono',
            'correo',
            'cargo'
        )->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Título',
            'Nombre',
            'Apellido Paterno',
            'Apellido Materno',
            'Teléfono',
            'Correo',
            'Cargo',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Estilos para el encabezado
        $sheet->getStyle('A1:H1')->applyFromArray([
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
                $rowCount = Directivo::count(); // número de filas de datos
                $range = 'A1:H' . ($rowCount + 1); // desde encabezado hasta la última fila

                // Aplicar bordes a toda la tabla
                $event->sheet->getStyle($range)->applyFromArray([
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
