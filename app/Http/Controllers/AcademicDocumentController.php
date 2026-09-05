<?php

namespace App\Http\Controllers;

use App\Models\AsignacionMateria;
use App\Models\Calificacion;
use App\Models\Constancia;
use App\Models\Cuatrimestre;
use App\Models\Directivo;
use App\Models\Escuela;
use App\Models\Generacion;
use App\Models\Grupo;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use App\Models\Modalidad;
use App\Services\AcademicPeriodResolver;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AcademicDocumentController extends Controller
{
    public function __construct(
        private readonly AcademicPeriodResolver $periodResolver
    ) {
    }

    public function constancia(Request $request)
    {
        $id = $request->constancia_id;

        $constancia = Constancia::findOrFail($id);
        $alumno = Inscripcion::findOrFail($constancia->alumno_id);
        $rector = Directivo::where('cargo', 'Rector')->first();
        $escuela = Escuela::query()->first();

        $periodo = $this->periodResolver->resolveFor(
            (int) $alumno->generacion_id,
            (int) $alumno->cuatrimestre_id
        );

        $data = [
            'constancia' => $constancia,
            'rector' => $rector,
            'escuela' => $escuela,
            'ciclo_escolar' => $periodo,
            'periodo' => $periodo,
        ];

        $pdf = Pdf::loadView(
            'livewire.admin.licenciaturas.submodulo.pdf.constanciasPDF',
            $data
        )->setPaper('letter', 'portrait');

        return $pdf->stream(
            'CONSTANCIA_'.$alumno->nombre.'_'.$alumno->apellido_paterno.'_'.$alumno->apellido_materno.'_'.($alumno->matricula ?: $alumno->matricula_interna ?: $alumno->id).'.pdf'
        );
    }

    public function lista_asistencia_escolarizada(Request $request)
    {
        return $this->listaAsistencia($request, true);
    }

    public function lista_asistencia_semiescolarizada(Request $request)
    {
        return $this->listaAsistencia($request, false);
    }

    private function listaAsistencia(Request $request, bool $esEscolarizada)
    {
        $datos = $request->validate([
            'asignacion_materia' => ['required', 'integer', 'exists:asignacion_materias,id'],
            'licenciatura_id' => ['required', 'integer', 'exists:licenciaturas,id'],
            'cuatrimestre_id' => ['required', 'integer', 'exists:cuatrimestres,id'],
            'generacion_id' => ['required', 'integer', 'exists:generaciones,id'],
            'modalidad_id' => ['required', 'integer', 'exists:modalidades,id'],
            'periodo' => ['required', 'in:9-12,1-4,5-8'],
        ]);

        $periodoEscolar = $this->periodResolver->resolveFor(
            (int) $datos['generacion_id'],
            (int) $datos['cuatrimestre_id']
        );

        $this->periodResolver->assertRequestedRange($periodoEscolar, $datos['periodo']);

        [$mesInicial, $mesFinal] = $this->periodResolver->monthBounds($periodoEscolar);
        $fechas = $this->periodResolver->attendanceDays($periodoEscolar, $esEscolarizada);

        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        $generacion = Generacion::findOrFail($datos['generacion_id']);
        $licenciatura = Licenciatura::findOrFail($datos['licenciatura_id']);

        $materia = AsignacionMateria::query()
            ->with(['materia', 'profesor', 'cuatrimestre'])
            ->whereKey($datos['asignacion_materia'])
            ->where('licenciatura_id', $datos['licenciatura_id'])
            ->where('cuatrimestre_id', $datos['cuatrimestre_id'])
            ->where('modalidad_id', $datos['modalidad_id'])
            ->firstOrFail();

        $alumnos = Inscripcion::query()
            ->where('licenciatura_id', $datos['licenciatura_id'])
            ->where('cuatrimestre_id', $datos['cuatrimestre_id'])
            ->where('generacion_id', $datos['generacion_id'])
            ->where('modalidad_id', $datos['modalidad_id'])
            ->where('status', 'true')
            ->where('foraneo', 'false')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get();

        $vista = $esEscolarizada
            ? 'livewire.admin.licenciaturas.submodulo.pdf.lista-asistencia-escolarizadaPDF'
            : 'livewire.admin.licenciaturas.submodulo.pdf.lista-asistencia-semiescolarizadaPDF';

        $data = [
            'escuela' => Escuela::query()->first(),
            'materia' => $materia,
            'alumnos' => $alumnos,
            'periodo' => [$mesInicial, $mesFinal],
            'fechas' => $fechas,
            'meses' => $meses,
            'generacion' => $generacion,
            'periodos' => $periodoEscolar,
            'ciclo_escolar' => $periodoEscolar,
        ];

        $exportacion = $licenciatura->nombre.'_'
            .$materia->materia->slug.'_'
            .$generacion->generacion.'_'
            .$datos['cuatrimestre_id'].'°_Cuatrimestre';

        $pdf = Pdf::loadView($vista, $data)->setPaper('letter', 'landscape');

        $prefijo = $esEscolarizada
            ? 'LISTA_ASISTENCIA_ESCOLARIZADO_'
            : 'LISTA_ASISTENCIA_';

        return $pdf->stream($prefijo.$exportacion.'.pdf');
    }

    public function lista_evaluacion(Request $request)
    {
        $datos = $request->validate([
            'asignacion_materia' => ['required', 'integer', 'exists:asignacion_materias,id'],
            'licenciatura_id' => ['required', 'integer', 'exists:licenciaturas,id'],
            'cuatrimestre_id' => ['required', 'integer', 'exists:cuatrimestres,id'],
            'generacion_id' => ['required', 'integer', 'exists:generaciones,id'],
            'modalidad_id' => ['required', 'integer', 'exists:modalidades,id'],
            'periodo' => ['required', 'in:9-12,1-4,5-8'],
        ]);

        $periodoEscolar = $this->periodResolver->resolveFor(
            (int) $datos['generacion_id'],
            (int) $datos['cuatrimestre_id']
        );

        $this->periodResolver->assertRequestedRange($periodoEscolar, $datos['periodo']);

        [$mesInicial, $mesFinal] = $this->periodResolver->monthBounds($periodoEscolar);

        $generacion = Generacion::findOrFail($datos['generacion_id']);
        $grupo = Grupo::query()
            ->where('licenciatura_id', $datos['licenciatura_id'])
            ->where('cuatrimestre_id', $datos['cuatrimestre_id'])
            ->first();

        $materia = AsignacionMateria::query()
            ->with(['materia', 'profesor', 'cuatrimestre'])
            ->whereKey($datos['asignacion_materia'])
            ->where('licenciatura_id', $datos['licenciatura_id'])
            ->where('cuatrimestre_id', $datos['cuatrimestre_id'])
            ->where('modalidad_id', $datos['modalidad_id'])
            ->firstOrFail();

        $alumnos = Inscripcion::query()
            ->where('licenciatura_id', $datos['licenciatura_id'])
            ->where('cuatrimestre_id', $datos['cuatrimestre_id'])
            ->where('generacion_id', $datos['generacion_id'])
            ->where('modalidad_id', $datos['modalidad_id'])
            ->where('status', 'true')
            ->where('foraneo', 'false')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get();

        $meses = [
            1 => 'ENE',
            2 => 'FEB',
            3 => 'MAR',
            4 => 'ABR',
            5 => 'MAY',
            6 => 'JUN',
            7 => 'JUL',
            8 => 'AGO',
            9 => 'SEP',
            10 => 'OCT',
            11 => 'NOV',
            12 => 'DIC',
        ];

        $modalidad = Modalidad::findOrFail($datos['modalidad_id']);

        $data = [
            'escuela' => Escuela::query()->first(),
            'materia' => $materia,
            'alumnos' => $alumnos,
            'periodo' => [$mesInicial, $mesFinal],
            'generacion' => $generacion,
            'ciclo_escolar' => $periodoEscolar,
            'meses' => $meses,
            'periodos' => $periodoEscolar,
            'grupo' => $grupo,
            'modalidad' => $modalidad,
        ];

        $pdf = Pdf::loadView(
            'livewire.admin.licenciaturas.submodulo.pdf.lista-evaluacionPDF',
            $data
        )->setPaper('letter', 'landscape');

        $nombreLista = $materia->profesor->nombre.' '
            .$materia->profesor->apellido_paterno.'_'
            .$materia->profesor->apellido_materno.'_'
            .mb_strtoupper($materia->materia->nombre).'_'
            .$materia->cuatrimestre->cuatrimestre.'°_CUATRIMESTRE';

        return $pdf->stream('LISTA_EVALUACION_'.$nombreLista.'.pdf');
    }

    public function calificaciones_generales(Request $request)
    {
        $datos = $request->validate([
            'licenciatura_id' => ['required', 'integer', 'exists:licenciaturas,id'],
            'modalidad_id' => ['required', 'integer', 'exists:modalidades,id'],
            'generacion_id' => ['required', 'integer', 'exists:generaciones,id'],
            'cuatrimestre_id' => ['required', 'integer', 'exists:cuatrimestres,id'],
        ]);

        $periodo = $this->periodResolver->resolveFor(
            (int) $datos['generacion_id'],
            (int) $datos['cuatrimestre_id']
        );

        $escuela = Escuela::query()->first();
        $licenciatura = Licenciatura::findOrFail($datos['licenciatura_id']);
        $generacion = Generacion::findOrFail($datos['generacion_id']);
        $cuatrimestre = Cuatrimestre::findOrFail($datos['cuatrimestre_id']);

        $calificaciones = Calificacion::with(['alumno', 'asignacionMateria.materia'])
            ->where('licenciatura_id', $licenciatura->id)
            ->where('modalidad_id', $datos['modalidad_id'])
            ->where('generacion_id', $generacion->id)
            ->where('cuatrimestre_id', $cuatrimestre->id)
            ->get();

        $totalMaterias = $calificaciones
            ->pluck('asignacionMateria.materia.clave')
            ->filter()
            ->unique()
            ->count();

        $alumnos = $calificaciones
            ->groupBy('alumno_id')
            ->map(function ($items) {
                $alumno = $items->first()->alumno;

                $materias = $items
                    ->sortBy(fn ($item) => $item->asignacionMateria->materia->clave ?? '')
                    ->mapWithKeys(function ($item) {
                        $materia = $item->asignacionMateria->materia;

                        return [
                            $materia->clave => [
                                'nombre' => $materia->nombre,
                                'calificacion' => is_numeric($item->calificacion)
                                    ? (float) $item->calificacion
                                    : $item->calificacion,
                            ],
                        ];
                    });

                $numericas = collect($materias)
                    ->pluck('calificacion')
                    ->filter(fn ($valor) => is_numeric($valor))
                    ->map(fn ($valor) => (float) $valor);

                return [
                    'matricula' => $alumno->matricula,
                    'nombre' => $alumno->nombre ?? '',
                    'apellido_paterno' => $alumno->apellido_paterno ?? '',
                    'apellido_materno' => $alumno->apellido_materno ?? '',
                    'materias' => $materias,
                    'promedio' => $numericas->isNotEmpty() ? $numericas->avg() : 0,
                ];
            })
            ->sortBy('apellido_paterno')
            ->values();

        $data = [
            'cuatrimestres' => $cuatrimestre->cuatrimestre,
            'ciclo_escolar' => $periodo,
            'escuela' => $escuela,
            'licenciatura' => $licenciatura,
            'generacion' => $generacion,
            'periodo' => $periodo,
            'alumnos' => $alumnos,
            'totalMaterias' => $totalMaterias,
        ];

        $pdf = Pdf::loadView(
            'livewire.admin.licenciaturas.submodulo.pdf.calificacionesGeneralesPDF',
            $data
        )->setPaper('letter', 'landscape');

        return $pdf->stream(
            'CALIFICACIONES_GENERALES_'
            .strtoupper($licenciatura->nombre).'_'
            .strtoupper($generacion->generacion).'_'
            .strtoupper((string) $cuatrimestre->cuatrimestre)
            .'°_CUATRIMESTRE.pdf'
        );
    }
}
