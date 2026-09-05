<?php

namespace App\Services;

use App\Models\CalificacionDocenteCaptura;
use App\Models\Inscripcion;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CalificacionesDocenteService
{
    public function esProfesorRestringido(User $user): bool
    {
        return $user->hasRole('Profesor') && !$user->hasAnyRole(['SuperAdmin', 'Admin']);
    }

    public function profesorId(User $user): ?int
    {
        return $user->profesor?->id ? (int) $user->profesor->id : null;
    }

    /**
     * Fuente de verdad: horarios -> asignacion_materias -> profesor/materia.
     * Una materia solamente aparece aquí si realmente existe en horarios y es calificable.
     */
    public function consultaAsignaciones(User $user, array $filtros = []): Builder
    {
        $query = DB::table('horarios as h')
            ->join('asignacion_materias as am', 'am.id', '=', 'h.asignacion_materia_id')
            ->join('profesores as p', 'p.id', '=', 'am.profesor_id')
            ->join('materias as m', 'm.id', '=', 'am.materia_id')
            ->join('licenciaturas as l', 'l.id', '=', 'h.licenciatura_id')
            ->join('modalidades as mo', 'mo.id', '=', 'h.modalidad_id')
            ->join('cuatrimestres as c', 'c.id', '=', 'h.cuatrimestre_id')
            ->join('generaciones as g', 'g.id', '=', 'h.generacion_id')
            ->whereNotNull('h.asignacion_materia_id')
            ->whereNotNull('h.generacion_id')
            ->whereNotNull('am.profesor_id')
            ->whereColumn('am.licenciatura_id', 'h.licenciatura_id')
            ->whereColumn('am.modalidad_id', 'h.modalidad_id')
            ->whereColumn('am.cuatrimestre_id', 'h.cuatrimestre_id')
            ->where('m.calificable', 'true')
            ->select([
                'am.id as asignacion_materia_id',
                'am.profesor_id',
                'm.id as materia_id',
                'm.nombre as materia',
                'm.clave as materia_clave',
                'l.id as licenciatura_id',
                'l.nombre as licenciatura',
                'l.nombre_corto as licenciatura_corta',
                'mo.id as modalidad_id',
                'mo.nombre as modalidad',
                'c.id as cuatrimestre_id',
                'c.cuatrimestre',
                'g.id as generacion_id',
                'g.generacion',
                'p.nombre as profesor_nombre',
                'p.apellido_paterno as profesor_apellido_paterno',
                'p.apellido_materno as profesor_apellido_materno',
            ])
            ->distinct();

        if ($this->esProfesorRestringido($user)) {
            $profesorId = $this->profesorId($user);
            if (!$profesorId) {
                return $query->whereRaw('1 = 0');
            }
            $query->where('am.profesor_id', $profesorId);
        } elseif (!empty($filtros['profesor_id'])) {
            $query->where('am.profesor_id', (int) $filtros['profesor_id']);
        }

        $map = [
            'licenciatura_id' => 'h.licenciatura_id',
            'modalidad_id' => 'h.modalidad_id',
            'generacion_id' => 'h.generacion_id',
            'cuatrimestre_id' => 'h.cuatrimestre_id',
        ];

        foreach ($map as $filtro => $columna) {
            if (!empty($filtros[$filtro])) {
                $query->where($columna, (int) $filtros[$filtro]);
            }
        }

        return $query;
    }

    public function asignaciones(User $user, array $filtros = []): Collection
    {
        $filas = $this->consultaAsignaciones($user, $filtros)
            ->orderBy('p.apellido_paterno')
            ->orderBy('p.apellido_materno')
            ->orderBy('p.nombre')
            ->orderBy('l.nombre')
            ->orderBy('c.cuatrimestre')
            ->orderBy('m.nombre')
            ->orderBy('g.generacion')
            ->get();

        return $this->anexarEstado($filas);
    }

    public function opciones(User $user): array
    {
        $filas = $this->consultaAsignaciones($user)->get();

        return [
            'profesores' => $filas->map(fn ($r) => [
                'id' => (int) $r->profesor_id,
                'nombre' => trim("{$r->profesor_apellido_paterno} {$r->profesor_apellido_materno} {$r->profesor_nombre}"),
            ])->unique('id')->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)->values(),
            'licenciaturas' => $filas->map(fn ($r) => ['id' => (int) $r->licenciatura_id, 'nombre' => $r->licenciatura])
                ->unique('id')->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)->values(),
            'modalidades' => $filas->map(fn ($r) => ['id' => (int) $r->modalidad_id, 'nombre' => $r->modalidad])
                ->unique('id')->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)->values(),
            'generaciones' => $filas->map(fn ($r) => ['id' => (int) $r->generacion_id, 'nombre' => $r->generacion])
                ->unique('id')->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)->values(),
            'cuatrimestres' => $filas->map(fn ($r) => ['id' => (int) $r->cuatrimestre_id, 'nombre' => $r->cuatrimestre])
                ->unique('id')->sortBy(fn ($r) => (int) $r['nombre'])->values(),
        ];
    }

    public function contextoAsignacion(User $user, int $asignacionId, int $generacionId): ?object
    {
        return $this->consultaAsignaciones($user, ['generacion_id' => $generacionId])
            ->where('am.id', $asignacionId)
            ->first();
    }

    public function alumnosParaContexto(object $contexto, string $buscar = ''): Collection
    {
        return Inscripcion::query()
            ->where('licenciatura_id', (int) $contexto->licenciatura_id)
            ->where('modalidad_id', (int) $contexto->modalidad_id)
            ->where('generacion_id', (int) $contexto->generacion_id)
            ->where('status', 'true')
            ->when(trim($buscar) !== '', function ($query) use ($buscar) {
                $s = trim($buscar);
                $query->where(function ($q) use ($s) {
                    $q->where('matricula', 'like', "%{$s}%")
                        ->orWhere('matricula_interna', 'like', "%{$s}%")
                        ->orWhere('nombre', 'like', "%{$s}%")
                        ->orWhere('apellido_paterno', 'like', "%{$s}%")
                        ->orWhere('apellido_materno', 'like', "%{$s}%");
                });
            })
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get();
    }

    public function resumen(Collection $asignaciones): array
    {
        return [
            'docentes' => $asignaciones->pluck('profesor_id')->unique()->count(),
            'materias' => $asignaciones->count(),
            'esperadas' => (int) $asignaciones->sum('total_alumnos'),
            'capturadas' => (int) $asignaciones->sum('capturadas'),
            'pendientes' => max(0, (int) $asignaciones->sum('total_alumnos') - (int) $asignaciones->sum('capturadas')),
        ];
    }

    public function nombreProfesor(object $contexto): string
    {
        return trim(collect([
            $contexto->profesor_nombre ?? null,
            $contexto->profesor_apellido_paterno ?? null,
            $contexto->profesor_apellido_materno ?? null,
        ])->filter()->implode(' '));
    }

    private function anexarEstado(Collection $filas): Collection
    {
        if ($filas->isEmpty()) {
            return $filas;
        }

        $contextosAlumnos = DB::table('inscripciones')
            ->select(['licenciatura_id', 'modalidad_id', 'generacion_id'])
            ->selectRaw('COUNT(*) as total')
            ->where('status', 'true')
            ->whereIn('generacion_id', $filas->pluck('generacion_id')->unique()->all())
            ->groupBy('licenciatura_id', 'modalidad_id', 'generacion_id')
            ->get()
            ->keyBy(fn ($r) => $this->claveAlumnos($r->licenciatura_id, $r->modalidad_id, $r->generacion_id));

        $capturadas = DB::table('calificaciones as cal')
            ->join('inscripciones as i', 'i.id', '=', 'cal.alumno_id')
            ->select([
                'cal.alumno_id', 'cal.asignacion_materia_id', 'cal.licenciatura_id', 'cal.modalidad_id', 'cal.generacion_id',
                'cal.cuatrimestre_id', 'cal.calificacion',
            ])
            ->where('i.status', 'true')
            ->whereColumn('i.licenciatura_id', 'cal.licenciatura_id')
            ->whereColumn('i.modalidad_id', 'cal.modalidad_id')
            ->whereColumn('i.generacion_id', 'cal.generacion_id')
            ->whereIn('cal.asignacion_materia_id', $filas->pluck('asignacion_materia_id')->unique()->all())
            ->whereIn('cal.generacion_id', $filas->pluck('generacion_id')->unique()->all())
            ->get()
            ->filter(fn ($r) => $this->valorCalificacionValido($r->calificacion))
            ->groupBy(fn ($r) => $this->claveCaptura(
                $r->asignacion_materia_id,
                $r->licenciatura_id,
                $r->modalidad_id,
                $r->generacion_id,
                $r->cuatrimestre_id
            ))
            ->map(fn (Collection $items) => (object) [
                'total' => $items->pluck('alumno_id')->unique()->count(),
            ]);

        $registros = CalificacionDocenteCaptura::query()
            ->whereIn('asignacion_materia_id', $filas->pluck('asignacion_materia_id')->unique()->all())
            ->whereIn('generacion_id', $filas->pluck('generacion_id')->unique()->all())
            ->get()
            ->keyBy(fn ($r) => $this->claveCaptura(
                $r->asignacion_materia_id,
                $r->licenciatura_id,
                $r->modalidad_id,
                $r->generacion_id,
                $r->cuatrimestre_id
            ));

        return $filas->map(function ($fila) use ($contextosAlumnos, $capturadas, $registros) {
            $keyAlumnos = $this->claveAlumnos($fila->licenciatura_id, $fila->modalidad_id, $fila->generacion_id);
            $keyCaptura = $this->claveCaptura(
                $fila->asignacion_materia_id,
                $fila->licenciatura_id,
                $fila->modalidad_id,
                $fila->generacion_id,
                $fila->cuatrimestre_id
            );

            $total = (int) ($contextosAlumnos[$keyAlumnos]->total ?? 0);
            $capturado = min($total, (int) ($capturadas[$keyCaptura]->total ?? 0));
            $registro = $registros[$keyCaptura] ?? null;

            $estadoCalculado = match (true) {
                $registro?->estado === 'validada' => 'validada',
                $registro?->estado === 'entregada' => 'entregada',
                $total > 0 && $capturado >= $total => 'completa',
                $capturado > 0 => 'parcial',
                default => 'sin_captura',
            };

            $fila->total_alumnos = $total;
            $fila->capturadas = $capturado;
            $fila->estado = $estadoCalculado;
            $fila->fecha_limite = $registro?->fecha_limite?->format('Y-m-d');
            $fila->entregado_at = $registro?->entregado_at;
            $fila->validado_at = $registro?->validado_at;
            $fila->bloqueada_docente = in_array($estadoCalculado, ['entregada', 'validada'], true)
                || ($registro?->fecha_limite && now()->startOfDay()->gt($registro->fecha_limite->startOfDay()));

            return $fila;
        });
    }

    private function valorCalificacionValido(mixed $valor): bool
    {
        if ($valor === null) {
            return false;
        }

        $v = strtoupper(trim((string) $valor));
        if ($v === 'NP') {
            return true;
        }

        $v = str_replace(',', '.', $v);
        return is_numeric($v) && (float) $v >= 5 && (float) $v <= 10;
    }

    private function claveAlumnos($licenciaturaId, $modalidadId, $generacionId): string
    {
        return implode(':', [(int) $licenciaturaId, (int) $modalidadId, (int) $generacionId]);
    }

    private function claveCaptura($asignacionId, $licenciaturaId, $modalidadId, $generacionId, $cuatrimestreId): string
    {
        return implode(':', [
            (int) $asignacionId,
            (int) $licenciaturaId,
            (int) $modalidadId,
            (int) $generacionId,
            (int) $cuatrimestreId,
        ]);
    }
}
