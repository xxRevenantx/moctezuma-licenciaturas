<?php

namespace App\Console\Commands;

use App\Models\DescargaExpedienteIdentidad;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class LimpiarExportacionesExpedientesIdentidad extends Command
{
    protected $signature = 'expedientes-identidad:limpiar {--horas= : Antigüedad máxima de los ZIP en horas}';

    protected $description = 'Elimina los ZIP de expedientes de identidad que superaron el tiempo de conservación';

    public function handle(): int
    {
        $horas = max(1, (int) ($this->option('horas') ?: config('documentos_identidad.export.retention_hours', 48)));
        $limite = now()->subHours($horas);
        $disk = (string) config('documentos_identidad.export.disk', 'local');
        $eliminados = 0;

        DescargaExpedienteIdentidad::query()
            ->whereNotNull('archivo_ruta')
            ->whereIn('estado', ['listo', 'error'])
            ->where(function ($query) use ($limite): void {
                $query->where('completado_at', '<=', $limite)
                    ->orWhere(function ($q) use ($limite): void {
                        $q->whereNull('completado_at')->where('created_at', '<=', $limite);
                    });
            })
            ->orderBy('id')
            ->chunkById(100, function ($descargas) use ($disk, &$eliminados): void {
                foreach ($descargas as $descarga) {
                    if ($descarga->archivo_ruta) {
                        Storage::disk($disk)->delete($descarga->archivo_ruta);
                    }

                    $descarga->forceFill([
                        'estado' => 'expirado',
                        'archivo_ruta' => null,
                        'archivo_tamano' => null,
                    ])->save();

                    $eliminados++;
                }
            });

        $this->info("Exportaciones limpiadas: {$eliminados}");

        return self::SUCCESS;
    }
}
