<?php

namespace App\Services\DocumentosIdentidad;

use App\Models\DocumentoIdentidad;
use App\Models\DocumentoIdentidadFuente;
use App\Models\Inscripcion;
use App\Models\OrganizacionDocumentoIdentidad;
use App\Support\Pdf\RotatableFpdi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use setasign\Fpdi\Fpdi;
use Throwable;

class DocumentoIdentidadService
{
    public function disk(): string
    {
        return (string) config('documentos_identidad.disk', 'local');
    }

    public function tipos(): array
    {
        return (array) config('documentos_identidad.types', []);
    }

    public function configuracionTipo(string $tipo): array
    {
        $config = $this->tipos()[$tipo] ?? null;

        if (! is_array($config)) {
            throw ValidationException::withMessages([
                'archivo' => 'El tipo de documento solicitado no es válido.',
            ]);
        }

        return $config;
    }

    public function actual(int $inscripcionId, string $tipo): ?DocumentoIdentidad
    {
        return DocumentoIdentidad::query()
            ->where('inscripcion_id', $inscripcionId)
            ->where('tipo', $tipo)
            ->actual()
            ->latest('version')
            ->first();
    }

    /**
     * Flujo antiguo conservado para importaciones y compatibilidad.
     */
    public function guardarSubida(
        UploadedFile $archivo,
        Inscripcion $inscripcion,
        string $tipo,
        ?int $usuarioId = null
    ): DocumentoIdentidad {
        $this->configuracionTipo($tipo);
        $mime = $this->validarMime($archivo);
        $temporal = $this->crearPdfTemporalDesdeArchivo($archivo, $mime);

        try {
            return $this->guardarPdfTemporal(
                $temporal,
                $inscripcion,
                $tipo,
                $archivo->getClientOriginalName(),
                $usuarioId,
                [
                    'mime_original' => $mime,
                    'convertido_desde_imagen' => str_starts_with($mime, 'image/'),
                ]
            );
        } finally {
            File::delete($temporal);
        }
    }

    public function inspeccionarArchivoSubido(UploadedFile $archivo): array
    {
        $mime = $this->validarMime($archivo);
        $temporal = $this->crearPdfTemporalDesdeArchivo($archivo, $mime);

        try {
            return array_merge($this->validarPdf($temporal), [
                'mime_original' => $mime,
                'nombre_original' => $archivo->getClientOriginalName(),
                'tamano_original' => (int) ($archivo->getSize() ?: File::size($archivo->getRealPath())),
            ]);
        } finally {
            File::delete($temporal);
        }
    }

    /**
     * Registra un archivo fuente, agrega sus páginas al borrador y confirma
     * automáticamente cuando sólo contiene una página.
     */
    public function guardarFuenteDesdeSubida(
        UploadedFile $archivo,
        Inscripcion $inscripcion,
        string $tipo,
        string $modo = 'agregar',
        ?int $usuarioId = null
    ): array {
        $this->configuracionTipo($tipo);

        if (! in_array($modo, ['agregar', 'reemplazar'], true)) {
            throw ValidationException::withMessages([
                'archivo' => 'Selecciona si deseas agregar páginas o reemplazar el documento.',
            ]);
        }

        $this->sincronizarFuentesLegadas($inscripcion, $usuarioId);
        $teniaBorrador = $this->borradorTieneCambios($inscripcion->id);
        $mime = $this->validarMime($archivo);
        $temporalPdf = $this->crearPdfTemporalDesdeArchivo($archivo, $mime);
        $validacion = $this->validarPdf($temporalPdf);
        $extensionOriginal = strtolower($archivo->getClientOriginalExtension() ?: 'bin');
        $uuid = (string) Str::uuid();
        $base = "documentos-identidad-fuentes/{$inscripcion->id}/{$uuid}";
        $rutaOriginal = "{$base}/original.{$extensionOriginal}";
        $rutaPdf = $mime === 'application/pdf' ? $rutaOriginal : "{$base}/normalizado.pdf";
        $contenidoOriginal = File::get($archivo->getRealPath());
        $contenidoPdf = File::get($temporalPdf);
        $disk = Storage::disk($this->disk());
        $rutasGuardadas = [];
        $fuente = null;

        try {
            if (! $disk->put($rutaOriginal, $contenidoOriginal)) {
                throw new RuntimeException('No se pudo guardar el archivo fuente original.');
            }
            $rutasGuardadas[] = $rutaOriginal;

            if ($rutaPdf !== $rutaOriginal) {
                if (! $disk->put($rutaPdf, $contenidoPdf)) {
                    throw new RuntimeException('No se pudo guardar la versión PDF del archivo fuente.');
                }
                $rutasGuardadas[] = $rutaPdf;
            }

            $fuente = DocumentoIdentidadFuente::query()->create([
                'inscripcion_id' => $inscripcion->id,
                'ruta' => $rutaPdf,
                'ruta_original' => $rutaOriginal,
                'nombre_original' => Str::limit($archivo->getClientOriginalName(), 255, ''),
                'nombre_almacenado' => basename($rutaPdf),
                'mime_type' => 'application/pdf',
                'mime_original' => $mime,
                'tamano' => strlen($contenidoOriginal),
                'hash_sha256' => hash('sha256', $contenidoOriginal),
                'paginas' => (int) $validacion['paginas'],
                'estado' => 'activo',
                'subido_por' => $usuarioId,
                'metadatos' => [
                    'convertido_desde_imagen' => str_starts_with($mime, 'image/'),
                    'tipo_origen' => $tipo,
                    'modo_carga' => $modo,
                ],
            ]);

            $borrador = $this->obtenerOCrearBorrador($inscripcion, $usuarioId);
            $asignaciones = collect($borrador->asignaciones ?? []);

            if ($modo === 'reemplazar') {
                $asignaciones = $asignaciones->map(function (array $asignacion) use ($tipo): array {
                    if (($asignacion['tipo'] ?? null) === $tipo) {
                        $asignacion['tipo'] = null;
                        $asignacion['orden'] = 0;
                    }

                    return $asignacion;
                });
            }

            $siguienteOrden = ((int) $asignaciones
                ->where('tipo', $tipo)
                ->max('orden')) + 1;

            for ($pagina = 1; $pagina <= (int) $validacion['paginas']; $pagina++) {
                $asignaciones->push([
                    'fuente_id' => $fuente->id,
                    'pagina' => $pagina,
                    'tipo' => $tipo,
                    'orden' => $siguienteOrden++,
                    'rotacion' => 0,
                ]);
            }

            $borrador = $this->guardarBorrador(
                $inscripcion,
                $asignaciones->values()->all(),
                $usuarioId,
                $borrador->id
            );

            $autoConfirmado = (int) $validacion['paginas'] === 1 && ! $teniaBorrador;
            if ($autoConfirmado) {
                $this->confirmarOrganizacion($inscripcion, $borrador->id, $usuarioId);
            }

            return [
                'fuente' => $fuente->fresh(),
                'paginas' => (int) $validacion['paginas'],
                'auto_confirmado' => $autoConfirmado,
                'requiere_organizacion' => ! $autoConfirmado,
                'organizacion_id' => $borrador->id,
            ];
        } catch (Throwable $e) {
            // Si la fuente ya quedó registrada se conserva para que el usuario
            // pueda recuperarla desde el organizador, aun cuando falle la
            // generación automática del documento confirmado.
            if (! $fuente instanceof DocumentoIdentidadFuente) {
                foreach (array_unique($rutasGuardadas) as $ruta) {
                    $disk->delete($ruta);
                }
            }

            throw $e;
        } finally {
            File::delete($temporalPdf);
        }
    }

    public function importarPdfExistente(
        string $rutaAbsoluta,
        Inscripcion $inscripcion,
        string $tipo,
        ?int $usuarioId = null
    ): DocumentoIdentidad {
        $this->configuracionTipo($tipo);

        if (! File::exists($rutaAbsoluta)) {
            throw new RuntimeException("El archivo no existe: {$rutaAbsoluta}");
        }

        return $this->guardarPdfTemporal(
            $rutaAbsoluta,
            $inscripcion,
            $tipo,
            basename($rutaAbsoluta),
            $usuarioId,
            ['migrado_desde_almacenamiento_publico' => true]
        );
    }

    public function eliminarActual(Inscripcion $inscripcion, string $tipo, ?int $usuarioId = null): void
    {
        $config = $this->configuracionTipo($tipo);

        DB::transaction(function () use ($inscripcion, $tipo, $config, $usuarioId): void {
            $actual = DocumentoIdentidad::query()
                ->where('inscripcion_id', $inscripcion->id)
                ->where('tipo', $tipo)
                ->where('es_actual', true)
                ->lockForUpdate()
                ->latest('version')
                ->first();

            if (! $actual) {
                return;
            }

            $actual->update([
                'es_actual' => false,
                'estado' => 'eliminado',
                'fecha_eliminacion' => now(),
                'metadatos' => array_merge($actual->metadatos ?? [], [
                    'eliminado_por' => $usuarioId,
                ]),
            ]);

            $inscripcion->forceFill([$config['column'] => null])->save();
        });
    }

    public function retirarTipoOrganizado(Inscripcion $inscripcion, string $tipo, ?int $usuarioId = null): void
    {
        $this->configuracionTipo($tipo);
        $this->sincronizarFuentesLegadas($inscripcion, $usuarioId);
        $borrador = $this->obtenerOCrearBorrador($inscripcion, $usuarioId);
        $asignaciones = collect($borrador->asignaciones ?? [])
            ->map(function (array $asignacion) use ($tipo): array {
                if (($asignacion['tipo'] ?? null) === $tipo) {
                    $asignacion['tipo'] = null;
                    $asignacion['orden'] = 0;
                }

                return $asignacion;
            })
            ->values()
            ->all();

        $borrador = $this->guardarBorrador($inscripcion, $asignaciones, $usuarioId, $borrador->id);
        $this->confirmarOrganizacion($inscripcion, $borrador->id, $usuarioId);
    }

    public function validarPdf(string $rutaAbsoluta): array
    {
        if (! File::exists($rutaAbsoluta) || File::size($rutaAbsoluta) <= 0) {
            throw new RuntimeException('El archivo está vacío o no existe.');
        }

        try {
            $fpdi = new Fpdi();
            $paginas = $fpdi->setSourceFile($rutaAbsoluta);

            if ($paginas < 1) {
                throw new RuntimeException('El PDF no contiene páginas válidas.');
            }

            return ['paginas' => $paginas];
        } catch (Throwable $e) {
            throw new RuntimeException(
                'El PDF está corrupto, cifrado o protegido con contraseña y no puede procesarse.',
                previous: $e
            );
        }
    }

    /**
     * Crea fuentes y una primera organización confirmada para documentos
     * existentes antes de la incorporación del organizador.
     */
    public function sincronizarFuentesLegadas(Inscripcion $inscripcion, ?int $usuarioId = null): void
    {
        if (DocumentoIdentidadFuente::query()->where('inscripcion_id', $inscripcion->id)->exists()) {
            return;
        }

        $documentos = DocumentoIdentidad::query()
            ->where('inscripcion_id', $inscripcion->id)
            ->actual()
            ->orderBy('tipo')
            ->get();

        if ($documentos->isEmpty()) {
            return;
        }

        $disk = Storage::disk($this->disk());
        $fuentes = [];
        $asignaciones = [];
        $ordenes = [];

        foreach ($documentos as $documento) {
            if (! $disk->exists($documento->ruta)) {
                continue;
            }

            $rutaLocal = $this->rutaLocalStorage($documento->ruta, 'legado-' . $documento->id . '.pdf');
            $paginas = (int) (($documento->metadatos ?? [])['paginas'] ?? 0);
            if ($paginas < 1) {
                $paginas = (int) $this->validarPdf($rutaLocal)['paginas'];
            }

            $fuente = DocumentoIdentidadFuente::query()->create([
                'inscripcion_id' => $inscripcion->id,
                'documento_identidad_id' => $documento->id,
                'ruta' => $documento->ruta,
                'ruta_original' => $documento->ruta,
                'nombre_original' => $documento->nombre_original,
                'nombre_almacenado' => $documento->nombre_almacenado,
                'mime_type' => 'application/pdf',
                'mime_original' => 'application/pdf',
                'tamano' => $documento->tamano,
                'hash_sha256' => $documento->hash_sha256,
                'paginas' => $paginas,
                'estado' => 'activo',
                'subido_por' => $documento->subido_por ?: $usuarioId,
                'metadatos' => [
                    'origen' => 'documento_legado',
                    'documento_version' => $documento->version,
                ],
            ]);

            $fuentes[] = $fuente->id;
            $ordenes[$documento->tipo] = $ordenes[$documento->tipo] ?? 0;

            for ($pagina = 1; $pagina <= $paginas; $pagina++) {
                $asignaciones[] = [
                    'fuente_id' => $fuente->id,
                    'pagina' => $pagina,
                    'tipo' => $documento->tipo,
                    'orden' => ++$ordenes[$documento->tipo],
                    'rotacion' => 0,
                ];
            }
        }

        if ($asignaciones === []) {
            return;
        }

        $borradorExistente = OrganizacionDocumentoIdentidad::query()
            ->where('inscripcion_id', $inscripcion->id)
            ->borradores()
            ->latest('version')
            ->first();

        if ($borradorExistente) {
            $borradorExistente->forceFill([
                'estado' => 'confirmado',
                'asignaciones' => $asignaciones,
                'fuentes_ids' => array_values(array_unique($fuentes)),
                'confirmado_por' => $usuarioId,
                'confirmado_at' => now(),
                'metadatos' => [
                    'origen' => 'sincronizacion_legada',
                    'paginas' => count($asignaciones),
                ],
            ])->save();

            return;
        }

        $version = ((int) OrganizacionDocumentoIdentidad::query()
            ->where('inscripcion_id', $inscripcion->id)
            ->max('version')) + 1;

        OrganizacionDocumentoIdentidad::query()->create([
            'inscripcion_id' => $inscripcion->id,
            'version' => max(1, $version),
            'estado' => 'confirmado',
            'asignaciones' => $asignaciones,
            'fuentes_ids' => array_values(array_unique($fuentes)),
            'confirmado_por' => $usuarioId,
            'confirmado_at' => now(),
            'metadatos' => [
                'origen' => 'sincronizacion_legada',
                'paginas' => count($asignaciones),
            ],
        ]);
    }

    public function obtenerOCrearBorrador(Inscripcion $inscripcion, ?int $usuarioId = null): OrganizacionDocumentoIdentidad
    {
        $this->sincronizarFuentesLegadas($inscripcion, $usuarioId);

        $borrador = OrganizacionDocumentoIdentidad::query()
            ->where('inscripcion_id', $inscripcion->id)
            ->borradores()
            ->latest('version')
            ->first();

        if ($borrador) {
            return $borrador;
        }

        $confirmada = OrganizacionDocumentoIdentidad::query()
            ->where('inscripcion_id', $inscripcion->id)
            ->confirmadas()
            ->latest('version')
            ->first();

        $version = ((int) OrganizacionDocumentoIdentidad::query()
            ->where('inscripcion_id', $inscripcion->id)
            ->max('version')) + 1;

        return OrganizacionDocumentoIdentidad::query()->create([
            'inscripcion_id' => $inscripcion->id,
            'version' => max(1, $version),
            'estado' => 'borrador',
            'asignaciones' => $confirmada?->asignaciones ?? [],
            'fuentes_ids' => $confirmada?->fuentes_ids ?? [],
            'metadatos' => [
                'creado_por' => $usuarioId,
                'basado_en_version' => $confirmada?->version,
            ],
        ]);
    }

    public function guardarBorrador(
        Inscripcion $inscripcion,
        array $asignaciones,
        ?int $usuarioId = null,
        ?int $organizacionId = null
    ): OrganizacionDocumentoIdentidad {
        $this->sincronizarFuentesLegadas($inscripcion, $usuarioId);
        $organizacion = $organizacionId
            ? OrganizacionDocumentoIdentidad::query()
                ->where('inscripcion_id', $inscripcion->id)
                ->where('estado', 'borrador')
                ->findOrFail($organizacionId)
            : $this->obtenerOCrearBorrador($inscripcion, $usuarioId);

        $normalizadas = $this->normalizarYValidarAsignaciones($inscripcion, $asignaciones);
        $fuentesIds = collect($normalizadas)->pluck('fuente_id')->unique()->values()->all();

        $organizacion->forceFill([
            'asignaciones' => $normalizadas,
            'fuentes_ids' => $fuentesIds,
            'metadatos' => array_merge($organizacion->metadatos ?? [], [
                'ultima_edicion_por' => $usuarioId,
                'ultima_edicion_at' => now()->toIso8601String(),
                'paginas' => count($normalizadas),
                'sin_clasificar' => collect($normalizadas)->whereNull('tipo')->count(),
            ]),
        ])->save();

        return $organizacion->fresh();
    }

    public function confirmarOrganizacion(
        Inscripcion $inscripcion,
        int $organizacionId,
        ?int $usuarioId = null
    ): OrganizacionDocumentoIdentidad {
        $organizacion = OrganizacionDocumentoIdentidad::query()
            ->where('inscripcion_id', $inscripcion->id)
            ->where('estado', 'borrador')
            ->findOrFail($organizacionId);

        $asignaciones = $this->normalizarYValidarAsignaciones(
            $inscripcion,
            $organizacion->asignaciones ?? []
        );
        $tipos = $this->tipos();
        $directorioTemporal = storage_path('app/documentos-identidad-temp/organizacion-' . $organizacion->id . '-' . Str::uuid());
        File::ensureDirectoryExists($directorioTemporal);
        $preparados = [];
        $rutasFinales = [];
        $disk = Storage::disk($this->disk());

        try {
            foreach ($tipos as $tipo => $config) {
                $paginasTipo = collect($asignaciones)
                    ->where('tipo', $tipo)
                    ->sortBy('orden')
                    ->values()
                    ->all();

                if ($paginasTipo === []) {
                    continue;
                }

                $rutaTemporal = $directorioTemporal . DIRECTORY_SEPARATOR . $tipo . '.pdf';
                $this->construirPdfDesdeAsignaciones($inscripcion, $paginasTipo, $rutaTemporal, $directorioTemporal);
                $contenido = File::get($rutaTemporal);
                $nombreAlmacenado = Str::uuid() . '.pdf';
                $rutaPrivada = "documentos-identidad/{$inscripcion->id}/{$tipo}/{$nombreAlmacenado}";

                if (! $disk->put($rutaPrivada, $contenido)) {
                    throw new RuntimeException("No se pudo guardar el documento organizado: {$config['label']}.");
                }

                $rutasFinales[] = $rutaPrivada;
                $preparados[$tipo] = [
                    'config' => $config,
                    'contenido' => $contenido,
                    'ruta' => $rutaPrivada,
                    'nombre_almacenado' => $nombreAlmacenado,
                    'asignaciones' => $paginasTipo,
                ];
            }

            DB::transaction(function () use (
                $inscripcion,
                $organizacion,
                $asignaciones,
                $preparados,
                $tipos,
                $usuarioId
            ): void {
                $alumno = Inscripcion::query()->lockForUpdate()->findOrFail($inscripcion->id);
                $organizacionBloqueada = OrganizacionDocumentoIdentidad::query()
                    ->lockForUpdate()
                    ->findOrFail($organizacion->id);

                if ($organizacionBloqueada->estado !== 'borrador') {
                    throw new RuntimeException('La organización ya fue confirmada o dejó de estar disponible.');
                }

                foreach ($tipos as $tipo => $config) {
                    $versiones = DocumentoIdentidad::query()
                        ->where('inscripcion_id', $inscripcion->id)
                        ->where('tipo', $tipo)
                        ->lockForUpdate()
                        ->get();

                    DocumentoIdentidad::query()
                        ->whereKey($versiones->where('es_actual', true)->pluck('id'))
                        ->update([
                            'es_actual' => false,
                            'estado' => isset($preparados[$tipo]) ? 'reemplazado' : 'eliminado',
                            'fecha_eliminacion' => isset($preparados[$tipo]) ? null : now(),
                        ]);

                    if (! isset($preparados[$tipo])) {
                        $alumno->forceFill([$config['column'] => null]);
                        continue;
                    }

                    $datos = $preparados[$tipo];
                    $version = ((int) $versiones->max('version')) + 1;
                    $nombreOriginal = Str::upper(Str::ascii($config['label'])) . '_ORGANIZADO_V' . $organizacionBloqueada->version . '.pdf';

                    DocumentoIdentidad::query()->create([
                        'inscripcion_id' => $inscripcion->id,
                        'tipo' => $tipo,
                        'ruta' => $datos['ruta'],
                        'nombre_original' => Str::limit($nombreOriginal, 255, ''),
                        'nombre_almacenado' => $datos['nombre_almacenado'],
                        'mime_type' => 'application/pdf',
                        'tamano' => strlen($datos['contenido']),
                        'hash_sha256' => hash('sha256', $datos['contenido']),
                        'version' => $version,
                        'es_actual' => true,
                        'estado' => 'activo',
                        'subido_por' => $usuarioId,
                        'metadatos' => [
                            'paginas' => count($datos['asignaciones']),
                            'organizacion_id' => $organizacionBloqueada->id,
                            'organizacion_version' => $organizacionBloqueada->version,
                            'asignaciones' => $datos['asignaciones'],
                            'confirmado_por' => $usuarioId,
                        ],
                    ]);

                    $alumno->forceFill([$config['column'] => $datos['nombre_almacenado']]);
                }

                $alumno->save();

                $organizacionBloqueada->forceFill([
                    'estado' => 'confirmado',
                    'asignaciones' => $asignaciones,
                    'fuentes_ids' => collect($asignaciones)->pluck('fuente_id')->unique()->values()->all(),
                    'confirmado_por' => $usuarioId,
                    'confirmado_at' => now(),
                    'metadatos' => array_merge($organizacionBloqueada->metadatos ?? [], [
                        'paginas_confirmadas' => collect($asignaciones)->whereNotNull('tipo')->count(),
                        'paginas_sin_clasificar' => collect($asignaciones)->whereNull('tipo')->count(),
                        'tipos_generados' => array_keys($preparados),
                    ]),
                ])->save();
            });

            return $organizacion->fresh();
        } catch (Throwable $e) {
            foreach ($rutasFinales as $ruta) {
                $disk->delete($ruta);
            }

            throw $e;
        } finally {
            File::deleteDirectory($directorioTemporal);
        }
    }

    public function datosOrganizador(Inscripcion $inscripcion, ?int $usuarioId = null): array
    {
        $this->sincronizarFuentesLegadas($inscripcion, $usuarioId);
        $borrador = $this->obtenerOCrearBorrador($inscripcion, $usuarioId);
        $fuentes = DocumentoIdentidadFuente::query()
            ->where('inscripcion_id', $inscripcion->id)
            ->activas()
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $asignaciones = collect($borrador->asignaciones ?? [])->keyBy(
            fn (array $item): string => $this->clavePagina((int) $item['fuente_id'], (int) $item['pagina'])
        );
        $paginas = [];

        foreach ($fuentes as $fuente) {
            for ($pagina = 1; $pagina <= $fuente->paginas; $pagina++) {
                $clave = $this->clavePagina($fuente->id, $pagina);
                $asignacion = $asignaciones->get($clave, [
                    'fuente_id' => $fuente->id,
                    'pagina' => $pagina,
                    'tipo' => null,
                    'orden' => 0,
                    'rotacion' => 0,
                ]);

                $paginas[] = array_merge($asignacion, [
                    'clave' => $clave,
                    'fuente_nombre' => $fuente->nombre_original,
                ]);
            }
        }

        return [
            'organizacion' => $borrador,
            'fuentes' => $fuentes,
            'paginas' => $paginas,
        ];
    }

    public function estadoOrganizacion(Inscripcion $inscripcion, ?int $usuarioId = null): array
    {
        $this->sincronizarFuentesLegadas($inscripcion, $usuarioId);
        $borrador = OrganizacionDocumentoIdentidad::query()
            ->where('inscripcion_id', $inscripcion->id)
            ->borradores()
            ->latest('version')
            ->first();
        $confirmada = OrganizacionDocumentoIdentidad::query()
            ->where('inscripcion_id', $inscripcion->id)
            ->confirmadas()
            ->latest('version')
            ->first();
        $asignaciones = collect($borrador?->asignaciones ?? []);
        $pendiente = $borrador
            ? $this->firmaAsignaciones($borrador->asignaciones ?? []) !== $this->firmaAsignaciones($confirmada?->asignaciones ?? [])
            : false;

        return [
            'pendiente' => $pendiente,
            'organizacion_id' => $borrador?->id,
            'version_borrador' => $borrador?->version,
            'version_confirmada' => $confirmada?->version,
            'paginas_sin_clasificar' => $asignaciones->whereNull('tipo')->count(),
            'paginas_borrador' => $asignaciones->count(),
            'fuentes' => DocumentoIdentidadFuente::query()
                ->where('inscripcion_id', $inscripcion->id)
                ->activas()
                ->count(),
        ];
    }

    public function fuentesConfirmadasTipo(Inscripcion $inscripcion, string $tipo): array
    {
        $this->configuracionTipo($tipo);
        $this->sincronizarFuentesLegadas($inscripcion, auth()->id());
        $confirmada = OrganizacionDocumentoIdentidad::query()
            ->where('inscripcion_id', $inscripcion->id)
            ->confirmadas()
            ->latest('version')
            ->first();

        if (! $confirmada) {
            return [];
        }

        $porFuente = collect($confirmada->asignaciones ?? [])
            ->where('tipo', $tipo)
            ->groupBy('fuente_id');
        $fuentes = DocumentoIdentidadFuente::query()
            ->where('inscripcion_id', $inscripcion->id)
            ->whereIn('id', $porFuente->keys()->map(fn ($id): int => (int) $id))
            ->get()
            ->keyBy('id');

        return $porFuente->map(function (Collection $paginas, $fuenteId) use ($fuentes): ?array {
            $fuente = $fuentes->get((int) $fuenteId);
            if (! $fuente) {
                return null;
            }

            return [
                'id' => $fuente->id,
                'nombre' => $fuente->nombre_original,
                'paginas' => $paginas->pluck('pagina')->sort()->values()->all(),
                'total_paginas' => $fuente->paginas,
                'url' => route('admin.documentos-identidad.fuentes.descargar', $fuente),
            ];
        })->filter()->values()->all();
    }

    public function rutaVistaPagina(DocumentoIdentidadFuente $fuente, int $pagina, int $rotacion = 0): string
    {
        if ($pagina < 1 || $pagina > $fuente->paginas) {
            throw new RuntimeException('La página solicitada no existe.');
        }

        $rotacion = $this->normalizarRotacion($rotacion);
        $directorio = storage_path('app/' . trim((string) config(
            'documentos_identidad.organizer.preview_directory',
            'documentos-identidad-temp/previews'
        ), '/'));
        File::ensureDirectoryExists($directorio);
        $firma = substr(hash('sha256', $fuente->hash_sha256 . '|' . $pagina . '|' . $rotacion), 0, 24);
        $ruta = $directorio . DIRECTORY_SEPARATOR . "{$firma}.pdf";

        if (File::exists($ruta) && File::size($ruta) > 0) {
            return $ruta;
        }

        $temporal = storage_path('app/documentos-identidad-temp/fuente-' . $fuente->id . '-' . Str::uuid() . '.pdf');
        File::ensureDirectoryExists(dirname($temporal));
        $rutaFuente = $this->rutaLocalFuente($fuente, $temporal);

        try {
            $pdf = new RotatableFpdi();
            $pdf->setSourceFile($rutaFuente);
            $plantilla = $pdf->importPage($pagina);
            $tamano = $pdf->getTemplateSize($plantilla);
            $ancho = in_array($rotacion, [90, 270], true) ? $tamano['height'] : $tamano['width'];
            $alto = in_array($rotacion, [90, 270], true) ? $tamano['width'] : $tamano['height'];
            $pdf->AddPage($ancho > $alto ? 'L' : 'P', [$ancho, $alto]);
            $pdf->placeTemplateRotated($plantilla, $tamano, $rotacion);
            File::put($ruta, $pdf->Output('S'));

            return $ruta;
        } finally {
            if ($rutaFuente === $temporal) {
                File::delete($temporal);
            }
        }
    }

    protected function construirPdfDesdeAsignaciones(
        Inscripcion $inscripcion,
        array $asignaciones,
        string $rutaDestino,
        string $directorioTemporal
    ): void {
        $fuentes = DocumentoIdentidadFuente::query()
            ->where('inscripcion_id', $inscripcion->id)
            ->whereIn('id', collect($asignaciones)->pluck('fuente_id')->unique())
            ->activas()
            ->get()
            ->keyBy('id');
        $rutasLocales = [];
        $pdf = new RotatableFpdi();

        foreach ($asignaciones as $asignacion) {
            $fuente = $fuentes->get((int) $asignacion['fuente_id']);
            if (! $fuente) {
                throw new RuntimeException('Una de las fuentes del documento ya no está disponible.');
            }

            if (! isset($rutasLocales[$fuente->id])) {
                $rutaTemporal = $directorioTemporal . DIRECTORY_SEPARATOR . 'fuente-' . $fuente->id . '.pdf';
                $rutasLocales[$fuente->id] = $this->rutaLocalFuente($fuente, $rutaTemporal);
            }

            $pdf->setSourceFile($rutasLocales[$fuente->id]);
            $plantilla = $pdf->importPage((int) $asignacion['pagina']);
            $tamano = $pdf->getTemplateSize($plantilla);
            $rotacion = $this->normalizarRotacion((int) ($asignacion['rotacion'] ?? 0));
            $ancho = in_array($rotacion, [90, 270], true) ? $tamano['height'] : $tamano['width'];
            $alto = in_array($rotacion, [90, 270], true) ? $tamano['width'] : $tamano['height'];
            $pdf->AddPage($ancho > $alto ? 'L' : 'P', [$ancho, $alto]);
            $pdf->placeTemplateRotated($plantilla, $tamano, $rotacion);
        }

        File::put($rutaDestino, $pdf->Output('S'));
    }

    protected function normalizarYValidarAsignaciones(Inscripcion $inscripcion, array $asignaciones): array
    {
        $tipos = array_keys($this->tipos());
        $fuentes = DocumentoIdentidadFuente::query()
            ->where('inscripcion_id', $inscripcion->id)
            ->activas()
            ->get()
            ->keyBy('id');
        $vistas = [];
        $normalizadas = [];

        foreach ($asignaciones as $asignacion) {
            $fuenteId = (int) ($asignacion['fuente_id'] ?? 0);
            $pagina = (int) ($asignacion['pagina'] ?? 0);
            $fuente = $fuentes->get($fuenteId);

            if (! $fuente || $pagina < 1 || $pagina > $fuente->paginas) {
                throw ValidationException::withMessages([
                    'organizacion' => 'La organización contiene una página o una fuente que ya no es válida.',
                ]);
            }

            $clave = $this->clavePagina($fuenteId, $pagina);
            if (isset($vistas[$clave])) {
                throw ValidationException::withMessages([
                    'organizacion' => 'Una página no puede asignarse más de una vez.',
                ]);
            }
            $vistas[$clave] = true;

            $tipo = $asignacion['tipo'] ?? null;
            $tipo = $tipo === '' ? null : $tipo;
            if ($tipo !== null && ! in_array($tipo, $tipos, true)) {
                throw ValidationException::withMessages([
                    'organizacion' => 'Uno de los tipos documentales seleccionados no es válido.',
                ]);
            }

            $normalizadas[] = [
                'fuente_id' => $fuenteId,
                'pagina' => $pagina,
                'tipo' => $tipo,
                'orden' => max(0, (int) ($asignacion['orden'] ?? 0)),
                'rotacion' => $this->normalizarRotacion((int) ($asignacion['rotacion'] ?? 0)),
            ];
        }

        // Añade páginas nuevas que aún no estén en el borrador como “Sin clasificar”.
        foreach ($fuentes as $fuente) {
            for ($pagina = 1; $pagina <= $fuente->paginas; $pagina++) {
                $clave = $this->clavePagina($fuente->id, $pagina);
                if (! isset($vistas[$clave])) {
                    $normalizadas[] = [
                        'fuente_id' => $fuente->id,
                        'pagina' => $pagina,
                        'tipo' => null,
                        'orden' => 0,
                        'rotacion' => 0,
                    ];
                }
            }
        }

        $coleccion = collect($normalizadas);
        foreach ($tipos as $tipo) {
            $orden = 1;
            $items = $coleccion
                ->where('tipo', $tipo)
                ->sortBy(fn (array $item): string => str_pad((string) ($item['orden'] ?: 999999), 8, '0', STR_PAD_LEFT)
                    . '-' . str_pad((string) $item['fuente_id'], 10, '0', STR_PAD_LEFT)
                    . '-' . str_pad((string) $item['pagina'], 6, '0', STR_PAD_LEFT));

            foreach ($items as $item) {
                $clave = $this->clavePagina((int) $item['fuente_id'], (int) $item['pagina']);
                $indice = $coleccion->search(
                    fn (array $actual): bool => $this->clavePagina((int) $actual['fuente_id'], (int) $actual['pagina']) === $clave
                );
                $item['orden'] = $orden++;
                $coleccion->put($indice, $item);
            }
        }

        return $coleccion
            ->sortBy(fn (array $item): string => ($item['tipo'] ?? 'zzzz_sin_clasificar')
                . '-' . str_pad((string) $item['orden'], 8, '0', STR_PAD_LEFT)
                . '-' . str_pad((string) $item['fuente_id'], 10, '0', STR_PAD_LEFT)
                . '-' . str_pad((string) $item['pagina'], 6, '0', STR_PAD_LEFT))
            ->values()
            ->all();
    }

    protected function crearPdfTemporalDesdeArchivo(UploadedFile $archivo, string $mime): string
    {
        $directorio = storage_path('app/documentos-identidad-temp');
        File::ensureDirectoryExists($directorio);
        $ruta = $directorio . DIRECTORY_SEPARATOR . Str::uuid() . '.pdf';

        if ($mime === 'application/pdf') {
            if (! File::copy($archivo->getRealPath(), $ruta)) {
                throw new RuntimeException('No se pudo preparar el PDF para su validación.');
            }

            return $ruta;
        }

        $contenido = File::get($archivo->getRealPath());
        $dataUri = 'data:' . $mime . ';base64,' . base64_encode($contenido);
        $dimensiones = @getimagesize($archivo->getRealPath());
        $orientacion = is_array($dimensiones) && ($dimensiones[0] ?? 0) > ($dimensiones[1] ?? 0)
            ? 'landscape'
            : 'portrait';

        $pdf = Pdf::loadView('pdf.documento-imagen', [
            'dataUri' => $dataUri,
        ])->setPaper('letter', $orientacion);

        File::put($ruta, $pdf->output());

        return $ruta;
    }

    protected function guardarPdfTemporal(
        string $rutaTemporal,
        Inscripcion $inscripcion,
        string $tipo,
        string $nombreOriginal,
        ?int $usuarioId,
        array $metadatos = []
    ): DocumentoIdentidad {
        $config = $this->configuracionTipo($tipo);
        $validacion = $this->validarPdf($rutaTemporal);
        $nombreAlmacenado = Str::uuid() . '.pdf';
        $rutaPrivada = "documentos-identidad/{$inscripcion->id}/{$tipo}/{$nombreAlmacenado}";
        $contenido = File::get($rutaTemporal);

        if (! Storage::disk($this->disk())->put($rutaPrivada, $contenido)) {
            throw new RuntimeException('No se pudo guardar el documento en el almacenamiento privado.');
        }

        try {
            return DB::transaction(function () use (
                $inscripcion,
                $tipo,
                $config,
                $rutaPrivada,
                $nombreOriginal,
                $nombreAlmacenado,
                $contenido,
                $usuarioId,
                $metadatos,
                $validacion
            ): DocumentoIdentidad {
                $inscripcionBloqueada = Inscripcion::query()->lockForUpdate()->findOrFail($inscripcion->id);
                $versiones = DocumentoIdentidad::query()
                    ->where('inscripcion_id', $inscripcion->id)
                    ->where('tipo', $tipo)
                    ->lockForUpdate()
                    ->get();

                DocumentoIdentidad::query()
                    ->whereKey($versiones->where('es_actual', true)->pluck('id'))
                    ->update([
                        'es_actual' => false,
                        'estado' => 'reemplazado',
                    ]);

                $version = ((int) $versiones->max('version')) + 1;

                $documento = DocumentoIdentidad::create([
                    'inscripcion_id' => $inscripcion->id,
                    'tipo' => $tipo,
                    'ruta' => $rutaPrivada,
                    'nombre_original' => Str::limit($nombreOriginal, 255, ''),
                    'nombre_almacenado' => $nombreAlmacenado,
                    'mime_type' => 'application/pdf',
                    'tamano' => strlen($contenido),
                    'hash_sha256' => hash('sha256', $contenido),
                    'version' => $version,
                    'es_actual' => true,
                    'estado' => 'activo',
                    'subido_por' => $usuarioId,
                    'metadatos' => array_merge($metadatos, $validacion),
                ]);

                $inscripcionBloqueada->forceFill([$config['column'] => $nombreAlmacenado])->save();

                return $documento;
            });
        } catch (Throwable $e) {
            Storage::disk($this->disk())->delete($rutaPrivada);
            throw $e;
        }
    }

    protected function validarMime(UploadedFile $archivo): string
    {
        $mime = strtolower((string) $archivo->getMimeType());
        $permitidos = (array) config('documentos_identidad.allowed_mime_types', []);

        if (! in_array($mime, $permitidos, true)) {
            throw ValidationException::withMessages([
                'archivo' => 'El archivo debe ser PDF, JPG o PNG.',
            ]);
        }

        return $mime;
    }

    protected function rutaLocalFuente(DocumentoIdentidadFuente $fuente, string $rutaTemporal): string
    {
        return $this->rutaLocalStorage($fuente->ruta, basename($rutaTemporal), $rutaTemporal);
    }

    protected function rutaLocalStorage(string $ruta, string $nombreTemporal, ?string $rutaTemporal = null): string
    {
        $disk = Storage::disk($this->disk());

        try {
            return $disk->path($ruta);
        } catch (Throwable) {
            $rutaTemporal ??= storage_path('app/documentos-identidad-temp/' . $nombreTemporal);
            File::ensureDirectoryExists(dirname($rutaTemporal));
            $stream = $disk->readStream($ruta);

            if (! is_resource($stream)) {
                throw new RuntimeException('No se pudo leer el archivo desde el almacenamiento privado.');
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

    protected function borradorTieneCambios(int $inscripcionId): bool
    {
        $borrador = OrganizacionDocumentoIdentidad::query()
            ->where('inscripcion_id', $inscripcionId)
            ->borradores()
            ->latest('version')
            ->first();

        if (! $borrador) {
            return false;
        }

        $confirmada = OrganizacionDocumentoIdentidad::query()
            ->where('inscripcion_id', $inscripcionId)
            ->confirmadas()
            ->latest('version')
            ->first();

        return $this->firmaAsignaciones($borrador->asignaciones ?? [])
            !== $this->firmaAsignaciones($confirmada?->asignaciones ?? []);
    }

    protected function firmaAsignaciones(array $asignaciones): string
    {
        $normalizadas = collect($asignaciones)
            ->map(fn (array $item): array => [
                'fuente_id' => (int) ($item['fuente_id'] ?? 0),
                'pagina' => (int) ($item['pagina'] ?? 0),
                'tipo' => ($item['tipo'] ?? null) ?: null,
                'orden' => (int) ($item['orden'] ?? 0),
                'rotacion' => $this->normalizarRotacion((int) ($item['rotacion'] ?? 0)),
            ])
            ->sortBy(fn (array $item): string => str_pad((string) $item['fuente_id'], 10, '0', STR_PAD_LEFT)
                . '-' . str_pad((string) $item['pagina'], 6, '0', STR_PAD_LEFT))
            ->values()
            ->all();

        return hash('sha256', json_encode($normalizadas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    protected function clavePagina(int $fuenteId, int $pagina): string
    {
        return $fuenteId . ':' . $pagina;
    }

    protected function normalizarRotacion(int $rotacion): int
    {
        $rotacion = (($rotacion % 360) + 360) % 360;

        return in_array($rotacion, [0, 90, 180, 270], true) ? $rotacion : 0;
    }
}
