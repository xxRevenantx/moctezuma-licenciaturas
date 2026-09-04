<?php

namespace App\Http\Controllers;

use App\Exceptions\AcademicPeriodException;
use App\Models\AsignacionMateria;
use App\Models\Escuela;
use App\Models\Generacion;
use App\Models\Grupo;
use App\Models\Inscripcion;
use App\Models\Periodo;
use App\Models\Profesor;
use App\Services\AcademicPeriodResolver;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use setasign\Fpdi\Fpdi;

class AcademicListaProfesorController extends Controller
{
    public function __construct(
        private readonly AcademicPeriodResolver $periodResolver
    ) {
    }

    public function masivas(Request $request)
    {
        $datos = $request->validate([
            'profesor_id' => ['required', 'integer', 'exists:profesores,id'],
            'periodo' => ['required', 'in:9-12,1-4,5-8'],
            'tipo' => ['required', 'in:asistencia,evaluacion,ambas'],
            'filtro_alumnos' => ['required', 'in:locales,foraneos,todos'],
            'asignaciones' => ['required', 'array', 'min:1'],
            'asignaciones.*' => ['required', 'integer', 'distinct', 'exists:asignacion_materias,id'],
        ], [
            'profesor_id.required' => 'Debes seleccionar un profesor.',
            'periodo.required' => 'Debes seleccionar un periodo.',
            'asignaciones.required' => 'Debes seleccionar al menos una materia.',
            'asignaciones.min' => 'Debes seleccionar al menos una materia.',
        ]);

        $profesor = Profesor::with('user')->findOrFail($datos['profesor_id']);
        $escuela = Escuela::query()->firstOrFail();

        $asignaciones = AsignacionMateria::query()
            ->with([
                'materia',
                'profesor',
                'licenciatura',
                'modalidad',
                'cuatrimestre',
                'horarios' => function ($query) {
                    $query->select('id', 'asignacion_materia_id', 'generacion_id')
                        ->whereNotNull('generacion_id');
                },
            ])
            ->where('profesor_id', $profesor->id)
            ->whereIn('id', $datos['asignaciones'])
            ->whereHas('horarios')
            ->get()
            ->sortBy(function (AsignacionMateria $asignacion) {
                return mb_strtolower(implode('|', [
                    $asignacion->licenciatura->nombre ?? '',
                    str_pad((string) ($asignacion->cuatrimestre->cuatrimestre ?? ''), 3, '0', STR_PAD_LEFT),
                    $asignacion->materia->nombre ?? '',
                ]));
            })
            ->values();

        abort_if(
            $asignaciones->isEmpty(),
            422,
            'Las materias seleccionadas no pertenecen al profesor o no tienen horario asignado.'
        );

        $directorioTemporal = 'exportaciones/listas-profesor/'.Str::uuid();
        Storage::disk('local')->makeDirectory($directorioTemporal);

        try {
            [$documentos, $omitidas] = $this->crearListas(
                $asignaciones,
                $datos['periodo'],
                $datos['tipo'],
                $datos['filtro_alumnos'],
                $escuela,
                $directorioTemporal
            );

            abort_if(
                empty($documentos),
                422,
                'No fue posible generar listas. Revisa alumnos, periodos, ciclos, fechas y grupos. '
                .'Las inconsistencias de periodo no se corrigen automáticamente.'
            );

            $ciclos = collect($documentos)
                ->pluck('ciclo_escolar')
                ->filter()
                ->unique()
                ->values();

            $resumen = [
                'profesor' => $profesor,
                'ciclo_escolar' => $ciclos->count() === 1
                    ? (string) $ciclos->first()
                    : $ciclos->implode(', '),
                'periodo' => $this->etiquetaPeriodo($datos['periodo']),
                'tipo' => $this->etiquetaTipo($datos['tipo']),
                'filtro_alumnos' => $this->etiquetaFiltro($datos['filtro_alumnos']),
                'total_materias' => collect($documentos)->pluck('asignacion_id')->unique()->count(),
                'total_listas' => count($documentos),
                'total_omitidas' => count($omitidas),
                'total_alumnos' => collect($documentos)->sum('alumnos'),
                'generado_en' => now(),
            ];

            $rutaPortada = $this->guardarPdf(
                Pdf::loadView(
                    'livewire.admin.licenciaturas.submodulo.pdf.lista-masiva-profesor-portadaPDF',
                    [
                        'escuela' => $escuela,
                        'resumen' => $resumen,
                    ]
                )->setPaper('letter', 'landscape'),
                $directorioTemporal,
                '000_portada.pdf'
            );

            $paginasPortada = $this->contarPaginas($rutaPortada);

            $rutaIndice = $this->guardarPdf(
                Pdf::loadView(
                    'livewire.admin.licenciaturas.submodulo.pdf.lista-masiva-profesor-indicePDF',
                    [
                        'escuela' => $escuela,
                        'resumen' => $resumen,
                        'documentos' => $documentos,
                        'omitidas' => $omitidas,
                    ]
                )->setPaper('letter', 'landscape'),
                $directorioTemporal,
                '001_indice.pdf'
            );

            $paginasIndice = $this->contarPaginas($rutaIndice);

            for ($intento = 0; $intento < 3; $intento++) {
                $paginaSiguiente = $paginasPortada + $paginasIndice + 1;

                foreach ($documentos as $indice => $documento) {
                    $documentos[$indice]['pagina_inicio'] = $paginaSiguiente;
                    $paginaSiguiente += $documento['paginas'];
                }

                $rutaIndice = $this->guardarPdf(
                    Pdf::loadView(
                        'livewire.admin.licenciaturas.submodulo.pdf.lista-masiva-profesor-indicePDF',
                        [
                            'escuela' => $escuela,
                            'resumen' => $resumen,
                            'documentos' => $documentos,
                            'omitidas' => $omitidas,
                        ]
                    )->setPaper('letter', 'landscape'),
                    $directorioTemporal,
                    '001_indice.pdf'
                );

                $nuevoTotalPaginasIndice = $this->contarPaginas($rutaIndice);

                if ($nuevoTotalPaginasIndice === $paginasIndice) {
                    break;
                }

                $paginasIndice = $nuevoTotalPaginasIndice;
            }

            $rutas = array_merge(
                [$rutaPortada, $rutaIndice],
                collect($documentos)->pluck('ruta')->all()
            );

            $contenido = $this->combinarPdfs($rutas);
            $nombreArchivo = $this->nombreArchivo(
                $profesor,
                $datos['tipo'],
                $datos['periodo'],
                $resumen['ciclo_escolar']
            );

            return response($contenido, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$nombreArchivo.'"',
                'Cache-Control' => 'private, no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
            ]);
        } finally {
            Storage::disk('local')->deleteDirectory($directorioTemporal);
        }
    }

    /**
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, array<string, mixed>>}
     */
    private function crearListas(
        Collection $asignaciones,
        string $periodoSolicitado,
        string $tipo,
        string $filtroAlumnos,
        Escuela $escuela,
        string $directorioTemporal
    ): array {
        $documentos = [];
        $omitidas = [];
        $secuencia = 10;

        foreach ($asignaciones as $asignacion) {
            $generacionesIds = $asignacion->horarios
                ->pluck('generacion_id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->sort()
                ->values();

            if ($generacionesIds->isEmpty()) {
                $omitidas[] = $this->registroOmitido(
                    $asignacion,
                    null,
                    $this->etiquetaTipo($tipo),
                    'La materia no tiene una generación vinculada en el horario.'
                );
                continue;
            }

            foreach ($generacionesIds as $generacionId) {
                $generacion = Generacion::query()->find($generacionId);

                if (!$generacion) {
                    $omitidas[] = $this->registroOmitido(
                        $asignacion,
                        null,
                        $this->etiquetaTipo($tipo),
                        'La generación vinculada al horario ya no existe.'
                    );
                    continue;
                }

                try {
                    $periodoEscolar = $this->periodResolver->resolveFor(
                        (int) $generacionId,
                        (int) $asignacion->cuatrimestre_id
                    );

                    $this->periodResolver->assertRequestedRange(
                        $periodoEscolar,
                        $periodoSolicitado
                    );
                } catch (AcademicPeriodException $e) {
                    $omitidas[] = $this->registroOmitido(
                        $asignacion,
                        $generacion,
                        $this->etiquetaTipo($tipo),
                        $e->getMessage()
                    );
                    continue;
                }

                $alumnos = $this->alumnosDeLaLista(
                    $asignacion,
                    $generacionId,
                    $filtroAlumnos
                );

                if ($alumnos->isEmpty()) {
                    $omitidas[] = $this->registroOmitido(
                        $asignacion,
                        $generacion,
                        $this->etiquetaTipo($tipo),
                        'No hay alumnos activos que coincidan con el filtro seleccionado.'
                    );
                    continue;
                }

                if (in_array($tipo, ['asistencia', 'ambas'], true)) {
                    $pdfAsistencia = $this->pdfAsistencia(
                        $asignacion,
                        $generacion,
                        $alumnos,
                        $periodoEscolar,
                        $escuela
                    );

                    $ruta = $this->guardarPdf(
                        $pdfAsistencia,
                        $directorioTemporal,
                        sprintf('%03d_asistencia.pdf', $secuencia++)
                    );

                    $documentos[] = $this->registroDocumento(
                        $asignacion,
                        $generacion,
                        'Asistencia',
                        $alumnos->count(),
                        $ruta,
                        $periodoEscolar
                    );
                }

                if (in_array($tipo, ['evaluacion', 'ambas'], true)) {
                    $grupo = Grupo::query()
                        ->where('licenciatura_id', $asignacion->licenciatura_id)
                        ->where('cuatrimestre_id', $asignacion->cuatrimestre_id)
                        ->first();

                    if (!$grupo) {
                        $omitidas[] = $this->registroOmitido(
                            $asignacion,
                            $generacion,
                            'Evaluación',
                            'No existe un grupo configurado para la licenciatura y el cuatrimestre.'
                        );
                        continue;
                    }

                    $pdfEvaluacion = $this->pdfEvaluacion(
                        $asignacion,
                        $generacion,
                        $alumnos,
                        $periodoEscolar,
                        $grupo,
                        $escuela
                    );

                    $ruta = $this->guardarPdf(
                        $pdfEvaluacion,
                        $directorioTemporal,
                        sprintf('%03d_evaluacion.pdf', $secuencia++)
                    );

                    $documentos[] = $this->registroDocumento(
                        $asignacion,
                        $generacion,
                        'Evaluación',
                        $alumnos->count(),
                        $ruta,
                        $periodoEscolar
                    );
                }
            }
        }

        return [$documentos, $omitidas];
    }

    private function alumnosDeLaLista(
        AsignacionMateria $asignacion,
        int $generacionId,
        string $filtroAlumnos
    ): Collection {
        return Inscripcion::query()
            ->where('licenciatura_id', $asignacion->licenciatura_id)
            ->where('cuatrimestre_id', $asignacion->cuatrimestre_id)
            ->where('generacion_id', $generacionId)
            ->where('modalidad_id', $asignacion->modalidad_id)
            ->where('status', 'true')
            ->when(
                $filtroAlumnos === 'locales',
                fn ($query) => $query->where('foraneo', 'false')
            )
            ->when(
                $filtroAlumnos === 'foraneos',
                fn ($query) => $query->where('foraneo', 'true')
            )
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get();
    }

    private function pdfAsistencia(
        AsignacionMateria $asignacion,
        Generacion $generacion,
        Collection $alumnos,
        Periodo $periodoEscolar,
        Escuela $escuela
    ) {
        [$mesInicial, $mesFinal] = $this->periodResolver->monthBounds($periodoEscolar);

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

        $esEscolarizada = mb_strtoupper(
            (string) $asignacion->modalidad->nombre,
            'UTF-8'
        ) === 'ESCOLARIZADA';

        $fechas = $this->periodResolver->attendanceDays(
            $periodoEscolar,
            $esEscolarizada
        );

        $vista = $esEscolarizada
            ? 'livewire.admin.licenciaturas.submodulo.pdf.lista-asistencia-escolarizadaPDF'
            : 'livewire.admin.licenciaturas.submodulo.pdf.lista-asistencia-semiescolarizadaPDF';

        return Pdf::loadView($vista, [
            'escuela' => $escuela,
            'materia' => $asignacion,
            'alumnos' => $alumnos,
            'periodo' => [$mesInicial, $mesFinal],
            'fechas' => $fechas,
            'meses' => $meses,
            'generacion' => $generacion,
            'periodos' => $periodoEscolar,
            'ciclo_escolar' => $periodoEscolar,
        ])->setPaper('letter', 'landscape');
    }

    private function pdfEvaluacion(
        AsignacionMateria $asignacion,
        Generacion $generacion,
        Collection $alumnos,
        Periodo $periodoEscolar,
        Grupo $grupo,
        Escuela $escuela
    ) {
        [$mesInicial, $mesFinal] = $this->periodResolver->monthBounds($periodoEscolar);

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

        return Pdf::loadView(
            'livewire.admin.licenciaturas.submodulo.pdf.lista-evaluacionPDF',
            [
                'escuela' => $escuela,
                'materia' => $asignacion,
                'alumnos' => $alumnos,
                'periodo' => [$mesInicial, $mesFinal],
                'generacion' => $generacion,
                'ciclo_escolar' => $periodoEscolar,
                'meses' => $meses,
                'periodos' => $periodoEscolar,
                'grupo' => $grupo,
                'modalidad' => $asignacion->modalidad,
            ]
        )->setPaper('letter', 'landscape');
    }

    private function registroDocumento(
        AsignacionMateria $asignacion,
        Generacion $generacion,
        string $tipo,
        int $alumnos,
        string $ruta,
        Periodo $periodoEscolar
    ): array {
        return [
            'asignacion_id' => $asignacion->id,
            'tipo' => $tipo,
            'materia' => $asignacion->materia->nombre,
            'licenciatura' => $asignacion->licenciatura->nombre,
            'cuatrimestre' => $asignacion->cuatrimestre->cuatrimestre,
            'modalidad' => $asignacion->modalidad->nombre,
            'generacion' => $generacion->generacion,
            'ciclo_escolar' => $periodoEscolar->ciclo_escolar,
            'periodo' => $periodoEscolar->mes?->meses_corto,
            'alumnos' => $alumnos,
            'ruta' => $ruta,
            'paginas' => $this->contarPaginas($ruta),
            'pagina_inicio' => null,
        ];
    }

    private function registroOmitido(
        AsignacionMateria $asignacion,
        ?Generacion $generacion,
        string $tipo,
        string $motivo
    ): array {
        return [
            'tipo' => $tipo,
            'materia' => $asignacion->materia->nombre ?? 'Materia no disponible',
            'licenciatura' => $asignacion->licenciatura->nombre ?? 'Licenciatura no disponible',
            'cuatrimestre' => $asignacion->cuatrimestre->cuatrimestre ?? '-',
            'modalidad' => $asignacion->modalidad->nombre ?? '-',
            'generacion' => $generacion?->generacion ?? '-',
            'motivo' => $motivo,
        ];
    }

    private function guardarPdf($pdf, string $directorio, string $nombre): string
    {
        $rutaRelativa = $directorio.'/'.$nombre;
        $rutaAbsoluta = Storage::disk('local')->path($rutaRelativa);

        file_put_contents($rutaAbsoluta, $pdf->output());

        return $rutaAbsoluta;
    }

    private function contarPaginas(string $ruta): int
    {
        $lector = new Fpdi();

        return $lector->setSourceFile($ruta);
    }

    private function combinarPdfs(array $rutas): string
    {
        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);

        foreach ($rutas as $ruta) {
            $totalPaginas = $pdf->setSourceFile($ruta);

            for ($pagina = 1; $pagina <= $totalPaginas; $pagina++) {
                $plantilla = $pdf->importPage($pagina);
                $tamano = $pdf->getTemplateSize($plantilla);
                $orientacion = $tamano['width'] > $tamano['height'] ? 'L' : 'P';

                $pdf->AddPage($orientacion, [$tamano['width'], $tamano['height']]);
                $pdf->useTemplate(
                    $plantilla,
                    0,
                    0,
                    $tamano['width'],
                    $tamano['height'],
                    true
                );
            }
        }

        return $pdf->Output('S');
    }

    private function etiquetaPeriodo(string $periodo): string
    {
        return match ($periodo) {
            '9-12' => 'SEP/DIC',
            '1-4' => 'ENE/ABR',
            '5-8' => 'MAY/AGO',
            default => $periodo,
        };
    }

    private function etiquetaTipo(string $tipo): string
    {
        return match ($tipo) {
            'asistencia' => 'Asistencia',
            'evaluacion' => 'Evaluación',
            'ambas' => 'Asistencia y evaluación',
            default => $tipo,
        };
    }

    private function etiquetaFiltro(string $filtro): string
    {
        return match ($filtro) {
            'locales' => 'Solo alumnos locales',
            'foraneos' => 'Solo alumnos foráneos',
            'todos' => 'Alumnos locales y foráneos',
            default => $filtro,
        };
    }

    private function nombreArchivo(
        Profesor $profesor,
        string $tipo,
        string $periodo,
        string $ciclo
    ): string {
        $prefijo = match ($tipo) {
            'asistencia' => 'ASISTENCIAS',
            'evaluacion' => 'EVALUACIONES',
            default => 'LISTAS',
        };

        $nombreProfesor = implode('_', array_filter([
            $profesor->nombre,
            $profesor->apellido_paterno,
            $profesor->apellido_materno,
        ]));

        $nombreProfesor = mb_strtoupper(Str::ascii($nombreProfesor));
        $nombreProfesor = preg_replace('/[^A-Z0-9_]+/', '_', $nombreProfesor) ?: 'PROFESOR';
        $periodoArchivo = str_replace('/', '_', $this->etiquetaPeriodo($periodo));
        $cicloArchivo = preg_replace('/[^0-9A-Za-z_-]+/', '_', $ciclo) ?: 'CICLO';

        return sprintf(
            '%s_%s_%s_%s.pdf',
            $prefijo,
            trim($nombreProfesor, '_'),
            $periodoArchivo,
            trim($cicloArchivo, '_')
        );
    }
}
