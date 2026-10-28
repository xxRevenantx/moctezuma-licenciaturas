<?php

namespace App\Http\Controllers;

use App\Exports\EstadisticaGeneralGradosExport;
use App\Models\Escuela;
use App\Services\EstadisticaGeneralGradosService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

class EstadisticaGeneralGradosController extends Controller
{
    public function pdf(Request $request, EstadisticaGeneralGradosService $servicio)
    {
        $reporte = $servicio->generar($request->all());
        $pdf = Pdf::loadView('livewire.admin.documentacion.pdf.estadistica-general-grados', [
            'reporte' => $reporte,
            'escuela' => $this->escuela(),
        ])->setPaper('letter', 'portrait');

        return $request->string('disposition')->toString() === 'download'
            ? $pdf->download($this->nombre('pdf'))
            : $pdf->stream($this->nombre('pdf'));
    }

    public function excel(Request $request, EstadisticaGeneralGradosService $servicio)
    {
        return Excel::download(
            new EstadisticaGeneralGradosExport($servicio->generar($request->all()), $this->escuela()),
            $this->nombre('xlsx')
        );
    }

    public function word(Request $request, EstadisticaGeneralGradosService $servicio)
    {
        $reporte = $servicio->generar($request->all());
        $escuela = $this->escuela();
        $rutaTemporal = storage_path('app/phpword-temp');
        if (! is_dir($rutaTemporal)) {
            mkdir($rutaTemporal, 0775, true);
        }
        Settings::setTempDir($rutaTemporal);

        $documento = new PhpWord();
        $documento->setDefaultFontName('Times New Roman');
        $documento->setDefaultFontSize(10);
        $seccion = $documento->addSection([
            'pageSizeW' => Converter::inchToTwip(8.5),
            'pageSizeH' => Converter::inchToTwip(11),
            'marginTop' => Converter::cmToTwip(3.1),
            'marginBottom' => Converter::cmToTwip(1.65),
            'marginLeft' => Converter::cmToTwip(1.8),
            'marginRight' => Converter::cmToTwip(1.8),
            'headerHeight' => Converter::cmToTwip(0.65),
        ]);

        $encabezado = $seccion->addHeader();
        $logo = collect([
            public_path('storage/logo.png'),
            public_path('storage/logo-moctezuma.jpg'),
        ])->first(fn ($ruta) => is_file($ruta));
        if ($logo) {
            $encabezado->addImage($logo, ['height' => 45, 'alignment' => Jc::CENTER]);
        }
        $encabezado->addText($escuela['nombre'] ?? 'CENTRO UNIVERSITARIO MOCTEZUMA A.C.',
            ['bold' => true, 'size' => 11, 'color' => '006492'], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $encabezado->addText('C.C.T. ' . ($escuela['CCT'] ?? '12PSU0173I'),
            ['size' => 7, 'color' => '6693A7'], ['alignment' => Jc::CENTER, 'spaceAfter' => 70]);

        $seccion->addText('ESTADÍSTICA', ['bold' => true, 'size' => 15], ['alignment' => Jc::CENTER, 'spaceAfter' => 120]);
        $seccion->addText('Alumnos activos · Generaciones vigentes · Corte: ' . now()->format('d/m/Y'),
            ['size' => 8, 'color' => '637989'], ['alignment' => Jc::CENTER, 'spaceAfter' => 430]);

        if (($reporte['alertas_avance']['total_pendientes'] ?? 0) > 0) {
            $seccion->addText('AVISO CONTROL ESCOLAR: '.$reporte['alertas_avance']['total_pendientes'].
                ' alumno(s) pendientes de revisión de cuatrimestre. Los grados reflejan la matrícula registrada.',
                ['bold' => true, 'size' => 8, 'color' => '955800'], ['spaceAfter' => 140]);
        }

        if ($reporte['licenciaturas'] === []) {
            $seccion->addText('No se encontraron alumnos activos de generaciones vigentes.');
        }
        foreach ($reporte['licenciaturas'] as $licenciatura) {
            $seccion->addText(mb_strtoupper($licenciatura['nombre']),
                ['bold' => true, 'size' => 10], ['keepNext' => true, 'spaceBefore' => 240, 'spaceAfter' => 200]);
            foreach ($licenciatura['grados'] as $grado) {
                $tabla = $seccion->addTable([
                    'borderSize' => 5, 'borderColor' => '333333',
                    'cellMargin' => 55, 'alignment' => Jc::CENTER,
                ]);
                $tabla->addRow(270);
                $celda = $tabla->addCell(9750, ['gridSpan' => 3, 'bgColor' => 'C8E2BA', 'valign' => 'center']);
                $celda->addText($grado['grado'] . 'º GRADO', ['bold' => true, 'size' => 9], ['alignment' => Jc::CENTER]);
                $tabla->addRow(260);
                foreach (['HOMBRES', 'MUJERES', 'TOTAL'] as $texto) {
                    $tabla->addCell(3250, ['bgColor' => 'B8CAE8', 'valign' => 'center'])
                        ->addText($texto, ['bold' => true, 'size' => 8], ['alignment' => Jc::CENTER]);
                }
                $tabla->addRow(270);
                foreach (['hombres', 'mujeres', 'total'] as $campo) {
                    $tabla->addCell(3250, ['valign' => 'center'])
                        ->addText((string) $grado[$campo], ['bold' => true, 'size' => 9], ['alignment' => Jc::CENTER]);
                }
                $seccion->addTextBreak(1);
            }
        }
        $seccion->addText('Total de alumnos: ' . $reporte['totales']['total'],
            ['bold' => true, 'size' => 11], ['spaceBefore' => 180]);
        $seccion->addText(sprintf('Hombres: %d    Mujeres: %d', $reporte['totales']['hombres'], $reporte['totales']['mujeres']),
            ['size' => 8, 'color' => '637989']);

        $temp = tempnam($rutaTemporal, 'estadistica_grados_');
        abort_if($temp === false, 500, 'No se pudo preparar el documento Word.');
        $archivo = $temp . '.docx';
        rename($temp, $archivo);
        try {
            IOFactory::createWriter($documento, 'Word2007')->save($archivo);
        } catch (\Throwable $e) {
            @unlink($archivo);
            throw $e;
        }

        return response()->download($archivo, $this->nombre('docx'))->deleteFileAfterSend(true);
    }

    private function escuela(): array
    {
        return Escuela::query()->first()?->toArray() ?? [];
    }

    private function nombre(string $extension): string
    {
        return 'estadistica-general-grados-' . now()->format('d-m-Y') . '.' . $extension;
    }
}
