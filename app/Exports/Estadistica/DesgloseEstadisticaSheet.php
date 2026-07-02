<?php

namespace App\Exports\Estadistica;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DesgloseEstadisticaSheet implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithStyles, WithTitle
{
    public function __construct(private readonly array $reporte)
    {
    }

    public function collection(): Collection
    {
        return collect($this->reporte['filas'])->map(fn (array $fila) => [
            $fila['ciclo_escolar'],
            $fila['licenciatura'],
            $fila['rvoe'],
            $fila['modalidad'],
            $fila['generacion'],
            $fila['cuatrimestre'],
            $fila['activos_hombres'],
            $fila['activos_mujeres'],
            $fila['activos_total'],
            $fila['bajas_hombres'],
            $fila['bajas_mujeres'],
            $fila['bajas_total'],
            $fila['egresados_hombres'],
            $fila['egresados_mujeres'],
            $fila['egresados_total'],
            $fila['hombres_total'],
            $fila['mujeres_total'],
            $fila['total_general'],
        ]);
    }

    public function headings(): array
    {
        return [
            'Ciclo escolar',
            'Licenciatura',
            'RVOE',
            'Modalidad',
            'Generación',
            'Cuatrimestre',
            'Activos H',
            'Activos M',
            'Activos total',
            'Bajas H',
            'Bajas M',
            'Bajas total',
            'Egresados H',
            'Egresados M',
            'Egresados total',
            'Hombres total',
            'Mujeres total',
            'Total general',
        ];
    }

    public function title(): string
    {
        return 'Desglose completo';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '006492']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $ultimaFila = max(1, $sheet->getHighestRow());

                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:R{$ultimaFila}");
                $sheet->getStyle("A1:R{$ultimaFila}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D9E2E7'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle("G2:R{$ultimaFila}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getColumnDimension('A')->setWidth(16);
                $sheet->getColumnDimension('B')->setWidth(38);
                $sheet->getColumnDimension('C')->setWidth(18);
                $sheet->getColumnDimension('D')->setWidth(22);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(20);

                foreach (range('G', 'R') as $columna) {
                    $sheet->getColumnDimension($columna)->setWidth(13);
                }

                $sheet->getStyle("B2:B{$ultimaFila}")->getAlignment()->setWrapText(true);
            },
        ];
    }
}
