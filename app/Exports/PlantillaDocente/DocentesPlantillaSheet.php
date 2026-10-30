<?php

namespace App\Exports\PlantillaDocente;

use App\Services\PlantillaDocenteWordService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class DocentesPlantillaSheet extends DefaultValueBinder implements FromArray, WithTitle, WithEvents, WithCustomValueBinder
{
    public function __construct(private readonly ?array $grupo, private readonly array $reporte,
                                private readonly array $escuela, private readonly int $orden) {}

    public function bindValue(Cell $cell, $value): bool
    {
        // No ejecutar fórmulas introducidas accidentalmente en nombres o materias.
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }
        return parent::bindValue($cell, $value);
    }

    public function title(): string
    {
        if ($this->grupo === null) { return 'Todos los docentes'; }
        $nombre = preg_replace('/[\\\\\/*?:\[\]]/u', '', $this->grupo['licenciatura']);
        return mb_substr(str_pad((string)$this->orden, 2, '0', STR_PAD_LEFT).' '.trim($nombre), 0, 31);
    }

    private function docentes(): array
    {
        if ($this->grupo !== null) { return array_map(fn ($d) => [$this->grupo['licenciatura'], $d], $this->grupo['docentes']); }
        $filas = [];
        foreach ($this->reporte['grupos'] as $grupo) {
            foreach ($grupo['docentes'] as $docente) { $filas[] = [$grupo['licenciatura'], $docente]; }
        }
        return $filas;
    }

    public function array(): array
    {
        $columnas = PlantillaDocenteWordService::columnas($this->reporte);
        if ($this->grupo === null) { $columnas = ['licenciatura' => 'Licenciatura'] + $columnas; }
        $nombre = $this->grupo['licenciatura'] ?? 'TODAS LAS LICENCIATURAS';
        $filas = [
            [$this->escuela['nombre'] ?? 'CENTRO UNIVERSITARIO MOCTEZUMA A.C.'],
            ['PLANTILLA DOCENTE POR LICENCIATURA'],
            ['Licenciatura: '.$nombre],
            ['Ciclo: '.$this->reporte['ciclo'].' · Periodo: '.$this->reporte['periodo'].' · Emitido: '.$this->reporte['fecha']],
            array_values($columnas),
        ];
        $n = 0;
        foreach ($this->docentes() as [$licenciatura, $docente]) {
            $n++;
            $datos = PlantillaDocenteWordService::fila($docente, $n, $this->reporte);
            if ($this->grupo === null) { $datos = ['licenciatura' => $licenciatura] + $datos; }
            $filas[] = array_values($datos);
        }
        $filas[] = ['TOTAL DOCENTES EN LISTADO: '.$n];
        return $filas;
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $evento): void {
            $sheet = $evento->sheet->getDelegate();
            $cols = PlantillaDocenteWordService::columnas($this->reporte);
            if ($this->grupo === null) { $cols = ['licenciatura' => 'Licenciatura'] + $cols; }
            $ultima = Coordinate::stringFromColumnIndex(count($cols));
            foreach ([1,2,3,4] as $r) { $sheet->mergeCells("A{$r}:{$ultima}{$r}"); }
            $sheet->setShowGridlines(false);
            $sheet->getStyle("A1:{$ultima}1")->getFont()->setBold(true)->setSize(13)->getColor()->setRGB('006492');
            $sheet->getStyle("A2:{$ultima}2")->applyFromArray([
                'font'=>['bold'=>true,'color'=>['rgb'=>'FFFFFF']],
                'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'006492']],
            ]);
            $sheet->getStyle("A5:{$ultima}5")->applyFromArray([
                'font'=>['bold'=>true],
                'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'B8CAE8']],
                'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER,'wrapText'=>true],
                'borders'=>['allBorders'=>['borderStyle'=>Border::BORDER_THIN]],
            ]);
            $total = count($this->docentes());
            if ($total > 0) {
                $fin = 5 + $total;
                $sheet->getStyle("A6:{$ultima}{$fin}")->applyFromArray([
                    'borders'=>['allBorders'=>['borderStyle'=>Border::BORDER_THIN]],
                    'alignment'=>['vertical'=>Alignment::VERTICAL_CENTER,'wrapText'=>true],
                ]);
                for ($r = 7; $r <= $fin; $r += 2) {
                    $sheet->getStyle("A{$r}:{$ultima}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F3F7F9');
                }
                $sheet->setAutoFilter("A5:{$ultima}{$fin}");
            }
            $sheet->getStyle('A'.(6+$total).":{$ultima}".(6+$total))->getFont()->setBold(true);
            foreach (array_keys($cols) as $i => $key) {
                $col = Coordinate::stringFromColumnIndex($i+1);
                $sheet->getColumnDimension($col)->setWidth(match ($key) {
                    'no' => 8, 'licenciatura'=>35, 'nombre'=>37, 'materias'=>65, 'generaciones'=>25,
                    'cuatrimestres'=>19, 'modalidades'=>23, 'horas'=>12, 'bloques'=>13, default=>25,
                });
            }
            $sheet->getRowDimension(5)->setRowHeight(30);
            $sheet->freezePane('B6');
            $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LETTER);
            $sheet->getPageSetup()->setOrientation('landscape');
            $sheet->getPageSetup()->setFitToPage(true);
            $sheet->getPageSetup()->setFitToWidth(1);
            $sheet->getPageSetup()->setFitToHeight(0);
            $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(5,5);
        }];
    }
}
