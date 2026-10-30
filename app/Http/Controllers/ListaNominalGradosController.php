<?php

namespace App\Http\Controllers;

use App\Exports\ListaNominalGradosExport;
use App\Models\Escuela;
use App\Services\ListaNominalGradosService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

class ListaNominalGradosController extends Controller
{
    public function pdf(Request $request, ListaNominalGradosService $servicio)
    {
        $pdf = Pdf::loadView('livewire.admin.documentacion.pdf.lista-nominal-grados', [
            'reporte' => $servicio->generar($request->all()),
            'escuela' => $this->escuela(),
        ])->setPaper('letter', 'portrait');

        return $request->query('disposition') === 'download'
            ? $pdf->download($this->nombre('pdf'))
            : $pdf->stream($this->nombre('pdf'));
    }

    public function excel(Request $request, ListaNominalGradosService $servicio)
    {
        return Excel::download(
            new ListaNominalGradosExport($servicio->generar($request->all()), $this->escuela()),
            $this->nombre('xlsx')
        );
    }

    public function word(Request $request, ListaNominalGradosService $servicio)
    {
        $reporte = $servicio->generar($request->all());
        $escuela = $this->escuela();
        $carpeta = storage_path('app/phpword-temp');
        if (! is_dir($carpeta)) {
            mkdir($carpeta, 0775, true);
        }
        Settings::setTempDir($carpeta);
        $word = new PhpWord();
        $word->setDefaultFontName('Arial');
        $word->setDefaultFontSize(9);
        $seccion = $word->addSection([
            'pageSizeW' => Converter::inchToTwip(8.5),
            'pageSizeH' => Converter::inchToTwip(11),
            'marginTop' => Converter::cmToTwip(2.85),
            'marginBottom' => Converter::cmToTwip(1.65),
            'marginLeft' => Converter::cmToTwip(1.7),
            'marginRight' => Converter::cmToTwip(1.7),
            'headerHeight' => Converter::cmToTwip(0.7),
        ]);
        $header = $seccion->addHeader();
        $logo = collect([public_path('storage/logo.png'), public_path('storage/logo-moctezuma.jpg')])
            ->first(fn ($archivo) => is_file($archivo));
        if ($logo) {
            $header->addImage($logo, ['height' => 40, 'alignment' => Jc::CENTER]);
        }
        $header->addText($escuela['nombre'] ?? 'CENTRO UNIVERSITARIO MOCTEZUMA A.C.',
            ['bold' => true, 'size' => 10, 'color' => '006492'], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $header->addText('C.C.T. '.($escuela['CCT'] ?? '12PSU0173I'),
            ['size' => 7, 'color' => '527389'], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $borde = ['borderSize' => 5, 'borderColor' => '303030', 'cellMargin' => 65, 'alignment' => Jc::CENTER];
        $anchos = [580, 2290, 2290, 2290, 850]; // Total: 8300 twips. Carta con márgenes.

        if ($reporte['grupos'] === []) {
            $seccion->addText('LISTA NOMINAL POR GRADOS', ['bold' => true, 'size' => 14], ['alignment' => Jc::CENTER]);
            $seccion->addText('No existen alumnos para los filtros seleccionados.');
        }
        foreach ($reporte['grupos'] as $indice => $grupo) {
            if ($indice > 0) {
                $seccion->addPageBreak();
            }
            $seccion->addText('LISTA NOMINAL POR GRADOS', ['bold' => true, 'size' => 14, 'color' => '006492'],
                ['alignment' => Jc::CENTER, 'spaceAfter' => 200]);
            $seccion->addText('Licenciatura: '.$grupo['licenciatura'], ['bold' => true, 'size' => 10], ['spaceAfter' => 75]);
            $seccion->addText('Grado: '.$grupo['grado'].'º GRADO', ['bold' => true, 'size' => 10], ['spaceAfter' => 75]);
            $seccion->addText('Modalidad(es): '.$reporte['modalidades'].'    Generación(es): '.$reporte['generaciones'],
                ['size' => 7, 'color' => '586D79'], ['spaceAfter' => 80]);
            $seccion->addText('Fecha de corte: '.\Carbon\Carbon::parse($reporte['fecha_corte'])->format('d/m/Y'),
                ['size' => 7, 'color' => '586D79'], ['spaceAfter' => 200]);
            if (($reporte['alertas_avance']['total_pendientes'] ?? 0) > 0) {
                $seccion->addText('AVISO: Existen '.$reporte['alertas_avance']['total_pendientes'].' alumnos pendientes de revisión de cuatrimestre.',
                    ['size' => 8, 'color' => '995500', 'bold' => true], ['spaceAfter' => 150]);
            }

            $tabla = $seccion->addTable($borde);
            $tabla->addRow(370, ['tblHeader' => true, 'cantSplit' => true]);
            foreach (['No.', 'Apellido\nPaterno', 'Apellido\nMaterno', 'Nombre', 'Edad'] as $indiceCol => $texto) {
                $tabla->addCell($anchos[$indiceCol], ['bgColor' => 'B8CAE8', 'valign' => 'center'])
                    ->addText(str_replace('\\n', "\n", $texto), ['bold' => true, 'size' => 8], ['alignment' => Jc::CENTER]);
            }
            foreach ($grupo['alumnos'] as $numero => $alumno) {
                $tabla->addRow(320, ['cantSplit' => true]);
                foreach ([$numero + 1, $alumno['apellido_paterno'], $alumno['apellido_materno'], $alumno['nombre'], $alumno['edad'] ?? '—'] as $col => $valor) {
                    $tabla->addCell($anchos[$col], ['valign' => 'center', 'bgColor' => $numero % 2 ? 'F5F8FA' : 'FFFFFF'])
                        ->addText((string) $valor, ['size' => 8], ['alignment' => in_array($col, [0, 4], true) ? Jc::CENTER : Jc::LEFT]);
                }
            }
            for ($i = 0; $i < $reporte['renglones_vacios']; $i++) {
                $tabla->addRow(300, ['cantSplit' => true]);
                foreach ($anchos as $ancho) {
                    $tabla->addCell($ancho)->addText(' ');
                }
            }
            $seccion->addTextBreak(1);
            $resumen = $seccion->addTable($borde);
            $resumen->addRow(300);
            foreach (['HOMBRES', 'MUJERES', 'TOTALES'] as $texto) {
                $resumen->addCell(2766, ['bgColor' => 'C8E2BA'])->addText($texto,
                    ['bold' => true, 'size' => 8], ['alignment' => Jc::CENTER]);
            }
            $resumen->addRow(320);
            foreach (['hombres', 'mujeres', 'total'] as $clave) {
                $resumen->addCell(2766)->addText((string) $grupo['totales'][$clave],
                    ['bold' => true, 'size' => 9], ['alignment' => Jc::CENTER]);
            }
        }
        $temp = tempnam($carpeta, 'lista_nominal_');
        abort_if($temp === false, 500, 'No se pudo crear el archivo temporal de Word.');
        $ruta = $temp.'.docx';
        rename($temp, $ruta);
        try {
            IOFactory::createWriter($word, 'Word2007')->save($ruta);
        } catch (\Throwable $error) {
            @unlink($ruta);
            throw $error;
        }
        return response()->download($ruta, $this->nombre('docx'))->deleteFileAfterSend(true);
    }

    private function escuela(): array
    {
        return Escuela::query()->first()?->toArray() ?? [];
    }

    private function nombre(string $extension): string
    {
        return 'lista-nominal-por-licenciatura-y-grado-'.now()->format('Y-m-d').'.'.$extension;
    }
}
