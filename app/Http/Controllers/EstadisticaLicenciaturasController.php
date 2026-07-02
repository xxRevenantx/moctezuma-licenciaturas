<?php

namespace App\Http\Controllers;

use App\Exports\EstadisticaExport;
use App\Models\Directivo;
use App\Models\Escuela;
use App\Services\EstadisticaLicenciaturasService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class EstadisticaLicenciaturasController extends Controller
{
    public function pdf(Request $request, EstadisticaLicenciaturasService $servicio)
    {
        $reporte = $servicio->generar($request->all());
        $institucion = $this->institucion();
        $nombreArchivo = $this->nombreArchivo('pdf', $reporte['filtros']);

        $pdf = Pdf::loadView('livewire.admin.documentacion.pdf.estadistica-licenciaturas', [
            'reporte' => $reporte,
            'escuela' => $institucion['escuela'],
            'directivo' => $institucion['directivo'],
            'fechaGeneracion' => now(),
        ])->setPaper('letter', 'landscape');

        if ($request->string('disposition')->toString() === 'download') {
            return $pdf->download($nombreArchivo);
        }

        return $pdf->stream($nombreArchivo);
    }

    public function excel(Request $request, EstadisticaLicenciaturasService $servicio)
    {
        $reporte = $servicio->generar($request->all());
        $institucion = $this->institucion();

        return Excel::download(
            new EstadisticaExport($reporte, $institucion),
            $this->nombreArchivo('xlsx', $reporte['filtros'])
        );
    }

    private function institucion(): array
    {
        $escuela = Escuela::query()->first();
        $directivo = Directivo::query()
            ->where('status', 'true')
            ->whereIn('identificador', ['rector', 'directora'])
            ->orderByRaw("CASE WHEN identificador = 'rector' THEN 0 ELSE 1 END")
            ->first();

        return [
            'escuela' => $escuela?->toArray() ?? [],
            'directivo' => $directivo?->toArray() ?? [],
        ];
    }

    private function nombreArchivo(string $extension, array $filtros): string
    {
        $ciclo = $filtros['ciclo_escolar'] ?: 'todos-los-ciclos';
        $ciclo = preg_replace('/[^A-Za-z0-9_-]+/', '-', $ciclo);

        return sprintf(
            'estadistica-licenciaturas-%s-%s.%s',
            trim($ciclo, '-'),
            now()->format('d-m-Y'),
            $extension
        );
    }
}
