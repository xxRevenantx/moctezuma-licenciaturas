<?php

namespace App\Http\Controllers;

use App\Exports\PlantillaDocente\PlantillaDocenteExport;
use App\Models\Escuela;
use App\Services\PlantillaDocenteLicenciaturaService;
use App\Services\PlantillaDocenteWordService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class PlantillaDocenteLicenciaturaController extends Controller
{
    private function reporte(Request $request, PlantillaDocenteLicenciaturaService $servicio): array
    {
        $data = $request->validate([
            'ciclo_escolar' => ['required', 'string', 'max:25', Rule::exists('periodos', 'ciclo_escolar')],
            'periodo_escolar' => ['required', 'string', 'max:30', Rule::exists('meses', 'meses_corto')],
            'licenciaturas' => ['sometimes', 'array'],
            'licenciaturas.*' => ['integer', 'min:0'],
            'modalidades' => ['sometimes', 'array'],
            'modalidades.*' => ['integer', 'min:0'],
            'generaciones' => ['sometimes', 'array'],
            'generaciones.*' => ['integer', 'min:0'],
            'cuatrimestres' => ['sometimes', 'array'],
            'cuatrimestres.*' => ['integer', 'min:0'],
            'materias' => ['sometimes', 'boolean'],
            'carga_horaria' => ['sometimes', 'boolean'],
            'mostrar_generaciones' => ['sometimes', 'boolean'],
        ]);
        // Evitar interpretar '0' como 'no proporcionado'.
        foreach (['materias', 'carga_horaria', 'mostrar_generaciones'] as $campo) {
            if (array_key_exists($campo, $data)) {
                $data[$campo] = filter_var($data[$campo], FILTER_VALIDATE_BOOLEAN);
            }
        }
        return $servicio->generar($data);
    }

    private function escuela(): array
    {
        return Escuela::query()->first()?->toArray() ?? [];
    }

    private function archivo(string $extension): string
    {
        return 'plantilla-docente-por-licenciatura-'.now()->format('Ymd-His').'.'.$extension;
    }

    public function preview(Request $request, PlantillaDocenteLicenciaturaService $servicio)
    {
        return Pdf::loadView('reportes.plantilla-docente-pdf', [
            'reporte' => $this->reporte($request, $servicio), 'escuela' => $this->escuela(),
        ])->setPaper('letter', 'landscape')->stream($this->archivo('pdf'));
    }

    public function pdf(Request $request, PlantillaDocenteLicenciaturaService $servicio)
    {
        return Pdf::loadView('reportes.plantilla-docente-pdf', [
            'reporte' => $this->reporte($request, $servicio), 'escuela' => $this->escuela(),
        ])->setPaper('letter', 'landscape')->download($this->archivo('pdf'));
    }

    public function word(Request $request, PlantillaDocenteLicenciaturaService $servicio, PlantillaDocenteWordService $word)
    {
        $ruta = $word->generar($this->reporte($request, $servicio), $this->escuela());
        return response()->download($ruta, $this->archivo('docx'), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    public function excel(Request $request, PlantillaDocenteLicenciaturaService $servicio)
    {
        return Excel::download(new PlantillaDocenteExport(
            $this->reporte($request, $servicio), $this->escuela()
        ), $this->archivo('xlsx'));
    }
}
