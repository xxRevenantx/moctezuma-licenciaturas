<?php

namespace App\Console\Commands;

use App\Models\DocumentoIdentidad;
use App\Models\Inscripcion;
use App\Services\DocumentosIdentidad\DocumentoIdentidadService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AuditarDocumentosIdentidad extends Command
{
    protected $signature = 'documentos-identidad:auditar
                            {--marcar : Marca como inconsistentes los registros actuales cuyo archivo no existe o no es un PDF válido}';

    protected $description = 'Detecta archivos faltantes, PDF inválidos, referencias históricas pendientes, duplicados y archivos huérfanos';

    public function handle(DocumentoIdentidadService $service): int
    {
        if (! Schema::hasTable('documentos_identidad')) {
            $this->error('Primero ejecuta php artisan migrate.');

            return self::FAILURE;
        }

        $disk = $service->disk();
        $marcar = (bool) $this->option('marcar');
        $referenciadas = [];
        $faltantes = [];
        $invalidos = [];
        $hashIncorrecto = [];
        $legacyPendiente = [];
        $publicos = [];

        DocumentoIdentidad::query()->orderBy('id')->chunkById(100, function ($documentos) use (
            $service,
            $disk,
            $marcar,
            &$referenciadas,
            &$faltantes,
            &$invalidos,
            &$hashIncorrecto
        ): void {
            foreach ($documentos as $documento) {
                $referenciadas[] = $documento->ruta;

                if (! Storage::disk($disk)->exists($documento->ruta)) {
                    $faltantes[] = $documento->id;
                    $this->error("Archivo faltante: registro {$documento->id}, {$documento->ruta}");
                    $this->marcarSiCorresponde($documento, $marcar);
                    continue;
                }

                $ruta = Storage::disk($disk)->path($documento->ruta);

                try {
                    $service->validarPdf($ruta);
                } catch (Throwable $e) {
                    $invalidos[] = $documento->id;
                    $this->error("PDF inválido: registro {$documento->id}. {$e->getMessage()}");
                    $this->marcarSiCorresponde($documento, $marcar);
                    continue;
                }

                $hash = hash_file('sha256', $ruta);
                if (! hash_equals((string) $documento->hash_sha256, (string) $hash)) {
                    $hashIncorrecto[] = $documento->id;
                    $this->warn("Hash diferente: registro {$documento->id}");
                }
            }
        });

        Inscripcion::query()->orderBy('id')->chunkById(100, function ($alumnos) use ($service, &$legacyPendiente, &$publicos): void {
            foreach ($alumnos as $alumno) {
                foreach ($service->tipos() as $tipo => $config) {
                    $nombre = trim((string) $alumno->{$config['column']});
                    if ($nombre === '') {
                        continue;
                    }

                    $actual = DocumentoIdentidad::query()
                        ->where('inscripcion_id', $alumno->id)
                        ->where('tipo', $tipo)
                        ->actual()
                        ->exists();

                    if (! $actual) {
                        $legacyPendiente[] = "{$alumno->id}:{$tipo}";
                    }

                    $rutaPublica = storage_path('app/public/documentos/' . $config['legacy_folder'] . '/' . basename($nombre));
                    if (File::exists($rutaPublica)) {
                        $publicos[] = $rutaPublica;
                    }
                }
            }
        });

        $archivosPrivados = collect(Storage::disk($disk)->allFiles('documentos-identidad'));
        $huerfanos = $archivosPrivados->diff(array_unique($referenciadas))->values();
        $directorioPublico = storage_path('app/public/documentos');
        $publicos = File::isDirectory($directorioPublico)
            ? collect(File::allFiles($directorioPublico))->map(fn ($archivo) => $archivo->getPathname())->values()->all()
            : [];

        $actualesDuplicados = DocumentoIdentidad::query()
            ->selectRaw('inscripcion_id, tipo, COUNT(*) AS total')
            ->where('es_actual', true)
            ->where('estado', 'activo')
            ->groupBy('inscripcion_id', 'tipo')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $duplicados = DocumentoIdentidad::query()
            ->selectRaw('hash_sha256, COUNT(*) AS total')
            ->groupBy('hash_sha256')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($huerfanos as $ruta) {
            $this->warn("Archivo huérfano privado: {$ruta}");
        }

        foreach ($publicos as $ruta) {
            $this->warn("Archivo todavía público: {$ruta}");
        }

        $this->newLine();
        $this->table(
            ['Faltantes', 'Inválidos', 'Hash distinto', 'Legacy sin migrar', 'Públicos', 'Huérfanos', 'Actuales duplicados', 'Hashes duplicados'],
            [[
                count($faltantes),
                count($invalidos),
                count($hashIncorrecto),
                count($legacyPendiente),
                count($publicos),
                $huerfanos->count(),
                $actualesDuplicados->count(),
                $duplicados->count(),
            ]]
        );

        return count($faltantes) + count($invalidos) > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function marcarSiCorresponde(DocumentoIdentidad $documento, bool $marcar): void
    {
        if (! $marcar || ! $documento->es_actual) {
            return;
        }

        $documento->update([
            'es_actual' => false,
            'estado' => 'inconsistente',
        ]);

        $config = config('documentos_identidad.types.' . $documento->tipo);
        if ($config && $documento->inscripcion) {
            $documento->inscripcion->forceFill([$config['column'] => null])->save();
        }
    }
}
