<?php

namespace App\Services\Matriculas;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

class ExpedicionMatriculasExportService
{
    /** @param array{generacion:string,columnas:array,grupos:array} $datos */
    public function excel(array $datos, string $plantilla, string $destino): void
    {
        $workbook = new Spreadsheet();
        $columnas = $datos['columnas'];
        $last = Coordinate::stringFromColumnIndex(count($columnas));

        foreach ($datos['grupos'] as $index => $grupo) {
            $sheet = $index === 0 ? $workbook->getActiveSheet() : $workbook->createSheet();
            $titulo = preg_replace('/[\\\\\/\?\*\[\]:]/u', ' ', $grupo['licenciatura']);
            $sheet->setTitle(mb_substr(sprintf('%02d ', $index + 1) . $titulo, 0, 31));
            $this->escribirHoja($sheet, $datos['generacion'], $grupo, $columnas, $last, $plantilla);
        }

        $workbook->setActiveSheetIndex(0);
        (new Xlsx($workbook))->save($destino);
        $workbook->disconnectWorksheets();
    }

    private function escribirHoja(Worksheet $sheet, string $generacion, array $grupo, array $columnas, string $last, string $plantilla): void
    {
        $color = $plantilla === 'seg' ? 'FFFFFF' : ($plantilla === 'personalizado' ? '88AC2E' : '006492');
        $texto = $plantilla === 'seg' ? '000000' : 'FFFFFF';
        $sheet->mergeCells("A1:{$last}2");
        $sheet->setCellValue('A1', 'GENERACIÓN ' . $generacion);
        $sheet->mergeCells("A3:{$last}3");
        $sheet->setCellValue('A3', 'LICENCIATURA EN ' . mb_strtoupper($grupo['licenciatura'], 'UTF-8'));
        $sheet->getStyle("A1:{$last}3")->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle("A1:{$last}3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(21);
        $sheet->getRowDimension(2)->setRowHeight(19);
        $sheet->getRowDimension(3)->setRowHeight(29);
        $sheet->getStyle("A3:{$last}3")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

        foreach ($columnas as $j => $key) {
            $col = Coordinate::stringFromColumnIndex($j + 1);
            $sheet->setCellValue("{$col}4", ExpedicionMatriculasService::COLUMNAS[$key]);
            $anchos = ['numero' => 7, 'nombre' => 23, 'apellido_paterno' => 22, 'apellido_materno' => 22,
                'curp' => 23, 'modalidad' => 19, 'mes_ingreso' => 19, 'observaciones' => 39,
                'matricula_seg' => 23, 'matricula_interna' => 23, 'cuatrimestre' => 20, 'estado' => 17, 'fecha_inscripcion' => 21];
            $sheet->getColumnDimension($col)->setWidth($anchos[$key] ?? 20);
        }
        $sheet->getStyle("A4:{$last}4")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => $texto], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(29);
        foreach ($grupo['filas'] as $i => $fila) {
            $n = 5 + $i;
            foreach ($columnas as $j => $key) {
                $col = Coordinate::stringFromColumnIndex($j + 1);
                $val = $fila[$key] ?? '';
                if ($key === 'numero') {
                    $sheet->setCellValue("{$col}{$n}", (int) $val);
                } else {
                    // Impide que contenido de CURP, observaciones o apellidos sea interpretado como fórmula.
                    $sheet->setCellValueExplicit("{$col}{$n}", (string) $val, DataType::TYPE_STRING);
                }
            }
            $sheet->getRowDimension($n)->setRowHeight(30);
            if ($plantilla !== 'seg' && $i % 2 === 1) {
                $sheet->getStyle("A{$n}:{$last}{$n}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F7F9');
            }
        }
        $hasta = max(5, 4 + count($grupo['filas']));
        $sheet->getStyle("A4:{$last}{$hasta}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('7E8891');
        $sheet->getStyle("A4:{$last}{$hasta}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet->getStyle("A5:{$last}{$hasta}")->getFont()->setSize(10);
        $sheet->freezePane('A5');
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setOrientation('landscape')->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LETTER)->setFitToWidth(1)->setFitToHeight(0);
        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 4);
        $sheet->getPageMargins()->setTop(0.4)->setBottom(0.4)->setLeft(0.3)->setRight(0.3);
        $sheet->getHeaderFooter()->setOddFooter('&C Página &P de &N');
        $sheet->getPageSetup()->setPrintArea("A1:{$last}{$hasta}");
    }

    public function word(array $datos, string $plantilla, string $destino): void
    {
        $temp = storage_path('app/phpword-temp');
        if (! is_dir($temp)) {
            mkdir($temp, 0775, true);
        }
        Settings::setTempDir($temp);
        $word = new PhpWord();
        $word->setDefaultFontName('Calibri');
        $word->setDefaultFontSize(9);
        $columnas = $datos['columnas'];
        $color = $plantilla === 'seg' ? 'FFFFFF' : ($plantilla === 'personalizado' ? '88AC2E' : '006492');
        $fontColor = $plantilla === 'seg' ? '000000' : 'FFFFFF';
        $word->addTableStyle('MatriculasTabla', [
            'borderSize' => 5, 'borderColor' => '687987',
            'cellMarginTop' => 90, 'cellMarginBottom' => 90, 'cellMarginLeft' => 75, 'cellMarginRight' => 75,
        ]);
        // Columnas ajustadas al ancho útil de carta horizontal (aproximado en twips).
        $anchoPagina = 13900;
        $proporciones = [
            'numero' => 0.43, 'nombre' => 1.4, 'apellido_paterno' => 1.35, 'apellido_materno' => 1.35,
            'curp' => 1.5, 'modalidad' => 1.1, 'mes_ingreso' => 1.0, 'observaciones' => 1.8,
            'matricula_seg' => 1.2, 'matricula_interna' => 1.2, 'cuatrimestre' => 1.05,
            'estado' => 0.9, 'fecha_inscripcion' => 1.1,
        ];
        $total = array_sum(array_map(fn ($k) => $proporciones[$k] ?? 1, $columnas));
        $anchos = array_map(fn ($k) => (int) round($anchoPagina * ($proporciones[$k] ?? 1) / $total), $columnas);

        $section = $word->addSection([
            'orientation' => 'landscape', 'pageSizeW' => Converter::cmToTwip(27.94),
            'pageSizeH' => Converter::cmToTwip(21.59),
            'marginTop' => Converter::cmToTwip(1.5), 'marginBottom' => Converter::cmToTwip(1.5),
            'marginLeft' => Converter::cmToTwip(1.5), 'marginRight' => Converter::cmToTwip(1.5),
        ]);
        $footer = $section->addFooter();
        $footer->addPreserveText('Centro Universitario Moctezuma  |  Página {PAGE} de {NUMPAGES}',
            ['size' => 8, 'color' => '667085'], ['alignment' => Jc::CENTER]);

        foreach ($datos['grupos'] as $index => $grupo) {
            if ($index > 0) {
                $section->addPageBreak();
            }
            if ($plantilla !== 'seg') {
                $section->addText('CENTRO UNIVERSITARIO MOCTEZUMA', ['bold' => true, 'size' => 11, 'color' => '006492'], ['alignment' => Jc::CENTER, 'spaceAfter' => 120]);
            }
            $section->addText('GENERACIÓN ' . $datos['generacion'], ['bold' => true, 'size' => 16], ['alignment' => Jc::CENTER, 'spaceAfter' => 120]);
            $section->addText('LICENCIATURA EN ' . mb_strtoupper($grupo['licenciatura'], 'UTF-8'),
                ['bold' => true, 'size' => 12], ['alignment' => Jc::CENTER, 'spaceAfter' => 220]);
            $tabla = $section->addTable('MatriculasTabla');
            $tabla->addRow(550, ['tblHeader' => true, 'cantSplit' => true]);
            foreach ($columnas as $j => $col) {
                $celda = $tabla->addCell($anchos[$j], ['bgColor' => $color, 'valign' => 'center']);
                $celda->addText(ExpedicionMatriculasService::COLUMNAS[$col],
                    ['bold' => true, 'size' => count($columnas) > 9 ? 7 : 8, 'color' => $fontColor],
                    ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
            }
            foreach ($grupo['filas'] as $i => $fila) {
                $tabla->addRow(null, ['cantSplit' => true]);
                foreach ($columnas as $j => $col) {
                    $fondo = $plantilla !== 'seg' && $i % 2 === 1 ? 'F1F7F9' : 'FFFFFF';
                    $celda = $tabla->addCell($anchos[$j], ['bgColor' => $fondo, 'valign' => 'center']);
                    $celda->addText((string) ($fila[$col] ?? ''), ['size' => count($columnas) > 9 ? 7 : 8],
                        ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
                }
            }
        }
        IOFactory::createWriter($word, 'Word2007')->save($destino);
    }
}
