<?php

namespace App\Services;

use Illuminate\Support\Str;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

class ListaProfesorChecklistWordService
{
    private const AZUL = '006492';
    private const VERDE = '88AC2E';
    private const TEXTO = '1F2937';
    private const SUAVE = '64748B';
    private const BORDE = 'CBD5E1';
    private const FONDO = 'F8FAFC';
    private const FONDO_AZUL = 'EAF4F8';

    public function generar(array $reporte): string
    {
        $tempPath = storage_path('app/phpword-temp');
        if (! is_dir($tempPath)) {
            mkdir($tempPath, 0775, true);
        }

        Settings::setTempDir($tempPath);

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(8.5);
        $phpWord->getSettings()->setUpdateFields(true);

        $section = $phpWord->addSection($this->configuracionHorizontal());
        $this->agregarPie($section, $reporte);
        $this->agregarEncabezado($section, $reporte);
        $this->agregarResumen($section, $reporte);

        if ($reporte['profesores']->isEmpty()) {
            $section->addText(
                'No hay profesores con materias en horario para el ciclo, periodo y filtros seleccionados.',
                ['bold' => true, 'size' => 10, 'color' => self::SUAVE],
                ['alignment' => Jc::CENTER, 'spaceBefore' => 300, 'spaceAfter' => 0]
            );
        } else {
            foreach ($reporte['profesores'] as $indice => $profesor) {
                if ($indice > 0) {
                    $section->addTextBreak(1, ['size' => 2], ['spaceBefore' => 0, 'spaceAfter' => 0]);
                }

                $this->agregarProfesor($section, $profesor, $indice + 1);
            }
        }

        $ruta = $tempPath . DIRECTORY_SEPARATOR . 'checklist-listas-profesores-' . Str::uuid() . '.docx';
        IOFactory::createWriter($phpWord, 'Word2007')->save($ruta);

        return $ruta;
    }

    private function configuracionHorizontal(): array
    {
        return [
            'orientation' => 'landscape',
            'pageSizeW' => Converter::inchToTwip(11),
            'pageSizeH' => Converter::inchToTwip(8.5),
            'marginTop' => Converter::cmToTwip(1.05),
            'marginBottom' => Converter::cmToTwip(1.25),
            'marginLeft' => Converter::cmToTwip(1.0),
            'marginRight' => Converter::cmToTwip(1.0),
            'headerHeight' => Converter::cmToTwip(0.3),
            'footerHeight' => Converter::cmToTwip(0.55),
        ];
    }

    private function agregarEncabezado($section, array $reporte): void
    {
        $tabla = $section->addTable([
            'cellMarginTop' => 20,
            'cellMarginBottom' => 20,
            'cellMarginLeft' => 40,
            'cellMarginRight' => 40,
        ]);
        $fila = $tabla->addRow(900, ['cantSplit' => true]);

        $logo = $fila->addCell(1900, ['valign' => 'center']);
        if ($rutaLogo = $this->rutaLogo()) {
            $logo->addImage($rutaLogo, [
                'width' => 92,
                'height' => 46,
                'alignment' => Jc::CENTER,
            ]);
        }

        $centro = $fila->addCell(9700, ['valign' => 'center']);
        $centro->addText(
            mb_strtoupper((string) ($reporte['contexto']['escuela']?->nombre ?? 'Centro Universitario Moctezuma')),
            ['bold' => true, 'size' => 15, 'color' => self::AZUL],
            ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 10]
        );
        $centro->addText(
            'CONTROL DE ENTREGA DE LISTAS A PROFESORES',
            ['bold' => true, 'size' => 11, 'color' => self::TEXTO],
            ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]
        );

        $cct = $fila->addCell(2500, ['valign' => 'center']);
        $cct->addText(
            'C.C.T. ' . ($reporte['contexto']['escuela']?->CCT ?? '12PSU0173I'),
            ['bold' => true, 'size' => 8, 'color' => self::SUAVE],
            ['alignment' => Jc::RIGHT, 'spaceBefore' => 0, 'spaceAfter' => 0]
        );

        $section->addTextBreak(1, ['size' => 2], ['spaceBefore' => 0, 'spaceAfter' => 0]);

        $meta = $section->addTable([
            'borderSize' => 4,
            'borderColor' => self::BORDE,
            'cellMarginTop' => 50,
            'cellMarginBottom' => 50,
            'cellMarginLeft' => 80,
            'cellMarginRight' => 80,
        ]);
        $filaMeta = $meta->addRow(null, ['cantSplit' => true]);
        $this->agregarMeta($filaMeta, 'CICLO ESCOLAR', $reporte['contexto']['ciclo_escolar'] ?: 'SIN CONFIGURAR', 3500);
        $this->agregarMeta($filaMeta, 'PERIODO', $reporte['contexto']['periodo_escolar'] ?: 'SIN CONFIGURAR', 3000);
        $this->agregarMeta($filaMeta, 'FECHA', $reporte['fecha_emision']->format('d/m/Y H:i'), 3000);
        $this->agregarMeta($filaMeta, 'FILTRO', $this->filtroCorto($reporte), 4900);
    }

    private function agregarResumen($section, array $reporte): void
    {
        $section->addTextBreak(1, ['size' => 2], ['spaceBefore' => 0, 'spaceAfter' => 0]);

        $tabla = $section->addTable([
            'cellMarginTop' => 70,
            'cellMarginBottom' => 70,
            'cellMarginLeft' => 60,
            'cellMarginRight' => 60,
        ]);
        $fila = $tabla->addRow(null, ['cantSplit' => true]);

        $datos = [
            ['PROFESORES', $reporte['resumen']['profesores']],
            ['MATERIAS / GRUPOS', $reporte['resumen']['materias_grupos']],
            ['ASISTENCIAS', $reporte['resumen']['asistencias']],
            ['EVALUACIONES', $reporte['resumen']['evaluaciones']],
            ['DOCUMENTOS', $reporte['resumen']['documentos']],
        ];

        foreach ($datos as [$etiqueta, $valor]) {
            $celda = $fila->addCell(2820, [
                'bgColor' => $etiqueta === 'DOCUMENTOS' ? self::VERDE : self::FONDO_AZUL,
                'valign' => 'center',
            ]);
            $color = $etiqueta === 'DOCUMENTOS' ? 'FFFFFF' : self::AZUL;
            $celda->addText((string) $valor, ['bold' => true, 'size' => 13, 'color' => $color], [
                'alignment' => Jc::CENTER,
                'spaceBefore' => 0,
                'spaceAfter' => 0,
            ]);
            $celda->addText($etiqueta, ['bold' => true, 'size' => 7, 'color' => $color], [
                'alignment' => Jc::CENTER,
                'spaceBefore' => 0,
                'spaceAfter' => 0,
            ]);
        }

        $section->addTextBreak(1, ['size' => 3], ['spaceBefore' => 0, 'spaceAfter' => 0]);
    }

    private function agregarProfesor($section, array $profesor, int $numero): void
    {
        $cabecera = $section->addTable([
            'cellMarginTop' => 65,
            'cellMarginBottom' => 65,
            'cellMarginLeft' => 90,
            'cellMarginRight' => 90,
        ]);
        $fila = $cabecera->addRow(null, ['cantSplit' => true]);

        $nombre = $fila->addCell(10100, ['bgColor' => self::AZUL, 'valign' => 'center']);
        $nombre->addText(
            str_pad((string) $numero, 2, '0', STR_PAD_LEFT) . '. ' . mb_strtoupper($profesor['nombre'], 'UTF-8'),
            ['bold' => true, 'size' => 9.2, 'color' => 'FFFFFF'],
            ['spaceBefore' => 0, 'spaceAfter' => 0]
        );

        $totales = $fila->addCell(4000, ['bgColor' => self::VERDE, 'valign' => 'center']);
        $totales->addText(
            $profesor['total_listas'] . ' materias/grupos · ' . $profesor['total_documentos'] . ' documentos',
            ['bold' => true, 'size' => 8, 'color' => 'FFFFFF'],
            ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]
        );

        $tabla = $section->addTable([
            'borderSize' => 4,
            'borderColor' => self::BORDE,
            'cellMarginTop' => 45,
            'cellMarginBottom' => 45,
            'cellMarginLeft' => 55,
            'cellMarginRight' => 55,
        ]);

        $this->cabeceraTabla($tabla, [
            ['#', 520],
            ['MATERIA', 3100],
            ['LICENCIATURA', 2900],
            ['MODALIDAD', 1450],
            ['CUATR.', 800],
            ['GENERACIÓN', 1300],
            ['ASIST.', 850],
            ['EVAL.', 850],
            ['OBSERVACIONES', 2330],
        ]);

        foreach ($profesor['registros'] as $indice => $registro) {
            $filaDetalle = $tabla->addRow(null, ['cantSplit' => true]);
            $this->celda($filaDetalle, (string) ($indice + 1), 520, Jc::CENTER);
            $this->celda($filaDetalle, (string) $registro->materia, 3100);
            $this->celda($filaDetalle, (string) $registro->licenciatura, 2900);
            $this->celda($filaDetalle, (string) $registro->modalidad, 1450, Jc::CENTER);
            $this->celda($filaDetalle, (string) $registro->cuatrimestre . '°', 800, Jc::CENTER);
            $this->celda($filaDetalle, (string) $registro->generacion, 1300, Jc::CENTER);
            $this->celda($filaDetalle, '☐', 850, Jc::CENTER, true, 12);
            $this->celda($filaDetalle, '☐', 850, Jc::CENTER, true, 12);
            $this->celda($filaDetalle, '________________________________', 2330, Jc::LEFT, false, 7);
        }

        $control = $section->addTable([
            'borderSize' => 4,
            'borderColor' => self::BORDE,
            'cellMarginTop' => 70,
            'cellMarginBottom' => 70,
            'cellMarginLeft' => 90,
            'cellMarginRight' => 90,
        ]);
        $filaControl = $control->addRow(null, ['cantSplit' => true]);
        $this->celda($filaControl, '☐ PAQUETE COMPLETO ENTREGADO', 4100, Jc::LEFT, true, 8);
        $this->celda($filaControl, 'FECHA: __________________', 2500, Jc::LEFT, true, 8);
        $this->celda($filaControl, 'RECIBIÓ / FIRMA: ____________________________________', 7500, Jc::LEFT, true, 8);
    }

    private function cabeceraTabla($tabla, array $columnas): void
    {
        $fila = $tabla->addRow(null, ['cantSplit' => true]);
        foreach ($columnas as [$texto, $ancho]) {
            $celda = $fila->addCell($ancho, ['bgColor' => 'E2E8F0', 'valign' => 'center']);
            $celda->addText($texto, ['bold' => true, 'size' => 7.2, 'color' => self::TEXTO], [
                'alignment' => Jc::CENTER,
                'spaceBefore' => 0,
                'spaceAfter' => 0,
            ]);
        }
    }

    private function celda(
        $fila,
        string $texto,
        int $ancho,
        string $alineacion = Jc::LEFT,
        bool $bold = false,
        float $size = 7.4
    ): void {
        $celda = $fila->addCell($ancho, ['valign' => 'center']);
        $celda->addText($texto, ['bold' => $bold, 'size' => $size, 'color' => self::TEXTO], [
            'alignment' => $alineacion,
            'spaceBefore' => 0,
            'spaceAfter' => 0,
        ]);
    }

    private function agregarMeta($fila, string $etiqueta, string $valor, int $ancho): void
    {
        $celda = $fila->addCell($ancho, ['valign' => 'center', 'bgColor' => self::FONDO]);
        $run = $celda->addTextRun(['spaceBefore' => 0, 'spaceAfter' => 0]);
        $run->addText($etiqueta . ': ', ['bold' => true, 'size' => 7.2, 'color' => self::SUAVE]);
        $run->addText(mb_strtoupper($valor, 'UTF-8'), ['bold' => true, 'size' => 7.4, 'color' => self::TEXTO]);
    }

    private function agregarPie($section, array $reporte): void
    {
        $footer = $section->addFooter();
        $tabla = $footer->addTable([
            'cellMarginTop' => 0,
            'cellMarginBottom' => 0,
            'cellMarginLeft' => 20,
            'cellMarginRight' => 20,
        ]);
        $fila = $tabla->addRow();
        $izquierda = $fila->addCell(10500);
        $izquierda->addText(
            'Control de entrega · ' . ($reporte['contexto']['ciclo_escolar'] ?: 'Sin ciclo') . ' · ' . ($reporte['contexto']['periodo_escolar'] ?: 'Sin periodo'),
            ['size' => 7, 'color' => self::SUAVE],
            ['spaceBefore' => 0, 'spaceAfter' => 0]
        );
        $derecha = $fila->addCell(3600);
        $derecha->addPreserveText(
            'Página {PAGE} de {NUMPAGES}',
            ['size' => 7, 'color' => self::SUAVE],
            ['alignment' => Jc::RIGHT, 'spaceBefore' => 0, 'spaceAfter' => 0]
        );
    }

    private function filtroCorto(array $reporte): string
    {
        $partes = [];
        foreach (['licenciatura', 'modalidad', 'generacion'] as $campo) {
            $valor = $reporte['filtros_texto'][$campo] ?? 'Todas';
            if (! in_array(mb_strtoupper((string) $valor), ['TODAS', 'TODOS'], true)) {
                $partes[] = (string) $valor;
            }
        }

        return $partes ? implode(' · ', $partes) : 'Todos';
    }

    private function rutaLogo(): ?string
    {
        $rutas = [
            public_path('storage/letra2.jpg'),
            public_path('storage/logo.png'),
            public_path('storage/logo-moctezuma.jpg'),
            public_path('storage/moctezuma.png'),
        ];

        foreach ($rutas as $ruta) {
            if (is_file($ruta)) {
                return $ruta;
            }
        }

        return null;
    }
}
