<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

class GeneradorListasExportService
{
    public function __construct(private readonly GeneradorListasService $listas) {}

    public function pdf(array $grupos, array $filtros): string
    {
        $columnas = $this->listas->cabeceras($filtros);
        $landscape = $this->horizontal($columnas, $filtros);
        $institucion = $this->listas->encabezadoInstitucional();
        $institucion['logo'] = $this->dataUri($institucion['logo']);
        $institucion['agua'] = $this->dataUri($institucion['agua']);
        return Pdf::loadView('pdf.generador-listas', [
            'grupos' => $this->preparar($grupos, $filtros),
            'columnas' => $columnas,
            'institucion' => $institucion,
            'logos' => array_map(fn ($g) => $this->dataUri($this->listas->logoLicenciatura($g['licenciatura_id'])), $grupos),
            'filtros' => $filtros,
            'horizontal' => $landscape,
            'fecha' => now()->format('d/m/Y'),
        ])->setPaper('letter', $landscape ? 'landscape' : 'portrait')->output();
    }

    public function word(array $grupos, array $filtros): string
    {
        $base = storage_path('app/phpword-temp');
        if (! is_dir($base)) mkdir($base, 0775, true);
        Settings::setTempDir($base);
        $titulos = $this->listas->cabeceras($filtros);
        $horizontal = $this->horizontal($titulos, $filtros);
        $inst = $this->listas->encabezadoInstitucional();
        $word = new PhpWord();
        $word->setDefaultFontName('Arial');
        $word->setDefaultFontSize(8);
        $word->getSettings()->setUpdateFields(true);
        $seccion = $word->addSection([
            'orientation' => $horizontal ? 'landscape' : 'portrait',
            'pageSizeW' => Converter::inchToTwip($horizontal ? 11 : 8.5),
            'pageSizeH' => Converter::inchToTwip($horizontal ? 8.5 : 11),
            'marginLeft' => 750, 'marginRight' => 750, 'marginTop' => 770, 'marginBottom' => 950,
            'footerHeight' => 350,
        ]);
        $pie = $seccion->addFooter();
        $pie->addText('CENTRO UNIVERSITARIO MOCTEZUMA · ' . now()->format('d/m/Y') . '   ·   PÁG. ', ['size' => 7, 'italic' => true, 'color' => '526477'], ['alignment' => Jc::CENTER]);
        $pie->addPreserveText('{PAGE} / {NUMPAGES}', ['size' => 7, 'color' => '526477'], ['alignment' => Jc::CENTER]);
        $totalWidth = $horizontal ? 14050 : 10420;
        $anchos = $this->anchos($filtros['columnas'], $totalWidth);

        foreach ($grupos as $idx => $grupo) {
            if ($idx > 0) $seccion->addPageBreak();
            $header = $seccion->addTable(['borderSize' => 0, 'cellMargin' => 70]);
            $header->addRow();
            $izq = $header->addCell((int) ($totalWidth * .19), ['valign' => 'center']);
            if ($inst['logo']) $izq->addImage($inst['logo'], ['width' => 92, 'height' => 52, 'ratio' => true]);
            $medio = $header->addCell((int) ($totalWidth * .62), ['valign' => 'center']);
            $medio->addText(mb_strtoupper((string) ($inst['escuela']->nombre ?? 'CENTRO UNIVERSITARIO MOCTEZUMA')), ['bold' => true, 'size' => 13, 'color' => '006492'], ['alignment' => Jc::CENTER]);
            $medio->addText(mb_strtoupper($filtros['titulo'] ?? 'LISTA DE GRUPO'), ['bold' => true, 'size' => 11, 'color' => '1F2937'], ['alignment' => Jc::CENTER]);
            $derecho = $header->addCell((int) ($totalWidth * .19), ['valign' => 'center']);
            $logo = $this->listas->logoLicenciatura($grupo['licenciatura_id']);
            if ($logo) $derecho->addImage($logo, ['width' => 68, 'height' => 52, 'ratio' => true]);
            $seccion->addText(mb_strtoupper($grupo['titulo']), ['bold' => true, 'size' => 10, 'color' => '006492'], ['alignment' => Jc::CENTER, 'spaceAfter' => 80]);
            if (! empty($filtros['ciclo_escolar'])) $seccion->addText('CICLO ESCOLAR: ' . $filtros['ciclo_escolar'], ['size' => 8, 'bold' => true], ['alignment' => Jc::CENTER]);
            if (! empty($filtros['subtitulo'])) $seccion->addText($filtros['subtitulo'], ['size' => 8], ['alignment' => Jc::CENTER]);
            $tabla = $seccion->addTable(['borderSize' => 5, 'borderColor' => 'A9B8C8', 'cellMargin' => 55]);
            $tabla->addRow(null, ['tblHeader' => true, 'cantSplit' => true]);
            foreach ($titulos as $i => $titulo) {
                $celda = $tabla->addCell($anchos[$i], ['bgColor' => '006492', 'valign' => 'center']);
                $celda->addText(mb_strtoupper($titulo), ['bold' => true, 'size' => count($titulos) > 11 ? 6 : 7, 'color' => 'FFFFFF'], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
            }
            foreach ($this->listas->tabla($grupo, $filtros) as $k => $fila) {
                $tabla->addRow(null, ['cantSplit' => true]);
                foreach ($fila as $i => $valor) {
                    $celda = $tabla->addCell($anchos[$i], ['bgColor' => $k % 2 ? 'F2F7FA' : 'FFFFFF', 'valign' => 'center']);
                    $celda->addText($valor, ['size' => count($titulos) > 11 ? 6 : 7, 'color' => '203149'], ['spaceAfter' => 0]);
                }
            }
            $seccion->addText('TOTAL DE ALUMNOS: ' . count($grupo['alumnos']), ['bold' => true, 'size' => 8, 'color' => '006492'], ['alignment' => Jc::END, 'spaceBefore' => 90]);
        }
        $archivo = $base . '/lista-pro-' . Str::uuid() . '.docx';
        IOFactory::createWriter($word, 'Word2007')->save($archivo);
        return $archivo;
    }

    /** Embebe imágenes institucionales en el PDF para funcionar sin public/storage. */
    private function dataUri(?string $archivo): ?string
    {
        if (! $archivo || ! is_file($archivo)) return null;
        $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
        $mime = match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            default => null,
        };
        if (! $mime) return null;
        $contenido = @file_get_contents($archivo);
        if ($contenido === false) return null;
        return 'data:' . $mime . ';base64,' . base64_encode($contenido);
    }

    private function preparar(array $grupos, array $filtros): array
    {
        return array_map(fn ($g) => $g + ['filas' => $this->listas->tabla($g, $filtros)], $grupos);
    }

    private function horizontal(array $headers, array $filtros): bool
    {
        if (($filtros['orientacion'] ?? 'auto') === 'horizontal') return true;
        if (($filtros['orientacion'] ?? 'auto') === 'vertical') return false;
        return count($headers) > 5 || array_sum(array_map('mb_strlen', $headers)) > 55;
    }

    private function anchos(array $columnas, int $disponible): array
    {
        $pesos = array_map(fn ($c) => match ($c) {
            'numero', 'sexo', 'edad' => .65,
            'CURP', 'nombre_completo', 'observaciones' => 1.8,
            'apellido_paterno', 'apellido_materno', 'nombre', 'licenciatura' => 1.25,
            default => 1.0,
        }, $columnas);
        $total = array_sum($pesos);
        return array_map(fn ($w) => (int) round($w / $total * $disponible), $pesos);
    }
}
