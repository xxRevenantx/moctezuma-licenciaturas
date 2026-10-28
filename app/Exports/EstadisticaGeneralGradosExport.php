<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EstadisticaGeneralGradosExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    private array $filasEspeciales = [];

    public function __construct(
        private readonly array $reporte,
        private readonly array $escuela = [],
    ) {
    }

    public function array(): array
    {
        $filas = [
            [$this->escuela['nombre'] ?? 'CENTRO UNIVERSITARIO MOCTEZUMA A.C.'],
            ['C.C.T. ' . ($this->escuela['CCT'] ?? '12PSU0173I')],
            ['ESTADÍSTICA GENERAL POR GRADOS'],
            ['Fecha de corte', \Carbon\Carbon::parse($this->reporte['fecha_corte'])->format('d/m/Y')],
            [($this->reporte['alertas_avance']['total_pendientes'] ?? 0) > 0
                ? 'AVISO: '.$this->reporte['alertas_avance']['total_pendientes'].' alumno(s) por revisar de cuatrimestre; cifras según matrícula registrada.' : ''],
        ];
        $this->filasEspeciales = [];

        foreach ($this->reporte['licenciaturas'] as $licenciatura) {
            $filas[] = [mb_strtoupper($licenciatura['nombre'])];
            $this->filasEspeciales['licenciaturas'][] = count($filas);
            $filas[] = ['GRADO', 'HOMBRES', 'MUJERES', 'TOTAL'];
            $this->filasEspeciales['encabezados'][] = count($filas);
            foreach ($licenciatura['grados'] as $grado) {
                $filas[] = [$grado['grado'] . 'º GRADO', $grado['hombres'], $grado['mujeres'], $grado['total']];
                $this->filasEspeciales['datos'][] = count($filas);
            }
            $filas[] = ['TOTAL LICENCIATURA', $licenciatura['totales']['hombres'], $licenciatura['totales']['mujeres'], $licenciatura['totales']['total']];
            $this->filasEspeciales['subtotales'][] = count($filas);
            $filas[] = [];
        }
        if ($this->reporte['licenciaturas'] === []) {
            $filas[] = ['Sin alumnos activos en generaciones vigentes'];
        }
        $filas[] = ['TOTAL GENERAL', $this->reporte['totales']['hombres'], $this->reporte['totales']['mujeres'], $this->reporte['totales']['total']];
        $this->filasEspeciales['final'] = count($filas);

        return $filas;
    }

    public function title(): string
    {
        return 'Estadística por grados';
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $evento): void {
            $hoja = $evento->sheet->getDelegate();
            $hoja->setShowGridlines(false);
            $hoja->mergeCells('A1:D1');
            $hoja->mergeCells('A2:D2');
            $hoja->mergeCells('A3:D3');
            $hoja->mergeCells('A5:D5');
            $hoja->getStyle('A5:D5')->getFont()->getColor()->setRGB('955800');
            $hoja->getRowDimension(1)->setRowHeight(27);
            $hoja->getStyle('A1:D1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '006492']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $hoja->getStyle('A2:D2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $hoja->getStyle('A3:D3')->applyFromArray([
                'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '006492']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            foreach ($this->filasEspeciales['licenciaturas'] ?? [] as $fila) {
                $hoja->mergeCells("A{$fila}:D{$fila}");
                $hoja->getStyle("A{$fila}:D{$fila}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '006492']],
                ]);
                $hoja->getRowDimension($fila)->setRowHeight(23);
            }
            foreach ($this->filasEspeciales['encabezados'] ?? [] as $fila) {
                $hoja->getStyle("A{$fila}:D{$fila}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C8E2BA']],
                ]);
            }
            foreach ($this->filasEspeciales['datos'] ?? [] as $fila) {
                $hoja->getStyle("A{$fila}:D{$fila}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'A6B8C3']]],
                ]);
                $hoja->getStyle("D{$fila}")->getFont()->setBold(true);
            }
            foreach ($this->filasEspeciales['subtotales'] ?? [] as $fila) {
                $hoja->getStyle("A{$fila}:D{$fila}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7F3DC']],
                ]);
            }
            $final = $this->filasEspeciales['final'];
            $hoja->getStyle("A{$final}:D{$final}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '006492']],
            ]);
            $hoja->getStyle("B4:D{$final}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $hoja->getColumnDimension('A')->setWidth(54);
            foreach (['B', 'C', 'D'] as $col) {
                $hoja->getColumnDimension($col)->setWidth(19);
            }
            $hoja->getPageSetup()->setOrientation('portrait');
            $hoja->getPageSetup()->setFitToWidth(1);
            $hoja->getPageSetup()->setFitToHeight(0);
            $hoja->getPageSetup()->setFitToPage(true);
            $hoja->freezePane('A6');
            $hoja->getStyle("A1:D{$final}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        }];
    }
}
