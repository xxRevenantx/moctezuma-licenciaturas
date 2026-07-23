<?php

namespace App\Livewire\Admin;

use App\Helpers\Flash;
use App\Models\Dashboard as ModelsDashboard;
use App\Models\Generacion;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use App\Models\Profesor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public string $ciclo_escolar = '';
    public string $periodo_escolar = '';

    public $licenciaturas;
    public $generacionesActivas;

    public int $profesoresActivos = 0;

    public array $resumenPorLicenciatura = [];
    public array $resumenPorLicenciaturaBaja = [];
    public array $resumenPorLicenciaturaForaneo = [];
    public array $resumenPorLicenciaturaBajaForaneo = [];

    public int $totalLocalesActivos = 0;
    public int $totalHombresLocalesActivos = 0;
    public int $totalMujeresLocalesActivos = 0;

    public int $totalLocalesBaja = 0;
    public int $totalHombresLocalesBaja = 0;
    public int $totalMujeresLocalesBaja = 0;

    public int $totalForaneosActivos = 0;
    public int $totalHombresForaneosActivos = 0;
    public int $totalMujeresForaneosActivos = 0;

    public int $totalForaneosBaja = 0;
    public int $totalHombresForaneosBaja = 0;
    public int $totalMujeresForaneosBaja = 0;

    /**
     * Información lista para ApexCharts. Se mantiene separada de la vista para
     * evitar consultas y transformaciones dentro de Blade.
     */
    public array $chartData = [];

    public function mount(): void
    {
        $dashboard = ModelsDashboard::query()->latest('id')->first();

        $this->ciclo_escolar = $dashboard?->ciclo_escolar ?? '';
        $this->periodo_escolar = $dashboard?->periodo_escolar ?? '';

        $this->cargarIndicadores();
    }

    public function guardarDatos(): void
    {
        $datos = $this->validate([
            'ciclo_escolar' => ['required', 'string', 'max:50'],
            'periodo_escolar' => ['required', 'string', 'max:100'],
        ]);

        ModelsDashboard::query()->create([
            'ciclo_escolar' => trim($datos['ciclo_escolar']),
            'periodo_escolar' => trim($datos['periodo_escolar']),
        ]);

        Flash::success('Datos guardados correctamente');
        $this->dispatch('refreshHeader');
    }

    private function cargarIndicadores(): void
    {
        $this->licenciaturas = Licenciatura::query()
            ->select(['id', 'nombre', 'nombre_corto'])
            ->orderBy('nombre')
            ->get();

        $this->generacionesActivas = Generacion::query()
            ->select(['id', 'generacion'])
            ->where('activa', 'true')
            ->orderBy('generacion')
            ->get();

        $this->profesoresActivos = Profesor::query()
            ->whereHas('user', fn ($query) => $query->where('status', 'true'))
            ->count();

        $conteos = Inscripcion::query()
            ->select([
                'licenciatura_id',
                'foraneo',
                'status',
                'sexo',
                DB::raw('COUNT(*) AS total'),
            ])
            ->whereHas('generacion', fn ($query) => $query->where('activa', 'true'))
            ->groupBy('licenciatura_id', 'foraneo', 'status', 'sexo')
            ->get()
            ->mapWithKeys(function ($fila) {
                $llave = $this->llaveConteo(
                    (int) $fila->licenciatura_id,
                    (string) $fila->foraneo,
                    (string) $fila->status,
                    (string) $fila->sexo,
                );

                return [$llave => (int) $fila->total];
            });

        $this->resumenPorLicenciatura = $this->crearResumen($conteos, 'false', 'true');
        $this->resumenPorLicenciaturaBaja = $this->crearResumen($conteos, 'false', 'false');
        $this->resumenPorLicenciaturaForaneo = $this->crearResumen($conteos, 'true', 'true');
        $this->resumenPorLicenciaturaBajaForaneo = $this->crearResumen($conteos, 'true', 'false');

        $this->asignarTotales();
        $this->prepararGraficas();
    }

    private function crearResumen(Collection $conteos, string $foraneo, string $status): array
    {
        return $this->licenciaturas
            ->map(function (Licenciatura $licenciatura) use ($conteos, $foraneo, $status) {
                $hombres = $this->obtenerConteo($conteos, $licenciatura->id, $foraneo, $status, 'H');
                $mujeres = $this->obtenerConteo($conteos, $licenciatura->id, $foraneo, $status, 'M');

                return [
                    'licenciatura_id' => $licenciatura->id,
                    'licenciatura' => $licenciatura->nombre,
                    'nombre_corto' => $licenciatura->nombre_corto ?: $licenciatura->nombre,
                    'hombres' => $hombres,
                    'mujeres' => $mujeres,
                    'total' => $hombres + $mujeres,
                ];
            })
            ->values()
            ->all();
    }

    private function asignarTotales(): void
    {
        [$this->totalHombresLocalesActivos, $this->totalMujeresLocalesActivos, $this->totalLocalesActivos]
            = $this->totalesDe($this->resumenPorLicenciatura);

        [$this->totalHombresLocalesBaja, $this->totalMujeresLocalesBaja, $this->totalLocalesBaja]
            = $this->totalesDe($this->resumenPorLicenciaturaBaja);

        [$this->totalHombresForaneosActivos, $this->totalMujeresForaneosActivos, $this->totalForaneosActivos]
            = $this->totalesDe($this->resumenPorLicenciaturaForaneo);

        [$this->totalHombresForaneosBaja, $this->totalMujeresForaneosBaja, $this->totalForaneosBaja]
            = $this->totalesDe($this->resumenPorLicenciaturaBajaForaneo);
    }

    private function totalesDe(array $resumen): array
    {
        $coleccion = collect($resumen);
        $hombres = (int) $coleccion->sum('hombres');
        $mujeres = (int) $coleccion->sum('mujeres');

        return [$hombres, $mujeres, $hombres + $mujeres];
    }

    private function prepararGraficas(): void
    {
        $this->chartData = [
            'categorias' => collect($this->resumenPorLicenciatura)
                ->pluck('nombre_corto')
                ->values()
                ->all(),
            'alumnosActivos' => [
                [
                    'name' => 'Locales · hombres',
                    'data' => collect($this->resumenPorLicenciatura)->pluck('hombres')->values()->all(),
                ],
                [
                    'name' => 'Locales · mujeres',
                    'data' => collect($this->resumenPorLicenciatura)->pluck('mujeres')->values()->all(),
                ],
                [
                    'name' => 'Foráneos · hombres',
                    'data' => collect($this->resumenPorLicenciaturaForaneo)->pluck('hombres')->values()->all(),
                ],
                [
                    'name' => 'Foráneos · mujeres',
                    'data' => collect($this->resumenPorLicenciaturaForaneo)->pluck('mujeres')->values()->all(),
                ],
            ],
            'estadoGeneral' => [
                'labels' => [
                    'Locales activos',
                    'Foráneos activos',
                    'Locales dados de baja',
                    'Foráneos dados de baja',
                ],
                'series' => [
                    $this->totalLocalesActivos,
                    $this->totalForaneosActivos,
                    $this->totalLocalesBaja,
                    $this->totalForaneosBaja,
                ],
            ],
            'totales' => [
                'activos' => $this->totalLocalesActivos + $this->totalForaneosActivos,
                'bajas' => $this->totalLocalesBaja + $this->totalForaneosBaja,
                'hombresActivos' => $this->totalHombresLocalesActivos + $this->totalHombresForaneosActivos,
                'mujeresActivas' => $this->totalMujeresLocalesActivos + $this->totalMujeresForaneosActivos,
            ],
        ];
    }

    private function obtenerConteo(
        Collection $conteos,
        int $licenciaturaId,
        string $foraneo,
        string $status,
        string $sexo,
    ): int {
        return (int) $conteos->get(
            $this->llaveConteo($licenciaturaId, $foraneo, $status, $sexo),
            0,
        );
    }

    private function llaveConteo(
        int $licenciaturaId,
        string $foraneo,
        string $status,
        string $sexo,
    ): string {
        return implode('|', [$licenciaturaId, $foraneo, $status, $sexo]);
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
