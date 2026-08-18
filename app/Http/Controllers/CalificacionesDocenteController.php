<?php

namespace App\Http\Controllers;

use App\Exports\CalificacionesDocente\ReporteCalificacionesExport;
use App\Models\Calificacion;
use App\Services\CalificacionesDocenteService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class CalificacionesDocenteController extends Controller
{
    public function index()
    {
        return view('admin.calificaciones-docente.index');
    }

    public function pdf(Request $request, CalificacionesDocenteService $service)
    {
        [$contexto, $alumnos, $calificaciones, $promedio] = $this->dataset($request, $service);

        $pdf = Pdf::loadView('pdf.calificaciones-docente.lista', compact(
            'contexto', 'alumnos', 'calificaciones', 'promedio'
        ))->setPaper('letter', 'portrait');

        return $pdf->stream($this->nombreArchivo($contexto, 'pdf'));
    }

    public function excel(Request $request, CalificacionesDocenteService $service)
    {
        [$contexto, $alumnos, $calificaciones] = $this->dataset($request, $service);

        return Excel::download(
            new ReporteCalificacionesExport($contexto, $alumnos, $calificaciones),
            $this->nombreArchivo($contexto, 'xlsx')
        );
    }

    private function dataset(Request $request, CalificacionesDocenteService $service): array
    {
        $datos = $request->validate([
            'asignacion_materia_id' => ['required', 'integer'],
            'generacion_id' => ['required', 'integer'],
        ]);

        $contexto = $service->contextoAsignacion(
            $request->user(),
            (int) $datos['asignacion_materia_id'],
            (int) $datos['generacion_id']
        );
        abort_unless($contexto, 403);

        $alumnos = $service->alumnosParaContexto($contexto);
        $calificaciones = Calificacion::query()
            ->whereIn('alumno_id', $alumnos->pluck('id')->all())
            ->where('asignacion_materia_id', (int) $contexto->asignacion_materia_id)
            ->where('modalidad_id', (int) $contexto->modalidad_id)
            ->where('generacion_id', (int) $contexto->generacion_id)
            ->where('licenciatura_id', (int) $contexto->licenciatura_id)
            ->where('cuatrimestre_id', (int) $contexto->cuatrimestre_id)
            ->orderBy('id')
            ->get()
            ->groupBy('alumno_id')
            ->map(fn ($items) => $items->last()->calificacion)
            ->all();

        $numericas = collect($calificaciones)
            ->filter(fn ($valor) => is_numeric($valor) && (float) $valor >= 5 && (float) $valor <= 10)
            ->map(fn ($valor) => (float) $valor);
        $promedio = $numericas->isNotEmpty() ? round($numericas->avg(), 2) : null;

        return [$contexto, $alumnos, $calificaciones, $promedio];
    }

    private function nombreArchivo(object $contexto, string $extension): string
    {
        $base = collect([
            'CALIFICACIONES',
            $contexto->materia_clave ?: $contexto->materia,
            $contexto->generacion,
            $contexto->cuatrimestre . 'CUAT',
        ])->map(fn ($p) => Str::upper(Str::slug((string) $p, '_')))->filter()->implode('_');

        return "{$base}.{$extension}";
    }
}
