<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Directivo;
use App\Models\Escuela;
use App\Models\Generacion;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use App\Services\Documentacion\DocumentacionExportService;
use App\Services\EstadisticaLicenciaturasService;
use App\Support\Pdf\RegistroEscolaridadHeader;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\SimpleType\Jc;
use ZipArchive;

class WordController extends Controller
{
    public function acta_examen(Request $request)
    {
        // Conserva el formato Word existente de Titulación / Acta de examen.
        $tempPath = storage_path('app/phpword-temp');
        if (! is_dir($tempPath)) {
            mkdir($tempPath, 0775, true);
        }
        Settings::setTempDir($tempPath);

        $html = view('admin.word.acta-examen', [
            'nombreAlumno' => 'Juan Pérez de Prueba',
        ])->render();

        $phpWord = new PhpWord();
        $section = $phpWord->addSection([
            'pageSizeW' => Converter::inchToTwip(8.5),
            'pageSizeH' => Converter::inchToTwip(13.38),
            'marginTop' => Converter::cmToTwip(2.5),
            'marginBottom' => Converter::cmToTwip(2.5),
            'marginLeft' => Converter::cmToTwip(2.5),
            'marginRight' => Converter::cmToTwip(2.5),
        ]);

        Html::addHtml($section, $html, false, false);

        $fileName = 'Acta_Examen_' . time() . '.docx';
        $filePath = storage_path('app/' . $fileName);
        IOFactory::createWriter($phpWord, 'Word2007')->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function documento_personal(Request $request, DocumentacionExportService $exports)
    {
        $modo = $request->input('modo_documento', 'alumno');
        $documento = (string) $request->input('tipo_documento');
        $fecha = (string) $request->input('fecha_expedicion');
        $salidaWord = $request->input('word_salida', 'consolidado');

        abort_if(! $documento || ! $fecha, 422, 'Faltan parámetros para generar el documento.');
        abort_unless(in_array($modo, ['alumno', 'licenciatura', 'generacion'], true), 422, 'Modo de generación no válido.');
        abort_unless(in_array($salidaWord, ['consolidado', 'separados'], true), 422, 'Salida Word no válida.');

        if ($modo === 'alumno') {
            $alumno = Inscripcion::query()->findOrFail((int) $request->input('alumno_id'));
            $definition = $exports->documentoPersonal($alumno, $documento, $fecha);

            return $this->descargarDefiniciones([$definition], $definition['filenameBase'] . '.docx');
        }

        $alumnos = match ($modo) {
            'licenciatura' => Inscripcion::query()
                ->where('licenciatura_id', (int) $request->input('licenciatura_id'))
                ->where('status', 'true')
                ->orderBy('apellido_paterno')->orderBy('apellido_materno')->orderBy('nombre')->get(),
            'generacion' => Inscripcion::query()
                ->where('generacion_id', (int) $request->input('generacion_id'))
                ->where('status', 'true')
                ->orderBy('apellido_paterno')->orderBy('apellido_materno')->orderBy('nombre')->get(),
        };

        abort_if($alumnos->isEmpty(), 422, 'No se encontraron alumnos activos para generar documentos.');

        $definitions = $alumnos
            ->map(fn (Inscripcion $alumno) => $exports->documentoPersonal($alumno, $documento, $fecha))
            ->all();

        $scopeName = $modo === 'licenciatura'
            ? Licenciatura::findOrFail((int) $request->input('licenciatura_id'))->nombre
            : Generacion::findOrFail((int) $request->input('generacion_id'))->generacion;

        $base = ($modo === 'licenciatura' ? 'DOCUMENTOS_' : 'DOCUMENTOS_GENERACION_') . $exports->nombreSeguro($scopeName);

        if ($salidaWord === 'separados') {
            return $this->descargarZipDefiniciones($definitions, $base . '_WORD.zip');
        }

        return $this->descargarDefiniciones($definitions, $base . '.docx');
    }

    public function documento_oficios(Request $request, DocumentacionExportService $exports)
    {
        $datos = $request->validate([
            'generacion' => ['required', 'integer', 'exists:generaciones,id'],
            'tipo_documento' => ['required', 'in:matriculas,kardex,registro-boletos,folios,certificados'],
            'fecha_expedicion' => ['required', 'date'],
        ]);

        $definition = $exports->oficio(
            (int) $datos['generacion'],
            $datos['tipo_documento'],
            $datos['fecha_expedicion']
        );

        return $this->descargarOficioSolicitud($definition, $datos['tipo_documento']);
    }

    public function documento_expedicion(Request $request, DocumentacionExportService $exports)
    {
        $datos = $request->validate([
            'modo' => ['required', 'in:licenciatura,generacion'],
            'licenciatura_id' => ['nullable', 'integer', 'exists:licenciaturas,id'],
            'generacion_id' => ['required', 'integer', 'exists:generaciones,id'],
            'alumno_ids' => ['nullable', 'array'],
            'alumno_ids.*' => ['integer', 'distinct', 'exists:inscripciones,id'],
            'documentos' => ['required', 'array', 'min:1', 'max:2'],
            'documentos.*' => ['required', 'in:registro-escolaridad,acta-resultados'],
            'salida' => ['required', 'in:consolidado,separados'],
            'alcance_alumnos' => ['required', 'in:seleccion,todos'],
        ]);

        $generacion = Generacion::findOrFail((int) $datos['generacion_id']);
        $alumnoIds = collect((array) ($datos['alumno_ids'] ?? []))
            ->map(fn ($id) => (int) $id)->filter()->unique()->values();

        if ($datos['modo'] === 'generacion') {
            $licenciaturaIds = Inscripcion::query()
                ->where('generacion_id', $generacion->id)
                ->where('status', 'true')
                ->whereNotNull('licenciatura_id')
                ->distinct()
                ->pluck('licenciatura_id');

            $licenciaturas = Licenciatura::query()->whereIn('id', $licenciaturaIds)->orderBy('nombre')->get();
            abort_if($licenciaturas->isEmpty(), 422, 'La generación seleccionada no tiene alumnos activos.');
        } else {
            $licenciaturaId = (int) ($datos['licenciatura_id'] ?? 0);
            abort_if(! $licenciaturaId, 422, 'Selecciona una licenciatura.');

            $licenciatura = Licenciatura::query()
                ->whereKey($licenciaturaId)
                ->whereHas('inscripciones', fn ($q) => $q->where('generacion_id', $generacion->id)->where('status', 'true'))
                ->first();
            abort_if(! $licenciatura, 422, 'La licenciatura seleccionada no tiene alumnos activos en esta generación.');

            if ($datos['alcance_alumnos'] === 'seleccion') {
                abort_if($alumnoIds->isEmpty(), 422, 'Selecciona al menos un alumno.');
                $idsValidos = Inscripcion::query()
                    ->where('licenciatura_id', $licenciaturaId)
                    ->where('generacion_id', $generacion->id)
                    ->where('status', 'true')
                    ->whereIn('id', $alumnoIds)
                    ->pluck('id')->map(fn ($id) => (int) $id)->sort()->values();
                abort_if($idsValidos->all() !== $alumnoIds->sort()->values()->all(), 422, 'La selección contiene alumnos que no corresponden a la licenciatura o generación elegida.');
            }

            $licenciaturas = collect([$licenciatura]);
        }

        $documentos = collect($datos['documentos'])
            ->unique()
            ->sortBy(fn ($doc) => $doc === 'registro-escolaridad' ? 0 : 1)
            ->values();

        $definitions = [];
        foreach ($documentos as $documento) {
            foreach ($licenciaturas as $licenciatura) {
                $ids = $datos['modo'] === 'licenciatura' && $datos['alcance_alumnos'] === 'seleccion'
                    ? $alumnoIds->all()
                    : null;
                $definition = $exports->expedicion($documento, $licenciatura, $generacion, $ids);
                if ($definition) {
                    $definitions[] = $definition;
                }
            }
        }

        abort_if(empty($definitions), 422, 'No hay información disponible para generar los documentos solicitados.');

        $nombreBase = $datos['modo'] === 'generacion'
            ? 'EXPEDICION_COMPLETA_GEN_' . $exports->nombreSeguro($generacion->generacion)
            : 'EXPEDICION_' . $exports->nombreSeguro($licenciaturas->first()->nombre) . '_GEN_' . $exports->nombreSeguro($generacion->generacion);

        if ($datos['salida'] === 'separados' && count($definitions) > 1) {
            return $this->descargarZipDefiniciones($definitions, $nombreBase . '_WORD.zip');
        }

        return $this->descargarDefiniciones($definitions, $nombreBase . '.docx');
    }

    public function credencial_alumno(Request $request, DocumentacionExportService $exports)
    {
        $data = $exports->credenciales((array) $request->input('alumnos_ids', []));
        $phpWord = $this->nuevoDocumento();
        $section = $this->agregarSeccion($phpWord, 'letter', 'portrait', 0.45);
        $this->encabezadoInstitucional($section, 'CREDENCIALES DE ESTUDIANTE');

        $style = $this->tablaEstilo();
        foreach ($data['alumnos'] as $index => $alumno) {
            $card = $section->addTable($style);
            $card->addRow();
            $cell = $card->addCell(9600, ['bgColor' => 'F7FAFC']);
            $run = $cell->addTextRun(['alignment' => Jc::LEFT]);
            $run->addText('CREDENCIAL DEL ESTUDIANTE', ['bold' => true, 'size' => 13, 'color' => '006492']);
            $cell->addText(trim("{$alumno->nombre} {$alumno->apellido_paterno} {$alumno->apellido_materno}"), ['bold' => true, 'size' => 11]);
            $cell->addText('Matrícula: ' . ($alumno->matricula ?: $alumno->matricula_interna ?: 'N/A'));
            $cell->addText('CURP: ' . ($alumno->CURP ?: 'N/A'));
            $cell->addText('Licenciatura: ' . ($alumno->licenciatura?->nombre_corto ?: $alumno->licenciatura?->nombre ?: 'N/A'));
            $ciclo = $data['ciclo_escolar']?->ciclo_escolar ?: 'N/A';
            $cell->addText('Ciclo escolar: ' . $ciclo);
            $vigencia = preg_match('/(\d{4})$/', (string) $ciclo, $m) ? 'Agosto ' . $m[1] : 'N/A';
            $cell->addText('Vigencia: ' . $vigencia);
            $section->addTextBreak(1);
            if (($index + 1) % 4 === 0 && ($index + 1) < $data['alumnos']->count()) {
                // Conserva una distribución compacta de cuatro credenciales por página.
                $section->addPageBreak();
            }
        }

        return $this->descargar($phpWord, 'CREDENCIALES_ESTUDIANTES.docx');
    }

    public function etiquetas(Request $request, DocumentacionExportService $exports)
    {
        $data = $exports->etiquetas((array) $request->input('alumnos_ids', []));
        $phpWord = $this->nuevoDocumento();
        $section = $this->agregarSeccion($phpWord, 'letter', 'portrait', 0.35);
        $this->encabezadoInstitucional($section, 'ETIQUETAS');

        foreach ($data['alumnos'] as $alumno) {
            for ($copy = 1; $copy <= 5; $copy++) {
                $table = $section->addTable($this->tablaEstilo());
                $table->addRow(800);
                $cell = $table->addCell(9600, ['bgColor' => 'F8FAFC']);
                $cell->addText(
                    trim("{$alumno->nombre} {$alumno->apellido_paterno} {$alumno->apellido_materno}"),
                    ['bold' => true, 'size' => 14],
                    ['alignment' => Jc::CENTER]
                );
                $cell->addText('Lic. en ' . ($alumno->licenciatura?->nombre ?: 'N/A'), ['size' => 10], ['alignment' => Jc::CENTER]);
                $cell->addText('Generación: ' . ($alumno->generacion?->generacion ?: 'N/A'), ['size' => 10], ['alignment' => Jc::CENTER]);
                $section->addTextBreak(1);
            }
            $section->addPageBreak();
        }

        return $this->descargar($phpWord, 'ETIQUETAS_ESTUDIANTES.docx');
    }

    public function justificante(int $justificante, DocumentacionExportService $exports)
    {
        $j = $exports->justificante($justificante);
        $alumno = $j->alumno;
        $phpWord = $this->nuevoDocumento();
        $section = $this->agregarSeccion($phpWord);
        $this->encabezadoInstitucional($section, 'JUSTIFICANTE');

        $section->addText('Fecha: ' . Carbon::parse($j->fecha_expedicion)->translatedFormat('d \d\e F \d\e Y'), [], ['alignment' => Jc::RIGHT]);
        $section->addTextBreak();
        $section->addText('De: ' . trim("{$alumno->nombre} {$alumno->apellido_paterno} {$alumno->apellido_materno}"), ['bold' => true, 'size' => 12]);
        $section->addText('Licenciatura y Cuatrimestre: ' . ($alumno->licenciatura?->nombre ?: 'N/A') . ' - ' . ($alumno->cuatrimestre?->cuatrimestre ?? 'N/A') . '° CUATRIMESTRE');

        $fechas = collect(explode(',', (string) $j->fechas_justificacion))
            ->filter()
            ->map(fn ($fecha) => Carbon::parse(trim($fecha))->translatedFormat('d \d\e F \d\e Y'))
            ->implode(', ');
        $section->addText('Día(s) justificado(s): ' . $fechas, ['bold' => true]);
        $section->addTextBreak();
        $section->addText('Razón:');

        foreach (['Asuntos personales', 'Problemas de salud', 'Otro'] as $motivo) {
            $marca = $j->justificacion === $motivo ? '☒ ' : '☐ ';
            $section->addText($marca . $motivo, ['bold' => $j->justificacion === $motivo]);
        }

        $section->addTextBreak(3);
        $firmas = $section->addTable(['width' => 100 * 50, 'unit' => 'pct']);
        $firmas->addRow();
        $firmas->addCell(4800)->addText("________________________\nFirma del tutor", [], ['alignment' => Jc::CENTER]);
        $firmas->addCell(4800)->addText("________________________\nExpide", [], ['alignment' => Jc::CENTER]);
        $section->addTextBreak(2);
        $section->addText('C.c.p. Sistema Escolar Interno', ['size' => 9]);
        $section->addText('C.c.p. Archivo', ['size' => 9]);

        $nombre = $this->nombreSeguro(trim("{$alumno->nombre}_{$alumno->apellido_paterno}_{$alumno->apellido_materno}"));
        return $this->descargar($phpWord, 'JUSTIFICANTE_' . $nombre . '.docx');
    }

    public function promedios(Request $request, DocumentacionExportService $exports)
    {
        $datos = $request->validate([
            'licenciatura_id' => ['required', 'integer', 'exists:licenciaturas,id'],
            'generacion_id' => ['required', 'integer', 'exists:generaciones,id'],
        ]);
        $data = $exports->promedios((int) $datos['licenciatura_id'], (int) $datos['generacion_id']);

        $phpWord = $this->nuevoDocumento();
        $section = $this->agregarSeccion($phpWord);
        $this->encabezadoInstitucional($section, 'LISTA GENERAL DE PROMEDIOS', 'Generación ' . $data['generacion']->generacion);
        $section->addText('Licenciatura: ' . $data['licenciatura']->nombre, ['bold' => true]);
        $section->addText('Total de alumnos: ' . $data['alumnos']->count());
        $section->addTextBreak();

        $table = $section->addTable($this->tablaEstilo());
        $this->filaEncabezado($table, ['#', 'Matrícula', 'Nombre del alumno', 'Materias', 'Promedio']);
        foreach ($data['alumnos'] as $index => $alumno) {
            $table->addRow();
            $table->addCell(500)->addText((string) ($index + 1));
            $table->addCell(1700)->addText((string) ($alumno->matricula ?: $alumno->matricula_interna ?: 'N/A'));
            $table->addCell(4500)->addText(trim("{$alumno->apellido_paterno} {$alumno->apellido_materno} {$alumno->nombre}"));
            $table->addCell(1300)->addText((string) ($alumno->total_materias ?? 0));
            $table->addCell(1200)->addText($alumno->promedio_final !== null ? number_format((float) $alumno->promedio_final, 1) : 'S/P');
        }

        $filename = 'LISTA_PROMEDIOS_' . $exports->nombreSeguro($data['licenciatura']->nombre) . '_GEN_' . $exports->nombreSeguro($data['generacion']->generacion) . '.docx';
        return $this->descargar($phpWord, $filename);
    }

    public function alumnos_documentacion(Request $request, int $licenciatura, DocumentacionExportService $exports)
    {
        $datos = $request->validate([
            'generacion' => ['nullable', 'integer', 'min:0'],
            'estado' => ['nullable', 'in:activos,egresados,bajas,todos'],
        ]);
        $data = $exports->controlDocumental($licenciatura, (int) ($datos['generacion'] ?? 0), $datos['estado'] ?? 'activos');

        $phpWord = $this->nuevoDocumento();
        $section = $this->agregarSeccion($phpWord, 'letter', 'landscape', 0.35);
        $this->encabezadoInstitucional($section, 'CONTROL DOCUMENTAL', $data['licenciaturaNombre'] . ' · ' . $data['generacionNombre']);
        $section->addText(sprintf(
            'Alumnos: %d · Expedientes completos: %d · Con documentos: %d · Sin documentos: %d',
            $data['resumen']['alumnos'], $data['resumen']['completos'], $data['resumen']['con_documentos'], $data['resumen']['sin_documentos']
        ));
        $section->addTextBreak();

        foreach ($data['grupos'] as $generacionNombre => $alumnos) {
            $section->addText('Generación ' . $generacionNombre, ['bold' => true, 'size' => 12, 'color' => '006492']);
            $headers = ['#', 'Matrícula', 'Alumno'];
            foreach ($data['tipos'] as $config) {
                $headers[] = $config['label'] ?? $config['nombre'] ?? 'Documento';
            }
            $headers[] = 'Avance';

            $table = $section->addTable($this->tablaEstilo());
            $this->filaEncabezado($table, $headers);
            foreach ($alumnos->values() as $index => $alumno) {
                $table->addRow();
                $table->addCell(400)->addText((string) ($index + 1));
                $table->addCell(1300)->addText((string) ($alumno->matricula ?: $alumno->matricula_interna ?: 'N/A'));
                $table->addCell(3000)->addText(trim("{$alumno->apellido_paterno} {$alumno->apellido_materno} {$alumno->nombre}"));
                foreach (array_keys($data['tipos']) as $tipo) {
                    $table->addCell(900)->addText(($alumno->documentos_estado[$tipo] ?? false) ? '✓' : '—', [], ['alignment' => Jc::CENTER]);
                }
                $table->addCell(900)->addText(($alumno->documentos_porcentaje ?? 0) . '%', ['bold' => true]);
            }
            $section->addTextBreak(2);
        }

        return $this->descargar($phpWord, 'CONTROL_DOCUMENTAL_' . $exports->nombreSeguro($data['licenciaturaNombre']) . '.docx');
    }

    public function estadistica(Request $request, EstadisticaLicenciaturasService $servicio)
    {
        $reporte = $servicio->generar($request->all());
        $phpWord = $this->nuevoDocumento();
        $section = $this->agregarSeccion($phpWord, 'letter', 'landscape', 0.28);
        $this->encabezadoInstitucional($section, 'ESTADÍSTICA COMPLETA DE LICENCIATURAS', 'Generado: ' . now()->format('d/m/Y H:i'));

        $t = $reporte['totales'];
        $section->addText(sprintf(
            'Activos: %d (H:%d M:%d) · Bajas: %d (H:%d M:%d) · Egresados: %d (H:%d M:%d) · Total: %d',
            $t['activos_total'], $t['activos_hombres'], $t['activos_mujeres'],
            $t['bajas_total'], $t['bajas_hombres'], $t['bajas_mujeres'],
            $t['egresados_total'], $t['egresados_hombres'], $t['egresados_mujeres'],
            $t['total_general']
        ), ['bold' => true]);
        $section->addTextBreak();

        foreach ($reporte['secciones'] as $seccion) {
            $section->addText('Ciclo escolar ' . $seccion['ciclo_escolar'], ['bold' => true, 'size' => 12, 'color' => '006492']);
            $table = $section->addTable($this->tablaEstilo());
            $this->filaEncabezado($table, [
                'Licenciatura', 'RVOE', 'Modalidad', 'Generación', 'Cuatrimestre',
                'Act H', 'Act M', 'Act T', 'Baj H', 'Baj M', 'Baj T',
                'Egr H', 'Egr M', 'Egr T', 'Total',
            ]);
            foreach ($seccion['filas'] as $fila) {
                $table->addRow();
                foreach ([
                    $fila['licenciatura'], $fila['rvoe'], $fila['modalidad'], $fila['generacion'], $fila['cuatrimestre'],
                    $fila['activos_hombres'], $fila['activos_mujeres'], $fila['activos_total'],
                    $fila['bajas_hombres'], $fila['bajas_mujeres'], $fila['bajas_total'],
                    $fila['egresados_hombres'], $fila['egresados_mujeres'], $fila['egresados_total'], $fila['total_general'],
                ] as $value) {
                    $table->addCell(850)->addText((string) $value, ['size' => 7]);
                }
            }
            $section->addTextBreak(2);
        }

        $ciclo = $reporte['filtros']['ciclo_escolar'] ?: 'todos-los-ciclos';
        return $this->descargar($phpWord, 'estadistica-licenciaturas-' . Str::slug($ciclo) . '-' . now()->format('d-m-Y') . '.docx');
    }

    private function descargarOficioSolicitud(array $definition, string $tipo)
    {
        $phpWord = $this->nuevoDocumento('Calibri', 10);
        $section = $phpWord->addSection([
            'orientation' => 'portrait',
            'pageSizeW' => Converter::inchToTwip(8.5),
            'pageSizeH' => Converter::inchToTwip(11),
            'marginTop' => Converter::inchToTwip(1.02),
            'marginBottom' => Converter::inchToTwip(0.72),
            'marginLeft' => Converter::inchToTwip(0.50),
            'marginRight' => Converter::inchToTwip(0.50),
            'headerHeight' => 0,
            'footerHeight' => 0,
        ]);

        $this->agregarMembreteOficio($section);
        $this->agregarContenidoOficio($section, $definition['data'], $tipo);

        return $this->descargar($phpWord, $definition['filenameBase'] . '.docx');
    }

    /**
     * Renderiza los oficios de solicitud con estilos nativos de Word.
     * No reutilizamos el HTML de Dompdf porque PHPWord no interpreta de forma
     * consistente position:absolute, fondos de página, márgenes negativos ni
     * varias reglas CSS utilizadas por los oficios PDF.
     */
    private function agregarContenidoOficio($section, array $data, string $tipo): void
    {
        $generacion = $data['generacion'];
        $escuela = $data['escuela'];
        $rector = $data['rector'];
        $directora = $data['directora'] ?? null;
        $jefe = $data['jefe'];
        $subjefe = $data['subjefe'] ?? null;
        $licenciaturas = $data['licenciaturas'];

        $nombreRector = $this->nombreDirectivo($rector);
        $nombreDirectora = $directora ? $this->nombreDirectivo($directora) : '';
        $nombreJefe = $this->nombreDirectivo($jefe);
        $nombreSubjefe = $subjefe ? $this->nombreDirectivo($subjefe) : '';

        $fecha = Carbon::parse($data['fecha'])->locale('es')->translatedFormat('d \d\e F \d\e\l Y');
        $asuntos = [
            'matriculas' => 'Solicitud de Matrículas',
            'kardex' => 'Solicitud de Kardex',
            'registro-boletos' => 'Solicitud de validación',
            'folios' => 'Solicitud de Folios para certificados',
            'certificados' => 'Validación y Certificación',
        ];

        $fontBase = ['name' => 'Calibri', 'size' => 10.5, 'color' => '000000'];
        $parrafoDerecha = [
            'alignment' => Jc::RIGHT,
            'spaceBefore' => 0,
            'spaceAfter' => 0,
            'lineHeight' => 1.0,
        ];

        $section->addText(
            'Ciudad Altamirano, Gro., a ' . $fecha,
            [...$fontBase, 'bold' => true],
            $parrafoDerecha
        );
        $section->addText(
            'Asunto: ' . ($asuntos[$tipo] ?? 'Oficio de solicitud'),
            [...$fontBase, 'bold' => true],
            $parrafoDerecha
        );
        $section->addText(
            'Generación: ' . $generacion->generacion,
            [...$fontBase, 'bold' => true],
            $parrafoDerecha
        );

        $section->addTextBreak(1, ['size' => 4], ['spaceBefore' => 0, 'spaceAfter' => 0]);
        $this->agregarDestinatarioOficio(
            $section,
            $tipo,
            $jefe,
            $nombreJefe,
            $subjefe,
            $nombreSubjefe,
            $fontBase
        );

        $parrafoCuerpo = [
            'alignment' => Jc::BOTH,
            'indentation' => ['firstLine' => Converter::cmToTwip(0.75)],
            'spaceBefore' => 120,
            'spaceAfter' => 70,
            'lineHeight' => 1.03,
        ];
        $run = $section->addTextRun($parrafoCuerpo);
        $run->addText('El que suscribe ', $fontBase);
        $run->addText($nombreRector, [...$fontBase, 'bold' => true]);
        $run->addText(', en mi carácter de Rector del ', $fontBase);
        $run->addText((string) $escuela->nombre, [...$fontBase, 'bold' => true]);
        $run->addText(', con Clave de Centro de Trabajo ', $fontBase);
        $run->addText((string) $escuela->CCT, [...$fontBase, 'bold' => true]);
        $run->addText(', con domicilio en calle Francisco I. Madero Ote. 800, Col. Esquipulas de la localidad de Ciudad Altamirano, municipio de Pungarabato del estado de Guerrero, y de acuerdo a mis facultades concedidas, de manera atenta y respetuosa me dirijo a usted para solicitar ', $fontBase);

        switch ($tipo) {
            case 'matriculas':
                $this->agregarSolicitudDestacada($run, 'las ', 'MATRÍCULAS', ' de la ', $generacion->generacion, $fontBase);
                break;
            case 'kardex':
                $this->agregarSolicitudDestacada($run, 'los ', 'KARDEX', ' correspondientes a la ', $generacion->generacion, $fontBase);
                break;
            case 'registro-boletos':
                $this->agregarSolicitudDestacada($run, 'la ', 'validación y certificación de los registros de escolaridad y actas de resultados de evaluación', ' correspondientes a la ', $generacion->generacion, $fontBase);
                break;
            case 'folios':
                $this->agregarSolicitudDestacada($run, 'los ', 'FOLIOS PARA CERTIFICADOS', ' de la ', $generacion->generacion, $fontBase);
                break;
            case 'certificados':
                $this->agregarSolicitudDestacada($run, 'la ', 'validación y certificación de los certificados', ' de este centro de estudios de la ', $generacion->generacion, $fontBase);
                break;
            default:
                $run->addText('la documentación correspondiente a la GENERACIÓN ' . $generacion->generacion, [...$fontBase, 'bold' => true]);
                break;
        }

        $run->addText('. A continuación se anexan las licenciaturas correspondientes a la generación solicitada:', $fontBase);

        foreach ($licenciaturas as $licenciatura) {
            $section->addText(
                '• LICENCIATURA EN ' . mb_strtoupper((string) $licenciatura->nombre, 'UTF-8'),
                [...$fontBase, 'bold' => true, 'size' => 10.6],
                [
                    'indentation' => ['left' => Converter::cmToTwip(0.55), 'hanging' => Converter::cmToTwip(0.25)],
                    'spaceBefore' => 0,
                    'spaceAfter' => 0,
                    'lineHeight' => 1.0,
                ]
            );
        }

        if ($tipo === 'matriculas') {
            $section->addText(
                'Para tal efecto se adjunta la relación de los alumnos correspondiente.',
                $fontBase,
                ['spaceBefore' => 80, 'spaceAfter' => 40, 'lineHeight' => 1.0]
            );
        }

        $section->addText(
            'Quedando atento a nuestra solicitud, solo me resta enviar un afectuoso saludo y ponernos a sus finas atenciones.',
            $fontBase,
            [
                'alignment' => Jc::BOTH,
                'indentation' => ['firstLine' => Converter::cmToTwip(0.75)],
                'spaceBefore' => 80,
                'spaceAfter' => 0,
                'lineHeight' => 1.03,
            ]
        );

        if ($tipo === 'folios') {
            $this->agregarFirmasDoblesOficio($section, $rector, $nombreRector, $directora, $nombreDirectora);
        } else {
            $this->agregarFirmaRectorOficio($section, $rector, $nombreRector);
        }

        $section->addText(
            'C.c.p. Archivo',
            ['name' => 'Calibri', 'size' => 7.5],
            ['spaceBefore' => 80, 'spaceAfter' => 0]
        );
    }

    private function agregarSolicitudDestacada($run, string $articulo, string $solicitud, string $enlaceGeneracion, string $generacion, array $fontBase): void
    {
        $run->addText($articulo, $fontBase);
        $run->addText($solicitud, [...$fontBase, 'bold' => true]);
        $run->addText($enlaceGeneracion, $fontBase);
        $run->addText('GENERACIÓN ' . $generacion, [...$fontBase, 'bold' => true]);
    }

    private function agregarDestinatarioOficio($section, string $tipo, $jefe, string $nombreJefe, $subjefe, string $nombreSubjefe, array $fontBase): void
    {
        $conSubjefe = in_array($tipo, ['matriculas', 'folios'], true) && $subjefe;
        $soloJefe = $tipo === 'certificados';

        $tabla = $section->addTable([
            'width' => 100 * 50,
            'unit' => 'pct',
            'cellMarginTop' => 0,
            'cellMarginBottom' => 0,
            'cellMarginLeft' => 0,
            'cellMarginRight' => 0,
        ]);
        $fila = $tabla->addRow(null, ['cantSplit' => true]);
        $izquierda = $fila->addCell($conSubjefe ? 5200 : 9600, ['valign' => 'top']);

        $izquierda->addText(
            mb_strtoupper($nombreJefe, 'UTF-8'),
            [...$fontBase, 'bold' => true],
            ['spaceBefore' => 0, 'spaceAfter' => 0, 'lineHeight' => 1.0]
        );
        $izquierda->addText(
            (string) $jefe->cargo,
            $fontBase,
            ['spaceBefore' => 0, 'spaceAfter' => 0, 'lineHeight' => 1.0]
        );

        if (! $soloJefe) {
            $izquierda->addText('Nivel Superior', $fontBase, ['spaceBefore' => 0, 'spaceAfter' => 0, 'lineHeight' => 1.0]);
            $izquierda->addText('Secretaría de Educación Guerrero', $fontBase, ['spaceBefore' => 0, 'spaceAfter' => 0, 'lineHeight' => 1.0]);
        }

        $izquierda->addText(
            'P R E S E N T E',
            [...$fontBase, 'bold' => true],
            ['spaceBefore' => 0, 'spaceAfter' => 0, 'lineHeight' => 1.0]
        );

        if ($conSubjefe) {
            $derecha = $fila->addCell(4400, ['valign' => 'bottom']);
            $derecha->addText(
                "CATT'N",
                [...$fontBase, 'bold' => true],
                ['spaceBefore' => 160, 'spaceAfter' => 0, 'lineHeight' => 1.0]
            );
            $derecha->addText(
                mb_strtoupper($nombreSubjefe, 'UTF-8'),
                [...$fontBase, 'bold' => true],
                ['spaceBefore' => 0, 'spaceAfter' => 0, 'lineHeight' => 1.0]
            );
            $derecha->addText(
                (string) $subjefe->cargo,
                $fontBase,
                ['spaceBefore' => 0, 'spaceAfter' => 0, 'lineHeight' => 1.0]
            );
        }
    }

    private function agregarFirmaRectorOficio($section, $rector, string $nombreRector): void
    {
        $section->addTextBreak(1, ['size' => 5], ['spaceBefore' => 0, 'spaceAfter' => 0]);
        $section->addText(
            'A T E N T A M E N T E',
            ['name' => 'Calibri', 'size' => 9.5, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]
        );
        $section->addText(
            mb_strtoupper((string) $rector->cargo, 'UTF-8'),
            ['name' => 'Calibri', 'size' => 8.5],
            ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]
        );
        $section->addTextBreak(2, ['size' => 5], ['spaceBefore' => 0, 'spaceAfter' => 0]);

        $tabla = $section->addTable([
            'width' => 100 * 50,
            'unit' => 'pct',
            'cellMarginTop' => 0,
            'cellMarginBottom' => 0,
            'cellMarginLeft' => 0,
            'cellMarginRight' => 0,
        ]);
        $fila = $tabla->addRow(null, ['cantSplit' => true]);
        $fila->addCell(2600);
        $firma = $fila->addCell(4400, ['borderTopSize' => 6, 'borderTopColor' => '000000']);
        $fila->addCell(2600);
        $firma->addText(
            mb_strtoupper($nombreRector, 'UTF-8'),
            ['name' => 'Calibri', 'size' => 8.5],
            ['alignment' => Jc::CENTER, 'spaceBefore' => 20, 'spaceAfter' => 0]
        );
    }

    private function agregarFirmasDoblesOficio($section, $rector, string $nombreRector, $directora, string $nombreDirectora): void
    {
        $section->addTextBreak(2, ['size' => 5], ['spaceBefore' => 0, 'spaceAfter' => 0]);
        $tabla = $section->addTable([
            'width' => 100 * 50,
            'unit' => 'pct',
            'cellMarginTop' => 0,
            'cellMarginBottom' => 0,
            'cellMarginLeft' => 80,
            'cellMarginRight' => 80,
        ]);
        $fila = $tabla->addRow(null, ['cantSplit' => true]);

        $firmaRector = $fila->addCell(4500, ['borderTopSize' => 6, 'borderTopColor' => '000000']);
        $fila->addCell(600);
        $firmaDirectora = $fila->addCell(4500, ['borderTopSize' => 6, 'borderTopColor' => '000000']);

        $firmaRector->addText('RECTOR', ['name' => 'Calibri', 'size' => 8.5, 'bold' => true], ['alignment' => Jc::CENTER, 'spaceBefore' => 20, 'spaceAfter' => 0]);
        $firmaRector->addText(mb_strtoupper($nombreRector, 'UTF-8'), ['name' => 'Calibri', 'size' => 8.2, 'bold' => true], ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]);

        $firmaDirectora->addText('DIRECTORA DE CONTROL ESCOLAR', ['name' => 'Calibri', 'size' => 8.5, 'bold' => true], ['alignment' => Jc::CENTER, 'spaceBefore' => 20, 'spaceAfter' => 0]);
        $firmaDirectora->addText(mb_strtoupper($nombreDirectora, 'UTF-8'), ['name' => 'Calibri', 'size' => 8.2, 'bold' => true], ['alignment' => Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]);
    }

    private function agregarMembreteOficio($section): void
    {
        $membrete = public_path('storage/membrete_oficios.jpg');
        if (! is_file($membrete)) {
            return;
        }

        $header = $section->addHeader();
        $header->addImage($membrete, [
            'width' => Converter::inchToPoint(8.5),
            'height' => Converter::inchToPoint(11),
            'positioning' => 'absolute',
            'posHorizontal' => 'left',
            'posHorizontalRel' => 'page',
            'posVertical' => 'top',
            'posVerticalRel' => 'page',
            'wrappingStyle' => 'behind',
            'marginLeft' => 0,
            'marginTop' => 0,
        ]);
    }

    private function nombreDirectivo($directivo): string
    {
        return trim(implode(' ', array_filter([
            trim((string) ($directivo->titulo ?? '')),
            trim((string) ($directivo->nombre ?? '')),
            trim((string) ($directivo->apellido_paterno ?? '')),
            trim((string) ($directivo->apellido_materno ?? '')),
        ], fn ($valor) => $valor !== '')));
    }

    private function descargarDefiniciones(array $definitions, string $filename)
    {
        $phpWord = $this->nuevoDocumento();
        foreach ($definitions as $definition) {
            $section = $this->agregarSeccion($phpWord, $definition['paper'] ?? 'letter', $definition['orientation'] ?? 'portrait');
            $this->agregarVistaEditable($section, $definition);
        }

        return $this->descargar($phpWord, $filename);
    }

    private function descargarZipDefiniciones(array $definitions, string $filename)
    {
        $zipPath = tempnam(sys_get_temp_dir(), 'cum_word_zip_');
        abort_if($zipPath === false, 500, 'No fue posible preparar el ZIP.');

        $zip = new ZipArchive();
        abort_if($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true, 500, 'No fue posible crear el ZIP.');

        foreach ($definitions as $definition) {
            $phpWord = $this->nuevoDocumento();
            $section = $this->agregarSeccion($phpWord, $definition['paper'] ?? 'letter', $definition['orientation'] ?? 'portrait');
            $this->agregarVistaEditable($section, $definition);
            $zip->addFromString($definition['filenameBase'] . '.docx', $this->bytes($phpWord));
        }
        $zip->close();

        return response()->download($zipPath, $filename, ['Content-Type' => 'application/zip'])->deleteFileAfterSend(true);
    }

    private function agregarVistaEditable($section, array $definition): void
    {
        $data = $definition['data'];
        if (($definition['view'] ?? '') === 'livewire.admin.licenciaturas.submodulo.pdf.registroEscolaridadPDF') {
            $wrapper = app('dompdf.wrapper');
            $data['registroHeader'] = new RegistroEscolaridadHeader(
                $wrapper->getDomPDF(),
                storage_path('fonts/calibri/calibri.ttf')
            );
        }

        $html = view($definition['view'], $data)->render();
        $html = $this->limpiarHtmlParaWord($html);

        try {
            Html::addHtml($section, $html, false, false);
        } catch (\Throwable $e) {
            report($e);
            $texto = html_entity_decode(strip_tags(str_replace(['</p>', '</div>', '<br>', '<br/>', '<br />'], "\n", $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            foreach (preg_split('/\R{2,}/', $texto) ?: [] as $parrafo) {
                $parrafo = trim(preg_replace('/\s+/u', ' ', $parrafo));
                if ($parrafo !== '') {
                    $section->addText($parrafo);
                }
            }
        }
    }

    private function limpiarHtmlParaWord(string $html): string
    {
        $html = preg_replace('#<!DOCTYPE[^>]*>#i', '', $html) ?? $html;
        $html = preg_replace('#<(style|script|svg|canvas)[^>]*>.*?</\1>#is', '', $html) ?? $html;
        $html = preg_replace('#<img\b[^>]*>#i', '', $html) ?? $html;
        $html = preg_replace('#<!--.*?-->#s', '', $html) ?? $html;
        $html = preg_replace('#</?(html|head|body|header|footer|main|section|article|aside)[^>]*>#i', '', $html) ?? $html;
        $html = preg_replace('/\s(?:class|id|style|role|aria-[a-z-]+)=("[^"]*"|\'[^\']*\')/i', '', $html) ?? $html;
        $html = str_replace(['&nbsp;', "\u{00A0}"], ' ', $html);

        return trim($html);
    }

    private function nuevoDocumento(string $fontName = 'Arial', float $fontSize = 10): PhpWord
    {
        $tempPath = storage_path('app/phpword-temp');
        if (! is_dir($tempPath)) {
            mkdir($tempPath, 0775, true);
        }
        Settings::setTempDir($tempPath);

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName($fontName);
        $phpWord->setDefaultFontSize($fontSize);

        return $phpWord;
    }

    private function agregarSeccion(PhpWord $phpWord, string $paper = 'letter', string $orientation = 'portrait', float $marginInches = 0.55)
    {
        [$width, $height] = $paper === 'legal' ? [8.5, 14.0] : [8.5, 11.0];
        if ($orientation === 'landscape') {
            [$width, $height] = [$height, $width];
        }

        return $phpWord->addSection([
            'orientation' => $orientation,
            'pageSizeW' => Converter::inchToTwip($width),
            'pageSizeH' => Converter::inchToTwip($height),
            'marginTop' => Converter::inchToTwip($marginInches),
            'marginBottom' => Converter::inchToTwip($marginInches),
            'marginLeft' => Converter::inchToTwip($marginInches),
            'marginRight' => Converter::inchToTwip($marginInches),
        ]);
    }

    private function encabezadoInstitucional($section, string $titulo, ?string $subtitulo = null): void
    {
        $escuela = Escuela::query()->first();
        $nombre = $escuela?->nombre ?: 'CENTRO UNIVERSITARIO MOCTEZUMA';
        $section->addText($nombre, ['bold' => true, 'size' => 14, 'color' => '006492'], ['alignment' => Jc::CENTER]);
        $section->addText($titulo, ['bold' => true, 'size' => 13], ['alignment' => Jc::CENTER]);
        if ($subtitulo) {
            $section->addText($subtitulo, ['size' => 9, 'color' => '666666'], ['alignment' => Jc::CENTER]);
        }
        $section->addTextBreak(1);
    }

    private function tablaEstilo(): array
    {
        return [
            'borderSize' => 6,
            'borderColor' => 'B7C5CE',
            'cellMargin' => 80,
            'width' => 100 * 50,
            'unit' => 'pct',
        ];
    }

    private function filaEncabezado($table, array $headers): void
    {
        $table->addRow();
        foreach ($headers as $header) {
            $cell = $table->addCell(1000, ['bgColor' => '006492']);
            $cell->addText((string) $header, ['bold' => true, 'color' => 'FFFFFF', 'size' => 8], ['alignment' => Jc::CENTER]);
        }
    }

    private function descargar(PhpWord $phpWord, string $filename)
    {
        $path = $this->guardarTemporal($phpWord);
        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }

    private function bytes(PhpWord $phpWord): string
    {
        $path = $this->guardarTemporal($phpWord);
        $bytes = file_get_contents($path);
        @unlink($path);
        abort_if($bytes === false, 500, 'No fue posible leer el documento Word generado.');

        return $bytes;
    }

    private function guardarTemporal(PhpWord $phpWord): string
    {
        $base = tempnam(storage_path('app/phpword-temp'), 'cum_word_');
        abort_if($base === false, 500, 'No fue posible preparar el documento Word.');
        $path = $base . '.docx';
        @rename($base, $path);
        IOFactory::createWriter($phpWord, 'Word2007')->save($path);

        return $path;
    }

    private function nombreSeguro(?string $texto): string
    {
        $texto = Str::upper(Str::ascii(trim((string) $texto)));
        $texto = preg_replace('/[^A-Z0-9_-]+/', '_', $texto) ?? 'DOCUMENTO';

        return trim($texto, '_') ?: 'DOCUMENTO';
    }
}
