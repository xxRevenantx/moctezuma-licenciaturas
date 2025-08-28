<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ReprobadosExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    /** @var \Illuminate\Support\Collection<\App\Models\Calificacion> */
    protected Collection $rows;

    public function __construct(Collection $rows)
    {
        // Ordenamos por alumno para que queden contiguas sus materias
        $this->rows = $rows
            ->sortBy(fn($c) => sprintf('%08d-%08d', (int) ($c->alumno_id ?? 0), (int) ($c->id ?? 0)))
            ->values();
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Matrícula',            // A
            'Nombre completo',      // B
            'Licenciatura',         // C
            'RVOE',                 // D
            'Cuatrimestre (Al.)',   // E
            'Generación',           // F
            'Modalidad',            // G
            'Materia',              // H
            'Cuatrimestre (Mat.)',  // I  (Calificacion->asignacionMateria->cuatrimestre)
            'Calificación',         // J
            'Fecha registro',       // K
        ];
    }

    public function map($cal): array
    {
        $al  = $cal->alumno;
        $lic = optional($al?->licenciatura);
        $mod = optional($al?->modalidad);
        $cua = optional($al?->cuatrimestre);
        $gen = optional($al?->generacion);

        $materiaNombre = optional(optional($cal->asignacionMateria)->materia)->nombre ?? '—';

        // Cuatrimestre de la asignación de la materia
        $cuaMateria = optional(optional($cal->asignacionMateria)->cuatrimestre)->cuatrimestre
                      ?? optional($cal->cuatrimestre)->cuatrimestre
                      ?? null;

        $raw   = $cal->calificacion;
        $valor = is_numeric($raw) ? number_format((float) $raw, 1) : (string) $raw;

        return [
            $al->matricula ?? '—',
            trim(($al->nombre ?? '') . ' ' . ($al->apellido_paterno ?? '') . ' ' . ($al->apellido_materno ?? '')),
            $lic->nombre ?? '—',
            $lic->RVOE ?? '—',
            $cua->cuatrimestre ? ($cua->cuatrimestre . 'º') : '—', // E
            $gen->generacion ?? '—',
            $mod->nombre ?? '—',
            $materiaNombre,                                        // H
            $cuaMateria ? ($cuaMateria . 'º') : '—',               // I
            $valor,                                                // J
            optional($cal->created_at)?->format('d/m/Y') ?? '—',   // K
        ];
    }

    /* ============================
     * ESTILOS Y EVENTOS
     * ============================ */
    public function styles(Worksheet $sheet)
    {
        // Encabezado A1:K1
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 11,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'], // indigo-600
            ],
        ]);

        // Alineación por columnas
        $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Matrícula
        $sheet->getStyle('E:E')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Cuatrimestre (Al.)
        $sheet->getStyle('F:F')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Generación
        $sheet->getStyle('G:G')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Modalidad
        $sheet->getStyle('I:I')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Cuatrimestre (Mat.)
        $sheet->getStyle('J:J')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Calificación
        $sheet->getStyle('K:K')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Fecha

        // Altura de fila del encabezado
        $sheet->getRowDimension(1)->setRowHeight(22);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $dataCount = max(1, $this->rows->count()); // filas de datos
                $lastRow   = $dataCount + 1;               // + encabezado
                $rangeAll  = "A1:K{$lastRow}";

                // Borde a toda la tabla
                $event->sheet->getStyle($rangeAll)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Autofiltro y freeze del encabezado
                $event->sheet->setAutoFilter('A1:K1');
                $event->sheet->freezePane('A2');

                // Formato numérico para Calificación (J)
                $event->sheet->getStyle("J2:J{$lastRow}")
                    ->getNumberFormat()->setFormatCode('0.0');

                // === AGRUPAR POR ALUMNO: combinar celdas A..G para cada alumno ===
                $colsAlumno = ['A','B','C','D','E','F','G'];
                $row = 2; // primer renglón de datos
                $i   = 0; // índice en colección

                while ($i < $this->rows->count()) {
                    $current = $this->rows[$i];
                    $alumnoId = $current->alumno_id;
                    $groupLen = 1;

                    // Calcula tamaño del grupo consecutivo con el mismo alumno
                    while (($i + $groupLen) < $this->rows->count()
                        && $this->rows[$i + $groupLen]->alumno_id === $alumnoId) {
                        $groupLen++;
                    }

                    $start = $row;
                    $end   = $row + $groupLen - 1;

                    if ($groupLen > 1) {
                        foreach ($colsAlumno as $col) {
                            $event->sheet->mergeCells("{$col}{$start}:{$col}{$end}");
                            // Alineación vertical top para bloques altos
                            $event->sheet->getStyle("{$col}{$start}:{$col}{$end}")->applyFromArray([
                                'alignment' => [
                                    'vertical'   => Alignment::VERTICAL_TOP,
                                    'horizontal' => in_array($col, ['A','E','F','G'], true)
                                        ? Alignment::HORIZONTAL_CENTER
                                        : Alignment::HORIZONTAL_LEFT,
                                    'wrapText'   => true,
                                ],
                            ]);
                        }
                        // sombreado suave para distinguir grupos
                        $event->sheet->getStyle("A{$start}:K{$end}")->applyFromArray([
                            'fill' => [
                                'fillType'   => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => ($start % 2 === 0 ? 'F8FAFC' : 'FFFFFF')], // zebra respetando el grupo
                            ],
                        ]);
                    }

                    // Avanza punteros
                    $row += $groupLen;
                    $i   += $groupLen;
                }

                // Zebra stripes (si quedan filas sueltas no agrupadas)
                for ($r = 2; $r <= $lastRow; $r++) {
                    if ($r % 2 === 0) {
                        $event->sheet->getStyle("A{$r}:K{$r}")->applyFromArray([
                            'fill' => [
                                'fillType'   => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F8FAFC'],
                            ],
                        ]);
                    }
                }

                // Semáforo para calificación (columna J)
                $codes = ['NP','N/P','N.P.','NA'];
                for ($r = 2; $r <= $lastRow; $r++) {
                    $val = $event->sheet->getCell("J{$r}")->getValue();
                    $u   = strtoupper((string)$val);

                    if (in_array($u, $codes, true)) {
                        // Código NP/NA -> rosa
                        $event->sheet->getStyle("J{$r}")->applyFromArray([
                            'font' => ['bold' => true],
                            'fill' => [
                                'fillType'   => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'FECACA'], // red-200
                            ],
                        ]);
                    } elseif (is_numeric($val) && (float)$val <= 6) {
                        // Numérica <= 6 -> rojo
                        $event->sheet->getStyle("J{$r}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => '7F1D1D']], // red-900
                            'fill' => [
                                'fillType'   => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'FCA5A5'], // red-300
                            ],
                        ]);
                    }
                }
            },
        ];
    }
}
