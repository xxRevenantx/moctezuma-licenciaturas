<?php

namespace App\Services;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

class PlantillaDocenteWordService
{
    public function generar(array $reporte, array $escuela = []): string
    {
        $directorio = storage_path('app/phpword-temp');
        if (! is_dir($directorio)) {
            mkdir($directorio, 0775, true);
        }
        Settings::setTempDir($directorio);
        $word = new PhpWord();
        $word->setDefaultFontName('Arial');
        $word->setDefaultFontSize(8);
        $seccion = $word->addSection([
            'orientation' => 'landscape',
            'pageSizeW' => Converter::inchToTwip(11),
            'pageSizeH' => Converter::inchToTwip(8.5),
            'marginTop' => Converter::cmToTwip(2.2),
            'marginBottom' => Converter::cmToTwip(1.5),
            'marginLeft' => Converter::cmToTwip(1.4),
            'marginRight' => Converter::cmToTwip(1.4),
            'headerHeight' => Converter::cmToTwip(0.8),
        ]);
        $cabecera = $seccion->addHeader();
        $logo = collect([public_path('storage/logo.png'), public_path('storage/logo-moctezuma.jpg')])
            ->first(fn ($archivo) => is_file($archivo));
        if ($logo) {
            $cabecera->addImage($logo, ['height' => 37, 'alignment' => Jc::CENTER]);
        }
        $cabecera->addText($escuela['nombre'] ?? 'CENTRO UNIVERSITARIO MOCTEZUMA A.C.',
            ['bold' => true, 'size' => 9, 'color' => '006492'], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $cabecera->addText('C.C.T. '.($escuela['CCT'] ?? '12PSU0173I'),
            ['size' => 7, 'color' => '526F82'], ['alignment' => Jc::CENTER]);
        $pie = $seccion->addFooter();
        $pie->addText('Control Escolar  ·  '.$reporte['ciclo'].' / '.$reporte['periodo'].'  ·  Emitido: '.$reporte['fecha'],
            ['size' => 7, 'color' => '789099'], ['alignment' => Jc::CENTER]);
        $columnas = self::columnas($reporte);
        $anchos = [600, 2450, 3100, 1230, 1250, 1200, 800, 850];
        $columnasAncho = [];
        foreach ($columnas as $key => $titulo) {
            $columnasAncho[$key] = $anchos[array_search($key, array_keys(self::columnasCompletas()), true)];
        }
        // Ajustar anchos al espacio horizontal disponible sin tablas fuera de página.
        $disponible = Converter::cmToTwip(25.1);
        $escala = $disponible / array_sum($columnasAncho);
        $columnasAncho = array_map(fn ($v) => (int) ($v * $escala), $columnasAncho);
        if ($reporte['grupos'] === []) {
            $seccion->addText('PLANTILLA DOCENTE POR LICENCIATURA', ['bold' => true, 'size' => 14], ['alignment' => Jc::CENTER]);
            $seccion->addText('No hay profesores programados para los filtros seleccionados.');
        }
        foreach ($reporte['grupos'] as $indice => $grupo) {
            if ($indice > 0) {
                $seccion->addPageBreak();
            }
            $seccion->addText('PLANTILLA DOCENTE POR LICENCIATURA',
                ['bold' => true, 'size' => 13, 'color' => '006492'], ['alignment' => Jc::CENTER, 'spaceAfter' => 140]);
            $seccion->addText(mb_strtoupper($grupo['licenciatura']),
                ['bold' => true, 'size' => 10], ['spaceAfter' => 60]);
            $seccion->addText('Ciclo: '.$reporte['ciclo'].'   Periodo: '.$reporte['periodo'].'   Fuente: Profesores con horario programado',
                ['size' => 8, 'color' => '456379'], ['spaceAfter' => 140]);
            $tabla = $seccion->addTable(['borderSize' => 5, 'borderColor' => '8699A5', 'cellMargin' => 70]);
            $tabla->addRow(390, ['tblHeader' => true, 'cantSplit' => true]);
            foreach ($columnas as $key => $titulo) {
                $tabla->addCell($columnasAncho[$key], ['bgColor' => 'B8CAE8', 'valign' => 'center'])
                    ->addText($titulo, ['bold' => true, 'size' => 7.5], ['alignment' => Jc::CENTER]);
            }
            foreach ($grupo['docentes'] as $numero => $docente) {
                $tabla->addRow(null, ['cantSplit' => true]);
                foreach (self::fila($docente, $numero + 1, $reporte) as $key => $valor) {
                    $tabla->addCell($columnasAncho[$key], ['valign' => 'center', 'bgColor' => $numero % 2 ? 'F3F7F9' : 'FFFFFF'])
                        ->addText((string) $valor, ['size' => 7.5], ['alignment' => in_array($key, ['no', 'bloques', 'horas'], true) ? Jc::CENTER : Jc::LEFT]);
                }
            }
            $seccion->addTextBreak();
            $seccion->addText('TOTAL LICENCIATURA: '.$grupo['total_docentes'].' docentes'.($reporte['mostrar_horas'] ? ' · '.$grupo['horas'].' h programadas' : ''),
                ['bold' => true, 'size' => 9, 'color' => '006492']);
        }
        $ruta = tempnam($directorio, 'plantilla_docente_');
        if ($ruta === false) {
            throw new \RuntimeException('No se pudo crear el archivo temporal de Word.');
        }
        IOFactory::createWriter($word, 'Word2007')->save($ruta);
        return $ruta;
    }

    public static function columnasCompletas(): array
    {
        return ['no' => 'No.', 'nombre' => 'Nombre del profesor', 'materias' => 'Materias',
            'cuatrimestres' => 'Cuatrimestres', 'modalidades' => 'Modalidades',
            'generaciones' => 'Generaciones', 'bloques' => 'Bloques', 'horas' => 'Horas'];
    }

    public static function columnas(array $reporte): array
    {
        return array_filter(self::columnasCompletas(), fn ($key) =>
            ($key !== 'materias' || $reporte['mostrar_materias'])
            && ($key !== 'generaciones' || $reporte['mostrar_generaciones'])
            && (! in_array($key, ['bloques', 'horas'], true) || $reporte['mostrar_horas']), ARRAY_FILTER_USE_KEY);
    }

    public static function fila(array $docente, int $numero, array $reporte): array
    {
        $datos = [
            'no' => $numero,
            'nombre' => $docente['nombre'],
            'materias' => implode(', ', $docente['materias']) ?: '—',
            'cuatrimestres' => implode(', ', $docente['cuatrimestres']) ?: '—',
            'modalidades' => implode(', ', $docente['modalidades']) ?: '—',
            'generaciones' => implode(', ', $docente['generaciones']) ?: '—',
            'bloques' => $docente['bloques'],
            'horas' => $docente['horas'],
        ];
        return array_intersect_key($datos, self::columnas($reporte));
    }
}
