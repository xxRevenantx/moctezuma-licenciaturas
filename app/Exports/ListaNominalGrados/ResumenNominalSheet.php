<?php

namespace App\Exports\ListaNominalGrados;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ResumenNominalSheet extends DefaultValueBinder implements FromArray, WithTitle, WithEvents, WithCustomValueBinder
{
    public function __construct(private readonly array $reporte, private readonly array $escuela) {}

    public function bindValue(Cell $cell, $value): bool
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }
        return parent::bindValue($cell, $value);
    }

    public function title(): string { return 'Resumen general'; }

    public function array(): array
    {
        $filas = [
            [$this->escuela['nombre'] ?? 'CENTRO UNIVERSITARIO MOCTEZUMA A.C.'],
            ['LISTAS NOMINALES POR LICENCIATURA Y GRADO'],
            ['Corte: '.\Carbon\Carbon::parse($this->reporte['fecha_corte'])->format('d/m/Y')],
            ['Modalidad(es): '.$this->reporte['modalidades'].' · Generación(es): '.$this->reporte['generaciones']],
            [],
            ['LICENCIATURA', 'GRADO', 'HOMBRES', 'MUJERES', 'TOTAL'],
        ];
        foreach ($this->reporte['grupos'] as $grupo) {
            $filas[] = [$grupo['licenciatura'], $grupo['grado'].'º', $grupo['totales']['hombres'], $grupo['totales']['mujeres'], $grupo['totales']['total']];
        }
        $filas[] = ['TOTAL GENERAL', '', $this->reporte['totales']['hombres'], $this->reporte['totales']['mujeres'], $this->reporte['totales']['total']];
        return $filas;
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $evento): void {
            $hoja = $evento->sheet->getDelegate();
            $hoja->setShowGridlines(false);
            foreach ([1, 2, 3, 4] as $fila) { $hoja->mergeCells("A{$fila}:E{$fila}"); }
            $hoja->getStyle('A1:E1')->getFont()->setBold(true)->setSize(13)->getColor()->setRGB('006492');
            $hoja->getStyle('A2:E2')->getFont()->setBold(true)->setSize(12);
            $hoja->getStyle('A6:E6')->applyFromArray(['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '006492']]]);
            $final = 7 + count($this->reporte['grupos']);
            $hoja->getStyle("A6:E{$final}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $hoja->getStyle("A{$final}:E{$final}")->applyFromArray(['font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C8E2BA']]]);
            $hoja->getColumnDimension('A')->setWidth(54);
            $hoja->getColumnDimension('B')->setWidth(13);
            foreach (['C','D','E'] as $col) { $hoja->getColumnDimension($col)->setWidth(17); }
            $hoja->freezePane('B7');
            $hoja->getPageSetup()->setFitToPage(true);
            $hoja->getPageSetup()->setFitToWidth(1);
            $hoja->getPageSetup()->setFitToHeight(0);
        }];
    }
}
