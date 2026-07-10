<?php

namespace App\Http\Controllers;

use App\Exports\ListasGeneracionExport;
use App\Models\Dashboard;
use App\Models\Escuela;
use App\Models\Generacion;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use App\Services\ListasGeneracionWordService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ReporteGeneracionController extends Controller
{
    public function pdf(Request $request)
    {
        $datos = $this->validar($request);
        $reporte = $this->construirReporte((int) $datos['generacion_id'], $datos['procedencia']);

        $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.listasGeneracionPDF', $reporte)
            ->setPaper('letter', 'portrait');

        return $pdf->stream($this->nombreArchivo($reporte, 'pdf'));
    }

    public function excel(Request $request)
    {
        $datos = $this->validar($request);
        $reporte = $this->construirReporte((int) $datos['generacion_id'], $datos['procedencia']);

        return Excel::download(
            new ListasGeneracionExport($reporte),
            $this->nombreArchivo($reporte, 'xlsx')
        );
    }

    public function word(Request $request, ListasGeneracionWordService $wordService)
    {
        $datos = $this->validar($request);
        $reporte = $this->construirReporte((int) $datos['generacion_id'], $datos['procedencia']);
        $ruta = $wordService->generar($reporte);

        return response()
            ->download(
                $ruta,
                $this->nombreArchivo($reporte, 'docx'),
                ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
            )
            ->deleteFileAfterSend(true);
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'generacion_id' => ['required', 'integer', 'exists:generaciones,id'],
            'procedencia' => ['required', Rule::in(['todos', 'true', 'false'])],
        ], [
            'generacion_id.required' => 'Debes seleccionar una generación.',
            'generacion_id.exists' => 'La generación seleccionada no es válida.',
            'procedencia.required' => 'Debes seleccionar la procedencia.',
            'procedencia.in' => 'La procedencia seleccionada no es válida.',
        ]);
    }

    private function construirReporte(int $generacionId, string $procedencia): array
    {
        $generacion = Generacion::query()->findOrFail($generacionId);

        // La BD confirma que la procedencia se guarda directamente en
        // inscripciones.foraneo como enum('true', 'false').
        $licenciaturas = Licenciatura::query()
            ->orderBy('id')
            ->get(['id', 'nombre', 'nombre_corto', 'RVOE', 'imagen']);

        $alumnos = Inscripcion::query()
            ->with([
                'licenciatura:id,nombre,nombre_corto,RVOE',
                'generacion:id,generacion,activa',
            ])
            ->where('generacion_id', $generacionId)
            ->where('status', 'true')
            ->when($procedencia !== 'todos', function (Builder $query) use ($procedencia) {
                $query->where('foraneo', $procedencia);
            })
            ->orderBy('licenciatura_id')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get();

        $alumnosPorLicenciatura = $alumnos->groupBy('licenciatura_id');

        $listas = $licenciaturas->map(function (Licenciatura $licenciatura) use ($alumnosPorLicenciatura) {
            /** @var Collection<int, Inscripcion> $alumnosLicenciatura */
            $alumnosLicenciatura = $alumnosPorLicenciatura->get($licenciatura->id, collect())->values();

            return [
                'licenciatura' => $licenciatura,
                'alumnos' => $alumnosLicenciatura,
                'locales' => $alumnosLicenciatura->where('foraneo', 'false')->count(),
                'foraneos' => $alumnosLicenciatura->where('foraneo', 'true')->count(),
                'total' => $alumnosLicenciatura->count(),
            ];
        })->values();

        $dashboard = Dashboard::query()->latest('id')->first();

        return [
            'generacion' => $generacion,
            'procedencia' => $procedencia,
            'procedenciaTexto' => $this->textoProcedencia($procedencia),
            'listas' => $listas,
            'escuela' => Escuela::query()->first(),
            'cicloEscolar' => $dashboard?->ciclo_escolar ?? 'NO REGISTRADO',
            'periodoEscolar' => $dashboard?->periodo_escolar ?? 'NO REGISTRADO',
            'totalLocales' => $alumnos->where('foraneo', 'false')->count(),
            'totalForaneos' => $alumnos->where('foraneo', 'true')->count(),
            'totalGeneral' => $alumnos->count(),
            'totalHombres' => $alumnos->where('sexo', 'H')->count(),
            'totalMujeres' => $alumnos->where('sexo', 'M')->count(),
            'alumnosGenerales' => $alumnos->groupBy('licenciatura_id'),
            'fechaEmision' => now(),
        ];
    }

    private function textoProcedencia(string $procedencia): string
    {
        return match ($procedencia) {
            'true' => 'FORÁNEOS',
            'false' => 'LOCALES',
            default => 'TODOS',
        };
    }

    private function nombreArchivo(array $reporte, string $extension): string
    {
        $generacion = preg_replace('/[^A-Za-z0-9_-]/', '_', $reporte['generacion']->generacion);
        $ciclo = preg_replace('/[^A-Za-z0-9_-]/', '_', $reporte['cicloEscolar']);
        $fecha = $reporte['fechaEmision']->format('Y-m-d');

        return "LISTAS_GENERACION_{$generacion}_{$reporte['procedenciaTexto']}_CICLO_{$ciclo}_{$fecha}.{$extension}";
    }
}
