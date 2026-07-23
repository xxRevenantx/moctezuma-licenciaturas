<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cuatrimestre;
use App\Models\Directivo;
use App\Models\Escuela;
use App\Models\Inscripcion;
use App\Models\Periodo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HistorialAcademicoAlumnoController extends Controller
{
    public function __invoke(Request $request, Inscripcion $alumno)
    {
        $alumno->loadMissing(['licenciatura', 'modalidad', 'generacion']);

        $escuela = Escuela::query()->firstOrFail();
        $licenciatura = $alumno->licenciatura;
        $cuatrimestres = Cuatrimestre::query()->orderBy('cuatrimestre')->get();
        $periodos = Periodo::query()
            ->where('generacion_id', $alumno->generacion_id)
            ->with('cuatrimestre')
            ->orderBy('id')
            ->get();

        $rector = Directivo::query()->where('identificador', 'rector')->first();
        $directora = Directivo::query()->where('identificador', 'directora')->first();

        $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.historialAcademicoPDF', [
            'alumno' => $alumno,
            'escuela' => $escuela,
            'licenciatura' => $licenciatura,
            'cuatrimestres' => $cuatrimestres,
            'periodos' => $periodos,
            'rector' => $rector,
            'directora' => $directora,
            'fecha' => $request->string('fecha')->toString() ?: now()->toDateString(),
        ])->setPaper('legal', 'portrait');

        $nombre = Str::slug(trim("{$alumno->nombre} {$alumno->apellido_paterno} {$alumno->apellido_materno}"), '_');

        return $pdf->stream("HISTORIAL_ACADEMICO_{$nombre}.pdf");
    }
}
