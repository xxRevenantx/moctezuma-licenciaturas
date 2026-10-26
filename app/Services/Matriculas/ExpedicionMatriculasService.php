<?php

namespace App\Services\Matriculas;

use App\Models\Generacion;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Fuente única de elegibilidad para la vista y los documentos.
 * Solo lectura: nunca asigna ni modifica matrículas oficiales SEG.
 */
class ExpedicionMatriculasService
{
    public const COLUMNAS = [
        'numero' => 'Nº',
        'nombre' => 'NOMBRE(S)',
        'apellido_paterno' => 'APELLIDO PATERNO',
        'apellido_materno' => 'APELLIDO MATERNO',
        'curp' => 'CURP',
        'modalidad' => 'MODALIDAD',
        'mes_ingreso' => 'MES DE INGRESO',
        'observaciones' => 'OBSERVACIONES',
        'matricula_seg' => 'MATRÍCULA SEG',
        'matricula_interna' => 'CONTROL INTERNO',
        'cuatrimestre' => 'CUATRIMESTRE',
        'estado' => 'ESTADO ESCOLAR',
        'fecha_inscripcion' => 'FECHA DE REGISTRO',
    ];

    public const COLUMNAS_PREDETERMINADAS = [
        'numero', 'nombre', 'apellido_paterno', 'apellido_materno',
        'curp', 'modalidad', 'mes_ingreso', 'observaciones',
    ];

    public const MESES = [
        'ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO',
        'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE',
    ];

    public function generaciones(): Collection
    {
        // Una generación puede ser histórica; no se limita a activa = true.
        return Generacion::query()
            ->whereHas('inscripcion', fn ($q) => $q->whereIn('licenciatura_id', function ($sub) {
                $sub->select('licenciatura_id')->from('asignar_generaciones')
                    ->whereColumn('asignar_generaciones.generacion_id', 'inscripciones.generacion_id');
            }))
            ->orderByDesc('generacion')->get(['id', 'generacion', 'activa']);
    }

    public function licenciaturasDisponibles(int $generacionId): Collection
    {
        return Licenciatura::query()
            ->whereHas('asignarGeneraciones', fn ($q) => $q->where('generacion_id', $generacionId))
            ->whereHas('inscripciones', fn ($q) => $q->where('generacion_id', $generacionId))
            ->withCount(['inscripciones as alumnos_count' => fn ($q) => $q->where('generacion_id', $generacionId)])
            ->orderBy('nombre')->get(['id', 'nombre', 'RVOE']);
    }

    /** @return Collection<int, Inscripcion> */
    public function alumnos(int $generacionId, array $licenciaturaIds, string $estado = 'activos', string $seg = 'pendientes', ?int $modalidadId = null): Collection
    {
        if (! in_array($estado, ['activos', 'bajas', 'todos'], true)
            || ! in_array($seg, ['pendientes', 'asignados', 'todos'], true)) {
            throw ValidationException::withMessages(['filtros' => 'El filtro de alumnos no es válido.']);
        }

        if ($generacionId <= 0 || $licenciaturaIds === []) {
            return collect();
        }

        $validos = $this->licenciaturasDisponibles($generacionId)->pluck('id')->map(fn ($id) => (int) $id)->all();
        $solicitados = array_values(array_unique(array_map('intval', $licenciaturaIds)));
        if (array_diff($solicitados, $validos) !== [] || in_array(0, $solicitados, true)) {
            throw ValidationException::withMessages(['licenciaturas' => 'Selecciona licenciaturas asignadas a la generación y con alumnos.']);
        }

        return Inscripcion::query()
            ->with(['licenciatura:id,nombre', 'generacion:id,generacion', 'modalidad:id,nombre', 'cuatrimestre:id,nombre_cuatrimestre'])
            ->where('generacion_id', $generacionId)
            ->whereIn('licenciatura_id', $solicitados)
            ->when($estado !== 'todos', fn ($q) => $q->where('status', $estado === 'activos' ? 'true' : 'false'))
            ->when($seg === 'pendientes', fn ($q) => $q->whereRaw("TRIM(COALESCE(matricula, '')) = ''"))
            ->when($seg === 'asignados', fn ($q) => $q->whereRaw("TRIM(COALESCE(matricula, '')) <> ''"))
            ->when($modalidadId, fn ($q) => $q->where('modalidad_id', $modalidadId))
            ->orderBy('licenciatura_id')->orderBy('apellido_paterno')->orderBy('apellido_materno')
            ->orderBy('nombre')->orderBy('id')->get();
    }

    public function mesSugerido(Inscripcion $alumno): string
    {
        return $alumno->created_at
            ? mb_strtoupper($alumno->created_at->copy()->locale('es')->translatedFormat('F'), 'UTF-8')
            : '';
    }

    public function datos(int $generacionId, array $licenciaturaIds, array $alumnoIds, array $columnas, string $estado, string $seg, ?int $modalidadId, array $meses, array $observaciones): array
    {
        $columnas = array_values(array_unique($columnas));
        if ($columnas === [] || array_diff($columnas, array_keys(self::COLUMNAS)) !== []) {
            throw ValidationException::withMessages(['columnas' => 'Debes seleccionar columnas válidas.']);
        }
        $alumnos = $this->alumnos($generacionId, $licenciaturaIds, $estado, $seg, $modalidadId);
        $ids = array_values(array_unique(array_map('intval', $alumnoIds)));
        if ($ids === [] || in_array(0, $ids, true)
            || count(array_diff($ids, $alumnos->pluck('id')->map(fn ($id) => (int) $id)->all())) > 0) {
            throw ValidationException::withMessages(['alumnos' => 'Selecciona alumnos vigentes dentro de los filtros indicados.']);
        }
        $orden = array_flip($ids);
        $alumnos = $alumnos->whereIn('id', $ids)
            ->sortBy(fn (Inscripcion $a) => $orden[(int) $a->id] ?? PHP_INT_MAX)->values();
        $generacion = Generacion::findOrFail($generacionId);
        $filas = $alumnos->groupBy('licenciatura_id')->map(function ($grupo) use ($meses, $observaciones) {
            $cont = 0;
            return [
                'licenciatura' => $grupo->first()->licenciatura->nombre,
                'filas' => $grupo->map(function (Inscripcion $alumno) use (&$cont, $meses, $observaciones) {
                    $mes = (string) ($meses[$alumno->id] ?? $this->mesSugerido($alumno));
                    $mes = mb_strtoupper(trim($mes), 'UTF-8');
                    if ($mes !== '' && ! in_array($mes, self::MESES, true)) {
                        throw ValidationException::withMessages(['meses' => 'Mes inválido para el alumno ' . $alumno->id]);
                    }
                    $obs = trim((string) ($observaciones[$alumno->id] ?? ''));
                    if (mb_strlen($obs) > 500) {
                        throw ValidationException::withMessages(['observaciones' => 'Observación demasiado larga para el alumno ' . $alumno->id]);
                    }
                    return [
                        'numero' => ++$cont,
                        'nombre' => $alumno->nombre ?? '',
                        'apellido_paterno' => $alumno->apellido_paterno ?? '',
                        'apellido_materno' => $alumno->apellido_materno ?? '',
                        'curp' => $alumno->CURP ?? '',
                        'modalidad' => mb_strtoupper($alumno->modalidad?->nombre ?? '', 'UTF-8'),
                        'mes_ingreso' => $mes,
                        'observaciones' => $obs,
                        'matricula_seg' => $alumno->matricula ?? '',
                        'matricula_interna' => $alumno->matricula_interna ?? '',
                        'cuatrimestre' => $alumno->cuatrimestre?->nombre_cuatrimestre ?? '',
                        'estado' => $alumno->status === 'true' ? 'ACTIVO' : 'BAJA',
                        'fecha_inscripcion' => $alumno->created_at?->format('d/m/Y') ?? '',
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        return ['generacion' => $generacion->generacion, 'columnas' => $columnas, 'grupos' => $filas];
    }
}
