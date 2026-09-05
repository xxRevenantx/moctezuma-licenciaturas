<?php

namespace App\Services;

use App\Models\Horario;
use App\Models\Mes;
use App\Models\Periodo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class HorarioGeneralService
{
    public function aplicarCiclo(Builder $query, string $cicloEscolar, ?string $periodoEscolar = null): Builder
    {
        $periodos = Periodo::query()->where('ciclo_escolar', $cicloEscolar);

        if ($periodoEscolar) {
            $mesId = Mes::query()->where('meses_corto', $periodoEscolar)->value('id');
            if (! $mesId) {
                return $query->whereRaw('1 = 0');
            }
            $periodos->where('mes_id', $mesId);
        }

        $pares = $periodos
            ->get(['generacion_id', 'cuatrimestre_id'])
            ->map(fn ($p) => [(int) $p->generacion_id, (int) $p->cuatrimestre_id])
            ->unique(fn (array $p) => $p[0] . ':' . $p[1])
            ->values();

        if ($pares->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $q) use ($pares) {
            foreach ($pares as [$generacionId, $cuatrimestreId]) {
                $q->orWhere(function (Builder $par) use ($generacionId, $cuatrimestreId) {
                    $par->where('generacion_id', $generacionId)
                        ->where('cuatrimestre_id', $cuatrimestreId);
                });
            }
        });
    }

    /**
     * @param array{cuatrimestre_id?:int|null,licenciatura_id?:int|null,generacion_id?:int|null} $filtros
     */
    public function horarios(int $modalidadId, string $cicloEscolar, array $filtros = [], ?string $busqueda = null, ?string $periodoEscolar = null): Collection
    {
        $query = Horario::query()
            ->with([
                'asignacionMateria.materia:id,nombre,clave,licenciatura_id',
                'asignacionMateria.profesor:id,nombre,apellido_paterno,apellido_materno,color,status',
                'licenciatura:id,nombre,nombre_corto',
                'modalidad:id,nombre',
                'generacion:id,generacion',
                'cuatrimestre:id,cuatrimestre,nombre_cuatrimestre',
                'dia:id,dia',
            ])
            ->where('modalidad_id', $modalidadId);

        $this->aplicarCiclo($query, $cicloEscolar, $periodoEscolar);

        foreach (['cuatrimestre_id', 'licenciatura_id', 'generacion_id'] as $campo) {
            if (! empty($filtros[$campo])) {
                $query->where($campo, (int) $filtros[$campo]);
            }
        }

        $termino = trim((string) $busqueda);
        if ($termino !== '') {
            $query->where(function (Builder $q) use ($termino) {
                $q->whereHas('asignacionMateria.materia', fn (Builder $m) => $m->where('nombre', 'like', "%{$termino}%")->orWhere('clave', 'like', "%{$termino}%"))
                    ->orWhereHas('asignacionMateria.profesor', function (Builder $p) use ($termino) {
                        $p->where('nombre', 'like', "%{$termino}%")
                            ->orWhere('apellido_paterno', 'like', "%{$termino}%")
                            ->orWhere('apellido_materno', 'like', "%{$termino}%");
                    })
                    ->orWhereHas('licenciatura', fn (Builder $l) => $l->where('nombre', 'like', "%{$termino}%"));
            });
        }

        return $query->get();
    }

    public function columnas(Collection $horarios): Collection
    {
        return $horarios
            ->unique(fn ($h) => $h->cuatrimestre_id . ':' . $h->licenciatura_id . ':' . $h->generacion_id)
            ->map(fn ($h) => [
                'clave' => $h->cuatrimestre_id . ':' . $h->licenciatura_id . ':' . $h->generacion_id,
                'cuatrimestre_id' => (int) $h->cuatrimestre_id,
                'cuatrimestre' => $h->cuatrimestre?->cuatrimestre ?? $h->cuatrimestre_id,
                'licenciatura_id' => (int) $h->licenciatura_id,
                'licenciatura' => $h->licenciatura?->nombre ?? 'Licenciatura',
                'licenciatura_corta' => $h->licenciatura?->nombre_corto ?: ($h->licenciatura?->nombre ?? 'Licenciatura'),
                'generacion_id' => (int) $h->generacion_id,
                'generacion' => $h->generacion?->generacion ?? '—',
                'etiqueta' => sprintf(
                    '%s · %s.º · %s',
                    $h->licenciatura?->nombre_corto ?: ($h->licenciatura?->nombre ?? 'Lic.'),
                    $h->cuatrimestre?->cuatrimestre ?? $h->cuatrimestre_id,
                    $h->generacion?->generacion ?? '—'
                ),
            ])
            ->sortBy(fn (array $c) => sprintf('%03d-%05d-%05d', $c['cuatrimestre_id'], $c['licenciatura_id'], $c['generacion_id']))
            ->values();
    }

    public function horas(Collection $horarios): Collection
    {
        return $horarios->pluck('hora')->filter()->unique()->sortBy(fn ($hora) => $this->inicioMinutos((string) $hora) ?? PHP_INT_MAX)->values();
    }

    public function celdas(Collection $horarios): Collection
    {
        return $horarios->keyBy(fn ($h) => $this->claveCelda((string) $h->hora, (int) $h->cuatrimestre_id, (int) $h->licenciatura_id, (int) $h->generacion_id));
    }

    public function claveCelda(string $hora, int $cuatrimestreId, int $licenciaturaId, int $generacionId): string
    {
        return $hora . '|' . $cuatrimestreId . '|' . $licenciaturaId . '|' . $generacionId;
    }

    public function minutosRango(?string $rango): int
    {
        $partes = array_map('trim', explode('-', strtolower((string) $rango), 2));
        if (count($partes) !== 2) {
            return 0;
        }

        $inicio = strtotime($partes[0]);
        $fin = strtotime($partes[1]);
        if ($inicio === false || $fin === false) {
            return 0;
        }

        $i = ((int) date('G', $inicio) * 60) + (int) date('i', $inicio);
        $f = ((int) date('G', $fin) * 60) + (int) date('i', $fin);
        return max($f - $i, 0);
    }

    public function formatoHoras(int $minutos): string
    {
        return sprintf('%02d:%02d', intdiv(max($minutos, 0), 60), max($minutos, 0) % 60);
    }

    private function inicioMinutos(string $rango): ?int
    {
        $inicio = trim(explode('-', strtolower($rango), 2)[0] ?? '');
        $ts = strtotime($inicio);
        return $ts === false ? null : ((int) date('G', $ts) * 60) + (int) date('i', $ts);
    }
}
