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

        try {
            foreach ($alumnos as $indice => $alumno) {
                $carpetaPadre = $this->carpetaPadre($alumno, $descarga->tipo);
                $nombreBase = $this->nombreAlumno($alumno);
                $claveDuplicado = $carpetaPadre . '/' . $nombreBase;
                $carpetaAlumno = $nombreBase;

                if (($duplicados[$claveDuplicado] ?? 0) > 1) {
                    $identificador = $this->normalizarNombre((string) ($alumno->matricula ?: $alumno->id));
                    $carpetaAlumno .= '_' . $identificador;
                }

                $rutaAlumnoZip = $carpetaPadre . '/' . $carpetaAlumno;
                $rutaPdfTemporal = $directorioTrabajo . DIRECTORY_SEPARATOR . 'alumno-' . $alumno->id . '.pdf';
                $resultadoPdf = $this->generarPdfAlumnoEnRuta($alumno, $rutaPdfTemporal, $directorioTrabajo);

                if ($resultadoPdf['paginas'] > 0 && File::exists($rutaPdfTemporal)) {
                    $zip->addFile($rutaPdfTemporal, $rutaAlumnoZip . '/' . $this->nombrePdfAlumno($alumno));
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
            File::put($rutaDestino, $pdf->Output('S'));
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
