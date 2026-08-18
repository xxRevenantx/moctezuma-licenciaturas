<?php

namespace App\Exports\CalificacionesDocente;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReporteCalificacionesExport implements FromArray, WithStyles, WithEvents, ShouldAutoSize
{
    public function __construct(
        private readonly object $contexto,
        private readonly Collection $alumnos,
        private readonly array $calificaciones,
    ) {
    }

    public function array(): array
    {
        $profesor = trim(collect([
            $this->contexto->profesor_nombre,
            $this->contexto->profesor_apellido_paterno,
            $this->contexto->profesor_apellido_materno,
        ])->filter()->implode(' '));

        $rows = [
            ['CENTRO UNIVERSITARIO MOCTEZUMA - CALIFICACIONES POR DOCENTE', '', '', ''],
            ['Docente', $profesor, 'Materia', $this->contexto->materia],
            ['Licenciatura', $this->contexto->licenciatura, 'Modalidad', $this->contexto->modalidad],
            ['Generación', $this->contexto->generacion, 'Cuatrimestre', $this->contexto->cuatrimestre . '°'],
            ['', '', '', ''],
            ['No.', 'Matrícula', 'Alumno', 'Calificación'],
        ];

        foreach ($this->alumnos->values() as $i => $alumno) {
            $rows[] = [
                $i + 1,
                $alumno->matricula,
                trim("{$alumno->apellido_paterno} {$alumno->apellido_materno} {$alumno->nombre}"),
                $this->calificaciones[$alumno->id] ?? '',
            ];
        }

        $numericas = collect($this->calificaciones)
            ->filter(fn ($valor) => is_numeric($valor) && (float) $valor >= 5 && (float) $valor <= 10)
            ->map(fn ($valor) => (float) $valor);
        $promedio = $numericas->isNotEmpty() ? round($numericas->avg(), 2) : null;

        $rows[] = ['', '', '', ''];
        $rows[] = ['Alumnos', $this->alumnos->count(), 'Promedio numérico', $promedio ?? '—'];
        $rows[] = ['', '', '', ''];
        $rows[] = ['Firma docente', '', 'Control escolar', ''];

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF006492']],
                'alignment' => ['horizontal' => 'center'],
            ],
            6 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF006492']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $ultimaAlumno = 6 + $this->alumnos->count();
                $firmaRow = $ultimaAlumno + 4;

                $sheet->mergeCells('A1:D1');
                $sheet->freezePane('A7');
                $sheet->getStyle("A6:D{$ultimaAlumno}")->getBorders()->getAllBorders()->setBorderStyle('thin');
                $sheet->getStyle("A2:D4")->getFont()->setSize(10);
                $sheet->getStyle("A{$firmaRow}:D{$firmaRow}")->getBorders()->getTop()->setBorderStyle('thin');
                $sheet->getStyle("A{$firmaRow}:D{$firmaRow}")->getFont()->setBold(true);
            },
        ];
    }
}
