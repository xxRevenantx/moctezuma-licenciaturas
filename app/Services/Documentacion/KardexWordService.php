<?php

namespace App\Services\Documentacion;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Kardex con tablas REALES de Word. Reutiliza el mismo expediente y contexto del PDF.
 * No actualiza datos ni modifica las plantillas PDF existentes.
 */
class KardexWordService
{
    private const INSTITUCIONAL = '006994';
    private const VERDE = '88AC24';
    private const BORDE = '222222';

    private const CUATRIMESTRES = [
        1 => 'PRIMER CUATRIMESTRE', 2 => 'SEGUNDO CUATRIMESTRE',
        3 => 'TERCER CUATRIMESTRE', 4 => 'CUARTO CUATRIMESTRE',
        5 => 'QUINTO CUATRIMESTRE', 6 => 'SEXTO CUATRIMESTRE',
        7 => 'SÉPTIMO CUATRIMESTRE', 8 => 'OCTAVO CUATRIMESTRE',
        9 => 'NOVENO CUATRIMESTRE',
    ];

    /** @param array<string,mixed> $data Datos de DocumentacionExportService::documentoPersonal */
    public function agregar(Section $section, array $data): void
    {
        $alumno = $data['alumno'];
        $escuela = $data['escuela'];
        $licenciatura = $data['licenciatura'];
        $rector = $data['rector'] ?? null;
        $licenciaturaId = (int) $alumno->licenciatura_id;
        $modalidadId = (int) $alumno->modalidad_id;
        $generacionId = (int) $alumno->generacion_id;

        // Misma fuente que el PDF, consultada una vez por documento en lugar de una vez por fila.
        $asignaciones = DB::table('asignacion_materias')
            ->join('materias', 'asignacion_materias.materia_id', '=', 'materias.id')
            ->where('asignacion_materias.licenciatura_id', $licenciaturaId)
            ->where('asignacion_materias.modalidad_id', $modalidadId)
            ->where('materias.calificable', '!=', 'false')
            ->orderBy('materias.clave')
            ->select('asignacion_materias.id as asignacion_materia_id',
                'asignacion_materias.cuatrimestre_id', 'materias.id as materia_id',
                'materias.clave', 'materias.nombre', 'materias.calificable')
            ->get()
            ->groupBy('cuatrimestre_id');

        $calificaciones = DB::table('calificaciones')
            ->where('alumno_id', $alumno->id)
            ->whereIn('asignacion_materia_id', $asignaciones->flatten()->pluck('asignacion_materia_id'))
            ->get()
            ->keyBy('asignacion_materia_id');

        $periodos = \App\Models\Periodo::query()
            ->with('mes')
            ->where('generacion_id', $generacionId)
            ->get()
            ->keyBy('cuatrimestre_id');

        $this->encabezado($section, $escuela, $licenciatura, $alumno);

        // El documento original siempre divide después del quinto cuatrimestre.
        foreach (self::CUATRIMESTRES as $id => $titulo) {
            if ($id === 6) {
                $section->addPageBreak();
            }
            $this->bloqueCuatrimestre(
                $section, $titulo, $periodos->get($id), $asignaciones->get($id, collect()), $calificaciones
            );
        }

        $validas = $asignaciones->flatten()
            ->filter(fn ($asignacion) => (string) $asignacion->calificable === 'true')
            ->map(fn ($asignacion) => $calificaciones->get($asignacion->asignacion_materia_id)?->calificacion)
            ->filter(fn ($cal) => is_numeric($cal) && (float) $cal > 0)
            ->map(fn ($cal) => (float) $cal);
        $promedio = $validas->isNotEmpty()
            ? number_format(floor(($validas->average()) * 10) / 10, 1, '.', '')
            : '';

        $this->pieFirma($section, $rector, $promedio);
    }

    private function encabezado(Section $section, $escuela, $licenciatura, $alumno): void
    {
        $imagen = public_path('storage/encabezado.png');
        if (is_file($imagen) && @getimagesize($imagen)) {
            // Reutiliza la MISMA imagen institucional que el Kardex PDF.
            $section->addImage($imagen, [
                'width' => 580, 'alignment' => Jc::CENTER,
                'spaceAfter' => 10,
            ]);
        } else {
            $header = $section->addTable(['borderSize' => 0, 'cellMargin' => 35]);
            $header->addRow();
            $header->addCell(1550)->addText('SEP', ['name' => 'Arial', 'size' => 22, 'bold' => true], ['alignment' => Jc::CENTER]);
            $inst = $header->addCell(9300);
            $inst->addText('Centro Universitario Moctezuma',
                ['size' => 17, 'bold' => true, 'color' => self::INSTITUCIONAL], ['alignment' => Jc::CENTER]);
            $inst->addText('GESTIÓN ACADÉMICA INSTITUCIONAL',
                ['size' => 7, 'bold' => true, 'color' => self::VERDE], ['alignment' => Jc::CENTER]);
        }
        $section->addText('KARDEX DEL ALUMNO',
            ['size' => 9.5, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceBefore' => 35, 'spaceAfter' => 70]);

        $datos = $section->addTable($this->tabla(['borderSize' => 6, 'cellMargin' => 35]));
        $this->filaDato($datos, [
            (string) ($escuela?->nombre ?? 'CENTRO UNIVERSITARIO MOCTEZUMA'),
            (string) ($licenciatura?->RVOE ?? '-------'),
            (string) ($escuela?->CCT ?? '-------'),
        ], ['NOMBRE DE LA ESCUELA', 'No. DE ACUERDO DE INCORPORACIÓN', 'CLAVE DEL CENTRO DE TRABAJO']);
        $this->filaDato($datos, [
            (string) ($alumno->nombre ?? ''), (string) ($alumno->apellido_paterno ?? ''),
            (string) ($alumno->apellido_materno ?? ''),
        ], ['NOMBRE(S)', 'PRIMER APELLIDO', 'SEGUNDO APELLIDO']);
        $modalidad = \App\Models\Modalidad::query()->whereKey($alumno->modalidad_id)->value('nombre') ?: '----';
        $datos->addRow();
        $celda = $datos->addCell(7200, ['gridSpan' => 2]);
        $celda->addText(mb_strtoupper((string) ($licenciatura?->nombre ?? '')), ['bold' => true, 'size' => 7], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $celda->addText('LICENCIATURA', ['size' => 5.8], ['alignment' => Jc::CENTER]);
        $celda = $datos->addCell(3600);
        $celda->addText(mb_strtoupper((string) $modalidad), ['bold' => true, 'size' => 7], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $celda->addText('MODALIDAD', ['size' => 5.8], ['alignment' => Jc::CENTER]);
        $section->addText('', ['size' => 2], ['spaceAfter' => 30]);
    }

    private function filaDato($tabla, array $valores, array $etiquetas): void
    {
        $tabla->addRow();
        foreach ($valores as $n => $valor) {
            $celda = $tabla->addCell(3600);
            $celda->addText(mb_strtoupper($valor),
                ['size' => 7, 'bold' => true, 'underline' => 'single'],
                ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'keepTogether' => true]);
            $celda->addText($etiquetas[$n], ['size' => 5.8], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        }
    }

    private function bloqueCuatrimestre(Section $section, string $titulo, $periodo, $materias, $calificaciones): void
    {
        $mes = $periodo?->mes?->meses ?? null;
        $inicio = $periodo?->inicio_periodo ?? null;
        $periodoTexto = ($mes && $inicio)
            ? mb_strtoupper((string) $mes) . ' ' . Carbon::parse($inicio)->year
            : '-----';
        $ciclo = (string) ($periodo?->ciclo_escolar ?? '----');

        $info = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        $info->addRow();
        foreach ([
            [3000, $titulo], [4300, 'PERIODO: ' . $periodoTexto], [3500, 'CICLO ESCOLAR: ' . $ciclo]
        ] as [$ancho, $valor]) {
            $info->addCell($ancho)->addText($valor, ['size' => 6.5, 'bold' => true],
                ['spaceBefore' => 48, 'spaceAfter' => 12, 'keepNext' => true]);
        }

        // Tabla exterior con la misma disposición que el PDF: 10 columnas, separador y firma/fecha.
        $exterior = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        $exterior->addRow(null, ['cantSplit' => true]);
        $izquierda = $exterior->addCell(9040, ['valign' => 'top']);
        $tabla = $izquierda->addTable($this->tabla(['cellMargin' => 12, 'borderSize' => 5]));
        $columnas = [760, 3260, 575, 575, 640, 640, 640, 640, 640, 670];
        $tabla->addRow(225, ['cantSplit' => true]);
        foreach (['CLAVE', 'ASIGNATURA', 'CAL. FINAL', '%. ASIST.'] as $i => $cab) {
            $c = $tabla->addCell($columnas[$i], ['vMerge' => 'restart']);
            $this->textoCelda($c, $cab, true, 6.0);
        }
        $c = $tabla->addCell(3870, ['gridSpan' => 6]);
        $this->textoCelda($c, 'PERIODOS DE REGULARIZACIÓN', true, 6.0);

        $tabla->addRow(225, ['cantSplit' => true]);
        foreach (array_slice($columnas, 0, 4) as $ancho) {
            $tabla->addCell($ancho, ['vMerge' => 'continue']);
        }
        for ($i = 0; $i < 6; $i++) {
            $c = $tabla->addCell($columnas[$i + 4]);
            $this->textoCelda($c, $i % 2 ? 'CALIF.' : 'FECHA', true, 5.5);
        }
        foreach ($materias as $materia) {
            $tabla->addRow(275, ['cantSplit' => true]);
            $nota = $calificaciones->get($materia->asignacion_materia_id)?->calificacion;
            $valores = [
                (string) $materia->clave,
                mb_strtoupper((string) $materia->nombre),
                $nota === null || trim((string) $nota) === '' ? '---' : (string) $nota,
                '100', '', '', '', '', '', '',
            ];
            foreach ($valores as $i => $valor) {
                $celda = $tabla->addCell($columnas[$i]);
                $this->textoCelda($celda, $valor, false, $i === 1 ? 7.0 : 6.4, $i === 1 ? Jc::LEFT : Jc::CENTER);
            }
        }

        $exterior->addCell(200);
        $derecha = $exterior->addCell(1550, ['valign' => 'top']);
        $revision = $derecha->addTable($this->tabla(['cellMargin' => 35, 'borderSize' => 6]));
        $revision->addRow(520, ['cantSplit' => true]);
        $c = $revision->addCell(1550);
        $c->addText('REVISADO Y', ['size' => 6.0, 'bold' => true], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $c->addText('CONFRONTADO', ['size' => 6.0, 'bold' => true], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        // No se escribe una firma inventada; se conserva área disponible para firma física.
        $revision->addRow(690, ['cantSplit' => true]);
        $revision->addCell(1550)->addText('__________', ['size' => 6], ['alignment' => Jc::CENTER]);
        $revision->addRow(430, ['cantSplit' => true]);
        $c = $revision->addCell(1550);
        $fecha = ($periodo && $periodo->termino_periodo)
            ? Carbon::parse($periodo->termino_periodo)->format('d/m/y') : '------';
        $c->addText('DÍA/MES/AÑO', ['size' => 6, 'bold' => true], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $c->addText($fecha, ['size' => 6.5], ['alignment' => Jc::CENTER]);
    }

    private function pieFirma(Section $section, $rector, string $promedio): void
    {
        $section->addText('', ['size' => 4], ['spaceBefore' => 200]);
        $pie = $section->addTable($this->tabla(['cellMargin' => 140, 'borderSize' => 8]));
        $pie->addRow(1650, ['cantSplit' => true]);
        $nombre = trim(implode(' ', array_filter([
            $rector?->nombre ?? null, $rector?->apellido_paterno ?? null, $rector?->apellido_materno ?? null
        ])));
        $firma = $pie->addCell(5300, ['valign' => 'bottom']);
        $firma->addText('___________________________________', ['size' => 7], ['alignment' => Jc::CENTER, 'spaceBefore' => 600]);
        $firma->addText(mb_strtoupper($nombre ?: 'RECTORÍA'), ['size' => 7, 'bold' => true], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $firma->addText('RECTOR(A)', ['size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
        $prom = $pie->addCell(5500, ['valign' => 'bottom']);
        $run = $prom->addTextRun(['alignment' => Jc::CENTER, 'spaceBefore' => 570]);
        $run->addText('PROMEDIO GENERAL DE APROVECHAMIENTO:  ', ['bold' => true, 'size' => 7]);
        $run->addText($promedio ?: '       ', ['bold' => true, 'size' => 10, 'underline' => 'single']);
    }

    private function textoCelda($celda, string $texto, bool $negrita, float $tam, string $alineacion = Jc::CENTER): void
    {
        $celda->addText($texto, ['size' => $tam, 'bold' => $negrita],
            ['alignment' => $alineacion, 'spaceBefore' => 0, 'spaceAfter' => 0, 'lineSpacing' => 1.0]);
    }

    /** @param array<string,mixed> $extra */
    private function tabla(array $extra = []): array
    {
        return array_merge([
            'borderSize' => 6, 'borderColor' => self::BORDE, 'cellMargin' => 18,
            'layout' => 'fixed', 'alignment' => Jc::CENTER,
        ], $extra);
    }
}
