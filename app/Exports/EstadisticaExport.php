<?php

namespace App\Exports;


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

class EstadisticaExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{

       protected $estadistica;
       protected $rango_edad;

    public function __construct(Collection $estadistica, string $rango_edad)
    {
        $this->estadistica = $estadistica;
        $this->rango_edad = $rango_edad;
    }


    public function collection()
    {
       return $this->estadistica->map(function ($estadistica) {

            // dd($estadistica);
            return [
                "Licenciatura" => $estadistica->licenciatura->nombre,
                "Total Inscritos" => $estadistica->total_inscritos,
                "Total Masculinos" => $estadistica->total_masculinos,
                "Total Femeninos" => $estadistica->total_femeninos,
                "Rango de edad" => $this->rango_edad,
            ];
        });
    }
     public function headings(): array
    {
        return [
            "Licenciatura",
            "Total Inscritos",
            "Total Masculinos",
            "Total Femeninos",
            "Rango de edad",
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
                $rowCount = count($this->estadistica) + 1;
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
