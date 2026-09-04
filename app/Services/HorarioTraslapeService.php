<?php

namespace App\Services;

use App\Models\Horario;
use Illuminate\Support\Collection;
use Throwable;

class HorarioTraslapeService
{
    /**
     * Detecta choques que se producirían si las asignaciones indicadas pasan
     * al mismo profesor. La comparación se limita a la misma modalidad y al
     * mismo día, pero admite rangos de hora que se intersecten parcialmente.
     *
     * @param  array<int>  $asignacionIds
     * @return array<int, array<string, mixed>>
     */
    public function conflictosParaCambioProfesor(int $profesorId, int $modalidadId, array $asignacionIds): array
    {
        $asignacionIds = collect($asignacionIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($profesorId <= 0 || $modalidadId <= 0 || empty($asignacionIds)) {
            return [];
        }

        $relaciones = [
            'dia:id,dia',
            'licenciatura:id,nombre,nombre_corto',
            'generacion:id,generacion',
            'asignacionMateria:id,materia_id,profesor_id',
            'asignacionMateria.materia:id,nombre,clave',
        ];

        $afectados = Horario::query()
            ->with($relaciones)
            ->where('modalidad_id', $modalidadId)
            ->whereIn('asignacion_materia_id', $asignacionIds)
            ->whereNotNull('dia_id')
            ->whereNotNull('hora')
            ->get();

        if ($afectados->isEmpty()) {
            return [];
        }

        $ocupados = Horario::query()
            ->with($relaciones)
            ->where('modalidad_id', $modalidadId)
            ->whereNotIn('asignacion_materia_id', $asignacionIds)
            ->whereIn('dia_id', $afectados->pluck('dia_id')->filter()->unique()->all())
            ->whereNotNull('hora')
            ->whereHas('asignacionMateria', fn ($query) => $query->where('profesor_id', $profesorId))
            ->get();

        $conflictos = collect();

        // Choques contra otras materias que el docente ya tiene en horario.
        foreach ($afectados as $afectado) {
            foreach ($ocupados->where('dia_id', $afectado->dia_id) as $ocupado) {
                if ($this->rangosSeTraslapan($afectado->hora, $ocupado->hora)) {
                    $conflictos->push($this->formatearConflicto($afectado, $ocupado, 'existente'));
                }
            }
        }

        // En asignación masiva también detecta choques entre materias
        // distintas de la propia selección que pasarán al mismo profesor.
        if (count($asignacionIds) > 1) {
            $afectadosPorDia = $afectados->groupBy('dia_id');

            foreach ($afectadosPorDia as $registrosDia) {
                /** @var Collection<int, Horario> $registrosDia */
                $registrosDia = $registrosDia->values();
                $total = $registrosDia->count();

                for ($i = 0; $i < $total; $i++) {
                    for ($j = $i + 1; $j < $total; $j++) {
                        $a = $registrosDia[$i];
                        $b = $registrosDia[$j];

                        if ((int) $a->asignacion_materia_id === (int) $b->asignacion_materia_id) {
                            continue;
                        }

                        if ($this->rangosSeTraslapan($a->hora, $b->hora)) {
                            $conflictos->push($this->formatearConflicto($a, $b, 'seleccion'));
                        }
                    }
                }
            }
        }

        return $conflictos
            ->unique(fn (array $conflicto) => implode('|', [
                min((int) $conflicto['horario_origen_id'], (int) $conflicto['horario_conflicto_id']),
                max((int) $conflicto['horario_origen_id'], (int) $conflicto['horario_conflicto_id']),
            ]))
            ->sortBy(fn (array $conflicto) => sprintf(
                '%02d-%s-%s',
                (int) $conflicto['dia_id'],
                $conflicto['hora_origen'],
                $conflicto['materia_conflicto']
            ))
            ->values()
            ->all();
    }

    public function rangosSeTraslapan(?string $rangoA, ?string $rangoB): bool
    {
        $a = $this->rangoAMinutos($rangoA);
        $b = $this->rangoAMinutos($rangoB);

        if ($a === null || $b === null) {
            return trim((string) $rangoA) !== ''
                && trim((string) $rangoA) === trim((string) $rangoB);
        }

        return $a['inicio'] < $b['fin'] && $a['fin'] > $b['inicio'];
    }

    /** @return array{inicio:int,fin:int}|null */
    private function rangoAMinutos(?string $rango): ?array
    {
        $partes = array_map('trim', explode('-', strtolower((string) $rango), 2));

        if (count($partes) !== 2 || $partes[0] === '' || $partes[1] === '') {
            return null;
        }

        try {
            $inicio = strtotime($partes[0]);
            $fin = strtotime($partes[1]);

            if ($inicio === false || $fin === false) {
                return null;
            }

            $inicioMinutos = ((int) date('G', $inicio) * 60) + (int) date('i', $inicio);
            $finMinutos = ((int) date('G', $fin) * 60) + (int) date('i', $fin);

            if ($finMinutos <= $inicioMinutos) {
                return null;
            }

            return [
                'inicio' => $inicioMinutos,
                'fin' => $finMinutos,
            ];
        } catch (Throwable) {
            return null;
        }
    }

    /** @return array<string, mixed> */
    private function formatearConflicto(Horario $origen, Horario $conflicto, string $tipo): array
    {
        return [
            'tipo' => $tipo,
            'dia_id' => (int) $origen->dia_id,
            'dia' => $origen->dia?->dia ?? 'Día no disponible',
            'hora_origen' => (string) $origen->hora,
            'hora_conflicto' => (string) $conflicto->hora,
            'horario_origen_id' => (int) $origen->id,
            'horario_conflicto_id' => (int) $conflicto->id,
            'materia_origen' => $origen->asignacionMateria?->materia?->nombre ?? 'Materia no disponible',
            'clave_origen' => $origen->asignacionMateria?->materia?->clave,
            'licenciatura_origen' => $origen->licenciatura?->nombre_corto
                ?: ($origen->licenciatura?->nombre ?? 'Licenciatura no disponible'),
            'generacion_origen' => $origen->generacion?->generacion ?? 'Generación no disponible',
            'materia_conflicto' => $conflicto->asignacionMateria?->materia?->nombre ?? 'Materia no disponible',
            'clave_conflicto' => $conflicto->asignacionMateria?->materia?->clave,
            'licenciatura_conflicto' => $conflicto->licenciatura?->nombre_corto
                ?: ($conflicto->licenciatura?->nombre ?? 'Licenciatura no disponible'),
            'generacion_conflicto' => $conflicto->generacion?->generacion ?? 'Generación no disponible',
        ];
    }
}
