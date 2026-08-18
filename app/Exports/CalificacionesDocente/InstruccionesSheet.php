<?php

namespace App\Exports\CalificacionesDocente;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InstruccionesSheet implements FromArray, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(private readonly iterable $contextos)
    {
    }

    public function array(): array
    {
        $rows = [
            ['PLANTILLA DE CALIFICACIONES POR DOCENTE'],
            ['Instrucciones'],
            ['1.', 'Captura únicamente la columna Calificación de cada hoja de materia.'],
            ['2.', 'Valores permitidos: números de 5 a 10 (incluye decimales) o NP.'],
            ['3.', 'Una celda vacía significa “no modificar”; no elimina una calificación existente.'],
            ['4.', 'No cambies matrícula, nombres ni identificadores ocultos. El sistema los valida al importar.'],
            ['5.', 'Antes de guardar, el sistema mostrará una vista previa con altas, cambios y errores.'],
            [],
            ['Materias incluidas'],
            ['Materia', 'Generación', 'Licenciatura', 'Modalidad', 'Cuatrimestre'],
        ];

        foreach ($this->contextos as $c) {
            $rows[] = [
                $c->materia,
                $c->generacion,
                $c->licenciatura,
                $c->modalidad,
                $c->cuatrimestre,
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'INSTRUCCIONES';
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF006492']],
            'alignment' => ['horizontal' => 'center'],
        ]);
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A9:E10')->getFont()->setBold(true);

        return [];
    }
}
