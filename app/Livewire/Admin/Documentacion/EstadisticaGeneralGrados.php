<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\Licenciatura;
use App\Models\Modalidad;
use App\Services\EstadisticaGeneralGradosService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class EstadisticaGeneralGrados extends Component
{
    public array $licenciaturas = [];
    public array $modalidades = [];
    public array $generaciones = [];

    /**
     * Se guardan IDs como cadenas porque los checkboxes HTML entregan strings.
     * Un arreglo vacío significa «no incluir ninguna opción», nunca «todas».
     */
    public array $licenciaturasSeleccionadas = [];
    public array $modalidadesSeleccionadas = [];
    public array $generacionesSeleccionadas = [];
    public bool $mostrar_grados_vacios = false;

    public function mount(): void
    {
        $this->licenciaturas = Licenciatura::query()->orderBy('nombre')->get(['id', 'nombre'])->toArray();
        $this->modalidades = Modalidad::query()->orderBy('nombre')->get(['id', 'nombre'])->toArray();
        $this->generaciones = app(EstadisticaGeneralGradosService::class)->generacionesVigentes();
        $this->seleccionarTodo('licenciaturas');
        $this->seleccionarTodo('modalidades');
        $this->seleccionarTodo('generaciones');
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
            default => throw new \InvalidArgumentException('Grupo de filtros no válido.'),
        };
    }

    public function limpiarFiltros(): void
    {
        $this->seleccionarTodo('licenciaturas');
        $this->seleccionarTodo('modalidades');
        $this->seleccionarTodo('generaciones');
        $this->mostrar_grados_vacios = false;
    }

    #[Computed]
    public function reporte(): array
    {
        return app(EstadisticaGeneralGradosService::class)->generar($this->filtros());
    }

    #[Computed]
    public function pdfVistaUrl(): string
    {
        return route('admin.estadistica-grados.pdf', [...$this->filtros(), 'disposition' => 'inline']);
    }

    #[Computed]
    public function pdfDescargaUrl(): string
    {
        return route('admin.estadistica-grados.pdf', [...$this->filtros(), 'disposition' => 'download']);
    }

    #[Computed]
    public function wordUrl(): string
    {
        return route('admin.estadistica-grados.word', $this->filtros());
    }

    #[Computed]
    public function excelUrl(): string
    {
        return route('admin.estadistica-grados.excel', $this->filtros());
    }

    /**
     * El enlace exportable transmite listas separadas por comas para impedir
     * URLs extensas o parámetros de tipo arreglo malformados.  Si están todas
     * seleccionadas no se añade un filtro. «none» significa ninguna.
     */
    private function parametroSeleccion(string $grupo): ?string
    {
        [$opciones, $propiedad] = $this->grupoPermitido($grupo);
        $disponibles = array_map(static fn ($item): int => (int) $item['id'], $this->{$opciones});
        $seleccionados = array_values(array_unique(array_filter(
            array_map('intval', $this->{$propiedad}),
            static fn (int $id): bool => $id > 0 && in_array($id, $disponibles, true)
        )));
        sort($disponibles);
        sort($seleccionados);

        if ($disponibles === $seleccionados) {
            return null;
        }

        return $seleccionados === [] ? 'none' : implode(',', $seleccionados);
    }

    private function filtros(): array
    {
        return array_filter([
            'licenciatura_ids' => $this->parametroSeleccion('licenciaturas'),
            'modalidad_ids' => $this->parametroSeleccion('modalidades'),
            'generacion_ids' => $this->parametroSeleccion('generaciones'),
            'mostrar_grados_vacios' => $this->mostrar_grados_vacios ? 1 : 0,
        ], static fn ($valor): bool => $valor !== null);
    }

    public function render()
    {
        return view('livewire.admin.documentacion.estadistica-general-grados');
    }
}
