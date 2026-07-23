<?php

namespace App\Jobs;

use App\Models\DescargaExpedienteIdentidad;
use App\Services\DocumentosIdentidad\ExpedienteIdentidadExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerarExpedientesIdentidadZip implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 1800;

    public function __construct(
        public int $descargaId
    ) {
    }

    public function handle(ExpedienteIdentidadExportService $service): void
    {
        $descarga = DescargaExpedienteIdentidad::query()->find($this->descargaId);

        if (! $descarga || in_array($descarga->estado, ['listo', 'cancelado'], true)) {
            return;
        }

        try {
            $service->generarZip($descarga);
        } catch (Throwable $e) {
            $descarga->forceFill([
                'estado' => 'error',
                'error' => mb_substr($e->getMessage(), 0, 5000),
                'completado_at' => now(),
            ])->save();

            Log::error('Error al generar ZIP de expedientes de identidad', [
                'descarga_id' => $descarga->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
