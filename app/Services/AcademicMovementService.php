<?php

namespace App\Services;

use App\Models\AsignacionMateria;
use App\Models\AsignarGeneracion;
use App\Models\Calificacion;
use App\Models\CalificacionAuditoria;
use App\Models\Cuatrimestre;
use App\Models\Inscripcion;
use App\Models\Materia;
use App\Models\Modalidad;
use App\Models\MovimientoAcademico;
use App\Models\Periodo;
use DomainException;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AcademicMovementService
{
    public const TIPO_MODALIDAD = 'cambio_modalidad';
    public const TIPO_CUATRIMESTRE = 'cambio_cuatrimestre';

    /**
     * Analiza el cambio sin escribir datos. El resultado es serializable y puede
     * mostrarse directamente como revisión de impacto en Livewire.
     */
    public function analizarCambioModalidad(array $alumnoIds, int $modalidadDestinoId): array
    {
        $idsSolicitados = $this->normalizarIds($alumnoIds);
        $alumnos = $this->alumnosPorIds($idsSolicitados);
        $destino = Modalidad::query()->find($modalidadDestinoId);

        $resultado = $this->resultadoBase(self::TIPO_MODALIDAD, $alumnos);

        if ($alumnos->count() !== count($idsSolicitados)) {
            $resultado['bloqueos'][] = 'Uno o más alumnos seleccionados ya no existen o no están disponibles.';
        }

        if (! $destino) {
            $resultado['bloqueos'][] = 'La modalidad destino no existe.';
            return $this->finalizarAnalisis($resultado);
        }

        $resultado['modalidad_destino'] = [
            'id' => (int) $destino->id,
            'nombre' => (string) $destino->nombre,
        ];

        if ($alumnos->isEmpty()) {
            $resultado['bloqueos'][] = 'No hay alumnos válidos seleccionados.';
            return $this->finalizarAnalisis($resultado);
        }

        $cacheMaterias = [];
        $cacheGeneracion = [];
        $cacheHorarios = [];
        $totalCalificaciones = 0;

        foreach ($alumnos as $alumno) {
            $bloqueosAlumno = [];
            $advertenciasAlumno = [];

            if ($alumno->status !== 'true') {
                $bloqueosAlumno[] = 'El alumno no está activo.';
            }

            if ((int) $alumno->modalidad_id === (int) $modalidadDestinoId) {
                $bloqueosAlumno[] = 'El alumno ya pertenece a la modalidad destino.';
            }

            $otrasModalidades = Calificacion::query()
                ->where('alumno_id', $alumno->id)
                ->where('licenciatura_id', $alumno->licenciatura_id)
                ->where('generacion_id', $alumno->generacion_id)
                ->where(function ($query) use ($alumno) {
                    $query->whereNull('modalidad_id')
                        ->orWhere('modalidad_id', '!=', $alumno->modalidad_id);
                })
                ->exists();

            if ($otrasModalidades) {
                $bloqueosAlumno[] = 'Existen calificaciones de esta licenciatura/generación en otra modalidad. Requiere revisión manual antes del traslado.';
            }

            $claveGeneracion = $alumno->licenciatura_id.'-'.$modalidadDestinoId.'-'.$alumno->generacion_id;
            if (! array_key_exists($claveGeneracion, $cacheGeneracion)) {
                $cacheGeneracion[$claveGeneracion] = AsignarGeneracion::query()
                    ->where('licenciatura_id', $alumno->licenciatura_id)
                    ->where('modalidad_id', $modalidadDestinoId)
                    ->where('generacion_id', $alumno->generacion_id)
                    ->exists();
            }

            if (! $cacheGeneracion[$claveGeneracion]) {
                $advertenciasAlumno[] = 'La generación no está habilitada en la modalidad destino; se habilitará automáticamente al confirmar.';
                $resultado['creara_asignacion_generacion'] = true;
            }

            $claveMaterias = $alumno->licenciatura_id.'-'.$modalidadDestinoId.'-'.$alumno->cuatrimestre_id;
            if (! array_key_exists($claveMaterias, $cacheMaterias)) {
                $cacheMaterias[$claveMaterias] = $this->validarMateriasDestino(
                    (int) $alumno->licenciatura_id,
                    (int) $modalidadDestinoId,
                    (int) $alumno->cuatrimestre_id
                );
            }

            foreach ($cacheMaterias[$claveMaterias]['bloqueos'] as $bloqueo) {
                $bloqueosAlumno[] = $bloqueo;
            }

            foreach ($cacheMaterias[$claveMaterias]['advertencias'] as $advertencia) {
                $advertenciasAlumno[] = $advertencia;
            }

            $resultado['materias_faltantes'] = array_values(array_unique(array_merge(
                $resultado['materias_faltantes'],
                $cacheMaterias[$claveMaterias]['materias_faltantes']
            )));

            $claveHorario = $alumno->licenciatura_id.'-'.$modalidadDestinoId.'-'.$alumno->generacion_id.'-'.$alumno->cuatrimestre_id;
            if (! array_key_exists($claveHorario, $cacheHorarios)) {
                $cacheHorarios[$claveHorario] = ! Schema::hasTable('horarios') || DB::table('horarios')
                    ->where('licenciatura_id', $alumno->licenciatura_id)
                    ->where('modalidad_id', $modalidadDestinoId)
                    ->where('generacion_id', $alumno->generacion_id)
                    ->where('cuatrimestre_id', $alumno->cuatrimestre_id)
                    ->whereNotNull('asignacion_materia_id')
                    ->exists();
            }

            if (! $cacheHorarios[$claveHorario]) {
                $advertenciasAlumno[] = 'No hay horarios configurados en el contexto destino para el cuatrimestre actual. El traslado puede conservar el historial, pero Calificaciones por docente requerirá configurar horarios.';
            }

            $mapeo = $this->mapearCalificaciones($alumno, $modalidadDestinoId, true);
            $totalCalificaciones += $mapeo['total'];

            if ($mapeo['total'] > 0 && ! Schema::hasTable('calificacion_auditorias')) {
                $bloqueosAlumno[] = 'No existe la tabla de auditoría de calificaciones. Ejecuta las migraciones pendientes antes de trasladar calificaciones.';
            }

            foreach ($mapeo['bloqueos'] as $bloqueo) {
                $bloqueosAlumno[] = $bloqueo;
            }

            foreach ($mapeo['advertencias'] as $advertencia) {
                $advertenciasAlumno[] = $advertencia;
            }

            $resultado['alumnos'][] = [
                'id' => (int) $alumno->id,
                'matricula' => (string) $alumno->matricula,
                'nombre' => $this->nombreAlumno($alumno),
                'calificaciones' => (int) $mapeo['total'],
                'bloqueos' => array_values(array_unique($bloqueosAlumno)),
                'advertencias' => array_values(array_unique($advertenciasAlumno)),
            ];

            foreach ($bloqueosAlumno as $bloqueo) {
                $resultado['bloqueos'][] = $this->nombreAlumno($alumno).': '.$bloqueo;
            }

            foreach ($advertenciasAlumno as $advertencia) {
                $resultado['advertencias'][] = $this->nombreAlumno($alumno).': '.$advertencia;
            }
        }

        $resultado['calificaciones_total'] = $totalCalificaciones;
        $resultado['calificaciones_trasladables'] = $totalCalificaciones;

        return $this->finalizarAnalisis($resultado);
    }

    public function analizarCambioCuatrimestre(array $alumnoIds, int $cuatrimestreDestinoId): array
    {
        $idsSolicitados = $this->normalizarIds($alumnoIds);
        $alumnos = $this->alumnosPorIds($idsSolicitados);
        $destino = Cuatrimestre::query()->find($cuatrimestreDestinoId);
        $resultado = $this->resultadoBase(self::TIPO_CUATRIMESTRE, $alumnos);

        if ($alumnos->count() !== count($idsSolicitados)) {
            $resultado['bloqueos'][] = 'Uno o más alumnos seleccionados ya no existen o no están disponibles.';
        }

        if (! $destino) {
            $resultado['bloqueos'][] = 'El cuatrimestre destino no existe.';
            return $this->finalizarAnalisis($resultado);
        }

        $resultado['cuatrimestre_destino'] = [
            'id' => (int) $destino->id,
            'nombre' => (string) $destino->nombre_cuatrimestre,
        ];

        if ($alumnos->isEmpty()) {
            $resultado['bloqueos'][] = 'No hay alumnos válidos seleccionados.';
            return $this->finalizarAnalisis($resultado);
        }

        $cacheMaterias = [];
        $cachePeriodos = [];
        $cacheHorarios = [];

        foreach ($alumnos as $alumno) {
            $bloqueosAlumno = [];
            $advertenciasAlumno = [];

            if ($alumno->status !== 'true') {
                $bloqueosAlumno[] = 'El alumno no está activo.';
            }

            if ((int) $alumno->cuatrimestre_id === (int) $cuatrimestreDestinoId) {
                $bloqueosAlumno[] = 'El alumno ya pertenece al cuatrimestre destino.';
            }

            $clavePeriodo = $alumno->generacion_id.'-'.$cuatrimestreDestinoId;
            if (! array_key_exists($clavePeriodo, $cachePeriodos)) {
                $cachePeriodos[$clavePeriodo] = Periodo::query()
                    ->where('generacion_id', $alumno->generacion_id)
                    ->where('cuatrimestre_id', $cuatrimestreDestinoId)
                    ->exists();
            }

            if (! $cachePeriodos[$clavePeriodo]) {
                $bloqueosAlumno[] = 'La generación no tiene un periodo configurado para el cuatrimestre destino.';
            }

            $claveMaterias = $alumno->licenciatura_id.'-'.$alumno->modalidad_id.'-'.$cuatrimestreDestinoId;
            if (! array_key_exists($claveMaterias, $cacheMaterias)) {
                $cacheMaterias[$claveMaterias] = $this->validarMateriasDestino(
                    (int) $alumno->licenciatura_id,
                    (int) $alumno->modalidad_id,
                    (int) $cuatrimestreDestinoId
                );
            }

            foreach ($cacheMaterias[$claveMaterias]['bloqueos'] as $bloqueo) {
                $bloqueosAlumno[] = $bloqueo;
            }

            foreach ($cacheMaterias[$claveMaterias]['advertencias'] as $advertencia) {
                $advertenciasAlumno[] = $advertencia;
            }

            $resultado['materias_faltantes'] = array_values(array_unique(array_merge(
                $resultado['materias_faltantes'],
                $cacheMaterias[$claveMaterias]['materias_faltantes']
            )));

            $claveHorario = $alumno->licenciatura_id.'-'.$alumno->modalidad_id.'-'.$alumno->generacion_id.'-'.$cuatrimestreDestinoId;
            if (! array_key_exists($claveHorario, $cacheHorarios)) {
                $cacheHorarios[$claveHorario] = ! Schema::hasTable('horarios') || DB::table('horarios')
                    ->where('licenciatura_id', $alumno->licenciatura_id)
                    ->where('modalidad_id', $alumno->modalidad_id)
                    ->where('generacion_id', $alumno->generacion_id)
                    ->where('cuatrimestre_id', $cuatrimestreDestinoId)
                    ->whereNotNull('asignacion_materia_id')
                    ->exists();
            }

            if (! $cacheHorarios[$claveHorario]) {
                $advertenciasAlumno[] = 'No hay horarios configurados para el cuatrimestre destino. Calificaciones por docente requerirá configurarlos.';
            }

            $advertenciasAlumno[] = 'Las calificaciones históricas permanecen en sus cuatrimestres originales; solo cambia el cuatrimestre actual de la inscripción.';

            $resultado['alumnos'][] = [
                'id' => (int) $alumno->id,
                'matricula' => (string) $alumno->matricula,
                'nombre' => $this->nombreAlumno($alumno),
                'calificaciones' => 0,
                'bloqueos' => array_values(array_unique($bloqueosAlumno)),
                'advertencias' => array_values(array_unique($advertenciasAlumno)),
            ];

            foreach ($bloqueosAlumno as $bloqueo) {
                $resultado['bloqueos'][] = $this->nombreAlumno($alumno).': '.$bloqueo;
            }

            foreach ($advertenciasAlumno as $advertencia) {
                $resultado['advertencias'][] = $this->nombreAlumno($alumno).': '.$advertencia;
            }
        }

        return $this->finalizarAnalisis($resultado);
    }

    public function cambiarModalidad(
        array $alumnoIds,
        int $modalidadDestinoId,
        string $motivo,
        ?string $observaciones = null
    ): array {
        return DB::transaction(function () use ($alumnoIds, $modalidadDestinoId, $motivo, $observaciones) {
            $ids = $this->normalizarIds($alumnoIds);
            $alumnos = Inscripcion::query()
                ->whereIn('id', $ids)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $analisis = $this->analizarCambioModalidad($ids, $modalidadDestinoId);
            $this->asegurarAnalisisValido($analisis);

            $lote = $this->generarCodigoLote('CM');
            $movimientos = [];

            foreach ($alumnos as $alumno) {
                $modalidadOrigenId = (int) $alumno->modalidad_id;
                $cuatrimestreActualId = (int) $alumno->cuatrimestre_id;

                $asignarGeneracion = AsignarGeneracion::query()
                    ->where('licenciatura_id', $alumno->licenciatura_id)
                    ->where('modalidad_id', $modalidadDestinoId)
                    ->where('generacion_id', $alumno->generacion_id)
                    ->first();

                $generacionCreada = false;
                if (! $asignarGeneracion) {
                    $asignarGeneracion = AsignarGeneracion::query()->create([
                        'licenciatura_id' => $alumno->licenciatura_id,
                        'modalidad_id' => $modalidadDestinoId,
                        'generacion_id' => $alumno->generacion_id,
                    ]);
                    $generacionCreada = true;
                }

                $mapeo = $this->mapearCalificaciones($alumno, $modalidadDestinoId, false);
                if ($mapeo['bloqueos'] !== []) {
                    throw new DomainException($this->nombreAlumno($alumno).': '.$mapeo['bloqueos'][0]);
                }

                $detallesCalificaciones = [];
                foreach ($mapeo['mapeos'] as $item) {
                    /** @var Calificacion $calificacion */
                    $calificacion = $item['calificacion'];
                    /** @var AsignacionMateria $destino */
                    $destino = $item['destino'];

                    $asignacionOrigenId = (int) $calificacion->asignacion_materia_id;
                    $modalidadCalificacionOrigen = (int) $calificacion->modalidad_id;

                    $calificacion->forceFill([
                        'asignacion_materia_id' => $destino->id,
                        'modalidad_id' => $modalidadDestinoId,
                    ])->save();

                    $detallesCalificaciones[] = [
                        'calificacion_id' => (int) $calificacion->id,
                        'asignacion_origen_id' => $asignacionOrigenId,
                        'asignacion_destino_id' => (int) $destino->id,
                        'modalidad_origen_id' => $modalidadCalificacionOrigen,
                        'modalidad_destino_id' => (int) $modalidadDestinoId,
                        'cuatrimestre_id' => (int) $calificacion->cuatrimestre_id,
                    ];

                    $this->auditarCalificacion(
                        $calificacion,
                        'cambio_modalidad',
                        'traslado_contexto',
                        [
                            'lote' => $lote,
                            'asignacion_origen_id' => $asignacionOrigenId,
                            'asignacion_destino_id' => (int) $destino->id,
                            'modalidad_origen_id' => $modalidadCalificacionOrigen,
                            'modalidad_destino_id' => (int) $modalidadDestinoId,
                        ]
                    );
                }

                $alumno->forceFill(['modalidad_id' => $modalidadDestinoId])->save();

                $movimiento = MovimientoAcademico::query()->create([
                    'lote' => $lote,
                    'tipo' => self::TIPO_MODALIDAD,
                    'inscripcion_id' => $alumno->id,
                    'matricula_snapshot' => $alumno->matricula,
                    'alumno_snapshot' => $this->nombreAlumno($alumno),
                    'licenciatura_id' => $alumno->licenciatura_id,
                    'generacion_id' => $alumno->generacion_id,
                    'modalidad_origen_id' => $modalidadOrigenId,
                    'modalidad_destino_id' => $modalidadDestinoId,
                    'cuatrimestre_origen_id' => $cuatrimestreActualId,
                    'cuatrimestre_destino_id' => $cuatrimestreActualId,
                    'motivo' => $motivo,
                    'observaciones' => $observaciones,
                    'calificaciones_trasladadas' => count($detallesCalificaciones),
                    'estado' => 'aplicado',
                    'ejecutado_por' => auth()->id(),
                    'detalles' => [
                        'generacion_destino_creada' => $generacionCreada,
                        'asignar_generacion_id' => $asignarGeneracion?->id,
                        'calificaciones' => $detallesCalificaciones,
                    ],
                ]);

                $movimientos[] = $movimiento->id;
            }

            return [
                'lote' => $lote,
                'total_alumnos' => count($movimientos),
                'movimientos_ids' => $movimientos,
                'calificaciones_trasladadas' => (int) collect($analisis['alumnos'])->sum('calificaciones'),
            ];
        }, 3);
    }

    public function cambiarCuatrimestre(
        array $alumnoIds,
        int $cuatrimestreDestinoId,
        string $motivo,
        ?string $observaciones = null
    ): array {
        return DB::transaction(function () use ($alumnoIds, $cuatrimestreDestinoId, $motivo, $observaciones) {
            $ids = $this->normalizarIds($alumnoIds);
            $alumnos = Inscripcion::query()
                ->whereIn('id', $ids)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $analisis = $this->analizarCambioCuatrimestre($ids, $cuatrimestreDestinoId);
            $this->asegurarAnalisisValido($analisis);

            $lote = $this->generarCodigoLote('CC');
            $movimientos = [];

            foreach ($alumnos as $alumno) {
                $cuatrimestreOrigenId = (int) $alumno->cuatrimestre_id;

                $preexistentes = Calificacion::query()
                    ->where('alumno_id', $alumno->id)
                    ->where('licenciatura_id', $alumno->licenciatura_id)
                    ->where('generacion_id', $alumno->generacion_id)
                    ->where('modalidad_id', $alumno->modalidad_id)
                    ->where('cuatrimestre_id', $cuatrimestreDestinoId)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all();

                $alumno->forceFill(['cuatrimestre_id' => $cuatrimestreDestinoId])->save();

                $movimiento = MovimientoAcademico::query()->create([
                    'lote' => $lote,
                    'tipo' => self::TIPO_CUATRIMESTRE,
                    'inscripcion_id' => $alumno->id,
                    'matricula_snapshot' => $alumno->matricula,
                    'alumno_snapshot' => $this->nombreAlumno($alumno),
                    'licenciatura_id' => $alumno->licenciatura_id,
                    'generacion_id' => $alumno->generacion_id,
                    'modalidad_origen_id' => $alumno->modalidad_id,
                    'modalidad_destino_id' => $alumno->modalidad_id,
                    'cuatrimestre_origen_id' => $cuatrimestreOrigenId,
                    'cuatrimestre_destino_id' => $cuatrimestreDestinoId,
                    'motivo' => $motivo,
                    'observaciones' => $observaciones,
                    'calificaciones_trasladadas' => 0,
                    'estado' => 'aplicado',
                    'ejecutado_por' => auth()->id(),
                    'detalles' => [
                        'calificaciones_destino_preexistentes_ids' => $preexistentes,
                    ],
                ]);

                $movimientos[] = $movimiento->id;
            }

            return [
                'lote' => $lote,
                'total_alumnos' => count($movimientos),
                'movimientos_ids' => $movimientos,
                'calificaciones_trasladadas' => 0,
            ];
        }, 3);
    }

    public function revertir(int $movimientoId): MovimientoAcademico
    {
        return DB::transaction(function () use ($movimientoId) {
            /** @var MovimientoAcademico $movimiento */
            $movimiento = MovimientoAcademico::query()->lockForUpdate()->findOrFail($movimientoId);

            if ($movimiento->estado !== 'aplicado') {
                throw new DomainException('Este movimiento ya no está en estado aplicado y no puede revertirse de nuevo.');
            }

            /** @var Inscripcion|null $alumno */
            $alumno = Inscripcion::query()->lockForUpdate()->find($movimiento->inscripcion_id);
            if (! $alumno) {
                throw new DomainException('La inscripción ya no existe. El historial se conserva, pero no es posible revertirla automáticamente.');
            }

            if ((int) $alumno->licenciatura_id !== (int) $movimiento->licenciatura_id
                || (int) $alumno->generacion_id !== (int) $movimiento->generacion_id) {
                throw new DomainException('El contexto académico del alumno cambió después del movimiento. Se requiere revisión manual.');
            }

            if ($movimiento->tipo === self::TIPO_MODALIDAD) {
                $this->revertirModalidad($movimiento, $alumno);
            } elseif ($movimiento->tipo === self::TIPO_CUATRIMESTRE) {
                $this->revertirCuatrimestre($movimiento, $alumno);
            } else {
                throw new DomainException('El tipo de movimiento no admite reversión automática.');
            }

            $movimiento->forceFill([
                'estado' => 'revertido',
                'revertido_por' => auth()->id(),
                'revertido_at' => now(),
            ])->save();

            return $movimiento->fresh();
        }, 3);
    }

    private function revertirModalidad(MovimientoAcademico $movimiento, Inscripcion $alumno): void
    {
        if ((int) $alumno->modalidad_id !== (int) $movimiento->modalidad_destino_id) {
            throw new DomainException('El alumno ya no está en la modalidad destino registrada por este movimiento.');
        }

        $detalles = (array) ($movimiento->detalles ?? []);
        $items = collect($detalles['calificaciones'] ?? []);
        $idsMovidos = $items->pluck('calificacion_id')->map(fn ($id) => (int) $id)->values();

        $calificacionesDestino = Calificacion::query()
            ->where('alumno_id', $alumno->id)
            ->where('licenciatura_id', $movimiento->licenciatura_id)
            ->where('generacion_id', $movimiento->generacion_id)
            ->where('modalidad_id', $movimiento->modalidad_destino_id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id);

        $nuevas = $calificacionesDestino->diff($idsMovidos);
        if ($nuevas->isNotEmpty()) {
            throw new DomainException('Después del traslado se registraron nuevas calificaciones en la modalidad destino. No es seguro revertir automáticamente.');
        }

        foreach ($items as $item) {
            $calificacion = Calificacion::query()->lockForUpdate()->find((int) ($item['calificacion_id'] ?? 0));
            if (! $calificacion) {
                throw new DomainException('Una calificación trasladada ya no existe. Se requiere revisión manual.');
            }

            if ((int) $calificacion->modalidad_id !== (int) ($item['modalidad_destino_id'] ?? 0)
                || (int) $calificacion->asignacion_materia_id !== (int) ($item['asignacion_destino_id'] ?? 0)) {
                throw new DomainException('Una calificación cambió de contexto después del traslado. No es seguro revertir automáticamente.');
            }

            $asignacionDestinoId = (int) $calificacion->asignacion_materia_id;
            $calificacion->forceFill([
                'asignacion_materia_id' => (int) $item['asignacion_origen_id'],
                'modalidad_id' => (int) $item['modalidad_origen_id'],
            ])->save();

            $this->auditarCalificacion(
                $calificacion,
                'reversion_modalidad',
                'reversion_contexto',
                [
                    'lote' => $movimiento->lote,
                    'movimiento_id' => $movimiento->id,
                    'asignacion_origen_reversion_id' => $asignacionDestinoId,
                    'asignacion_destino_reversion_id' => (int) $item['asignacion_origen_id'],
                    'modalidad_origen_reversion_id' => (int) $movimiento->modalidad_destino_id,
                    'modalidad_destino_reversion_id' => (int) $movimiento->modalidad_origen_id,
                ]
            );
        }

        $alumno->forceFill(['modalidad_id' => $movimiento->modalidad_origen_id])->save();
    }

    private function revertirCuatrimestre(MovimientoAcademico $movimiento, Inscripcion $alumno): void
    {
        if ((int) $alumno->cuatrimestre_id !== (int) $movimiento->cuatrimestre_destino_id) {
            throw new DomainException('El alumno ya no está en el cuatrimestre destino registrado por este movimiento.');
        }

        $preexistentes = collect((array) data_get($movimiento->detalles, 'calificaciones_destino_preexistentes_ids', []))
            ->map(fn ($id) => (int) $id);

        $actuales = Calificacion::query()
            ->where('alumno_id', $alumno->id)
            ->where('licenciatura_id', $movimiento->licenciatura_id)
            ->where('generacion_id', $movimiento->generacion_id)
            ->where('modalidad_id', $movimiento->modalidad_destino_id)
            ->where('cuatrimestre_id', $movimiento->cuatrimestre_destino_id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id);

        if ($actuales->diff($preexistentes)->isNotEmpty()) {
            throw new DomainException('Después del cambio se capturaron calificaciones en el cuatrimestre destino. No es seguro revertir automáticamente.');
        }

        $alumno->forceFill(['cuatrimestre_id' => $movimiento->cuatrimestre_origen_id])->save();
    }

    private function validarMateriasDestino(int $licenciaturaId, int $modalidadId, int $cuatrimestreId): array
    {
        $materias = Materia::query()
            ->where('licenciatura_id', $licenciaturaId)
            ->where('cuatrimestre_id', $cuatrimestreId)
            ->where('calificable', 'true')
            ->orderBy('orden')
            ->get(['id', 'nombre', 'clave']);

        if ($materias->isEmpty()) {
            return [
                'bloqueos' => ['No existen materias calificables definidas para el cuatrimestre destino.'],
                'advertencias' => [],
                'materias_faltantes' => [],
            ];
        }

        $asignaciones = AsignacionMateria::query()
            ->where('licenciatura_id', $licenciaturaId)
            ->where('modalidad_id', $modalidadId)
            ->where('cuatrimestre_id', $cuatrimestreId)
            ->whereIn('materia_id', $materias->pluck('id'))
            ->get(['id', 'materia_id', 'profesor_id']);

        $bloqueos = [];
        $advertencias = [];
        $grupos = $asignaciones->groupBy('materia_id');
        $duplicadas = $grupos->filter(fn ($grupo) => $grupo->count() > 1);

        // El módulo Calificaciones ya trabaja con una asignación canónica por
        // materia (el ID menor). Replicamos esa misma regla para no bloquear
        // contextos históricos que contienen asignaciones duplicadas válidas.
        if ($duplicadas->isNotEmpty()) {
            $nombres = $materias->whereIn('id', $duplicadas->keys())->pluck('nombre')->implode(', ');
            $advertencias[] = 'Hay varias asignaciones para: '.$nombres.'. Se utilizará la asignación canónica (ID menor), igual que en el módulo Calificaciones.';
        }

        $idsAsignados = $asignaciones->pluck('materia_id')->map(fn ($id) => (int) $id)->unique();
        $faltantes = $materias->reject(fn ($materia) => $idsAsignados->contains((int) $materia->id));

        if ($faltantes->isNotEmpty()) {
            $bloqueos[] = 'Faltan asignaciones de materias en la modalidad destino para este cuatrimestre: '.$faltantes->pluck('nombre')->implode(', ').'.';
        }

        $sinProfesor = $materias->filter(function ($materia) use ($grupos) {
            $grupo = $grupos->get($materia->id, collect());
            if ($grupo->isEmpty()) {
                return false;
            }

            return $grupo->sortBy('id')->first()?->profesor_id === null;
        });

        if ($sinProfesor->isNotEmpty()) {
            $advertencias[] = 'La asignación canónica de estas materias todavía no tiene profesor: '.$sinProfesor->pluck('nombre')->implode(', ').'.';
        }

        return [
            'bloqueos' => $bloqueos,
            'advertencias' => $advertencias,
            'materias_faltantes' => $faltantes->pluck('nombre')->values()->all(),
        ];
    }

    /**
     * @return array{total:int,bloqueos:array,advertencias:array,mapeos:array}
     */
    private function mapearCalificaciones(Inscripcion $alumno, int $modalidadDestinoId, bool $soloAnalisis): array
    {
        $calificacionesQuery = Calificacion::query()
            ->with('asignacionMateria.materia:id,nombre,clave')
            ->where('alumno_id', $alumno->id)
            ->where('licenciatura_id', $alumno->licenciatura_id)
            ->where('generacion_id', $alumno->generacion_id)
            ->where('modalidad_id', $alumno->modalidad_id)
            ->orderBy('id');

        // En la ejecución bloqueamos las filas para impedir que una captura
        // concurrente cambie el contexto mientras se realiza el remapeo.
        if (! $soloAnalisis) {
            $calificacionesQuery->lockForUpdate();
        }

        $calificaciones = $calificacionesQuery->get();

        $bloqueos = [];
        $advertencias = [];
        $mapeos = [];

        if ($calificaciones->isEmpty()) {
            return ['total' => 0, 'bloqueos' => [], 'advertencias' => [], 'mapeos' => []];
        }

        $cuatrimestres = $calificaciones->pluck('cuatrimestre_id')->filter()->unique()->values();
        $materiaIds = $calificaciones
            ->map(fn ($calificacion) => $calificacion->asignacionMateria?->materia_id)
            ->filter()
            ->unique()
            ->values();

        $destinos = AsignacionMateria::query()
            ->with('materia:id,nombre,clave')
            ->where('licenciatura_id', $alumno->licenciatura_id)
            ->where('modalidad_id', $modalidadDestinoId)
            ->whereIn('cuatrimestre_id', $cuatrimestres)
            ->whereIn('materia_id', $materiaIds)
            ->get()
            ->groupBy(fn ($asignacion) => $asignacion->cuatrimestre_id.'-'.$asignacion->materia_id);

        $existentesDestino = Calificacion::query()
            ->where('alumno_id', $alumno->id)
            ->where('licenciatura_id', $alumno->licenciatura_id)
            ->where('generacion_id', $alumno->generacion_id)
            ->where('modalidad_id', $modalidadDestinoId)
            ->get()
            ->keyBy(fn ($calificacion) => $calificacion->cuatrimestre_id.'-'.$calificacion->asignacion_materia_id);

        foreach ($calificaciones as $calificacion) {
            $asignacionOrigen = $calificacion->asignacionMateria;
            $materia = $asignacionOrigen?->materia;

            if (! $asignacionOrigen || ! $materia) {
                $bloqueos[] = 'La calificación #'.$calificacion->id.' no tiene una asignación/materia válida.';
                continue;
            }

            $clave = $calificacion->cuatrimestre_id.'-'.$asignacionOrigen->materia_id;
            $candidatos = $destinos->get($clave, collect());

            if ($candidatos->isEmpty()) {
                $bloqueos[] = 'No existe equivalencia en destino para '.$materia->nombre.' ('.$this->nombreCuatrimestre((int) $calificacion->cuatrimestre_id).').';
                continue;
            }

            if ($candidatos->count() > 1) {
                $advertencias[] = 'Hay varias asignaciones destino para '.$materia->nombre.' ('.$this->nombreCuatrimestre((int) $calificacion->cuatrimestre_id).'); se usará la canónica (ID menor), igual que en Calificaciones.';
            }

            /** @var AsignacionMateria $destino */
            $destino = $candidatos->sortBy('id')->first();
            $claveExistente = $calificacion->cuatrimestre_id.'-'.$destino->id;
            $conflicto = $existentesDestino->get($claveExistente);

            if ($conflicto && (int) $conflicto->id !== (int) $calificacion->id) {
                $bloqueos[] = 'Ya existe una calificación en destino para '.$materia->nombre.'; no se sobrescribirá automáticamente.';
                continue;
            }

            if (! $soloAnalisis) {
                // Durante la ejecución el unique index es la última barrera ante una condición de carrera.
                $mapeos[] = ['calificacion' => $calificacion, 'destino' => $destino];
            }
        }

        return [
            'total' => $calificaciones->count(),
            'bloqueos' => array_values(array_unique($bloqueos)),
            'advertencias' => array_values(array_unique($advertencias)),
            'mapeos' => $mapeos,
        ];
    }

    private function auditarCalificacion(Calificacion $calificacion, string $origen, string $accion, array $detalle): void
    {
        if (! Schema::hasTable('calificacion_auditorias')) {
            return;
        }

        CalificacionAuditoria::query()->create([
            'calificacion_id' => $calificacion->id,
            'alumno_id' => $calificacion->alumno_id,
            'asignacion_materia_id' => $calificacion->asignacion_materia_id,
            'profesor_id' => $calificacion->profesor_id,
            'licenciatura_id' => $calificacion->licenciatura_id,
            'modalidad_id' => $calificacion->modalidad_id,
            'generacion_id' => $calificacion->generacion_id,
            'cuatrimestre_id' => $calificacion->cuatrimestre_id,
            'user_id' => auth()->id(),
            'valor_anterior' => $calificacion->calificacion,
            'valor_nuevo' => $calificacion->calificacion,
            'origen' => $origen,
            'accion' => $accion,
            'detalle' => $detalle,
        ]);
    }

    private function alumnosPorIds(array $ids): EloquentCollection
    {
        $ids = $this->normalizarIds($ids);

        return Inscripcion::query()
            ->with(['licenciatura:id,nombre', 'generacion:id,generacion', 'cuatrimestre:id,nombre_cuatrimestre', 'modalidad:id,nombre'])
            ->whereIn('id', $ids)
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get();
    }

    private function normalizarIds(array $ids): array
    {
        return collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function resultadoBase(string $tipo, EloquentCollection $alumnos): array
    {
        $primerAlumno = $alumnos->first();

        return [
            'tipo' => $tipo,
            'valido' => false,
            'total_alumnos' => $alumnos->count(),
            'calificaciones_total' => 0,
            'calificaciones_trasladables' => 0,
            'creara_asignacion_generacion' => false,
            'materias_faltantes' => [],
            'bloqueos' => [],
            'advertencias' => [],
            'alumnos' => [],
            'modalidad_origen' => $primerAlumno ? [
                'id' => (int) $primerAlumno->modalidad_id,
                'nombre' => (string) ($primerAlumno->modalidad?->nombre ?? ''),
            ] : null,
            'modalidad_destino' => null,
            'cuatrimestre_origen' => $primerAlumno ? [
                'id' => (int) $primerAlumno->cuatrimestre_id,
                'nombre' => (string) ($primerAlumno->cuatrimestre?->nombre_cuatrimestre ?? ''),
            ] : null,
            'cuatrimestre_destino' => null,
        ];
    }

    private function finalizarAnalisis(array $resultado): array
    {
        $resultado['bloqueos'] = array_values(array_unique($resultado['bloqueos']));
        $resultado['advertencias'] = array_values(array_unique($resultado['advertencias']));
        $resultado['materias_faltantes'] = array_values(array_unique($resultado['materias_faltantes']));
        $resultado['valido'] = $resultado['bloqueos'] === [] && $resultado['total_alumnos'] > 0;

        return $resultado;
    }

    private function asegurarAnalisisValido(array $analisis): void
    {
        if (! ($analisis['valido'] ?? false)) {
            $mensaje = $analisis['bloqueos'][0] ?? 'El movimiento académico no pasó la validación previa.';
            throw new DomainException($mensaje);
        }
    }

    private function nombreAlumno(Inscripcion $alumno): string
    {
        return trim(implode(' ', array_filter([
            $alumno->apellido_paterno,
            $alumno->apellido_materno,
            $alumno->nombre,
        ])));
    }

    private function nombreCuatrimestre(int $id): string
    {
        static $cache = [];

        if (! array_key_exists($id, $cache)) {
            $cache[$id] = Cuatrimestre::query()->whereKey($id)->value('nombre_cuatrimestre') ?: 'cuatrimestre '.$id;
        }

        return $cache[$id];
    }

    private function generarCodigoLote(string $prefijo): string
    {
        $fecha = now()->format('Ymd');
        $prefijoCompleto = $prefijo.'-'.$fecha.'-';

        $ultimo = MovimientoAcademico::query()
            ->where('lote', 'like', $prefijoCompleto.'%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->value('lote');

        $secuencia = 1;
        if (is_string($ultimo) && preg_match('/-(\d+)$/', $ultimo, $coincidencias)) {
            $secuencia = ((int) $coincidencias[1]) + 1;
        }

        return $prefijoCompleto.str_pad((string) $secuencia, 3, '0', STR_PAD_LEFT);
    }
}
