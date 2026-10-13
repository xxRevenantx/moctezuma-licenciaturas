<?php

namespace App\Services;

use App\Models\Calificacion;
use App\Models\CalificacionAuditoria;
use App\Models\Inscripcion;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Correcciones administrativas de notas YA registradas, independientemente del
 * cuatrimestre actual del alumno y de que el horario histórico siga vigente.
 * No mueve la nota a otro periodo ni crea expedientes de calificaciones paralelos.
 */
class CalificacionesHistoricasService
{
    public function buscarAlumnos(User $usuario, string $buscar): Collection
    {
        $this->autorizar($usuario);
        $buscar = trim($buscar);

        if (mb_strlen($buscar) < 2) {
            return collect();
        }

        return Inscripcion::query()
            ->where(function ($q) use ($buscar) {
                $like = '%'.$buscar.'%';
                $q->where('matricula', 'like', $like)
                    ->orWhere('matricula_interna', 'like', $like)
                    ->orWhere('CURP', 'like', $like)
                    ->orWhere('nombre', 'like', $like)
                    ->orWhere('apellido_paterno', 'like', $like)
                    ->orWhere('apellido_materno', 'like', $like);
            })
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->limit(25)
            ->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno', 'matricula', 'matricula_interna', 'status', 'cuatrimestre_id']);
    }

    public function alumno(User $usuario, int $alumnoId): Inscripcion
    {
        $this->autorizar($usuario);
        return Inscripcion::query()->findOrFail($alumnoId);
    }

    public function notas(User $usuario, int $alumnoId): Collection
    {
        $this->alumno($usuario, $alumnoId);

        return DB::table('calificaciones as cal')
            ->leftJoin('asignacion_materias as am', 'am.id', '=', 'cal.asignacion_materia_id')
            ->leftJoin('materias as m', 'm.id', '=', 'am.materia_id')
            ->leftJoin('cuatrimestres as c', 'c.id', '=', 'cal.cuatrimestre_id')
            ->leftJoin('generaciones as g', 'g.id', '=', 'cal.generacion_id')
            ->leftJoin('licenciaturas as l', 'l.id', '=', 'cal.licenciatura_id')
            ->leftJoin('modalidades as mo', 'mo.id', '=', 'cal.modalidad_id')
            ->where('cal.alumno_id', $alumnoId)
            ->orderBy('cal.cuatrimestre_id')
            ->orderBy('m.nombre')
            ->orderBy('cal.id')
            ->get([
                'cal.id', 'cal.calificacion', 'cal.cuatrimestre_id', 'cal.generacion_id',
                'cal.licenciatura_id', 'cal.modalidad_id', 'cal.asignacion_materia_id',
                'c.cuatrimestre', 'g.generacion', 'l.nombre as licenciatura',
                'mo.nombre as modalidad', 'm.nombre as materia', 'm.clave as materia_clave',
            ]);
    }

    /** Retorna true si hubo un cambio; protege de ediciones simultáneas. */
    public function corregirNota(
        User $usuario,
        int $alumnoId,
        int $notaId,
        string $nuevo,
        string $motivo,
        ?string $valorVisto
    ): bool {
        $this->autorizar($usuario);
        $motivo = trim($motivo);

        if (mb_strlen($motivo) < 10 || mb_strlen($motivo) > 500) {
            throw ValidationException::withMessages([
                'motivoCorreccionHistorica' => 'Escribe un motivo de corrección de 10 a 500 caracteres.',
            ]);
        }

        $nuevo = strtoupper(trim(str_replace(',', '.', $nuevo)));
        if ($nuevo !== 'NP' && (!is_numeric($nuevo) || (float) $nuevo < 5 || (float) $nuevo > 10)) {
            throw ValidationException::withMessages([
                'notasHistoricas.'.$notaId => 'La nota debe ser un número entre 5 y 10, o NP.',
            ]);
        }

        return DB::transaction(function () use ($usuario, $alumnoId, $notaId, $nuevo, $motivo, $valorVisto) {
            // ID exacto de la nota existente, nunca el cuatrimestre actual del alumno.
            $nota = Calificacion::query()
                ->whereKey($notaId)
                ->where('alumno_id', $alumnoId)
                ->lockForUpdate()
                ->firstOrFail();

            if ((string) $nota->calificacion !== (string) $valorVisto) {
                throw ValidationException::withMessages([
                    'notasHistoricas.'.$notaId => 'Esta calificación cambió desde que abriste el historial. Recarga para revisar su valor.',
                ]);
            }

            if ((string) $nota->calificacion === $nuevo) {
                return false;
            }

            $anterior = $nota->calificacion;
            // Sólo actualizamos el valor: no se alteran materia, modalidad, docente,
            // licenciatura, generación ni cuatrimestre histórico.
            $nota->update(['calificacion' => $nuevo]);

            CalificacionAuditoria::create([
                'calificacion_id' => $nota->id,
                'alumno_id' => $nota->alumno_id,
                'asignacion_materia_id' => $nota->asignacion_materia_id,
                'profesor_id' => $nota->profesor_id,
                'licenciatura_id' => $nota->licenciatura_id,
                'modalidad_id' => $nota->modalidad_id,
                'generacion_id' => $nota->generacion_id,
                'cuatrimestre_id' => $nota->cuatrimestre_id,
                'user_id' => $usuario->getKey(),
                'valor_anterior' => $anterior,
                'valor_nuevo' => $nuevo,
                'origen' => 'historico',
                'accion' => 'actualizada',
                'detalle' => [
                    'motivo' => $motivo,
                    'tipo' => 'correccion_administrativa',
                    'cuatrimestre_id' => $nota->cuatrimestre_id,
                    'generacion_id' => $nota->generacion_id,
                    'modalidad_id' => $nota->modalidad_id,
                    'licenciatura_id' => $nota->licenciatura_id,
                ],
            ]);

            return true;
        }, 3);
    }

    private function autorizar(User $usuario): void
    {
        abort_unless(
            $usuario->hasAnyRole(['SuperAdmin', 'Admin'])
                && $usuario->can('calificaciones-docente.ver'),
            403,
            'Sólo Control Escolar puede corregir el historial completo.'
        );
    }
}
