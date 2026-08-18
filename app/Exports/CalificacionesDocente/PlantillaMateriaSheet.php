<?php

namespace App\Exports\CalificacionesDocente;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PlantillaMateriaSheet implements FromArray, WithHeadings, WithStyles, WithEvents, WithTitle, ShouldAutoSize
{
    public function __construct(
        private readonly object $contexto,
        private readonly Collection $alumnos,
        private readonly array $calificaciones = [],
        private readonly ?string $tituloPersonalizado = null,
    ) {
    }

    public function array(): array
    {
        return $this->alumnos->values()->map(function ($alumno, $index) {
            return [
                $index + 1,
                $alumno->matricula,
                $alumno->apellido_paterno,
                $alumno->apellido_materno,
                $alumno->nombre,
                $this->calificaciones[$alumno->id] ?? '',
                $alumno->id,
                $this->contexto->asignacion_materia_id,
                $this->contexto->generacion_id,
            ];
        })->all();
    }

    public function headings(): array
    {
        return [
            'No.',
            'Matrícula',
            'Apellido paterno',
            'Apellido materno',
            'Nombre',
            'Calificación',
            'alumno_id',
            'asignacion_materia_id',
            'generacion_id',
        ];
    }

    public function title(): string
    {
        $base = $this->tituloPersonalizado
            ?: trim(($this->contexto->materia_clave ? $this->contexto->materia_clave . ' ' : '') . $this->contexto->materia);

        $base = preg_replace('/[\\\/?*\[\]:]/', '-', $base) ?: 'Materia';
        $suffix = '-' . $this->contexto->asignacion_materia_id;

        return mb_substr($base, 0, 31 - mb_strlen($suffix)) . $suffix;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF006492']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $ultimaFila = max(2, $this->alumnos->count() + 1);

                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:I{$ultimaFila}");
                $sheet->getColumnDimension('G')->setVisible(false);
                $sheet->getColumnDimension('H')->setVisible(false);
                $sheet->getColumnDimension('I')->setVisible(false);
                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getStyle("A1:I{$ultimaFila}")->getBorders()->getAllBorders()
                    ->setBorderStyle('thin')->getColor()->setARGB('FFD1D5DB');
                $sheet->getStyle("F2:F{$ultimaFila}")->getNumberFormat()->setFormatCode('0.0');

                for ($row = 2; $row <= $ultimaFila; $row++) {
                    $validation = $sheet->getCell("F{$row}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_CUSTOM);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setShowInputMessage(true);
                    $validation->setErrorTitle('Calificación no válida');
                    $validation->setError('Captura un valor de 5 a 10 (puede tener decimales) o NP.');
                    $validation->setPromptTitle('Calificación');
                    $validation->setPrompt('Valores válidos: 5 a 10 o NP. Vacío = no modificar.');
                    $validation->setFormula1("OR(F{$row}=\"\",F{$row}=\"NP\",AND(ISNUMBER(F{$row}),F{$row}>=5,F{$row}<=10))");
                }

                // Protege identificadores y datos del alumno; sólo la calificación queda editable.
                $sheet->getStyle("F2:F{$ultimaFila}")->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);
                $sheet->getProtection()->setSheet(true);
                $sheet->getProtection()->setSort(true);
                $sheet->getProtection()->setAutoFilter(true);

                $sheet->getHeaderFooter()->setOddHeader(
                    '&C&BCentro Universitario Moctezuma - Plantilla de calificaciones'
                );
                $sheet->getHeaderFooter()->setOddFooter(
                    '&L' . $this->contexto->materia . '&RGeneración ' . $this->contexto->generacion . ' - Página &P de &N'
                );
            },
        ];
    }
}
