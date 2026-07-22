<?php

namespace App\Console\Commands;

use App\Models\DocumentoIdentidad;
use App\Models\Inscripcion;
use App\Services\DocumentosIdentidad\DocumentoIdentidadService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Throwable;

class MigrarDocumentosIdentidad extends Command
{
    protected $signature = 'documentos-identidad:migrar
                            {--aplicar : Realiza la migración; sin esta opción solo muestra una vista previa}
                            {--conservar-publicos : No elimina los archivos públicos después de migrarlos}';

    protected $description = 'Migra los documentos de identidad históricos del disco público al almacenamiento privado versionado';

    public function handle(DocumentoIdentidadService $service): int
    {
        if (! Schema::hasTable('documentos_identidad')) {
            $this->error('Primero ejecuta php artisan migrate.');

            return self::FAILURE;
        }

        $aplicar = (bool) $this->option('aplicar');
        $conservar = (bool) $this->option('conservar-publicos');
        $detectados = 0;
        $migrados = 0;
        $faltantes = 0;
        $omitidos = 0;
        $errores = 0;

        $this->info($aplicar ? 'Migración en ejecución…' : 'Vista previa: no se modificarán archivos ni base de datos.');

        Inscripcion::query()
            ->orderBy('id')
            ->chunkById(100, function ($alumnos) use (
                $service,
                $aplicar,
                $conservar,
                &$detectados,
                &$migrados,
                &$faltantes,
                &$omitidos,
                &$errores
            ): void {
                foreach ($alumnos as $alumno) {
                    foreach ($service->tipos() as $tipo => $config) {
                        $nombre = trim((string) $alumno->{$config['column']});

                        if ($nombre === '') {
                            continue;
                        }

                        $detectados++;

                        if (DocumentoIdentidad::query()
                            ->where('inscripcion_id', $alumno->id)
                            ->where('tipo', $tipo)
                            ->actual()
                            ->exists()) {
                            $omitidos++;
                            continue;
                        }

                        $ruta = storage_path('app/public/documentos/' . $config['legacy_folder'] . '/' . basename($nombre));

                        if (! File::exists($ruta)) {
                            $faltantes++;
                            $this->warn("Faltante: alumno {$alumno->id}, {$config['label']}, {$ruta}");
                            continue;
                        }

                        if (! $aplicar) {
                            $this->line("Migraría: alumno {$alumno->id}, {$config['label']}, " . basename($ruta));
                            continue;
                        }

                        try {
                            $service->importarPdfExistente($ruta, $alumno, $tipo);
                            $migrados++;

                            if (! $conservar) {
                                File::delete($ruta);
                            }
                        } catch (Throwable $e) {
                            $errores++;
                            $this->error("Error alumno {$alumno->id}, {$config['label']}: {$e->getMessage()}");
                        }
                    }
                }
            });

        $this->newLine();
        $this->table(
            ['Detectados', 'Migrados', 'Omitidos', 'Faltantes', 'Errores'],
            [[$detectados, $migrados, $omitidos, $faltantes, $errores]]
        );

        if (! $aplicar) {
            $this->warn('Para aplicar: php artisan documentos-identidad:migrar --aplicar');
        }

        return $errores > 0 ? self::FAILURE : self::SUCCESS;
    }
}
