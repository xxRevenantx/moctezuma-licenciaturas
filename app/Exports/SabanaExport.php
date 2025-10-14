<?php

namespace App\Exports;

use App\Models\Inscripcion;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Events\AfterSheet;

class SabanaExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected $sabana;

    public function __construct(Collection $sabana)
    {
        $this->sabana = $sabana;
    }

    public function collection()
    {

        // dd($this->sabana);

        return $this->sabana->map(function ($estudiante) {

            // dd($estudiante);
            return [
                "#" => $estudiante->orden,
                "Matrícula" => $estudiante->matricula,
                'Nivel Educativo' => "LICENCIATURA",
                'Tipo de documento' => "CERTIFICADO",
                'Folio' => $estudiante->folio,
                "Nombre" => $estudiante->nombre,
                "Apellido Paterno" => $estudiante->apellido_paterno,
                "Apellido Materno" => $estudiante->apellido_materno,
                "CURP" => $estudiante->CURP,
                'CCT' => "12PSU0173I",
                'Fecha de expedición' => now()->format('d/m/Y'),
                'Licenciatura' => $estudiante->licenciatura->nombre,
                'RVOE' => $estudiante->licenciatura->RVOE,
                'Status' => "Egresado",
                "Grupo" => "A",
               "Promedio" => (float) ($estudiante->promedio_final ?? 0),
               "Total de Materias" => (int) ($estudiante->total_materias ?? 0),
                "Creditos" => (int) ($estudiante->total_creditos_licenciatura ?? 0),
                "Turno" => "Matutino",
            ];
        });
    }

    public function headings(): array
    {
        return [
            '#',
            'Matrícula',
            'Nivel Educativo',
            'Tipo de documento',
            'Folio',
            'Nombre',
            'Apellido Paterno',
            'Apellido Materno',
            'CURP',
            'CCT',
            'Fecha de expedición',
            'Licenciatura',
            'RVOE',
            'Status',
            'Grupo',
            'Promedio',
            'Total de Materias',
            'Creditos',
            'Turno',
        ];
    }


     public function styles(Worksheet $sheet)
    {
        // Estilo del encabezado
        $sheet->getStyle('A1:S1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4CAF50'],
            ],
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $rowCount = count($this->sabana) + 1;
                $range = "A1:S{$rowCount}";

                // Apply border styles
                $event->sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);


            },
        ];
    }
}
