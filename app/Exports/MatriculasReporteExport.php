<?php

namespace App\Exports;

use App\Models\Inscripcion;
use App\Services\MatriculaService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MatriculasReporteExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithEvents
{
    private Collection $rows;

    private bool $riesgoAcademico;

    public function __construct(Collection $alumnos, string $tipo)
    {
        $this->riesgoAcademico = $tipo === 'bajos';
        $this->rows = $this->construirFilas($alumnos);
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        $base = [
            'Matrícula',
            'Estado de matrícula',
            'CURP',
            'Nombre',
            'Apellido paterno',
            'Apellido materno',
            'Licenciatura',
            'Modalidad',
            'Generación',
            'Cuatrimestre actual',
            'Sexo',
            'Procedencia',
            'Estado del alumno',
            'Fecha de inscripción',
        ];

        if ($this->riesgoAcademico) {
            array_push($base, 'Materia', 'Cuatrimestre de materia', 'Profesor', 'Calificación', 'Tipo de riesgo');
        }

        return $base;
    }

    public function styles(Worksheet $sheet): array
    {
        $ultimaColumna = $this->riesgoAcademico ? 'S' : 'N';

        $sheet->getStyle("A1:{$ultimaColumna}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '006492'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(24);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $ultimaColumna = $this->riesgoAcademico ? 'S' : 'N';
                $ultimaFila = max(2, $this->rows->count() + 1);
                $rango = "A1:{$ultimaColumna}{$ultimaFila}";

                $event->sheet->getStyle($rango)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D1D5DB'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_TOP,
                        'wrapText' => true,
                    ],
                ]);

                $event->sheet->freezePane('A2');
                $event->sheet->setAutoFilter("A1:{$ultimaColumna}1");

                for ($fila = 2; $fila <= $ultimaFila; $fila++) {
                    if ($fila % 2 === 0) {
                        $event->sheet->getStyle("A{$fila}:{$ultimaColumna}{$fila}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('F8FAFC');
                    }
                }
            },
        ];
    }

    private function construirFilas(Collection $alumnos): Collection
    {
        $filas = collect();
        $servicio = app(MatriculaService::class);

        /** @var Inscripcion $alumno */
        foreach ($alumnos as $alumno) {
            $base = [
                $alumno->matricula ?: '—',
                $this->estadoMatricula($alumno, $servicio),
                $alumno->CURP ?: '—',
                $alumno->nombre,
                $alumno->apellido_paterno,
                $alumno->apellido_materno,
                optional($alumno->licenciatura)->nombre ?? '—',
                optional($alumno->modalidad)->nombre ?? '—',
                optional($alumno->generacion)->generacion ?? '—',
                optional($alumno->cuatrimestre)->nombre_cuatrimestre
                    ?? optional($alumno->cuatrimestre)->cuatrimestre
                    ?? '—',
                $alumno->sexo === 'M' ? 'Mujer' : 'Hombre',
                $alumno->foraneo === 'true' ? 'Foráneo' : 'Local',
                $alumno->status === 'true' ? 'Activo' : 'Baja',
                optional($alumno->created_at)?->format('d/m/Y') ?? '—',
            ];

            if (! $this->riesgoAcademico) {
                $filas->push($base);
                continue;
            }

            foreach ($alumno->calificaciones as $calificacion) {
                $materia = optional(optional($calificacion->asignacionMateria)->materia)->nombre ?? '—';
                $cuatrimestre = optional(optional($calificacion->asignacionMateria)->cuatrimestre)->nombre_cuatrimestre
                    ?? optional(optional($calificacion->asignacionMateria)->cuatrimestre)->cuatrimestre
                    ?? '—';
                $profesor = trim(collect([
                    optional($calificacion->profesor)->nombre,
                    optional($calificacion->profesor)->apellido_paterno,
                    optional($calificacion->profesor)->apellido_materno,
                ])->filter()->implode(' ')) ?: '—';
                $valor = mb_strtoupper(trim((string) $calificacion->calificacion), 'UTF-8');
                $tipoRiesgo = is_numeric($valor) ? 'Calificación numérica ≤ 6' : 'No presentada';

                $filas->push([...$base, $materia, $cuatrimestre, $profesor, $valor, $tipoRiesgo]);
            }
        }

        return $filas;
    }

    private function estadoMatricula(Inscripcion $alumno, MatriculaService $servicio): string
    {
        $matricula = $servicio->normalizar($alumno->matricula);

        if ($matricula === '') {
            return 'Vacía';
        }

        if ((int) ($alumno->matricula_coincidencias ?? 1) > 1) {
            return 'Duplicada';
        }

        return $servicio->esValida($matricula) ? 'Válida' : 'Formato incorrecto';
    }
}
