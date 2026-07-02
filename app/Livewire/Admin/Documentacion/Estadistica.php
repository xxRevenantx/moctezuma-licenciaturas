<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\Cuatrimestre;
use App\Models\Generacion;
use App\Models\Licenciatura;
use App\Models\Modalidad;
use App\Models\Periodo;
use App\Services\EstadisticaLicenciaturasService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Estadistica extends Component
{
    public array $licenciaturas = [];
    public array $modalidades = [];
    public array $generaciones = [];
    public array $cuatrimestres = [];
    public array $ciclosEscolares = [];

    public $filtrar_ciclo = '';
    public $filtrar_licenciatura = '';
    public $filtrar_modalidad = '';
    public $filtrar_generacion = '';
    public $filtrar_cuatrimestre = '';

    public bool $separar_modalidades = true;
    public bool $detalle_cuatrimestres = true;

    public function mount(): void
    {
        $this->licenciaturas = Licenciatura::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'RVOE'])
            ->toArray();

        $this->modalidades = Modalidad::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->toArray();

        $this->generaciones = Generacion::query()
            ->orderBy('generacion')
            ->get(['id', 'generacion', 'activa'])
            ->toArray();

        $this->cuatrimestres = Cuatrimestre::query()
            ->orderBy('id')
            ->get(['id', 'nombre_cuatrimestre'])
            ->toArray();

        $this->ciclosEscolares = Periodo::query()
            ->select('ciclo_escolar')
            ->distinct()
            ->orderBy('ciclo_escolar')
            ->pluck('ciclo_escolar')
            ->values()
            ->all();
    }

    public function limpiarFiltros(): void
    {
        $this->reset([
            'filtrar_ciclo',
            'filtrar_licenciatura',
            'filtrar_modalidad',
            'filtrar_generacion',
            'filtrar_cuatrimestre',
        ]);

        $this->separar_modalidades = true;
        $this->detalle_cuatrimestres = true;
    }

    #[Computed]
    public function reporte(): array
    {
        return app(EstadisticaLicenciaturasService::class)->generar($this->filtros());
    }

    #[Computed]
    public function pdfVistaUrl(): string
    {
        return route('admin.estadistica-licenciaturas.pdf', [
            ...$this->parametrosUrl(),
            'disposition' => 'inline',
        ]);
    }

    #[Computed]
    public function pdfDescargaUrl(): string
    {
        return route('admin.estadistica-licenciaturas.pdf', [
            ...$this->parametrosUrl(),
            'disposition' => 'download',
        ]);
    }

    #[Computed]
    public function excelUrl(): string
    {
        return route('admin.estadistica-licenciaturas.excel', $this->parametrosUrl());
    }

    private function filtros(): array
    {
        return [
            'ciclo_escolar' => $this->filtrar_ciclo,
            'licenciatura_id' => $this->filtrar_licenciatura,
            'modalidad_id' => $this->filtrar_modalidad,
            'generacion_id' => $this->filtrar_generacion,
            'cuatrimestre_id' => $this->filtrar_cuatrimestre,
            'separar_modalidades' => $this->separar_modalidades,
            'detalle_cuatrimestres' => $this->detalle_cuatrimestres,
        ];
    }

    private function parametrosUrl(): array
    {
        return array_filter([
            ...$this->filtros(),
            'separar_modalidades' => $this->separar_modalidades ? 1 : 0,
            'detalle_cuatrimestres' => $this->detalle_cuatrimestres ? 1 : 0,
        ], fn ($valor) => $valor !== '' && $valor !== null);
    }

    public function render()
    {
        return view('livewire.admin.documentacion.estadistica');
    }
}
