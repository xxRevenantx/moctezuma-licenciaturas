<?php

namespace App\Services;

use App\Models\AsignacionMateria;
use App\Models\CalificacionDocenteCaptura;
use App\Models\Horario;
use App\Models\Generacion;
use App\Models\Profesor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReasignacionDocenteService
{
    public function preparar(array $ids, int $origen, int $destino): array
    {
        Gate::authorize('admin.administracion');
        if ($origen <= 0 || $destino <= 0 || $origen === $destino) {
            $this->fallar('Elige dos profesores diferentes.');
        }
        $profesores = Profesor::whereIn('id', [$origen, $destino])->get()->keyBy('id');
        if ($profesores->count() !== 2 || $profesores[$destino]->status !== 'true') {
            $this->fallar('El profesor de reemplazo debe existir y estar activo.');
        }
        $ids = collect($ids)->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();
        if (! $ids || in_array(0, $ids, true)) {
            $this->fallar('Selecciona al menos una asignación válida.');
        }
        $filas = AsignacionMateria::with(['materia', 'licenciatura', 'modalidad', 'cuatrimestre'])
            ->whereIn('id', $ids)->orderBy('id')->get();
        if ($filas->count() !== count($ids) || $filas->contains(fn ($a) => (int) $a->profesor_id !== $origen)) {
            $this->fallar('Cambió alguna asignación o ya no pertenece al profesor de origen. Vuelve a seleccionar.');
        }
        $objetivos = $filas->map(fn ($a) => ['materia_id' => (int) $a->materia_id, 'modalidad_id' => (int) $a->modalidad_id])->all();
        // Reutiliza la validación de integridad y duplicados del proyecto.
        $revision = app(AsignacionDocenteService::class)->preparar($objetivos, $destino);
        // null incluye todas las modalidades, también los choques entre ellas.
        $conflictos = app(HorarioTraslapeService::class)->conflictosParaCambioProfesor($destino, null, $ids);
        $horarios = Horario::whereIn('asignacion_materia_id', $ids)
            ->orWhereHas('asignacionMateria', fn ($q) => $q->where('profesor_id', $destino))
            ->orderBy('id')->get()->toArray();
        $capturas = CalificacionDocenteCaptura::whereIn('asignacion_materia_id', $ids)->orderBy('id')->get()->toArray();
        $generacionIds = collect($horarios)->whereIn('asignacion_materia_id', $ids)->pluck('generacion_id')
            ->merge(collect($capturas)->pluck('generacion_id'))
            ->merge(DB::table('calificaciones')->whereIn('asignacion_materia_id', $ids)->pluck('generacion_id'));
        foreach ($filas->unique(fn ($a) => $a->licenciatura_id.':'.$a->modalidad_id) as $a) {
            $generacionIds = $generacionIds->merge(DB::table('asignar_generaciones')
                ->where('licenciatura_id', $a->licenciatura_id)->where('modalidad_id', $a->modalidad_id)->pluck('generacion_id'));
        }
        $generaciones = Generacion::whereIn('id', $generacionIds->filter()->unique()->all())->orderBy('id')->pluck('generacion')->all();
        $detalle = $filas->map(fn ($a) => [
            'id' => (int) $a->id,
            'materia' => $a->materia?->nombre,
            'licenciatura' => $a->licenciatura?->nombre,
            'modalidad' => $a->modalidad?->nombre,
            'cuatrimestre' => $a->cuatrimestre?->cuatrimestre,
        ])->all();
        return [
            'ids' => $ids, 'origen' => $origen, 'destino' => $destino,
            'nombre_origen' => $this->nombre($profesores[$origen]),
            'nombre_destino' => $this->nombre($profesores[$destino]),
            'objetivos' => $objetivos, 'detalle' => $detalle,
            'resumen' => $filas->groupBy('licenciatura_id')->map(fn ($g) => [
                'licenciatura' => $g->first()->licenciatura?->nombre, 'total' => $g->count(),
            ])->values()->all(),
            'impacto' => $revision['impacto'], 'conflictos' => $conflictos, 'generaciones' => $generaciones,
            'huella' => hash('sha256', json_encode([$filas->toArray(), $horarios, $capturas, $revision['impacto'], $conflictos, $generaciones])),
        ];
    }

    public function ejecutar(array $revision, bool $aceptaVinculos, bool $aceptaTraslapes): int
    {
        Gate::authorize('admin.administracion');
        if (! Schema::hasTable('reasignacion_docente_auditorias')) {
            $this->fallar('Falta instalar la tabla de auditoría. Ejecuta la migración o el SQL incluido.');
        }
        return DB::transaction(function () use ($revision, $aceptaVinculos, $aceptaTraslapes) {
            // Orden estable; revalidación bajo bloqueo antes de escribir.
            Profesor::whereIn('id', [$revision['origen'], $revision['destino']])->orderBy('id')->lockForUpdate()->get();
            AsignacionMateria::whereIn('id', $revision['ids'])->orderBy('id')->lockForUpdate()->get();
            Horario::whereIn('asignacion_materia_id', $revision['ids'])->orderBy('id')->lockForUpdate()->get();
            $actual = $this->preparar($revision['ids'], $revision['origen'], $revision['destino']);
            if (! hash_equals($revision['huella'], $actual['huella'])) {
                $this->fallar('Los datos cambiaron desde la revisión. Cierra la confirmación y revisa nuevamente.');
            }
            if (! $aceptaVinculos || ($actual['conflictos'] && ! $aceptaTraslapes)) {
                $this->fallar('Confirma el alcance compartido y, si existen, los traslapes.');
            }
            // Conserva los IDs: no elimina materias, horarios ni calificaciones.
            $cantidad = AsignacionMateria::whereIn('id', $actual['ids'])
                ->where('profesor_id', $actual['origen'])
                ->update(['profesor_id' => $actual['destino'], 'updated_at' => now()]);
            if ($cantidad !== count($actual['ids'])) {
                $this->fallar('No fue posible actualizar toda la selección. No se guardó ningún cambio.');
            }
            // El responsable actual de captura cambia, la autoría histórica permanece.
            CalificacionDocenteCaptura::whereIn('asignacion_materia_id', $actual['ids'])
                ->update(['profesor_id' => $actual['destino'], 'updated_at' => now()]);
            DB::table('reasignacion_docente_auditorias')->insert([
                'operacion' => (string) Str::uuid(), 'usuario_id' => auth()->id(),
                'usuario' => (string) (auth()->user()?->username ?? auth()->id()),
                'profesor_anterior_id' => $actual['origen'], 'profesor_nuevo_id' => $actual['destino'],
                'profesor_anterior' => $actual['nombre_origen'], 'profesor_nuevo' => $actual['nombre_destino'],
                'total' => $cantidad,
                'detalle' => json_encode(['asignaciones' => $actual['detalle'], 'impacto' => $actual['impacto'], 'generaciones' => $actual['generaciones'], 'traslapes' => $actual['conflictos']], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
            ]);
            return $cantidad;
        });
    }

    private function nombre(Profesor $p): string
    {
        return trim($p->apellido_paterno.' '.$p->apellido_materno.' '.$p->nombre);
    }

    private function fallar(string $mensaje): never
    {
        throw ValidationException::withMessages(['reasignacion' => $mensaje]);
    }
}
