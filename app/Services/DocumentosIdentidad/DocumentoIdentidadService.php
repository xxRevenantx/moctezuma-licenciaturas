<?php

namespace App\Services\DocumentosIdentidad;

use App\Models\DocumentoIdentidad;
use App\Models\Inscripcion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\UploadedFile;
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

    public function guardarSubida(
        UploadedFile $archivo,
        Inscripcion $inscripcion,
        string $tipo,
        ?int $usuarioId = null
    ): DocumentoIdentidad {
        $this->configuracionTipo($tipo);

        $mime = strtolower((string) $archivo->getMimeType());
        $permitidos = (array) config('documentos_identidad.allowed_mime_types', []);

        if (! in_array($mime, $permitidos, true)) {
            throw ValidationException::withMessages([
                'archivo' => 'El archivo debe ser PDF, JPG o PNG.',
            ]);
        }

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

                // Compatibilidad temporal con los módulos antiguos.
                $inscripcionBloqueada->forceFill([$config['column'] => $nombreAlmacenado])->save();

                return $documento;
            });
        } catch (Throwable $e) {
            Storage::disk($this->disk())->delete($rutaPrivada);
            throw $e;
        }
    }
}
