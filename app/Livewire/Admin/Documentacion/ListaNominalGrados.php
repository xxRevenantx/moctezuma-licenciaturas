<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\Licenciatura;
use App\Models\Modalidad;
use App\Services\EstadisticaGeneralGradosService;
use App\Services\ListaNominalGradosService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ListaNominalGrados extends Component
{
    public array $licenciaturas = [];
    public array $modalidades = [];
    public array $generaciones = [];
    public array $licenciaturasSeleccionadas = [];
    public array $modalidadesSeleccionadas = [];
    public array $generacionesSeleccionadas = [];
    public array $gradosSeleccionados = ['1', '2', '3'];
    public bool $mostrar_grados_vacios = false;
    public int $renglones_vacios = 0;

    public function mount(): void
    {
        $this->licenciaturas = Licenciatura::query()->orderBy('nombre')->get(['id', 'nombre'])->toArray();
        $this->modalidades = Modalidad::query()->orderBy('nombre')->get(['id', 'nombre'])->toArray();
        $this->generaciones = app(EstadisticaGeneralGradosService::class)->generacionesVigentes();
        $this->limpiarFiltros();
    }

    public function seleccionarTodo(string $grupo): void
    {
        [$opciones, $propiedad] = $this->grupoPermitido($grupo);
        $this->{$propiedad} = array_map(static fn (array $item): string => (string) $item['id'], $this->{$opciones});
    }

    public function quitarTodo(string $grupo): void
    {
        [, $propiedad] = $this->grupoPermitido($grupo);
        $this->{$propiedad} = [];
    }

    private function grupoPermitido(string $grupo): array
    {
        return match ($grupo) {
            'licenciaturas' => ['licenciaturas', 'licenciaturasSeleccionadas'],
            'modalidades' => ['modalidades', 'modalidadesSeleccionadas'],
            'generaciones' => ['generaciones', 'generacionesSeleccionadas'],
            default => throw new \InvalidArgumentException('Filtro desconocido.'),
        };
    }

    public function limpiarFiltros(): void
    {
        foreach (['licenciaturas', 'modalidades', 'generaciones'] as $grupo) {
            $this->seleccionarTodo($grupo);
        }
        $this->gradosSeleccionados = ['1', '2', '3'];
        $this->mostrar_grados_vacios = false;
        $this->renglones_vacios = 0;
    }

    private function parametroSeleccion(string $grupo): ?string
    {
        [$opciones, $propiedad] = $this->grupoPermitido($grupo);
        $disponibles = array_map(static fn (array $v): int => (int) $v['id'], $this->{$opciones});
        $seleccionados = array_values(array_unique(array_filter(
            array_map('intval', $this->{$propiedad}),
            static fn (int $id): bool => $id > 0 && in_array($id, $disponibles, true)
        )));
        sort($disponibles);
        sort($seleccionados);
        return $disponibles === $seleccionados ? null : ($seleccionados ? implode(',', $seleccionados) : 'none');
    }

    public function updatedRenglonesVacios(): void
    {
        $this->renglones_vacios = min(10, max(0, $this->renglones_vacios));
    }

    private function filtros(): array
    {
        $grados = array_values(array_intersect(['1','2','3'], array_map('strval', $this->gradosSeleccionados)));
        return array_filter([
            'licenciatura_ids' => $this->parametroSeleccion('licenciaturas'),
            'modalidad_ids' => $this->parametroSeleccion('modalidades'),
            'generacion_ids' => $this->parametroSeleccion('generaciones'),
            'grados' => $grados ? implode(',', $grados) : 'none',
            'mostrar_grados_vacios' => $this->mostrar_grados_vacios ? 1 : 0,
            'renglones_vacios' => min(10, max(0, $this->renglones_vacios)),
        ], static fn ($v): bool => $v !== null);
    }

    #[Computed]
    public function reporte(): array
    {
        return app(ListaNominalGradosService::class)->generar($this->filtros());
    }

    #[Computed]
    public function pdfVistaUrl(): string
    {
        return route('admin.lista-nominal-grados.pdf', $this->filtros());
    }

    #[Computed]
    public function pdfDescargaUrl(): string
    {
        return route('admin.lista-nominal-grados.pdf', [...$this->filtros(), 'disposition' => 'download']);
    }

    #[Computed]
    public function wordUrl(): string
    {
        return route('admin.lista-nominal-grados.word', $this->filtros());
    }

    #[Computed]
    public function excelUrl(): string
    {
        return route('admin.lista-nominal-grados.excel', $this->filtros());
    }

    public function render()
    {
        return view('livewire.admin.documentacion.lista-nominal-grados');
    }
}
