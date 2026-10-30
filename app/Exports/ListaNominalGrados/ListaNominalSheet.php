<?php

namespace App\Exports\ListaNominalGrados;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ListaNominalSheet extends DefaultValueBinder implements FromArray, WithEvents, WithTitle, WithCustomValueBinder
{
    public function __construct(private readonly array $grupo, private readonly array $reporte,
                                private readonly array $escuela, private readonly int $orden) {}

    // Excel nunca interpreta nombres importados como fórmulas, incluso si empiezan por "=".
    public function bindValue(Cell $cell, $value): bool
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }
        return parent::bindValue($cell, $value);
    }

    public function title(): string
    {
        $prefijo = str_pad((string) $this->orden, 2, '0', STR_PAD_LEFT).' G'.$this->grupo['grado'].' ';
        $nombre = preg_replace('/[\\\\\/*?:\[\]]/u', '', $this->grupo['licenciatura']);
        return mb_substr($prefijo.$nombre, 0, 31);
    }

    public function array(): array
    {
        $filas = [
            [$this->escuela['nombre'] ?? 'CENTRO UNIVERSITARIO MOCTEZUMA A.C.'],
            ['LISTA NOMINAL POR LICENCIATURA Y GRADO'],
            ['Licenciatura:', $this->grupo['licenciatura']],
            ['Grado:', $this->grupo['grado'].'º GRADO'],
            ['Modalidad(es): '.$this->reporte['modalidades'].' · Generación(es): '.$this->reporte['generaciones']],
            ['Fecha de corte: '.\Carbon\Carbon::parse($this->reporte['fecha_corte'])->format('d/m/Y')],
            ['No.', 'Apellido Paterno', 'Apellido Materno', 'Nombre', 'Edad'],
        ];
        foreach ($this->grupo['alumnos'] as $indice => $alumno) {
            $filas[] = [$indice + 1, $alumno['apellido_paterno'], $alumno['apellido_materno'], $alumno['nombre'], $alumno['edad']];
        }
        for ($i = 0; $i < $this->reporte['renglones_vacios']; $i++) {
            $filas[] = ['', '', '', '', ''];
        }
        $filas[] = [];
        $filas[] = ['HOMBRES', 'MUJERES', 'TOTALES'];
        $filas[] = [$this->grupo['totales']['hombres'], $this->grupo['totales']['mujeres'], $this->grupo['totales']['total']];
        return $filas;
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $evento): void {
            $hoja = $evento->sheet->getDelegate();
            $hoja->setShowGridlines(false);
            foreach ([1, 2, 5, 6] as $fila) {
                $hoja->mergeCells("A{$fila}:E{$fila}");
            }
            $hoja->mergeCells('B3:E3');
            $hoja->mergeCells('B4:E4');
            $hoja->getStyle('A1:E1')->applyFromArray(['font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '006492']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
            $hoja->getStyle('A2:E2')->applyFromArray(['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '006492']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
            $hoja->getStyle('A3:A4')->getFont()->setBold(true);
            $hoja->getStyle('A7:E7')->applyFromArray(['font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B8CAE8']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]]);
            $n = count($this->grupo['alumnos']) + $this->reporte['renglones_vacios'];
            if ($n) {
                $fin = 7 + $n;
                $hoja->getStyle("A8:E{$fin}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $hoja->getStyle("A8:A{$fin}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $hoja->getStyle("E8:E{$fin}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                for ($fila = 9; $fila <= $fin; $fila += 2) {
                    $hoja->getStyle("A{$fila}:E{$fila}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F6F8');
                }
            }
            $resumen = 9 + $n;
            $hoja->getStyle("A{$resumen}:C".($resumen + 1))->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'font' => ['bold' => true],
            ]);
            $hoja->getStyle("A{$resumen}:C{$resumen}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('C8E2BA');
            $hoja->getColumnDimension('A')->setWidth(9);
            $hoja->getColumnDimension('B')->setWidth(26);
            $hoja->getColumnDimension('C')->setWidth(26);
            $hoja->getColumnDimension('D')->setWidth(33);
            $hoja->getColumnDimension('E')->setWidth(12);
            $hoja->getRowDimension(1)->setRowHeight(25);
            $hoja->getRowDimension(2)->setRowHeight(24);
            $hoja->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LETTER);
            $hoja->getPageSetup()->setOrientation('portrait');
            $hoja->getPageSetup()->setFitToPage(true);
            $hoja->getPageSetup()->setFitToWidth(1);
            $hoja->getPageSetup()->setFitToHeight(0);
            $hoja->getPageMargins()->setTop(0.5);
            $hoja->getPageMargins()->setBottom(0.5);
            $hoja->getPageMargins()->setLeft(0.25);
            $hoja->getPageMargins()->setRight(0.25);
            $hoja->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(7, 7);
            $hoja->freezePane('B8');
        }];
    }
}
