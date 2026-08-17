<?php

namespace App\Services\Boletas;

use App\Models\AsignacionMateria;
use App\Models\Calificacion;
use App\Models\Cuatrimestre;
use App\Models\Dashboard;
use App\Models\Escuela;
use App\Models\Generacion;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use App\Models\Modalidad;
use App\Models\Periodo;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BoletaService
{
    /**
     * Licenciaturas disponibles únicamente si tienen al menos un alumno activo.
     */
    public function licenciaturasConAlumnos(): EloquentCollection
    {
        return Licenciatura::query()
            ->whereHas('inscripciones', fn ($q) => $q->where('status', 'true'))
            ->withCount([
                'inscripciones as alumnos_activos_count' => fn ($q) => $q->where('status', 'true'),
            ])
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'nombre_corto', 'slug']);
    }

    public function modalidadesConAlumnos(int $licenciaturaId): EloquentCollection
    {
        return Modalidad::query()
            ->whereHas('inscripcion', function ($q) use ($licenciaturaId) {
                $q->where('licenciatura_id', $licenciaturaId)
                    ->where('status', 'true');
            })
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'slug']);
    }

    public function generacionesConAlumnos(int $licenciaturaId, int $modalidadId): EloquentCollection
    {
        return Generacion::query()
            ->whereHas('inscripcion', function ($q) use ($licenciaturaId, $modalidadId) {
                $q->where('licenciatura_id', $licenciaturaId)
                    ->where('modalidad_id', $modalidadId)
                    ->where('status', 'true');
            })
            ->orderByDesc('id')
            ->get(['id', 'generacion', 'activa']);
    }

    /**
     * Devuelve únicamente cuatrimestres que pertenecen a la generación y que,
     * para la licenciatura/modalidad, tienen materias calificables o calificaciones.
     */
    public function cuatrimestresDisponibles(int $licenciaturaId, int $modalidadId, int $generacionId): Collection
    {
        $idsAsignaciones = AsignacionMateria::query()
            ->where('licenciatura_id', $licenciaturaId)
            ->where('modalidad_id', $modalidadId)
            ->whereHas('materia', fn ($q) => $q->where('calificable', 'true'))
            ->distinct()
            ->pluck('cuatrimestre_id');

        $idsConCalificaciones = Calificacion::query()
            ->where('licenciatura_id', $licenciaturaId)
            ->where('modalidad_id', $modalidadId)
            ->where('generacion_id', $generacionId)
            ->distinct()
            ->pluck('cuatrimestre_id');

        $ids = $idsAsignaciones
            ->merge($idsConCalificaciones)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return Periodo::query()
            ->with(['cuatrimestre:id,cuatrimestre,nombre_cuatrimestre', 'mes:id,meses_corto'])
            ->where('generacion_id', $generacionId)
            ->whereIn('cuatrimestre_id', $ids)
            ->orderBy('order')
            ->get()
            ->unique('cuatrimestre_id')
            ->values();
    }

    public function alumnosActivos(
        int $licenciaturaId,
        int $modalidadId,
        int $generacionId,
        ?string $search = null
    ): EloquentCollection {
        return Inscripcion::query()
            ->with('user:id,email')
            ->where('licenciatura_id', $licenciaturaId)
            ->where('modalidad_id', $modalidadId)
            ->where('generacion_id', $generacionId)
            ->where('status', 'true')
            ->when(trim((string) $search) !== '', function ($q) use ($search) {
                $s = trim((string) $search);
                $q->where(function ($sub) use ($s) {
                    $sub->where('nombre', 'like', "%{$s}%")
                        ->orWhere('apellido_paterno', 'like', "%{$s}%")
                        ->orWhere('apellido_materno', 'like', "%{$s}%")
                        ->orWhere('matricula', 'like', "%{$s}%")
                        ->orWhere('CURP', 'like', "%{$s}%");
                });
            })
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get();
    }

    public function validarAlumnoEnContexto(
        int $alumnoId,
        int $licenciaturaId,
        int $modalidadId,
        int $generacionId,
        bool $soloActivo = true
    ): Inscripcion {
        return Inscripcion::query()
            ->with('user')
            ->whereKey($alumnoId)
            ->where('licenciatura_id', $licenciaturaId)
            ->where('modalidad_id', $modalidadId)
            ->where('generacion_id', $generacionId)
            ->when($soloActivo, fn ($q) => $q->where('status', 'true'))
            ->firstOrFail();
    }

    /**
     * Obtiene las asignaciones canónicas: una sola asignación por materia.
     * Si existen duplicados, se toma el ID menor, igual que en la captura de calificaciones.
     */
    public function asignacionesCanonicas(int $licenciaturaId, int $modalidadId, int $cuatrimestreId): Collection
    {
        return AsignacionMateria::query()
            ->with(['materia', 'profesor'])
            ->where('licenciatura_id', $licenciaturaId)
            ->where('modalidad_id', $modalidadId)
            ->where('cuatrimestre_id', $cuatrimestreId)
            ->whereHas('materia', fn ($q) => $q->where('calificable', 'true'))
            ->get()
            ->groupBy('materia_id')
            ->map(fn ($grupo) => $grupo->sortBy('id')->first())
            ->sortBy(fn ($asig) => optional($asig->materia)->clave ?? optional($asig->materia)->orden ?? $asig->materia_id)
            ->values();
    }

    /**
     * Normaliza calificaciones duplicadas por materia. La última calificación registrada
     * para la materia gana y el resultado queda ordenado por clave de materia.
     */
    public function calificacionesNormalizadas(
        int $alumnoId,
        int $licenciaturaId,
        int $modalidadId,
        int $generacionId,
        int $cuatrimestreId
    ): Collection {
        return Calificacion::query()
            ->with(['asignacionMateria.materia', 'asignacionMateria.profesor'])
            ->where('alumno_id', $alumnoId)
            ->where('licenciatura_id', $licenciaturaId)
            ->where('modalidad_id', $modalidadId)
            ->where('generacion_id', $generacionId)
            ->where('cuatrimestre_id', $cuatrimestreId)
            ->orderBy('id')
            ->get()
            ->filter(fn ($c) => $c->asignacionMateria?->materia_id)
            ->groupBy(fn ($c) => $c->asignacionMateria->materia_id)
            ->map(fn ($grupo) => $grupo->last())
            ->sortBy(fn ($c) => $c->asignacionMateria?->materia?->clave ?? '')
            ->values();
    }

    public function periodo(int $generacionId, int $cuatrimestreId): Periodo
    {
        return Periodo::query()
            ->with(['cuatrimestre', 'mes', 'generacion'])
            ->where('generacion_id', $generacionId)
            ->where('cuatrimestre_id', $cuatrimestreId)
            ->firstOrFail();
    }

    /**
     * Dataset único para PDF individual, masivo y correo.
     */
    public function datasetBoleta(
        int $alumnoId,
        int $licenciaturaId,
        int $modalidadId,
        int $generacionId,
        int $cuatrimestreId,
        bool $soloActivo = true
    ): array {
        $inscripcion = $this->validarAlumnoEnContexto(
            $alumnoId,
            $licenciaturaId,
            $modalidadId,
            $generacionId,
            $soloActivo
        );

        $calificaciones = $this->calificacionesNormalizadas(
            $alumnoId,
            $licenciaturaId,
            $modalidadId,
            $generacionId,
            $cuatrimestreId
        );

        $esperadas = $this->asignacionesCanonicas($licenciaturaId, $modalidadId, $cuatrimestreId);
        $periodo = $this->periodo($generacionId, $cuatrimestreId);

        return [
            'cuatrimestre' => Cuatrimestre::findOrFail($cuatrimestreId),
            'calificaciones' => $calificaciones,
            'ciclo_escolar' => Dashboard::query()->latest('id')->first(),
            'escuela' => Escuela::query()->first(),
            'licenciatura' => Licenciatura::findOrFail($licenciaturaId),
            'modalidad' => Modalidad::findOrFail($modalidadId),
            'generacion' => Generacion::findOrFail($generacionId),
            'periodo' => $periodo,
            'inscripcion' => $inscripcion,
            'estado' => $this->estadoDesdeConteos($esperadas->count(), $calificaciones->count()),
            'materias_esperadas' => $esperadas->count(),
            'materias_capturadas' => $calificaciones->count(),
        ];
    }

    public function estadoBoleta(
        int $alumnoId,
        int $licenciaturaId,
        int $modalidadId,
        int $generacionId,
        int $cuatrimestreId
    ): array {
        $esperadas = $this->asignacionesCanonicas($licenciaturaId, $modalidadId, $cuatrimestreId)->count();
        $capturadas = $this->calificacionesNormalizadas(
            $alumnoId,
            $licenciaturaId,
            $modalidadId,
            $generacionId,
            $cuatrimestreId
        )->count();

        return $this->estadoDesdeConteos($esperadas, $capturadas);
    }

    public function analizarAlumnos(
        Collection $alumnos,
        int $licenciaturaId,
        int $modalidadId,
        int $generacionId,
        Collection $cuatrimestreIds
    ): array {
        $alumnoIds = $alumnos->pluck('id')->map(fn ($id) => (int) $id)->values();
        $cuatrimestreIds = $cuatrimestreIds->map(fn ($id) => (int) $id)->filter()->unique()->values();

        if ($alumnoIds->isEmpty() || $cuatrimestreIds->isEmpty()) {
            return [
                'alumnos' => [],
                'resumen' => [
                    'alumnos' => $alumnoIds->count(),
                    'completas' => 0,
                    'incompletas' => 0,
                    'sin_calificaciones' => 0,
                    'boletas_posibles' => 0,
                    'boletas_completas' => 0,
                    'boletas_incompletas' => 0,
                    'boletas_sin_calificaciones' => 0,
                ],
            ];
        }

        $asignaciones = AsignacionMateria::query()
            ->with('materia:id,clave,nombre,calificable')
            ->where('licenciatura_id', $licenciaturaId)
            ->where('modalidad_id', $modalidadId)
            ->whereIn('cuatrimestre_id', $cuatrimestreIds)
            ->whereHas('materia', fn ($q) => $q->where('calificable', 'true'))
            ->get();

        $esperadasPorCuatri = [];
        foreach ($cuatrimestreIds as $cuatriId) {
            $esperadasPorCuatri[$cuatriId] = $asignaciones
                ->where('cuatrimestre_id', $cuatriId)
                ->groupBy('materia_id')
                ->count();
        }

        $calificaciones = Calificacion::query()
            ->with('asignacionMateria:id,materia_id')
            ->whereIn('alumno_id', $alumnoIds)
            ->where('licenciatura_id', $licenciaturaId)
            ->where('modalidad_id', $modalidadId)
            ->where('generacion_id', $generacionId)
            ->whereIn('cuatrimestre_id', $cuatrimestreIds)
            ->orderBy('id')
            ->get();

        $capturadas = [];
        foreach ($calificaciones as $calificacion) {
            $materiaId = $calificacion->asignacionMateria?->materia_id;
            if (!$materiaId) {
                continue;
            }

            $capturadas[$calificacion->alumno_id][$calificacion->cuatrimestre_id][$materiaId] = $calificacion->id;
        }

        $detalle = [];
        $resumen = [
            'alumnos' => $alumnos->count(),
            'completas' => 0,
            'incompletas' => 0,
            'sin_calificaciones' => 0,
            'boletas_posibles' => $alumnos->count() * $cuatrimestreIds->count(),
            'boletas_completas' => 0,
            'boletas_incompletas' => 0,
            'boletas_sin_calificaciones' => 0,
        ];

        foreach ($alumnos as $alumno) {
            $periodos = [];
            $completasAlumno = 0;
            $incompletasAlumno = 0;
            $sinAlumno = 0;

            foreach ($cuatrimestreIds as $cuatriId) {
                $esperadas = (int) ($esperadasPorCuatri[$cuatriId] ?? 0);
                $capt = count($capturadas[$alumno->id][$cuatriId] ?? []);
                $estado = $this->estadoDesdeConteos($esperadas, $capt);

                $periodos[$cuatriId] = $estado;

                if ($estado['codigo'] === 'completa') {
                    $completasAlumno++;
                    $resumen['boletas_completas']++;
                } elseif ($estado['codigo'] === 'incompleta') {
                    $incompletasAlumno++;
                    $resumen['boletas_incompletas']++;
                } else {
                    $sinAlumno++;
                    $resumen['boletas_sin_calificaciones']++;
                }
            }

            if ($completasAlumno === $cuatrimestreIds->count()) {
                $estadoGlobal = 'completa';
                $resumen['completas']++;
            } elseif ($completasAlumno === 0 && $incompletasAlumno === 0) {
                $estadoGlobal = 'sin_calificaciones';
                $resumen['sin_calificaciones']++;
            } else {
                $estadoGlobal = 'incompleta';
                $resumen['incompletas']++;
            }

            $detalle[$alumno->id] = [
                'estado' => $estadoGlobal,
                'completas' => $completasAlumno,
                'incompletas' => $incompletasAlumno,
                'sin_calificaciones' => $sinAlumno,
                'total_periodos' => $cuatrimestreIds->count(),
                'periodos' => $periodos,
            ];
        }

        return ['alumnos' => $detalle, 'resumen' => $resumen];
    }

    public function estadoDesdeConteos(int $esperadas, int $capturadas): array
    {
        if ($capturadas <= 0 || $esperadas <= 0) {
            return [
                'codigo' => 'sin_calificaciones',
                'label' => $esperadas <= 0 ? 'Sin materias configuradas' : 'Sin calificaciones',
                'esperadas' => $esperadas,
                'capturadas' => $capturadas,
                'faltantes' => max(0, $esperadas - $capturadas),
            ];
        }

        if ($capturadas >= $esperadas) {
            return [
                'codigo' => 'completa',
                'label' => 'Completa',
                'esperadas' => $esperadas,
                'capturadas' => $capturadas,
                'faltantes' => 0,
            ];
        }

        return [
            'codigo' => 'incompleta',
            'label' => 'Incompleta',
            'esperadas' => $esperadas,
            'capturadas' => $capturadas,
            'faltantes' => max(0, $esperadas - $capturadas),
        ];
    }

    public function nombreArchivo(array $dataset): string
    {
        $alumno = $dataset['inscripcion'];
        $cuatrimestre = $dataset['cuatrimestre'];

        $base = sprintf(
            'BOLETA_%s_CUATRIMESTRE_%s_%s_%s_%s',
            $cuatrimestre->cuatrimestre,
            $alumno->apellido_paterno,
            $alumno->apellido_materno,
            $alumno->nombre,
            $alumno->matricula
        );

        return Str::of($base)
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9_\-]+/', '_')
            ->replaceMatches('/_+/', '_')
            ->trim('_')
            ->append('.pdf')
            ->toString();
    }
}
