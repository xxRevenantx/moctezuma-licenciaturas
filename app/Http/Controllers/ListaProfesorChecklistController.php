<?php

namespace App\Http\Controllers;

use App\Exports\ChecklistListasProfesoresExport;
use App\Services\ListaProfesorChecklistService;
use App\Services\ListaProfesorChecklistWordService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ListaProfesorChecklistController extends Controller
{
    public function preview(Request $request, ListaProfesorChecklistService $service)
    {
        $filtros = $this->validar($request);
        $reporte = $service->construirReporte($filtros);

        $pdf = Pdf::loadView(
            'livewire.admin.licenciaturas.submodulo.pdf.checklist-listas-profesoresPDF',
            $reporte
        )->setPaper('letter', 'landscape');

        return $pdf->stream($service->nombreArchivo($filtros, 'pdf'));
    }

    public function pdf(Request $request, ListaProfesorChecklistService $service)
    {
        $filtros = $this->validar($request);
        $reporte = $service->construirReporte($filtros);

        $pdf = Pdf::loadView(
            'livewire.admin.licenciaturas.submodulo.pdf.checklist-listas-profesoresPDF',
            $reporte
        )->setPaper('letter', 'landscape');

        return $pdf->download($service->nombreArchivo($filtros, 'pdf'));
    }

    public function word(
        Request $request,
        ListaProfesorChecklistService $service,
        ListaProfesorChecklistWordService $wordService
    ) {
        $filtros = $this->validar($request);
        $reporte = $service->construirReporte($filtros);
        $ruta = $wordService->generar($reporte);

        return response()
            ->download(
                $ruta,
                $service->nombreArchivo($filtros, 'docx'),
                ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
            )
            ->deleteFileAfterSend(true);
    }

    public function excel(Request $request, ListaProfesorChecklistService $service)
    {
        $filtros = $this->validar($request);
        $reporte = $service->construirReporte($filtros);

        return Excel::download(
            new ChecklistListasProfesoresExport($reporte),
            $service->nombreArchivo($filtros, 'xlsx')
        );
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'licenciatura_id' => ['nullable', 'integer', 'exists:licenciaturas,id'],
            'modalidad_id' => ['nullable', 'integer', 'exists:modalidades,id'],
            'generacion_id' => ['nullable', 'integer', 'exists:generaciones,id'],
        ], [
            'licenciatura_id.exists' => 'La licenciatura seleccionada no es válida.',
            'modalidad_id.exists' => 'La modalidad seleccionada no es válida.',
            'generacion_id.exists' => 'La generación seleccionada no es válida.',
        ]);
    }
}
