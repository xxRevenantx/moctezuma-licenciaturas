<?php

namespace App\Exports\PlantillaDocente;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ResumenPlantillaSheet implements FromArray, WithEvents, WithTitle
{
    public function __construct(private readonly array $reporte, private readonly array $escuela) {}

    public function title(): string { return 'Resumen'; }

    public function array(): array
    {
        $totales = $this->reporte['totales'];
        $filas = [
            [$this->escuela['nombre'] ?? 'CENTRO UNIVERSITARIO MOCTEZUMA A.C.'],
            ['PLANTILLA DOCENTE · RESUMEN INSTITUCIONAL'],
            ['Ciclo: '.$this->reporte['ciclo'].'  |  Periodo: '.$this->reporte['periodo'].'  |  Corte: '.$this->reporte['fecha']],
            ['Fuente: profesores con horario programado'],
            ['Licenciatura', 'Profesores', 'Bloques programados', 'Carga horaria (hh:mm)'],
        ];
        foreach ($this->reporte['grupos'] as $g) {
            $filas[] = [$g['licenciatura'], $g['total_docentes'], $g['bloques'], $g['horas']];
        }
        $filas[] = ['TOTAL PARTICIPACIONES', $totales['participaciones'], $totales['bloques'], $totales['horas']];
        $filas[] = ['PROFESORES ÚNICOS EN TODAS LAS LICENCIATURAS', $totales['profesores_unicos']];
        return $filas;
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event): void {
            $sheet = $event->sheet->getDelegate();
            $end = 5 + count($this->reporte['grupos']) + 2;
            foreach ([1,2,3,4] as $fila) { $sheet->mergeCells("A{$fila}:D{$fila}"); }
            $sheet->setShowGridlines(false);
            $sheet->getStyle('A1:D1')->getFont()->setBold(true)->setSize(13)->getColor()->setRGB('006492');
            $sheet->getStyle('A2:D2')->applyFromArray(['font' => ['bold'=>true, 'color'=>['rgb'=>'FFFFFF']], 'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'006492']]]);
            $sheet->getStyle('A5:D5')->applyFromArray(['font'=>['bold'=>true], 'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'C8E2BA']]]);
            $sheet->getStyle("A".($end-1).":D{$end}")->getFont()->setBold(true);
            $sheet->getStyle("B6:D{$end}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            foreach (['A'=>56,'B'=>18,'C'=>23,'D'=>23] as $col=>$ancho) { $sheet->getColumnDimension($col)->setWidth($ancho); }
            $sheet->freezePane('B6');
            $sheet->getPageSetup()->setOrientation('landscape');
            $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LETTER);
            $sheet->getPageSetup()->setFitToPage(true);
            $sheet->getPageSetup()->setFitToWidth(1);
            $sheet->getPageSetup()->setFitToHeight(0);
        }];
    }
}
