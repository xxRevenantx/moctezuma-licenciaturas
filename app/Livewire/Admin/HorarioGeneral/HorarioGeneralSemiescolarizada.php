<?php

namespace App\Livewire\Admin\HorarioGeneral;

use App\Models\Dashboard;
use App\Models\Horario;
use App\Models\Mes;
use App\Models\Periodo;
use App\Services\HorarioGeneralService;
use Livewire\Component;

class HorarioGeneralSemiescolarizada extends Component
{
    public string $busqueda = '';
    public string $ciclo_escolar = '';
    public string $periodo_escolar = '';
    public string $filtrar_cuatrimestre = '';
    public string $filtrar_licenciatura = '';
    public string $filtrar_generacion = '';
    public array $grupos_eliminar = [];

    public function mount(): void
    {
        $dashboard = Dashboard::query()->latest('id')->first();
        $this->ciclo_escolar = (string) ($dashboard?->ciclo_escolar ?? '');
        $this->periodo_escolar = (string) ($dashboard?->periodo_escolar ?? '');
    }

    public function getHorariosProperty()
    {
        return app(HorarioGeneralService::class)->horarios(
            2,
            $this->ciclo_escolar,
            [
                'cuatrimestre_id' => $this->filtrar_cuatrimestre ?: null,
                'licenciatura_id' => $this->filtrar_licenciatura ?: null,
                'generacion_id' => $this->filtrar_generacion ?: null,
            ],
            $this->busqueda,
            $this->periodo_escolar ?: null,
        );
    }

    public function getHorariosEstructuralesProperty()
    {
        return app(HorarioGeneralService::class)->horarios(
            2,
            $this->ciclo_escolar,
            [
                'cuatrimestre_id' => $this->filtrar_cuatrimestre ?: null,
                'licenciatura_id' => $this->filtrar_licenciatura ?: null,
                'generacion_id' => $this->filtrar_generacion ?: null,
            ],
            null,
            $this->periodo_escolar ?: null,
        );
    }

    public function getOpcionesProperty(): array
    {
        $base = app(HorarioGeneralService::class)->horarios(
            2,
            $this->ciclo_escolar,
            [],
            null,
            $this->periodo_escolar ?: null,
        );

        return [
            'ciclos' => Periodo::query()->select('ciclo_escolar')->distinct()->orderByDesc('ciclo_escolar')->pluck('ciclo_escolar'),
            'periodos' => Periodo::query()
                ->where('ciclo_escolar', $this->ciclo_escolar)
                ->with('mes:id,meses_corto')
                ->orderBy('mes_id')
                ->get()
                ->pluck('mes.meses_corto')
                ->filter()
                ->unique()
                ->values(),
            'cuatrimestres' => $base->map(fn ($h) => [
                'id' => (int) $h->cuatrimestre_id,
                'nombre' => ($h->cuatrimestre?->cuatrimestre ?? $h->cuatrimestre_id) . '° Cuatrimestre',
            ])->unique('id')->sortBy('id')->values(),
            'licenciaturas' => $base->map(fn ($h) => [
                'id' => (int) $h->licenciatura_id,
                'nombre' => $h->licenciatura?->nombre ?? 'Licenciatura',
            ])->unique('id')->sortBy('nombre')->values(),
            'generaciones' => $base->map(fn ($h) => [
                'id' => (int) $h->generacion_id,
                'nombre' => $h->generacion?->generacion ?? '—',
            ])->unique('id')->sortByDesc('nombre')->values(),
        ];
    }

    public function getGruposDisponiblesProperty()
    {
        return app(HorarioGeneralService::class)
            ->columnas($this->horariosEstructurales)
            ->map(function (array $col) {
                $clave = implode(':', [$col['licenciatura_id'], $col['generacion_id'], $col['cuatrimestre_id']]);
                $total = $this->horariosEstructurales->filter(fn ($h) =>
                    (int) $h->licenciatura_id === $col['licenciatura_id']
                    && (int) $h->generacion_id === $col['generacion_id']
                    && (int) $h->cuatrimestre_id === $col['cuatrimestre_id']
                )->count();

                return $col + ['clave_grupo' => $clave, 'registros' => $total];
            });
    }

    public function abrirEliminarHorario(): void
    {
        if ($this->gruposDisponibles->isEmpty()) {
            $this->dispatch('horario-general-error', message: 'No hay grupos disponibles para eliminar con los filtros actuales.');
            return;
        }

        $this->grupos_eliminar = [];
        $this->dispatch('abrir-eliminar-horario-general');
    }

    public function seleccionarTodosGruposEliminar(): void
    {
        $this->grupos_eliminar = $this->gruposDisponibles->pluck('clave_grupo')->values()->all();
    }

    public function eliminarGruposSeleccionados(): void
    {
        $permitidos = $this->gruposDisponibles->keyBy('clave_grupo');
        $seleccion = collect($this->grupos_eliminar)->filter(fn ($clave) => $permitidos->has($clave))->unique()->values();

        if ($seleccion->isEmpty()) {
            $this->dispatch('horario-general-error', message: 'Selecciona al menos un grupo para eliminar.');
            return;
        }

        $servicio = app(HorarioGeneralService::class);
        $eliminados = 0;

        foreach ($seleccion as $clave) {
            [$licenciaturaId, $generacionId, $cuatrimestreId] = array_map('intval', explode(':', $clave));

            $query = Horario::query()
                ->where('modalidad_id', 2)
                ->where('licenciatura_id', $licenciaturaId)
                ->where('generacion_id', $generacionId)
                ->where('cuatrimestre_id', $cuatrimestreId);

            $servicio->aplicarCiclo($query, $this->ciclo_escolar, $this->periodo_escolar ?: null);
            $eliminados += $query->delete();
        }

        $this->grupos_eliminar = [];
        $this->dispatch('cerrar-eliminar-horario-general');
        $this->dispatch('horario-general-ok', message: "Se eliminaron {$eliminados} registros de horario de los grupos seleccionados.");
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['busqueda', 'filtrar_cuatrimestre', 'filtrar_licenciatura', 'filtrar_generacion']);
    }

    public function updatedCicloEscolar(): void
    {
        $periodos = Periodo::query()
            ->where('ciclo_escolar', $this->ciclo_escolar)
            ->with('mes:id,meses_corto')
            ->orderBy('mes_id')
            ->get()
            ->pluck('mes.meses_corto')
            ->filter()
            ->unique()
            ->values();

        $this->periodo_escolar = (string) ($periodos->first() ?? '');
        $this->filtrar_cuatrimestre = '';
        $this->filtrar_licenciatura = '';
        $this->filtrar_generacion = '';
    }

    public function updatedPeriodoEscolar(): void
    {
        $this->filtrar_cuatrimestre = '';
        $this->filtrar_licenciatura = '';
        $this->filtrar_generacion = '';
    }

    public function render()
    {
        $servicio = app(HorarioGeneralService::class);
        $horarios = $this->horarios;
        $columnas = $servicio->columnas($horarios);
        $horas = $servicio->horas($horarios);
        $celdas = $servicio->celdas($horarios);

        $horasPorDocente = $horarios
            ->groupBy(fn ($h) => $h->asignacionMateria?->profesor?->id ?: 'sin')
            ->map(function ($items) use ($servicio) {
                $profesor = $items->first()->asignacionMateria?->profesor;
                $minutos = $items->sum(fn ($h) => $servicio->minutosRango($h->hora));
                return [
                    'id' => $profesor?->id,
                    'nombre' => trim(($profesor?->apellido_paterno ?? '') . ' ' . ($profesor?->apellido_materno ?? '') . ' ' . ($profesor?->nombre ?? 'Sin asignar')),
                    'color' => $profesor?->color ?? '#cbd5e1',
                    'minutos' => $minutos,
                    'horas' => $servicio->formatoHoras($minutos),
                ];
            })
            ->sortBy('nombre')
            ->values();

        $paramsPdf = array_filter([
            'ciclo_escolar' => $this->ciclo_escolar,
            'periodo_escolar' => $this->periodo_escolar,
            'cuatrimestre_id' => $this->filtrar_cuatrimestre,
            'licenciatura_id' => $this->filtrar_licenciatura,
            'generacion_id' => $this->filtrar_generacion,
        ], fn ($v) => $v !== '' && $v !== null);

        return view('livewire.admin.horario-general.horario-general-semiescolarizada', [
            'horarios' => $horarios,
            'columnasUnicas' => $columnas,
            'horasUnicas' => $horas,
            'celdas' => $celdas,
            'horasPorDocente' => $horasPorDocente,
            'totalHoras' => $servicio->formatoHoras((int) $horasPorDocente->sum('minutos')),
            'opciones' => $this->opciones,
            'gruposDisponibles' => $this->gruposDisponibles,
            'pdfLegible' => route('admin.pdf.horario-general-semiescolarizada', $paramsPdf + ['modo' => 'legible']),
            'pdfCompacto' => route('admin.pdf.horario-general-semiescolarizada', $paramsPdf + ['modo' => 'compacto']),
        ]);
    }
}
