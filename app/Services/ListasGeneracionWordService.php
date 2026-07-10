<?php

namespace App\Services;

use Illuminate\Support\Str;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

class ListasGeneracionWordService
{
    private const AZUL = '006492';

    private const VERDE = '88AC2E';

    private const BORDE = '94A3B8';

    private const TEXTO = '1F2937';

    private const TEXTO_SUAVE = '64748B';

    private const FONDO_SUAVE = 'EEF6F8';

    private const LOCAL = '5B21B6';

    private const FORANEO = 'B91C1C';

    public function generar(array $reporte): string
    {
        $tempPath = storage_path('app/phpword-temp');

        if (! is_dir($tempPath)) {
            mkdir($tempPath, 0775, true);
        }

        Settings::setTempDir($tempPath);

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(9);
        $phpWord->getSettings()->setUpdateFields(true);

        $section = $phpWord->addSection($this->configuracionCartaVertical());
        $this->agregarPiePagina($section, $reporte);

        foreach ($reporte['listas'] as $indice => $lista) {
            if ($indice > 0) {
                $section->addPageBreak();
            }

            $this->agregarListaLicenciatura($section, $reporte, $lista);
        }

        $sectionGeneral = $phpWord->addSection($this->configuracionCartaHorizontal());
        $this->agregarPiePagina($sectionGeneral, $reporte);
        $this->agregarListaGeneral($sectionGeneral, $reporte);

        $ruta = $tempPath . DIRECTORY_SEPARATOR . 'listas-generacion-' . Str::uuid() . '.docx';
        IOFactory::createWriter($phpWord, 'Word2007')->save($ruta);

        return $ruta;
    }

    private function configuracionCartaVertical(): array
    {
        return [
            'pageSizeW' => Converter::inchToTwip(8.5),
            'pageSizeH' => Converter::inchToTwip(11),
            'marginTop' => Converter::cmToTwip(1.45),
            'marginBottom' => Converter::cmToTwip(1.65),
            'marginLeft' => Converter::cmToTwip(1.15),
            'marginRight' => Converter::cmToTwip(1.15),
            'headerHeight' => Converter::cmToTwip(0.4),
            'footerHeight' => Converter::cmToTwip(0.65),
        ];
    }

    private function configuracionCartaHorizontal(): array
    {
        return [
            'orientation' => 'landscape',
            'pageSizeW' => Converter::inchToTwip(11),
            'pageSizeH' => Converter::inchToTwip(8.5),
            'marginTop' => Converter::cmToTwip(1.25),
            'marginBottom' => Converter::cmToTwip(1.55),
            'marginLeft' => Converter::cmToTwip(1.15),
            'marginRight' => Converter::cmToTwip(1.15),
            'headerHeight' => Converter::cmToTwip(0.35),
            'footerHeight' => Converter::cmToTwip(0.6),
        ];
    }

    private function agregarListaLicenciatura(Section $section, array $reporte, array $lista): void
    {
        $this->agregarEncabezado(
            $section,
            'LISTA DE ALUMNOS POR GENERACIÓN',
            $this->rutaImagenLicenciatura($lista['licenciatura']->imagen)
        );

        $this->agregarMetadatos($section, $reporte);

        $tablaTitulo = $section->addTable([
            'cellMarginTop' => 70,
            'cellMarginBottom' => 70,
            'cellMarginLeft' => 100,
            'cellMarginRight' => 100,
        ]);
        $filaTitulo = $tablaTitulo->addRow(null, ['cantSplit' => true]);
        $celdaTitulo = $filaTitulo->addCell(10640, [
            'bgColor' => self::AZUL,
            'valign' => 'center',
        ]);
        $celdaTitulo->addText(
            'LICENCIATURA EN ' . mb_strtoupper((string) $lista['licenciatura']->nombre),
            [
                'name' => 'Arial',
                'size' => 10.5,
                'bold' => true,
                'color' => 'FFFFFF',
            ],
            [
                'spaceBefore' => 0,
                'spaceAfter' => 0,
            ]
        );

        $section->addTextBreak(1, ['size' => 2], ['spaceAfter' => 0, 'spaceBefore' => 0]);

        $tabla = $section->addTable($this->estiloTabla());
        $this->agregarCabeceraTabla($tabla, [
            ['N.º', 700],
            ['MATRÍCULA', 2050],
            ['NOMBRE COMPLETO', 5950],
            ['PROCEDENCIA', 1940],
        ]);

        if ($lista['alumnos']->isEmpty()) {
            $fila = $tabla->addRow(null, ['cantSplit' => true]);
            $celda = $fila->addCell(10640, [
                'gridSpan' => 4,
                'valign' => 'center',
            ]);
            $celda->addText(
                'SIN ALUMNOS REGISTRADOS PARA EL FILTRO SELECCIONADO',
                ['italic' => true, 'color' => self::TEXTO_SUAVE, 'size' => 8.5],
                ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'spaceBefore' => 0]
            );
        } else {
            foreach ($lista['alumnos'] as $indice => $alumno) {
                $fila = $tabla->addRow(null, ['cantSplit' => true]);

                $this->agregarCelda(
                    $fila,
                    (string) ($indice + 1),
                    700,
                    ['alignment' => Jc::CENTER]
                );
                $this->agregarCelda(
                    $fila,
                    (string) $alumno->matricula,
                    2050,
                    ['alignment' => Jc::CENTER]
                );
                $this->agregarCelda(
                    $fila,
                    mb_strtoupper(trim(
                        $alumno->apellido_paterno . ' ' .
                        $alumno->apellido_materno . ' ' .
                        $alumno->nombre
                    )),
                    5950
                );
                $this->agregarCelda(
                    $fila,
                    $alumno->foraneo === 'true' ? 'FORÁNEO' : 'LOCAL',
                    1940,
                    [
                        'alignment' => Jc::CENTER,
                        'bold' => true,
                        'color' => $alumno->foraneo === 'true' ? self::FORANEO : self::LOCAL,
                    ]
                );
            }
        }

        $section->addText(
            "LOCALES: {$lista['locales']}   |   FORÁNEOS: {$lista['foraneos']}   |   TOTAL LICENCIATURA: {$lista['total']}",
            ['bold' => true, 'size' => 8.5, 'color' => self::TEXTO],
            ['alignment' => Jc::RIGHT, 'spaceBefore' => 100, 'spaceAfter' => 0]
        );
    }

    private function agregarListaGeneral(Section $section, array $reporte): void
    {
        $this->agregarEncabezado($section, 'LISTA GENERAL DE ALUMNOS DE LA GENERACIÓN');
        $this->agregarMetadatos($section, $reporte, true);

        $tabla = $section->addTable($this->estiloTabla());
        $this->agregarCabeceraTabla($tabla, [
            ['N.º', 650],
            ['MATRÍCULA', 1800],
            ['NOMBRE COMPLETO', 3900],
            ['LICENCIATURA', 4300],
            ['GENERACIÓN', 1750],
            ['PROCEDENCIA', 1700],
        ]);

        foreach ($reporte['listas'] as $lista) {
            $filaGrupo = $tabla->addRow(null, ['cantSplit' => true]);
            $celdaGrupo = $filaGrupo->addCell(14100, [
                'gridSpan' => 6,
                'bgColor' => 'EAF3F6',
                'valign' => 'center',
            ]);
            $celdaGrupo->addText(
                'LICENCIATURA EN ' . mb_strtoupper((string) $lista['licenciatura']->nombre) .
                ' — TOTAL: ' . $lista['total'],
                ['bold' => true, 'color' => self::AZUL, 'size' => 8.5],
                ['spaceAfter' => 0, 'spaceBefore' => 0]
            );

            if ($lista['alumnos']->isEmpty()) {
                $fila = $tabla->addRow(null, ['cantSplit' => true]);
                $celda = $fila->addCell(14100, [
                    'gridSpan' => 6,
                    'valign' => 'center',
                ]);
                $celda->addText(
                    'SIN ALUMNOS REGISTRADOS PARA EL FILTRO SELECCIONADO',
                    ['italic' => true, 'color' => self::TEXTO_SUAVE, 'size' => 8],
                    ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'spaceBefore' => 0]
                );

                continue;
            }

            foreach ($lista['alumnos'] as $indice => $alumno) {
                $fila = $tabla->addRow(null, ['cantSplit' => true]);

                $this->agregarCelda($fila, (string) ($indice + 1), 650, [
                    'alignment' => Jc::CENTER,
                    'size' => 7.8,
                ]);
                $this->agregarCelda($fila, (string) $alumno->matricula, 1800, [
                    'alignment' => Jc::CENTER,
                    'size' => 7.8,
                ]);
                $this->agregarCelda(
                    $fila,
                    mb_strtoupper(trim(
                        $alumno->apellido_paterno . ' ' .
                        $alumno->apellido_materno . ' ' .
                        $alumno->nombre
                    )),
                    3900,
                    ['size' => 7.8]
                );
                $this->agregarCelda(
                    $fila,
                    mb_strtoupper((string) $lista['licenciatura']->nombre),
                    4300,
                    ['size' => 7.6]
                );
                $this->agregarCelda(
                    $fila,
                    (string) $reporte['generacion']->generacion,
                    1750,
                    ['alignment' => Jc::CENTER, 'size' => 7.8]
                );
                $this->agregarCelda(
                    $fila,
                    $alumno->foraneo === 'true' ? 'FORÁNEO' : 'LOCAL',
                    1700,
                    [
                        'alignment' => Jc::CENTER,
                        'bold' => true,
                        'size' => 7.8,
                        'color' => $alumno->foraneo === 'true' ? self::FORANEO : self::LOCAL,
                    ]
                );
            }
        }

        $section->addTextBreak(1, ['size' => 4], ['spaceAfter' => 0, 'spaceBefore' => 0]);
        $this->agregarEstadistica($section, $reporte);
    }

    private function agregarEncabezado(
        Section $section,
        string $titulo,
        ?string $imagenDerecha = null
    ): void {
        $tabla = $section->addTable([
            'cellMarginTop' => 0,
            'cellMarginBottom' => 0,
            'cellMarginLeft' => 40,
            'cellMarginRight' => 40,
        ]);
        $fila = $tabla->addRow(1100, ['cantSplit' => true]);

        $logoInstitucional = $this->rutaLogoInstitucional();
        $izquierda = $fila->addCell(1900, ['valign' => 'center']);
        if ($logoInstitucional) {
            $izquierda->addImage($logoInstitucional, [
                'width' => 66,
                'height' => 52,
                'alignment' => Jc::CENTER,
            ]);
        }

        $centro = $fila->addCell(6840, ['valign' => 'center']);
        $centro->addText(
            'CENTRO UNIVERSITARIO MOCTEZUMA',
            ['bold' => true, 'size' => 15.5, 'color' => self::AZUL],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 30, 'spaceBefore' => 0]
        );
        $centro->addText(
            $titulo,
            ['bold' => true, 'size' => 10.5, 'color' => self::TEXTO],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'spaceBefore' => 0]
        );

        $derecha = $fila->addCell(1900, ['valign' => 'center']);
        if ($imagenDerecha) {
            $derecha->addImage($imagenDerecha, [
                'width' => 62,
                'height' => 50,
                'alignment' => Jc::CENTER,
            ]);
        }

        $section->addTextBreak(1, ['size' => 2], ['spaceAfter' => 0, 'spaceBefore' => 0]);
    }

    private function agregarMetadatos(Section $section, array $reporte, bool $horizontal = false): void
    {
        $ancho = $horizontal ? 14100 : 10640;
        $anchoEtiqueta = $horizontal ? 2050 : 1900;
        $anchoValor = (int) (($ancho - ($anchoEtiqueta * 2)) / 2);

        $tabla = $section->addTable([
            'borderSize' => 4,
            'borderColor' => 'CBD5E1',
            'cellMarginTop' => 55,
            'cellMarginBottom' => 55,
            'cellMarginLeft' => 80,
            'cellMarginRight' => 80,
        ]);

        $this->agregarFilaMeta($tabla, $anchoEtiqueta, $anchoValor, [
            'GENERACIÓN',
            (string) $reporte['generacion']->generacion,
            'PROCEDENCIA',
            (string) $reporte['procedenciaTexto'],
        ]);
        $this->agregarFilaMeta($tabla, $anchoEtiqueta, $anchoValor, [
            'CICLO ESCOLAR',
            (string) $reporte['cicloEscolar'],
            'PERIODO',
            (string) $reporte['periodoEscolar'],
        ]);
        $this->agregarFilaMeta($tabla, $anchoEtiqueta, $anchoValor, [
            'FECHA DE EMISIÓN',
            $reporte['fechaEmision']->format('d/m/Y'),
            'TOTAL GENERAL',
            (string) $reporte['totalGeneral'],
        ]);

        $section->addTextBreak(1, ['size' => 3], ['spaceAfter' => 0, 'spaceBefore' => 0]);
    }

    private function agregarFilaMeta($tabla, int $anchoEtiqueta, int $anchoValor, array $valores): void
    {
        $fila = $tabla->addRow(null, ['cantSplit' => true]);

        for ($i = 0; $i < 4; $i++) {
            $esEtiqueta = $i % 2 === 0;
            $celda = $fila->addCell($esEtiqueta ? $anchoEtiqueta : $anchoValor, [
                'bgColor' => $esEtiqueta ? self::FONDO_SUAVE : 'FFFFFF',
                'valign' => 'center',
            ]);
            $celda->addText(
                (string) $valores[$i],
                [
                    'bold' => $esEtiqueta,
                    'size' => $esEtiqueta ? 8 : 8.5,
                    'color' => $esEtiqueta ? self::AZUL : self::TEXTO,
                ],
                ['spaceAfter' => 0, 'spaceBefore' => 0]
            );
        }
    }

    private function agregarCabeceraTabla($tabla, array $columnas): void
    {
        $fila = $tabla->addRow(null, [
            'tblHeader' => true,
            'cantSplit' => true,
        ]);

        foreach ($columnas as [$texto, $ancho]) {
            $celda = $fila->addCell($ancho, [
                'bgColor' => self::VERDE,
                'valign' => 'center',
            ]);
            $celda->addText(
                $texto,
                ['bold' => true, 'color' => 'FFFFFF', 'size' => 8],
                ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'spaceBefore' => 0]
            );
        }
    }

    private function agregarCelda($fila, string $texto, int $ancho, array $opciones = []): void
    {
        $celda = $fila->addCell($ancho, [
            'valign' => 'center',
        ]);

        $celda->addText(
            $texto,
            [
                'name' => 'Arial',
                'size' => $opciones['size'] ?? 8.5,
                'bold' => $opciones['bold'] ?? false,
                'color' => $opciones['color'] ?? self::TEXTO,
            ],
            [
                'alignment' => $opciones['alignment'] ?? Jc::LEFT,
                'spaceAfter' => 0,
                'spaceBefore' => 0,
            ]
        );
    }

    private function agregarEstadistica(Section $section, array $reporte): void
    {
        $tabla = $section->addTable([
            'alignment' => Jc::RIGHT,
            'borderSize' => 5,
            'borderColor' => self::BORDE,
            'cellMarginTop' => 60,
            'cellMarginBottom' => 60,
            'cellMarginLeft' => 100,
            'cellMarginRight' => 100,
        ]);

        $cabecera = $tabla->addRow(null, ['cantSplit' => true]);
        $celdaCabecera = $cabecera->addCell(5300, [
            'gridSpan' => 2,
            'bgColor' => self::AZUL,
            'valign' => 'center',
        ]);
        $celdaCabecera->addText(
            'ESTADÍSTICA GENERAL',
            ['bold' => true, 'color' => 'FFFFFF', 'size' => 8.5],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'spaceBefore' => 0]
        );

        $filas = [
            ['HOMBRES', $reporte['totalHombres'], false],
            ['MUJERES', $reporte['totalMujeres'], false],
            ['LOCALES', $reporte['totalLocales'], false],
            ['FORÁNEOS', $reporte['totalForaneos'], false],
            ['TOTAL GENERAL', $reporte['totalGeneral'], true],
        ];

        foreach ($filas as [$etiqueta, $valor, $total]) {
            $fila = $tabla->addRow(null, ['cantSplit' => true]);
            $fondo = $total ? self::FONDO_SUAVE : 'FFFFFF';

            $etiquetaCelda = $fila->addCell(3650, [
                'bgColor' => $fondo,
                'valign' => 'center',
            ]);
            $etiquetaCelda->addText(
                $etiqueta,
                ['bold' => $total, 'size' => 8.3, 'color' => self::TEXTO],
                ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'spaceBefore' => 0]
            );

            $valorCelda = $fila->addCell(1650, [
                'bgColor' => $fondo,
                'valign' => 'center',
            ]);
            $valorCelda->addText(
                (string) $valor,
                ['bold' => true, 'size' => 8.3, 'color' => self::TEXTO],
                ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'spaceBefore' => 0]
            );
        }
    }

    private function agregarPiePagina(Section $section, array $reporte): void
    {
        $footer = $section->addFooter();
        $footer->addPreserveText(
            'Centro Universitario Moctezuma · Generación ' . $reporte['generacion']->generacion .
            ' · Locales: ' . $reporte['totalLocales'] .
            ' · Foráneos: ' . $reporte['totalForaneos'] .
            ' · Total: ' . $reporte['totalGeneral'] .
            ' · Página {PAGE} de {NUMPAGES}',
            ['name' => 'Arial', 'size' => 7, 'color' => self::TEXTO_SUAVE],
            [
                'alignment' => Jc::CENTER,
                'spaceBefore' => 0,
                'spaceAfter' => 0,
            ]
        );
    }

    private function estiloTabla(): array
    {
        return [
            'borderSize' => 4,
            'borderColor' => self::BORDE,
            'cellMarginTop' => 50,
            'cellMarginBottom' => 50,
            'cellMarginLeft' => 70,
            'cellMarginRight' => 70,
        ];
    }

    private function rutaLogoInstitucional(): ?string
    {
        $ruta = public_path('storage/letra2.jpg');

        return is_file($ruta) ? $ruta : null;
    }

    private function rutaImagenLicenciatura(?string $imagen): ?string
    {
        if (! $imagen) {
            return null;
        }

        $ruta = public_path('storage/licenciaturas/' . $imagen);

        return is_file($ruta) ? $ruta : null;
    }
}
