<?php

namespace App\Http\Controllers;

use App\Models\DescargaExpedienteIdentidad;
use App\Services\DocumentosIdentidad\ExpedienteIdentidadExportService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ExpedientesIdentidadController extends Controller
{
    public function zipAlumno(int $id, ExpedienteIdentidadExportService $service)
    {
        Gate::authorize('documentos-identidad.descargar');

        $alumno = $service->consultaAlumnos([
            'alumno_id' => $id,
            'estados' => ['todos'],
        ])->firstOrFail();

        $descarga = DescargaExpedienteIdentidad::query()->create([
            'usuario_id' => auth()->id(),
            'tipo' => 'alumno',
            'formato' => 'zip',
            'estado' => 'pendiente',
            'filtros' => [
                'alumno_id' => $alumno->id,
                'licenciaturas' => [$alumno->licenciatura_id],
                'generaciones' => [$alumno->generacion_id],
                'estados' => ['todos'],
            ],
            'total_alumnos' => 1,
            'ip' => request()->ip(),
            'user_agent' => mb_substr((string) request()->userAgent(), 0, 500),
            'solicitado_at' => now(),
        ]);

        try {
            $service->generarZip($descarga);
        } catch (Throwable $e) {
            $descarga->forceFill([
                'estado' => 'error',
                'error' => mb_substr($e->getMessage(), 0, 5000),
                'completado_at' => now(),
            ])->save();

            throw $e;
        }

        $descarga->forceFill(['descargado_at' => now()])->save();

        $disk = (string) config('documentos_identidad.export.disk', 'local');

        return Storage::disk($disk)->download(
            $descarga->archivo_ruta,
            $descarga->archivo_nombre,
            [
                'Content-Type' => 'application/zip',
                'Cache-Control' => 'private, no-store, max-age=0',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function descargar(DescargaExpedienteIdentidad $descarga)
    {
        Gate::authorize('documentos-identidad.descargar');

        $esPropietario = (int) $descarga->usuario_id === (int) auth()->id();
        abort_unless($esPropietario || auth()->user()?->can('documentos-identidad.auditar'), 403);
        abort_unless($descarga->estaLista(), 409, 'La exportación todavía no está lista.');

        $disk = (string) config('documentos_identidad.export.disk', 'local');
        abort_unless(Storage::disk($disk)->exists($descarga->archivo_ruta), 404, 'El archivo ya no está disponible.');

        $descarga->forceFill(['descargado_at' => now()])->save();

        return Storage::disk($disk)->download(
            $descarga->archivo_ruta,
            $descarga->archivo_nombre,
            [
                'Content-Type' => 'application/zip',
                'Cache-Control' => 'private, no-store, max-age=0',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }
}
