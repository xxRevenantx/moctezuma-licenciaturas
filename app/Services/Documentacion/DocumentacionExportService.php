<?php

namespace App\Services\Documentacion;

use App\Models\Cuatrimestre;
use App\Models\Dashboard;
use App\Models\Directivo;
use App\Models\Escuela;
use App\Models\Generacion;
use App\Models\Inscripcion;
use App\Models\Justificante;
use App\Models\Licenciatura;
use App\Models\Materia;
use App\Models\Modalidad;
use App\Models\Periodo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentacionExportService
{
    public function documentoPersonal(Inscripcion $alumno, string $documento, string $fecha): array
    {
        $escuela = Escuela::query()->first();
        $licenciatura = Licenciatura::query()->find($alumno->licenciatura_id);
        $cuatrimestres = Cuatrimestre::query()->get();
        $rector = Directivo::where('cargo', 'Rector')->first();
        $directora = Directivo::where('cargo', 'Directora General')->first();
        $jefe = Directivo::where('identificador', 'jefe')->where('status', 'true')->first();
        $revisado = Directivo::where('identificador', 'revisado')->where('status', 'true')->first();
        $periodos = Periodo::where('generacion_id', $alumno->generacion_id)->get();

        $nombreAlumno = $this->nombreSeguro(trim(
            ($alumno->nombre ?? '') . '_' .
            ($alumno->apellido_paterno ?? '') . '_' .
            ($alumno->apellido_materno ?? '') . '_' .
            ($alumno->matricula ?: $alumno->matricula_interna ?: $alumno->id)
        ));

        return match ($documento) {
            'kardex' => $this->definition(
                'livewire.admin.licenciaturas.submodulo.pdf.kardexPDF',
                compact('alumno', 'escuela', 'licenciatura', 'cuatrimestres', 'rector'),
                "KARDEX_{$nombreAlumno}",
                'legal',
                'portrait'
            ),
            'historial-academico' => $this->definition(
                'livewire.admin.licenciaturas.submodulo.pdf.historialAcademicoPDF',
                compact('alumno', 'escuela', 'licenciatura', 'cuatrimestres', 'rector', 'directora', 'fecha', 'periodos'),
                "HISTORIAL_ACADEMICO_{$nombreAlumno}",
                'legal',
                'portrait'
            ),
            'diploma' => $this->definition(
                'livewire.admin.licenciaturas.submodulo.pdf.diplomaPDF',
                compact('alumno', 'escuela', 'licenciatura', 'fecha', 'rector', 'directora'),
                "DIPLOMA_{$nombreAlumno}"
            ),
            'carta-de-pasante' => $this->definition(
                'livewire.admin.licenciaturas.submodulo.pdf.cartaPasantePDF',
                compact('alumno', 'escuela', 'licenciatura', 'fecha', 'rector', 'directora'),
                "CARTA_DE_PASANTE_{$nombreAlumno}"
            ),
            'constancia-de-termino' => $this->definition(
                'livewire.admin.licenciaturas.submodulo.pdf.constanciaTerminoPDF',
                compact('alumno', 'escuela', 'licenciatura', 'fecha', 'rector', 'directora'),
                "CONSTANCIA_DE_TERMINO_{$nombreAlumno}"
            ),
            'certificado-de-estudios' => $this->definition(
                'livewire.admin.licenciaturas.submodulo.pdf.certificadoPDF',
                compact('alumno', 'escuela', 'licenciatura', 'fecha', 'cuatrimestres', 'rector', 'jefe', 'revisado', 'directora'),
                "CERTIFICADO_DE_ESTUDIOS_{$nombreAlumno}",
                'legal',
                'portrait'
            ),
            default => abort(404, 'Tipo de documento no válido.'),
        };
    }

    /**
     * Generaciones que poseen al menos una licenciatura asignada, con RVOE
     * registrado y estudiantes activos en esa misma generación.
     * No se utiliza el campo generaciones.activa: también pueden necesitar
     * oficios las generaciones que ya concluyeron sus estudios.
     */
    public function generacionesDisponiblesParaOficios(): Collection
    {
        return Generacion::query()
            ->whereIn('id', function ($query) {
                $query->select('asignaciones.generacion_id')
                    ->from('asignar_generaciones as asignaciones')
                    ->join('licenciaturas as carreras', 'carreras.id', '=', 'asignaciones.licenciatura_id')
                    ->join('inscripciones as alumnos', function ($join) {
                        $join->on('alumnos.licenciatura_id', '=', 'asignaciones.licenciatura_id')
                            ->on('alumnos.generacion_id', '=', 'asignaciones.generacion_id');
                    })
                    ->whereNotNull('carreras.RVOE')
                    ->where('alumnos.status', 'true');
            })
            ->orderByDesc('generacion')
            ->get(['id', 'generacion']);
    }

    /**
     * Única fuente de verdad para los filtros de UI y la expedición PDF/Word.
     * La tabla licenciaturas no tiene columna status/activa: una carrera es
     * elegible aquí si está asignada a la generación, tiene RVOE y al menos
     * un alumno con inscripciones.status = true en esa generación.
     */
    public function licenciaturasDisponiblesParaOficio(int $generacionId): Collection
    {
        return Licenciatura::query()
            ->whereNotNull('RVOE')
            ->whereHas('asignarGeneraciones', fn ($query) => $query
                ->where('generacion_id', $generacionId))
            ->whereHas('inscripciones', fn ($query) => $query
                ->where('generacion_id', $generacionId)
                ->where('status', 'true'))
            ->withCount(['inscripciones as alumnos_activos_count' => fn ($query) => $query
                ->where('generacion_id', $generacionId)
                ->where('status', 'true')])
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'RVOE']);
    }

    /**
     * @param array<int>|null $licenciaturaIds null = todas las disponibles;
     *        array explícito = selección de una o varias licenciaturas.
     */
    public function oficio(int $generacionId, string $documento, string $fecha, ?array $licenciaturaIds = null): array
    {
        $generacion = Generacion::findOrFail($generacionId);
        $escuela = Escuela::query()->first();
        $rector = Directivo::where('identificador', 'rector')->first();
        $directora = Directivo::where('identificador', 'directora')->first();
        $jefe = Directivo::where('identificador', 'jefe')->where('status', 'true')->first();
        $subjefe = Directivo::where('identificador', 'subjefe')->where('status', 'true')->first();
        $revisado = Directivo::where('identificador', 'revisado')->where('status', 'true')->first();

        $disponibles = $this->licenciaturasDisponiblesParaOficio($generacionId);
        abort_if($disponibles->isEmpty(), 422,
            'La generación no tiene licenciaturas con RVOE y alumnos activos para este oficio.');

        // Nunca confiar únicamente en los checkboxes del navegador.
        if ($licenciaturaIds !== null) {
            $ids = collect($licenciaturaIds)->map(fn ($id) => (int) $id)->unique()->sort()->values();
            abort_if($ids->isEmpty(), 422, 'Selecciona al menos una licenciatura.');

            $idsValidos = $disponibles->whereIn('id', $ids)->pluck('id')->sort()->values();
            abort_unless($idsValidos->all() === $ids->all(), 422,
                'La selección incluye licenciaturas sin alumnos activos, sin RVOE o ajenas a esta generación.');

            $licenciaturas = $disponibles->whereIn('id', $ids)->values();
            $identificador = '_LIC_' . $ids->implode('-');
        } else {
            $licenciaturas = $disponibles;
            $identificador = '';
        }

        $base = compact('generacion', 'escuela', 'rector', 'directora', 'fecha', 'jefe', 'subjefe', 'licenciaturas');
        $gen = $this->nombreSeguro($generacion->generacion) . $identificador;

        return match ($documento) {
            'matriculas' => $this->definition(
                'livewire.admin.licenciaturas.submodulo.pdf.matriculasOficioPDF',
                $base,
                "OFICIO_MATRICULAS_{$gen}"
            ),
            'kardex' => $this->definition(
                'livewire.admin.licenciaturas.submodulo.pdf.kardexOficioPDF',
                $base,
                "OFICIO_KARDEX_{$gen}"
            ),
            'registro-boletos' => $this->definition(
                'livewire.admin.licenciaturas.submodulo.pdf.registrosBoletasOficioPDF',
                $base,
                "OFICIO_REGISTROS_ESCOLARIDAD_ACTA_RESULTADOS_{$gen}"
            ),
            'folios' => $this->definition(
                'livewire.admin.licenciaturas.submodulo.pdf.foliosOficioPDF',
                $base,
                "OFICIO_FOLIOS_{$gen}"
            ),
            'certificados' => $this->definition(
                'livewire.admin.licenciaturas.submodulo.pdf.certificadosOficioPDF',
                [...$base, 'revisado' => $revisado],
                "OFICIO_CERTIFICADOS_{$gen}"
            ),
            default => abort(404, 'Tipo de oficio no válido.'),
        };
    }

    public function expedicion(
        string $documento,
        Licenciatura $licenciatura,
        Generacion $generacion,
        ?array $alumnoIds = null
    ): ?array {
        $alumnosQuery = Inscripcion::query()
            ->with('calificaciones')
            ->where('generacion_id', $generacion->id)
            ->where('licenciatura_id', $licenciatura->id)
            ->where('status', 'true');

        if ($alumnoIds !== null) {
            $alumnosQuery->whereIn('id', $alumnoIds);
        }

        $alumnos = $alumnosQuery
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get();

        if ($alumnos->isEmpty()) {
            return null;
        }

        $escuela = Escuela::query()->first();
        abort_if(! $escuela, 422, 'No se encontró la información del plantel.');

        $rector = Directivo::where('identificador', 'rector')->first();
        $directora = Directivo::where('identificador', 'directora')->first();
        $jefe = Directivo::where('identificador', 'jefe')->where('status', 'true')->first();
        $nombreLicenciatura = $this->nombreSeguro($licenciatura->nombre);
        $nombreGeneracion = $this->nombreSeguro($generacion->generacion);

        if ($documento === 'acta-resultados') {
            $materias = Materia::query()
                ->where('licenciatura_id', $licenciatura->id)
                ->where('calificable', '!=', 'false')
                ->orderBy('clave')
                ->get();

            return $this->definition(
                'livewire.admin.licenciaturas.submodulo.pdf.actaResultadosPDF',
                compact('generacion', 'escuela', 'licenciatura', 'materias', 'rector', 'directora', 'jefe', 'alumnos'),
                "ACTA_DE_RESULTADOS_{$nombreLicenciatura}_GEN_{$nombreGeneracion}"
            );
        }

        if ($documento === 'registro-escolaridad') {
            $materias = Materia::query()
                ->where('licenciatura_id', $licenciatura->id)
                ->where('calificable', '!=', 'false')
                ->orderBy('clave')
                ->get();
            $periodos = Periodo::where('generacion_id', $generacion->id)->get();
            $modalidades = Modalidad::all();

            return $this->definition(
                'livewire.admin.licenciaturas.submodulo.pdf.registroEscolaridadPDF',
                compact('generacion', 'escuela', 'materias', 'licenciatura', 'alumnos', 'periodos', 'modalidades', 'rector', 'jefe'),
                "REGISTRO_DE_ESCOLARIDAD_{$nombreLicenciatura}_GEN_{$nombreGeneracion}",
                'legal',
                'landscape'
            );
        }

        return null;
    }

    public function credenciales(array $alumnoIds): array
    {
        abort_if(empty($alumnoIds), 422, 'No se seleccionaron alumnos.');

        return [
            'alumnos' => Inscripcion::query()
                ->with(['licenciatura', 'generacion'])
                ->where('status', 'true')
                ->whereIn('id', $alumnoIds)
                ->orderBy('apellido_paterno')
                ->orderBy('apellido_materno')
                ->orderBy('nombre')
                ->get(),
            'ciclo_escolar' => Dashboard::orderByDesc('id')->first(),
        ];
    }

    public function etiquetas(array $alumnoIds): array
    {
        abort_if(empty($alumnoIds), 422, 'No se seleccionaron alumnos.');

        return [
            'alumnos' => Inscripcion::query()
                ->with(['licenciatura', 'generacion'])
                ->whereIn('id', $alumnoIds)
                ->orderBy('apellido_paterno')
                ->orderBy('apellido_materno')
                ->orderBy('nombre')
                ->get(),
            'ciclo_escolar' => Dashboard::orderByDesc('id')->first(),
        ];
    }

    public function justificante(int $id): Justificante
    {
        return Justificante::query()
            ->with(['alumno.licenciatura', 'alumno.cuatrimestre'])
            ->findOrFail($id);
    }

    public function promedios(int $licenciaturaId, int $generacionId): array
    {
        $licenciatura = Licenciatura::findOrFail($licenciaturaId);
        $generacion = Generacion::findOrFail($generacionId);
        $escuela = Escuela::query()->first();
        $ciclo_escolar = Dashboard::orderByDesc('id')->first();

        $stats = DB::table('calificaciones as c')
            ->select([
                'c.alumno_id',
                'c.licenciatura_id',
                'c.generacion_id',
                DB::raw("TRUNCATE(AVG(CASE WHEN c.calificacion REGEXP '^[0-9]+(\\.[0-9]+)?$' THEN c.calificacion + 0 ELSE NULL END), 1) AS promedio_final"),
                DB::raw("COUNT(DISTINCT CASE WHEN c.calificacion REGEXP '^[0-9]+(\\.[0-9]+)?$' THEN c.asignacion_materia_id ELSE NULL END) AS total_materias"),
            ])
            ->where('c.licenciatura_id', $licenciaturaId)
            ->where('c.generacion_id', $generacionId)
            ->groupBy('c.alumno_id', 'c.licenciatura_id', 'c.generacion_id');

        $alumnos = Inscripcion::query()
            ->leftJoinSub($stats, 'stats', function ($join) {
                $join->on('stats.alumno_id', '=', 'inscripciones.id')
                    ->on('stats.licenciatura_id', '=', 'inscripciones.licenciatura_id')
                    ->on('stats.generacion_id', '=', 'inscripciones.generacion_id');
            })
            ->where('inscripciones.licenciatura_id', $licenciaturaId)
            ->where('inscripciones.generacion_id', $generacionId)
            ->where('inscripciones.status', 'true')
            ->select('inscripciones.*')
            ->addSelect(['stats.promedio_final', 'stats.total_materias'])
            ->orderBy('inscripciones.apellido_paterno')
            ->orderBy('inscripciones.apellido_materno')
            ->orderBy('inscripciones.nombre')
            ->get();

        return compact('alumnos', 'licenciatura', 'generacion', 'escuela', 'ciclo_escolar');
    }

    public function controlDocumental(int $licenciaturaId = 0, int $generacionId = 0, string $estado = 'activos'): array
    {
        abort_unless(in_array($estado, ['activos', 'egresados', 'bajas', 'todos'], true), 422, 'Estado no válido.');

        $licenciaturaNombre = 'Todas las licenciaturas';
        $generacionNombre = 'Todas las generaciones';
        $tipos = config('documentos_identidad.types', []);
        $disk = (string) config('documentos_identidad.disk', 'local');

        $query = Inscripcion::query()->with([
            'generacion:id,generacion',
            'licenciatura:id,nombre',
            'documentosIdentidadActuales',
        ]);

        if ($licenciaturaId !== 0) {
            $lic = Licenciatura::findOrFail($licenciaturaId);
            $licenciaturaNombre = $lic->nombre;
            $query->where('licenciatura_id', $lic->id);
        }

        if ($generacionId !== 0) {
            $generacion = Generacion::findOrFail($generacionId);
            $generacionNombre = $generacion->generacion;
            $query->where('generacion_id', $generacionId);
        }

        match ($estado) {
            'activos' => $query->where('status', 'true')->where('egresado', 'false'),
            'egresados' => $query->where('egresado', 'true'),
            'bajas' => $query->where('status', 'false'),
            default => null,
        };

        $alumnos = $query
            ->orderBy('generacion_id')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get()
            ->map(function (Inscripcion $alumno) use ($tipos, $disk): Inscripcion {
                $actuales = $alumno->documentosIdentidadActuales->keyBy('tipo');
                $estados = [];
                $entregados = 0;

                foreach ($tipos as $tipo => $config) {
                    $documento = $actuales->get($tipo);
                    $existe = $documento && Storage::disk($disk)->exists($documento->ruta);
                    $estados[$tipo] = $existe;
                    $entregados += $existe ? 1 : 0;
                }

                $alumno->setAttribute('documentos_estado', $estados);
                $alumno->setAttribute('documentos_entregados', $entregados);
                $alumno->setAttribute('documentos_total', count($tipos));
                $alumno->setAttribute('documentos_porcentaje', count($tipos) > 0
                    ? (int) round(($entregados / count($tipos)) * 100)
                    : 0);

                return $alumno;
            });

        $grupos = $alumnos
            ->groupBy(fn ($alumno) => optional($alumno->generacion)->generacion ?: 'Sin generación')
            ->sortKeys();

        $resumen = [
            'alumnos' => $alumnos->count(),
            'completos' => $alumnos->where('documentos_porcentaje', 100)->count(),
            'con_documentos' => $alumnos->where('documentos_entregados', '>', 0)->count(),
            'sin_documentos' => $alumnos->where('documentos_entregados', 0)->count(),
        ];

        return compact('licenciaturaNombre', 'generacionNombre', 'estado', 'grupos', 'tipos', 'resumen');
    }

    public function nombreSeguro(?string $texto): string
    {
        $texto = trim((string) $texto);
        $ascii = Str::upper(Str::ascii($texto));
        $ascii = preg_replace('/[^A-Z0-9_-]+/', '_', $ascii) ?? 'DOCUMENTO';

        return trim($ascii, '_') ?: 'DOCUMENTO';
    }

    private function definition(
        string $view,
        array $data,
        string $filenameBase,
        string $paper = 'letter',
        string $orientation = 'portrait'
    ): array {
        return compact('view', 'data', 'filenameBase', 'paper', 'orientation');
    }
}
