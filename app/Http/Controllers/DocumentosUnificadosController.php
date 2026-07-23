<?php

namespace App\Http\Controllers;

use App\Models\DescargaExpedienteIdentidad;
use App\Models\Inscripcion;
use App\Services\DocumentosIdentidad\ExpedienteIdentidadExportService;
use Illuminate\Support\Facades\Gate;

class DocumentosUnificadosController extends Controller
{
    public function DocumentosUnificadosAlumno(int $id, ExpedienteIdentidadExportService $service)
    {
        Gate::authorize('documentos-identidad.descargar');

        $alumno = $service->consultaAlumnos([
            'alumno_id' => $id,
            'estados' => ['todos'],
        ])->firstOrFail();

        $resultado = $service->generarPdfAlumno($alumno);

        abort_if(
            empty($resultado['contenido']),
            404,
            'El alumno no tiene CURP, acta de nacimiento o certificado de estudios disponibles para combinar.'
        );

        DescargaExpedienteIdentidad::query()->create([
            'usuario_id' => auth()->id(),
            'tipo' => 'alumno',
            'formato' => 'pdf',
            'estado' => 'listo',
            'filtros' => [
                'alumno_id' => $alumno->id,
                'licenciaturas' => [$alumno->licenciatura_id],
                'generaciones' => [$alumno->generacion_id],
                'estados' => ['todos'],
            ],
            'total_alumnos' => 1,
            'alumnos_procesados' => 1,
            'alumnos_incompletos' => ($resultado['faltantes'] !== [] || $resultado['errores'] !== []) ? 1 : 0,
            'documentos_faltantes' => count($resultado['faltantes']),
            'archivo_nombre' => $service->nombrePdfAlumno($alumno),
            'archivo_tamano' => strlen($resultado['contenido']),
            'ip' => request()->ip(),
            'user_agent' => mb_substr((string) request()->userAgent(), 0, 500),
            'solicitado_at' => now(),
            'iniciado_at' => now(),
            'completado_at' => now(),
            'descargado_at' => now(),
        ]);

        return response($resultado['contenido'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $service->nombrePdfAlumno($alumno) . '"',
            'Content-Length' => (string) strlen($resultado['contenido']),
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
