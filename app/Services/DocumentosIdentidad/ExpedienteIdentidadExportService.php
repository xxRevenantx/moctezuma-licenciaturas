<?php

namespace App\Services\DocumentosIdentidad;

use App\Models\DescargaExpedienteIdentidad;
use App\Models\DocumentoIdentidad;
use App\Models\Generacion;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;
use setasign\Fpdi\Fpdi;
use Throwable;
use ZipArchive;

class ExpedienteIdentidadExportService
{
    public function __construct(
        protected DocumentoIdentidadService $documentosService
    ) {
    }

    public function tiposExportables(): array
    {
        $tiposConfigurados = $this->documentosService->tipos();
        $tipos = (array) config('documentos_identidad.export.types', [
            'curp',
            'acta_nacimiento',
            'certificado_estudios',
        ]);

        return collect($tipos)
            ->filter(fn (string $tipo): bool => isset($tiposConfigurados[$tipo]))
            ->values()
            ->all();
    }

    public function consultaAlumnos(array $filtros): Builder
    {
        $licenciaturas = $this->normalizarIds($filtros['licenciaturas'] ?? []);
        $generaciones = $this->normalizarIds($filtros['generaciones'] ?? []);
        $estados = $this->normalizarEstados($filtros['estados'] ?? ['activos']);
        $alumnoId = isset($filtros['alumno_id']) ? (int) $filtros['alumno_id'] : null;
        $idsLote = isset($filtros['ids_lote']) ? $this->normalizarIds($filtros['ids_lote']) : null;
        $tipos = $this->tiposExportables();

        return Inscripcion::query()
            ->with([
                'licenciatura:id,nombre',
                'generacion:id,generacion,activa',
                'documentosIdentidadActuales' => function (Relation $relation) use ($tipos): void {
                    $relation
                        ->whereIn('tipo', $tipos)
                        ->select([
                            'id',
                            'inscripcion_id',
                            'tipo',
                            'ruta',
                            'nombre_original',
                            'mime_type',
                            'version',
                            'es_actual',
                            'estado',
                        ]);
                },
            ])
            ->when($alumnoId, fn (Builder $query, int $id) => $query->whereKey($id))
            ->when($idsLote !== null, fn (Builder $query) => $query->whereIn('inscripciones.id', $idsLote))
            ->when($licenciaturas !== [], fn (Builder $query) => $query->whereIn('licenciatura_id', $licenciaturas))
            ->when($generaciones !== [], fn (Builder $query) => $query->whereIn('generacion_id', $generaciones))
            ->when(! $this->incluyeTodosLosEstados($estados), function (Builder $query) use ($estados): void {
                $query->where(function (Builder $estadoQuery) use ($estados): void {
                    foreach ($estados as $estado) {
                        $estadoQuery->orWhere(function (Builder $condicion) use ($estado): void {
                            match ($estado) {
                                'activos' => $condicion
                                    ->where('status', 'true')
                                    ->where(function (Builder $q): void {
                                        $q->whereNull('egresado')->orWhere('egresado', 'false');
                                    }),
                                'egresados' => $condicion->where('egresado', 'true'),
                                'bajas' => $condicion
                                    ->where('status', 'false')
                                    ->where(function (Builder $q): void {
                                        $q->whereNull('egresado')->orWhere('egresado', 'false');
                                    }),
                                default => null,
                            };
                        });
                    }
                });
            })
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->orderBy('id');
    }

    public function contarAlumnos(array $filtros): int
    {
        return (clone $this->consultaAlumnos($filtros))->count('inscripciones.id');
    }

    public function generarZip(DescargaExpedienteIdentidad $descarga): DescargaExpedienteIdentidad
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('La extensión ZIP de PHP no está habilitada. Activa extension=zip en php.ini.');
        }

        $descarga->forceFill([
            'estado' => 'procesando',
            'iniciado_at' => now(),
            'error' => null,
        ])->save();

        $alumnos = $this->consultaAlumnos($descarga->filtros ?? [])->get();

        if ($alumnos->count() > 50) {
            throw new RuntimeException('El límite por ZIP es de 50 alumnos. Selecciona un lote.');
        }

        if ($alumnos->isEmpty()) {
            throw new RuntimeException('No se encontraron alumnos con los filtros seleccionados.');
        }

        $disk = (string) config('documentos_identidad.export.disk', 'local');
        $directorio = trim((string) config('documentos_identidad.export.directory', 'expedientes-identidad-exportados'), '/');
        $rutaRelativa = "{$directorio}/{$descarga->id}/" . Str::uuid() . '.zip';
        $rutaAbsoluta = Storage::disk($disk)->path($rutaRelativa);
        File::ensureDirectoryExists(dirname($rutaAbsoluta));

        $directorioTrabajo = storage_path('app/documentos-identidad-temp/export-' . $descarga->id . '-' . Str::uuid());
        File::ensureDirectoryExists($directorioTrabajo);

        $zip = new ZipArchive();
        $resultadoApertura = $zip->open($rutaAbsoluta, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($resultadoApertura !== true) {
            File::deleteDirectory($directorioTrabajo);
            throw new RuntimeException("No se pudo crear el archivo ZIP. Código: {$resultadoApertura}");
        }

        $duplicados = $this->detectarNombresDuplicados($alumnos, $descarga->tipo);
        $reporte = [];
        $alumnosIncompletos = 0;
        $documentosFaltantes = 0;
        // Se reutilizan los PDF individuales ya generados. Los grupos permiten
        // producir un PDF por alumno y otro general por licenciatura, sin
        // modificar el arbol de expedientes ni sus archivos de faltantes.
        $gruposImpresion = [];

        try {
            foreach ($alumnos as $indice => $alumno) {
                $carpetaPadre = $this->carpetaPadre($alumno, $descarga->tipo);
                $nombreBase = $this->nombreAlumno($alumno);
                $claveDuplicado = $carpetaPadre . '/' . $nombreBase;
                $carpetaAlumno = $nombreBase;

                if (($duplicados[$claveDuplicado] ?? 0) > 1) {
                    $identificador = $this->normalizarNombre((string) ($alumno->matricula ?: $alumno->matricula_interna ?: $alumno->id));
                    $carpetaAlumno .= '_' . $identificador;
                }

                $rutaAlumnoZip = $carpetaPadre . '/' . $carpetaAlumno;
                $rutaPdfTemporal = $directorioTrabajo . DIRECTORY_SEPARATOR . 'alumno-' . $alumno->id . '.pdf';
                $resultadoPdf = $this->generarPdfAlumnoEnRuta($alumno, $rutaPdfTemporal, $directorioTrabajo);

                $tienePdfOriginal = $resultadoPdf['paginas'] > 0 && File::exists($rutaPdfTemporal);
                if ($tienePdfOriginal) {
                    // Se respeta exactamente la ubicacion original del PDF.
                    if (! $zip->addFile($rutaPdfTemporal, $rutaAlumnoZip . '/' . $this->nombrePdfAlumno($alumno))) {
                        throw new RuntimeException("No se pudo archivar el expediente de {$alumno->id}.");
                    }
                }

                if ($descarga->tipo !== 'alumno') {
                    // Un alumno sin documentos debe seguir apareciendo en la
                    // impresion y en la lista: se incluye una hoja informativa,
                    // sin simular que se entrego documentacion inexistente.
                    if (! $tienePdfOriginal) {
                        $rutaPdfTemporal = $directorioTrabajo . DIRECTORY_SEPARATOR
                            . 'sin-documentos-' . $alumno->id . '.pdf';
                        $this->generarHojaSinDocumentos($alumno, $rutaPdfTemporal);
                    }

                    $grupo = $this->carpetaPdfImprimibles($alumno, $descarga->tipo);
                    $gruposImpresion[$grupo][] = [
                        'alumno' => $alumno,
                        'ruta' => $rutaPdfTemporal,
                        'paginas' => $tienePdfOriginal ? $resultadoPdf['paginas'] : 1,
                    ];
                }

                $faltantes = array_values(array_unique(array_merge(
                    $resultadoPdf['faltantes'],
                    $resultadoPdf['errores']
                )));

                if ($faltantes !== []) {
                    $alumnosIncompletos++;
                    $documentosFaltantes += count($resultadoPdf['faltantes']);
                    $zip->addFromString(
                        $rutaAlumnoZip . '/DOCUMENTOS_FALTANTES.txt',
                        $this->contenidoFaltantes($alumno, $resultadoPdf)
                    );
                }

                $reporte[] = $this->filaReporte($indice + 1, $alumno, $resultadoPdf);

                $procesados = $indice + 1;
                if ($procesados % 5 === 0 || $procesados === $alumnos->count()) {
                    $descarga->forceFill([
                        'total_alumnos' => $alumnos->count(),
                        'alumnos_procesados' => $procesados,
                        'alumnos_incompletos' => $alumnosIncompletos,
                        'documentos_faltantes' => $documentosFaltantes,
                    ])->save();
                }
            }

            // Cada carrera incluye sus PDFs individuales, numerados segun el
            // listado alfabetico, y UN solo PDF continuo para imprimir.
            // Se realiza al final, mientras los PDF temporales aun existen.
            $this->agregarPdfsImprimibles($zip, $gruposImpresion, $directorioTrabajo);

            $rutaReporte = $directorioTrabajo . DIRECTORY_SEPARATOR . 'REPORTE_DE_EXPEDIENTES.xlsx';
            $this->generarReporteExcel($reporte, $rutaReporte);
            $zip->addFile($rutaReporte, 'REPORTE_DE_EXPEDIENTES.xlsx');
            $zip->close();
        } catch (Throwable $e) {
            $zip->close();
            File::delete($rutaAbsoluta);
            throw $e;
        } finally {
            File::deleteDirectory($directorioTrabajo);
        }

        clearstatcache(true, $rutaAbsoluta);
        $nombreArchivo = $this->nombreZip($descarga->tipo, $descarga->filtros ?? [], $alumnos);
        $numeroLote = (int) (($descarga->filtros ?? [])['numero_lote'] ?? 0);
        if ($numeroLote > 0) {
            $nombreArchivo = preg_replace('/\.zip$/i', '_LOTE_' . str_pad((string) $numeroLote, 2, '0', STR_PAD_LEFT) . '.zip', $nombreArchivo);
        }

        $descarga->forceFill([
            'estado' => 'listo',
            'total_alumnos' => $alumnos->count(),
            'alumnos_procesados' => $alumnos->count(),
            'alumnos_incompletos' => $alumnosIncompletos,
            'documentos_faltantes' => $documentosFaltantes,
            'archivo_ruta' => $rutaRelativa,
            'archivo_nombre' => $nombreArchivo,
            'archivo_tamano' => File::size($rutaAbsoluta),
            'completado_at' => now(),
            'error' => null,
        ])->save();

        return $descarga->refresh();
    }

    /**
     * Carpeta que contiene los PDFs listos para imprimir de una licenciatura.
     * Nunca se alteran las carpetas preexistentes de cada alumno.
     */
    protected function carpetaPdfImprimibles(Inscripcion $alumno, string $tipo): string
    {
        $carpetaPadre = $this->carpetaPadre($alumno, $tipo);

        if ($tipo === 'licenciatura') {
            // Si se exportan varias generaciones, el consolidado de la carrera
            // contiene a todos sus estudiantes, no uno por cada generacion.
            return explode('/', $carpetaPadre, 2)[0];
        }

        return $carpetaPadre;
    }

    /**
     * Archivos adicionales dentro de cada carpeta de licenciatura:
     * - 001_APELLIDO_APELLIDO_NOMBRE_GEN_XXXX.pdf (uno por alumno, con paginas).
     * - 00_TODOS_LOS_EXPEDIENTES_PARA_IMPRIMIR.pdf (todos en el mismo orden).
     * Si no hay documentos exportables se agrega una hoja identificadora,
     * mientras que DOCUMENTOS_FALTANTES.txt sigue en la carpeta original.
     *
     * @param array<string, array<int, array{alumno: Inscripcion, ruta: string, paginas: int}>> $grupos
     */
    protected function agregarPdfsImprimibles(ZipArchive $zip, array $grupos, string $directorioTrabajo): void
    {
        uksort($grupos, 'strnatcasecmp');

        foreach ($grupos as $carpetaLicenciatura => $entradas) {
            // La consulta ordena por apellido paterno, materno, nombre e ID.
            // El orden permanece estable aunque los estudiantes pertenezcan
            // a generaciones distintas dentro de la misma licenciatura.
            $rutasEnOrden = [];

            foreach ($entradas as $index => $entrada) {
                $numero = str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
                $alumno = $entrada['alumno'];
                $nombre = $numero . '_' . $this->nombrePdfAlumno($alumno);
                $rutaEnZip = $carpetaLicenciatura . '/' . $nombre;

                if (! $zip->addFile($entrada['ruta'], $rutaEnZip)) {
                    throw new RuntimeException("No se pudo agregar el PDF del alumno {$alumno->id}.");
                }

                $rutasEnOrden[] = $entrada['ruta'];
            }

            if ($rutasEnOrden === []) {
                continue;
            }

            // Un archivo temporal distinto por grupo; ZipArchive lee las
            // fuentes al cerrar el ZIP, por eso se eliminan solo al final.
            $rutaGeneral = $directorioTrabajo . DIRECTORY_SEPARATOR
                . 'imprimible-' . hash('sha256', $carpetaLicenciatura) . '.pdf';

            $this->unirPdfsParaImpresion($rutasEnOrden, $rutaGeneral);

            if (! $zip->addFile(
                $rutaGeneral,
                $carpetaLicenciatura . '/00_TODOS_LOS_EXPEDIENTES_PARA_IMPRIMIR.pdf'
            )) {
                throw new RuntimeException("No se pudo agregar el PDF general de {$carpetaLicenciatura}.");
            }
        }
    }

    /**
     * Representa de forma explicita al estudiante que no tiene ningun PDF
     * vigente, evitando su desaparicion del archivo de impresion.
     */
    protected function generarHojaSinDocumentos(Inscripcion $alumno, string $rutaDestino): void
    {
        $pdf = new Fpdi();
        $pdf->SetCompression(true);
        $pdf->SetMargins(18, 18, 18);
        $pdf->AddPage('P', 'A4');
        $pdf->SetTextColor(0, 100, 146);
        $pdf->SetFont('Helvetica', 'B', 15);
        $pdf->Cell(0, 11, 'CENTRO UNIVERSITARIO MOCTEZUMA', 0, 1, 'C');
        $pdf->Ln(12);
        $pdf->SetTextColor(160, 40, 40);
        $pdf->SetFont('Helvetica', 'B', 13);
        $pdf->Cell(0, 10, 'EXPEDIENTE SIN DOCUMENTOS DISPONIBLES', 0, 1, 'C');
        $pdf->Ln(12);
        $pdf->SetTextColor(35, 45, 65);
        $pdf->SetFont('Helvetica', '', 11);
        $lineas = [
            'ALUMNO: ' . $this->nombreAlumno($alumno),
            'MATRICULA: ' . (string) ($alumno->matricula ?: 'SIN MATRICULA'),
            'GENERACION: ' . $this->codigoGeneracion($alumno),
            'LICENCIATURA: ' . $this->normalizarNombre((string) optional($alumno->licenciatura)->nombre),
        ];

        foreach ($lineas as $linea) {
            $pdf->MultiCell(0, 9, $linea);
        }

        $pdf->Ln(9);
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->MultiCell(0, 7, 'Esta hoja solo conserva al alumno en el orden de impresion. No constituye un documento entregado ni valida un expediente completo.');
        File::ensureDirectoryExists(dirname($rutaDestino));
        $pdf->Output('F', $rutaDestino);
    }

    /**
     * Une los PDF sin redimensionar ni convertir sus paginas a imagenes.
     * Esto preserva legibilidad, orientacion y orden de impresion, con la
     * compresion interna estandar de FPDF/FPDI habilitada.
     *
     * @param list<string> $rutasPdf
     */
    protected function unirPdfsParaImpresion(array $rutasPdf, string $rutaDestino): void
    {
        $pdf = new Fpdi();
        $pdf->SetCompression(true);
        $numeroPaginas = 0;

        foreach ($rutasPdf as $rutaPdf) {
            if (! File::isFile($rutaPdf)) {
                throw new RuntimeException('Falta un expediente individual durante la integracion del PDF general.');
            }

            $paginas = $pdf->setSourceFile($rutaPdf);

            for ($pagina = 1; $pagina <= $paginas; $pagina++) {
                $plantilla = $pdf->importPage($pagina);
                $tamano = $pdf->getTemplateSize($plantilla);
                $pdf->AddPage($tamano['orientation'], [$tamano['width'], $tamano['height']]);
                $pdf->useTemplate($plantilla);
                $numeroPaginas++;
            }
        }

        if ($numeroPaginas === 0) {
            throw new RuntimeException('No hay paginas para preparar el PDF general.');
        }

        File::ensureDirectoryExists(dirname($rutaDestino));
        // Output F escribe directamente en disco y evita otra copia del PDF
        // completo como cadena de PHP.
        $pdf->Output('F', $rutaDestino);
    }

    public function generarPdfAlumno(Inscripcion $alumno): array
    {
        $directorioTrabajo = storage_path('app/documentos-identidad-temp/pdf-' . $alumno->id . '-' . Str::uuid());
        File::ensureDirectoryExists($directorioTrabajo);
        $ruta = $directorioTrabajo . DIRECTORY_SEPARATOR . $this->nombrePdfAlumno($alumno);

        try {
            $resultado = $this->generarPdfAlumnoEnRuta($alumno, $ruta, $directorioTrabajo);
            $resultado['contenido'] = $resultado['paginas'] > 0 && File::exists($ruta)
                ? File::get($ruta)
                : null;

            return $resultado;
        } finally {
            File::deleteDirectory($directorioTrabajo);
        }
    }

    public function nombrePdfAlumno(Inscripcion $alumno): string
    {
        return $this->nombreAlumno($alumno) . '_GEN_' . $this->codigoGeneracion($alumno) . '.pdf';
    }

    public function nombreZipAlumno(Inscripcion $alumno): string
    {
        return Str::replaceEnd('.pdf', '.zip', $this->nombrePdfAlumno($alumno));
    }

    public function estadoAlumno(Inscripcion $alumno): string
    {
        if ((string) $alumno->egresado === 'true') {
            return 'EGRESADO';
        }

        if ((string) $alumno->status === 'false') {
            return 'BAJA';
        }

        return 'ACTIVO';
    }

    protected function generarPdfAlumnoEnRuta(
        Inscripcion $alumno,
        string $rutaDestino,
        string $directorioTrabajo
    ): array {
        $pdf = new Fpdi();
        $pdf->SetCompression(true);
        $documentos = $this->documentosActuales($alumno);
        $configuraciones = $this->documentosService->tipos();
        $faltantes = [];
        $errores = [];
        $incluidos = [];
        $paginas = 0;

        foreach ($this->tiposExportables() as $tipo) {
            $config = $configuraciones[$tipo];
            /** @var DocumentoIdentidad|null $documento */
            $documento = $documentos->get($tipo);

            if (! $documento || ! Storage::disk($this->documentosService->disk())->exists($documento->ruta)) {
                $faltantes[] = $config['label'];
                continue;
            }

            try {
                $rutaDocumento = $this->rutaLocalDocumento($documento, $directorioTrabajo);
                $numeroPaginas = $pdf->setSourceFile($rutaDocumento);

                for ($numeroPagina = 1; $numeroPagina <= $numeroPaginas; $numeroPagina++) {
                    $plantilla = $pdf->importPage($numeroPagina);
                    $tamano = $pdf->getTemplateSize($plantilla);
                    $pdf->AddPage($tamano['orientation'], [$tamano['width'], $tamano['height']]);
                    $pdf->useTemplate($plantilla);
                    $paginas++;
                }

                $incluidos[$tipo] = true;
            } catch (Throwable $e) {
                $errores[] = $config['label'] . ': archivo corrupto, cifrado o no compatible.';
                Log::error('No fue posible integrar un documento al expediente de identidad', [
                    'documento_id' => $documento->id,
                    'inscripcion_id' => $alumno->id,
                    'tipo' => $tipo,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($paginas > 0) {
            File::ensureDirectoryExists(dirname($rutaDestino));
            $pdf->Output('F', $rutaDestino);
        }

        return [
            'paginas' => $paginas,
            'faltantes' => $faltantes,
            'errores' => $errores,
            'incluidos' => $incluidos,
        ];
    }

    protected function documentosActuales(Inscripcion $alumno): Collection
    {
        if ($alumno->relationLoaded('documentosIdentidadActuales')) {
            return $alumno->documentosIdentidadActuales->keyBy('tipo');
        }

        return DocumentoIdentidad::query()
            ->where('inscripcion_id', $alumno->id)
            ->whereIn('tipo', $this->tiposExportables())
            ->actual()
            ->get()
            ->keyBy('tipo');
    }

    protected function rutaLocalDocumento(DocumentoIdentidad $documento, string $directorioTrabajo): string
    {
        $disk = Storage::disk($this->documentosService->disk());

        try {
            return $disk->path($documento->ruta);
        } catch (Throwable) {
            $rutaTemporal = $directorioTrabajo . DIRECTORY_SEPARATOR . 'documento-' . $documento->id . '.pdf';
            $stream = $disk->readStream($documento->ruta);

            if (! is_resource($stream)) {
                throw new RuntimeException('No se pudo leer el documento desde el almacenamiento.');
            }

            $destino = fopen($rutaTemporal, 'wb');
            if (! is_resource($destino)) {
                fclose($stream);
                throw new RuntimeException('No se pudo crear el archivo temporal.');
            }

            stream_copy_to_stream($stream, $destino);
            fclose($stream);
            fclose($destino);

            return $rutaTemporal;
        }
    }

    protected function contenidoFaltantes(Inscripcion $alumno, array $resultadoPdf): string
    {
        $lineas = [
            'CENTRO UNIVERSITARIO MOCTEZUMA',
            'EXPEDIENTE DE IDENTIDAD INCOMPLETO',
            '',
            'ALUMNO: ' . $this->nombreAlumno($alumno),
            'MATRICULA: ' . ($alumno->matricula ?: 'SIN_MATRICULA'),
            'GENERACION: ' . optional($alumno->generacion)->generacion,
            'GENERADO: ' . now()->format('d/m/Y H:i:s'),
            '',
        ];

        if ($resultadoPdf['faltantes'] !== []) {
            $lineas[] = 'DOCUMENTOS FALTANTES:';
            foreach ($resultadoPdf['faltantes'] as $faltante) {
                $lineas[] = '- ' . $faltante;
            }
        }

        if ($resultadoPdf['errores'] !== []) {
            $lineas[] = '';
            $lineas[] = 'DOCUMENTOS QUE NO PUDIERON PROCESARSE:';
            foreach ($resultadoPdf['errores'] as $error) {
                $lineas[] = '- ' . $error;
            }
        }

        return implode(PHP_EOL, $lineas) . PHP_EOL;
    }

    protected function filaReporte(int $numero, Inscripcion $alumno, array $resultadoPdf): array
    {
        $incluidos = $resultadoPdf['incluidos'];
        $faltantes = array_merge($resultadoPdf['faltantes'], $resultadoPdf['errores']);

        return [
            $numero,
            mb_strtoupper((string) $alumno->apellido_paterno),
            mb_strtoupper((string) $alumno->apellido_materno),
            mb_strtoupper((string) $alumno->nombre),
            (string) $alumno->matricula,
            (string) optional($alumno->licenciatura)->nombre,
            (string) optional($alumno->generacion)->generacion,
            $this->estadoAlumno($alumno),
            isset($incluidos['curp']) ? 'SI' : 'NO',
            isset($incluidos['acta_nacimiento']) ? 'SI' : 'NO',
            isset($incluidos['certificado_estudios']) ? 'SI' : 'NO',
            $faltantes === [] ? 'COMPLETO' : 'INCOMPLETO',
            $faltantes === [] ? '' : implode(', ', $faltantes),
        ];
    }

    protected function generarReporteExcel(array $filas, string $ruta): void
    {
        $encabezados = [
            'No.',
            'APELLIDO PATERNO',
            'APELLIDO MATERNO',
            'NOMBRE(S)',
            'MATRICULA',
            'LICENCIATURA',
            'GENERACION',
            'ESTADO DEL ALUMNO',
            'CURP ENTREGADA',
            'ACTA ENTREGADA',
            'CERTIFICADO ENTREGADO',
            'ESTADO DEL EXPEDIENTE',
            'DOCUMENTOS FALTANTES / ERRORES',
        ];

        $spreadsheet = new Spreadsheet();
        $hoja = $spreadsheet->getActiveSheet();
        $hoja->setTitle('Expedientes');
        $hoja->fromArray($encabezados, null, 'A1');

        if ($filas !== []) {
            $hoja->fromArray($filas, null, 'A2');
        }

        $ultimaColumna = Coordinate::stringFromColumnIndex(count($encabezados));
        $ultimaFila = max(2, count($filas) + 1);
        $rangoEncabezado = "A1:{$ultimaColumna}1";
        $rangoDatos = "A1:{$ultimaColumna}{$ultimaFila}";

        $hoja->getStyle($rangoEncabezado)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '006492'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        $hoja->getStyle($rangoDatos)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D8E0E7');
        $hoja->getStyle("A2:{$ultimaColumna}{$ultimaFila}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        $hoja->getStyle("I2:L{$ultimaFila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $hoja->getStyle("M2:M{$ultimaFila}")->getAlignment()->setWrapText(true);
        $hoja->freezePane('A2');
        $hoja->setAutoFilter($rangoEncabezado);
        $hoja->getRowDimension(1)->setRowHeight(34);

        $anchos = [
            'A' => 7,
            'B' => 22,
            'C' => 22,
            'D' => 26,
            'E' => 16,
            'F' => 38,
            'G' => 18,
            'H' => 19,
            'I' => 17,
            'J' => 17,
            'K' => 23,
            'L' => 23,
            'M' => 45,
        ];

        foreach ($anchos as $columna => $ancho) {
            $hoja->getColumnDimension($columna)->setWidth($ancho);
        }

        for ($fila = 2; $fila <= $ultimaFila; $fila++) {
            if ($hoja->getCell("L{$fila}")->getValue() === 'INCOMPLETO') {
                $hoja->getStyle("L{$fila}:M{$fila}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FDECEC');
                $hoja->getStyle("L{$fila}:M{$fila}")->getFont()->getColor()->setRGB('9F1239');
            }
        }

        File::ensureDirectoryExists(dirname($ruta));
        (new Xlsx($spreadsheet))->save($ruta);
        $spreadsheet->disconnectWorksheets();
    }

    protected function detectarNombresDuplicados(Collection $alumnos, string $tipo): array
    {
        return $alumnos
            ->groupBy(fn (Inscripcion $alumno): string => $this->carpetaPadre($alumno, $tipo) . '/' . $this->nombreAlumno($alumno))
            ->map(fn (Collection $grupo): int => $grupo->count())
            ->all();
    }

    protected function carpetaPadre(Inscripcion $alumno, string $tipo): string
    {
        $generacion = 'GENERACION_' . $this->codigoGeneracion($alumno);
        $licenciatura = $this->normalizarNombre((string) optional($alumno->licenciatura)->nombre, 'SIN_LICENCIATURA');

        return match ($tipo) {
            'licenciatura' => $licenciatura . '/' . $generacion,
            default => $generacion . '/' . $licenciatura,
        };
    }

    protected function nombreZip(string $tipo, array $filtros, Collection $alumnos): string
    {
        if ($tipo === 'alumno' && $alumnos->count() === 1) {
            return $this->nombreZipAlumno($alumnos->first());
        }

        $generaciones = $this->normalizarIds($filtros['generaciones'] ?? []);
        $licenciaturas = $this->normalizarIds($filtros['licenciaturas'] ?? []);

        if ($tipo === 'generacion' && count($generaciones) === 1) {
            $generacion = Generacion::query()->find($generaciones[0]);
            return 'EXPEDIENTES_GENERACION_' . $this->normalizarGeneracion((string) optional($generacion)->generacion) . '.zip';
        }

        if ($tipo === 'licenciatura' && count($licenciaturas) === 1) {
            $licenciatura = Licenciatura::query()->find($licenciaturas[0]);
            return 'EXPEDIENTES_' . $this->normalizarNombre((string) optional($licenciatura)->nombre, 'LICENCIATURA') . '.zip';
        }

        return 'EXPEDIENTES_IDENTIDAD_' . mb_strtoupper($tipo) . '_' . now()->format('Ymd_His') . '.zip';
    }

    protected function nombreAlumno(Inscripcion $alumno): string
    {
        return collect([
            $alumno->apellido_paterno,
            $alumno->apellido_materno,
            $alumno->nombre,
        ])->map(fn ($parte): string => $this->normalizarNombre((string) $parte, 'SIN_DATO'))
            ->implode('_');
    }

    protected function codigoGeneracion(Inscripcion $alumno): string
    {
        return $this->normalizarGeneracion((string) optional($alumno->generacion)->generacion);
    }

    protected function normalizarGeneracion(string $generacion): string
    {
        $normalizada = $this->normalizarNombre($generacion, 'SIN_GENERACION');
        $normalizada = preg_replace('/^(GENERACION|GEN)_*/', '', $normalizada) ?: 'SIN_GENERACION';

        return trim($normalizada, '_');
    }

    protected function normalizarNombre(string $valor, string $fallback = 'SIN_DATO'): string
    {
        $valor = Str::of($valor)
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9]+/', '_')
            ->trim('_')
            ->toString();

        return $valor !== '' ? $valor : $fallback;
    }

    protected function normalizarIds(array $ids): array
    {
        return collect($ids)
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    protected function normalizarEstados(array $estados): array
    {
        $permitidos = ['activos', 'egresados', 'bajas', 'todos'];

        return collect($estados)
            ->map(fn ($estado): string => (string) $estado)
            ->filter(fn (string $estado): bool => in_array($estado, $permitidos, true))
            ->unique()
            ->values()
            ->whenEmpty(fn (Collection $collection) => $collection->push('activos'))
            ->all();
    }

    protected function incluyeTodosLosEstados(array $estados): bool
    {
        return in_array('todos', $estados, true)
            || collect(['activos', 'egresados', 'bajas'])->diff($estados)->isEmpty();
    }
}
