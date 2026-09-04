<?php

namespace App\Services;

use App\Exceptions\AcademicPeriodException;
use App\Models\Mes;
use App\Models\Periodo;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class AcademicPeriodResolver
{
    public function resolveFor(
        int $generacionId,
        int $cuatrimestreId,
        ?int $periodoId = null
    ): Periodo {
        $query = Periodo::query()
            ->with(['cuatrimestre', 'mes', 'generacion'])
            ->where('generacion_id', $generacionId)
            ->where('cuatrimestre_id', $cuatrimestreId);

        if ($periodoId) {
            $query->whereKey($periodoId);
        }

        $periodos = $query
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        if ($periodos->isEmpty()) {
            throw new AcademicPeriodException(
                "No existe un periodo escolar para la generación {$generacionId} y el cuatrimestre {$cuatrimestreId}."
            );
        }

        if ($periodos->count() > 1 && !$periodoId) {
            $ids = $periodos->pluck('id')->implode(', ');

            throw new AcademicPeriodException(
                "Hay más de un periodo escolar para la misma generación y cuatrimestre (IDs: {$ids}). "
                . 'No se puede elegir uno automáticamente porque se perdería trazabilidad histórica.'
            );
        }

        /** @var Periodo $periodo */
        $periodo = $periodos->first();

        return $this->assertConsistent($periodo);
    }

    public function latestForGeneration(int $generacionId): ?Periodo
    {
        $periodo = Periodo::query()
            ->with(['cuatrimestre', 'mes', 'generacion'])
            ->where('generacion_id', $generacionId)
            ->orderByDesc('order')
            ->orderByDesc('id')
            ->first();

        return $periodo ? $this->assertConsistent($periodo) : null;
    }

    public function assertConsistent(Periodo $periodo): Periodo
    {
        $issues = $this->issues($periodo);

        if ($issues !== []) {
            throw new AcademicPeriodException(
                'Periodo académico inconsistente (ID '.$periodo->id.'): '.implode(' ', $issues)
            );
        }

        return $periodo;
    }

    /**
     * @return array<int, string>
     */
    public function issues(Periodo $periodo): array
    {
        $issues = [];

        if (!$periodo->inicio_periodo) {
            $issues[] = 'Falta la fecha de inicio.';
        }

        if (!$periodo->termino_periodo) {
            $issues[] = 'Falta la fecha de término.';
        }

        if (!$periodo->inicio_periodo || !$periodo->termino_periodo) {
            return $issues;
        }

        $inicio = Carbon::parse($periodo->inicio_periodo)->startOfDay();
        $termino = Carbon::parse($periodo->termino_periodo)->startOfDay();

        if ($termino->lt($inicio)) {
            $issues[] = 'La fecha de término es anterior a la fecha de inicio.';
            return $issues;
        }

        $ciclo = trim((string) $periodo->ciclo_escolar);
        if (!preg_match('/^(\d{4})-(\d{4})$/', $ciclo, $m)) {
            $issues[] = "El ciclo escolar «{$ciclo}» no tiene el formato AAAA-AAAA.";
        } elseif ((int) $m[2] !== ((int) $m[1] + 1)) {
            $issues[] = "El ciclo escolar «{$ciclo}» no representa dos años consecutivos.";
        }

        $cicloEsperadoInicio = $this->expectedCycleFromDate($inicio);
        $cicloEsperadoTermino = $this->expectedCycleFromDate($termino);

        if ($cicloEsperadoInicio !== $cicloEsperadoTermino) {
            $issues[] = "Las fechas atraviesan ciclos escolares distintos ({$cicloEsperadoInicio} y {$cicloEsperadoTermino}).";
        }

        if ($ciclo !== $cicloEsperadoInicio) {
            $issues[] = "Por la fecha de inicio {$inicio->format('d/m/Y')}, el ciclo correcto es {$cicloEsperadoInicio}.";
        }

        $codigoInicio = $this->periodCodeFromDate($inicio);
        $codigoTermino = $this->periodCodeFromDate($termino);

        if ($codigoInicio !== $codigoTermino) {
            $issues[] = "Las fechas atraviesan periodos distintos ({$codigoInicio} y {$codigoTermino}).";
        }

        $periodo->loadMissing('mes');
        $codigoRegistrado = mb_strtoupper(trim((string) $periodo->mes?->meses_corto), 'UTF-8');

        if ($codigoRegistrado === '') {
            $issues[] = 'El registro no tiene un mes/periodo válido relacionado.';
        } elseif ($codigoRegistrado !== $codigoInicio) {
            $issues[] = "El periodo registrado es {$codigoRegistrado}, pero las fechas corresponden a {$codigoInicio}.";
        }

        return $issues;
    }

    public function expectedCycleFromDate(Carbon|string $date): string
    {
        $fecha = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
        $year = (int) $fecha->year;

        if ((int) $fecha->month >= 9) {
            return $year.'-'.($year + 1);
        }

        return ($year - 1).'-'.$year;
    }

    public function periodCodeFromDate(Carbon|string $date): string
    {
        $fecha = $date instanceof Carbon ? $date : Carbon::parse($date);
        $month = (int) $fecha->month;

        return match (true) {
            $month >= 9 => 'SEP/DIC',
            $month >= 5 => 'MAY/AGO',
            default => 'ENE/ABR',
        };
    }

    public function expectedMesIdFromDate(Carbon|string $date): ?int
    {
        $codigo = $this->periodCodeFromDate($date);

        $id = Mes::query()
            ->whereRaw('UPPER(meses_corto) = ?', [$codigo])
            ->value('id');

        return $id ? (int) $id : null;
    }

    public function rangeKey(Periodo $periodo): string
    {
        $periodo->loadMissing('mes');

        return match (mb_strtoupper(trim((string) $periodo->mes?->meses_corto), 'UTF-8')) {
            'SEP/DIC' => '9-12',
            'ENE/ABR' => '1-4',
            'MAY/AGO' => '5-8',
            default => throw new AcademicPeriodException(
                'El periodo ID '.$periodo->id.' tiene un rango mensual no reconocido.'
            ),
        };
    }

    /**
     * @return array{0:int,1:int}
     */
    public function monthBounds(Periodo $periodo): array
    {
        return match ($this->rangeKey($periodo)) {
            '9-12' => [9, 12],
            '1-4' => [1, 4],
            '5-8' => [5, 8],
        };
    }

    public function assertRequestedRange(Periodo $periodo, string $requestedRange): Periodo
    {
        $expected = $this->rangeKey($periodo);

        if ($expected !== $requestedRange) {
            $label = $this->label($periodo);

            throw new AcademicPeriodException(
                "El rango solicitado {$requestedRange} no coincide con el periodo académico {$label}."
            );
        }

        return $periodo;
    }

    public function label(Periodo $periodo): string
    {
        $periodo->loadMissing(['mes', 'cuatrimestre']);

        $codigo = mb_strtoupper(trim((string) $periodo->mes?->meses_corto), 'UTF-8');
        $inicio = $periodo->inicio_periodo ? Carbon::parse($periodo->inicio_periodo) : null;
        $anio = $inicio?->year ?? 'S/F';
        $cuatrimestre = $periodo->cuatrimestre?->cuatrimestre ?? $periodo->cuatrimestre_id;

        return trim(
            "{$codigo} {$anio} · Ciclo {$periodo->ciclo_escolar} · {$cuatrimestre}.º cuatrimestre"
        );
    }

    /**
     * Genera únicamente fechas reales comprendidas entre inicio_periodo y termino_periodo.
     * Escolarizada: lunes a viernes. Semiescolarizada: sábados.
     *
     * @return array<int, array<int, int>>
     */
    public function attendanceDays(Periodo $periodo, bool $esEscolarizada): array
    {
        $this->assertConsistent($periodo);

        $inicio = Carbon::parse($periodo->inicio_periodo)->startOfDay();
        $termino = Carbon::parse($periodo->termino_periodo)->startOfDay();
        [$mesInicial, $mesFinal] = $this->monthBounds($periodo);

        $fechas = [];
        for ($mes = $mesInicial; $mes <= $mesFinal; $mes++) {
            $fechas[$mes] = [];
        }

        foreach (CarbonPeriod::create($inicio, $termino) as $fecha) {
            $incluir = $esEscolarizada
                ? $fecha->isWeekday()
                : $fecha->dayOfWeekIso === 6;

            if (!$incluir) {
                continue;
            }

            $mes = (int) $fecha->month;
            $fechas[$mes] ??= [];
            $fechas[$mes][] = (int) $fecha->day;
        }

        return $fechas;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function audit(): Collection
    {
        return Periodo::query()
            ->with(['generacion', 'cuatrimestre', 'mes'])
            ->orderBy('id')
            ->get()
            ->map(function (Periodo $periodo) {
                return [
                    'id' => (int) $periodo->id,
                    'generacion' => $periodo->generacion?->generacion ?? '—',
                    'cuatrimestre' => $periodo->cuatrimestre?->cuatrimestre ?? $periodo->cuatrimestre_id,
                    'periodo' => $periodo->mes?->meses_corto ?? '—',
                    'ciclo_escolar' => $periodo->ciclo_escolar,
                    'inicio' => $periodo->inicio_periodo
                        ? Carbon::parse($periodo->inicio_periodo)->format('Y-m-d')
                        : null,
                    'termino' => $periodo->termino_periodo
                        ? Carbon::parse($periodo->termino_periodo)->format('Y-m-d')
                        : null,
                    'issues' => $this->issues($periodo),
                ];
            })
            ->filter(fn (array $row) => $row['issues'] !== [])
            ->values();
    }
}
