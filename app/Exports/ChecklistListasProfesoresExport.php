<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ChecklistListasProfesoresExport implements FromArray, WithColumnWidths, WithStyles, WithEvents
{
    private array $filas;

    public function __construct(private readonly array $reporte)
    {
        $this->filas = $this->construirFilas();
    }

    public function array(): array
    {
        return $this->filas;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 7,
            'B' => 34,
            'C' => 38,
            'D' => 34,
            'E' => 20,
            'F' => 13,
            'G' => 17,
            'H' => 12,
            'I' => 12,
            'J' => 30,
            'K' => 20,
            'L' => 17,
            'M' => 32,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:M1');
        $sheet->mergeCells('A2:M2');
        $sheet->mergeCells('A3:M3');
        $sheet->mergeCells('A4:M4');

        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '006492']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->getStyle('A2:M4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '334155']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getStyle('A4:M4')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEF6F8']],
            'font' => ['bold' => true, 'color' => ['rgb' => '006492']],
        ]);

        $sheet->getStyle('A6:M6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '006492']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
        $sheet->getRowDimension(6)->setRowHeight(27);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $ultimaFila = max(6, count($this->filas));

                if ($ultimaFila >= 7) {
                    $event->sheet->getStyle("A7:M{$ultimaFila}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'CBD5E1'],
                            ],
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_TOP,
                            'wrapText' => true,
                        ],
                    ]);

                    for ($fila = 7; $fila <= $ultimaFila; $fila++) {
                        if (($fila - 7) % 2 === 1) {
                            $event->sheet->getStyle("A{$fila}:M{$fila}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('F8FAFC');
                        }
                    }

                    $event->sheet->getStyle("H7:I{$ultimaFila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $event->sheet->getStyle("K7:L{$ultimaFila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                $event->sheet->freezePane('A7');
                $event->sheet->setAutoFilter('A6:M6');
                $event->sheet->getPageSetup()->setOrientation('landscape');
                $event->sheet->getPageSetup()->setFitToWidth(1);
                $event->sheet->getPageSetup()->setFitToHeight(0);
            },
        ];
    }

    private function construirFilas(): array
    {
        $contexto = $this->reporte['contexto'];
        $resumen = $this->reporte['resumen'];
        $filtros = $this->reporte['filtros_texto'];

        $filas = [
            ['CONTROL DE ENTREGA DE LISTAS A PROFESORES'],
            [sprintf(
                '%s · C.C.T. %s · Ciclo %s · Periodo %s',
                $contexto['escuela']?->nombre ?? 'Centro Universitario Moctezuma',
                $contexto['escuela']?->CCT ?? '12PSU0173I',
                $contexto['ciclo_escolar'] ?: 'SIN CONFIGURAR',
                $contexto['periodo_escolar'] ?: 'SIN CONFIGURAR'
            )],
            [sprintf(
                'Filtros: Licenciatura %s · Modalidad %s · Generación %s · Generado %s',
                $filtros['licenciatura'],
                $filtros['modalidad'],
                $filtros['generacion'],
                $this->reporte['fecha_emision']->format('d/m/Y H:i')
            )],
            [sprintf(
                'Profesores: %d · Materias/grupos: %d · Asistencias: %d · Evaluaciones: %d · Total documentos: %d',
                $resumen['profesores'],
                $resumen['materias_grupos'],
                $resumen['asistencias'],
                $resumen['evaluaciones'],
                $resumen['documentos']
            )],
            [],
            [
                'N.º',
                'Profesor',
                'Materia',
                'Licenciatura',
                'Modalidad',
                'Cuatrimestre',
                'Generación',
                'Asistencia',
                'Evaluación',
                'Observaciones',
                'Paquete completo',
                'Fecha',
                'Firma / Recibió',
            ],
        ];

        $numero = 1;
        foreach ($this->reporte['profesores'] as $profesor) {
            foreach ($profesor['registros'] as $indice => $registro) {
                $filas[] = [
                    $numero++,
                    mb_strtoupper($profesor['nombre'], 'UTF-8'),
                    $registro->materia,
                    $registro->licenciatura,
                    $registro->modalidad,
                    $registro->cuatrimestre . '°',
                    $registro->generacion,
                    '☐',
                    '☐',
                    '',
                    $indice === 0 ? '☐' : '',
                    $indice === 0 ? '____/____/______' : '',
                    $indice === 0 ? '____________________________' : '',
                ];
            }
        }

        if ($this->reporte['profesores']->isEmpty()) {
            $filas[] = ['', 'SIN PROFESORES CON MATERIAS EN HORARIO PARA LOS FILTROS SELECCIONADOS'];
        }

        return $filas;
    }
}
