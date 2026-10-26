<?php

namespace App\Livewire\Admin\Documentacion;

use App\Services\Documentacion\DocumentacionExportService;
use Livewire\Component;

class OficiosSolicitud extends Component
{
    public ?string $generacionId = null;

    /** Todas o selección de una o varias licenciaturas. */
    public string $alcance = 'todas';

    /** @var array<int> */
    public array $licenciaturaIds = [];

    public function updatedGeneracionId(): void
    {
        $this->alcance = 'todas';
        $this->licenciaturaIds = [];
    }

    public function updatedAlcance(): void
    {
        $this->licenciaturaIds = [];
    }

    public function seleccionarTodas(): void
    {
        $this->alcance = 'seleccion';
        $this->licenciaturaIds = $this->generacionId
            ? app(DocumentacionExportService::class)
                ->licenciaturasDisponiblesParaOficio((int) $this->generacionId)
                ->pluck('id')->map(fn ($id) => (int) $id)->all()
            : [];
    }

    public function limpiarSeleccion(): void
    {
        $this->licenciaturaIds = [];
    }

    public function render()
    {
        $exports = app(DocumentacionExportService::class);

        return view('livewire.admin.documentacion.oficios-solicitud', [
            'generaciones' => $exports->generacionesDisponiblesParaOficios(),
            'licenciaturas' => $this->generacionId
                ? $exports->licenciaturasDisponiblesParaOficio((int) $this->generacionId)
                : collect(),
        ]);
    }
}
