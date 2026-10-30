<?php

namespace App\Livewire\Admin\HorarioGeneral;

use App\Services\PlantillaDocenteLicenciaturaService;
use Livewire\Component;

class PlantillaDocente extends Component
{
    public string $ciclo = '2026-2027';
    public string $periodo = 'SEP/DIC';
    /** null = todas; [] = ninguna; [ids] = selección personalizada. */
    public array $seleccion = [
        'licenciaturas' => null,
        'modalidades' => null,
        'generaciones' => null,
        'cuatrimestres' => null,
    ];
    public array $buscar = ['licenciaturas' => '', 'modalidades' => '', 'generaciones' => '', 'cuatrimestres' => ''];
    public bool $materias = true;
    public bool $cargaHoraria = true;
    public bool $mostrarGeneraciones = true;

    public function updatedCiclo(): void
    {
        $periodos = app(PlantillaDocenteLicenciaturaService::class)->periodos($this->ciclo);
        $this->periodo = (string) ($periodos->contains('SEP/DIC') ? 'SEP/DIC' : ($periodos->first() ?: ''));
        $this->restablecer();
    }

    public function updatedPeriodo(): void
    {
        $this->restablecer();
    }

    private function restablecer(): void
    {
        foreach (array_keys($this->seleccion) as $categoria) {
            $this->seleccion[$categoria] = null;
            $this->buscar[$categoria] = '';
        }
    }

    public function todas(string $categoria): void
    {
        if (array_key_exists($categoria, $this->seleccion)) {
            $this->seleccion[$categoria] = null;
        }
    }

    public function ninguna(string $categoria): void
    {
        if (array_key_exists($categoria, $this->seleccion)) {
            $this->seleccion[$categoria] = [];
        }
    }

    public function alternar(string $categoria, int $id): void
    {
        if (! array_key_exists($categoria, $this->seleccion)) {
            return;
        }
        $opciones = app(PlantillaDocenteLicenciaturaService::class)->opciones($this->ciclo, $this->periodo);
        $permitidos = array_column($opciones[$categoria] ?? [], 'id');
        if (! in_array($id, $permitidos, true)) {
            return;
        }
        $actual = $this->seleccion[$categoria] === null ? $permitidos : (array) $this->seleccion[$categoria];
        $actual = in_array($id, $actual, true)
            ? array_values(array_diff($actual, [$id]))
            : array_values(array_unique([...$actual, $id]));
        $this->seleccion[$categoria] = count($actual) === count($permitidos) ? null : $actual;
    }

    public function render()
    {
        $servicio = app(PlantillaDocenteLicenciaturaService::class);
        $reporte = $servicio->generar([
            'ciclo_escolar' => $this->ciclo,
            'periodo_escolar' => $this->periodo,
            ...$this->seleccion,
            'materias' => $this->materias,
            'carga_horaria' => $this->cargaHoraria,
            'mostrar_generaciones' => $this->mostrarGeneraciones,
        ]);
        $parametros = [
            'ciclo_escolar' => $this->ciclo,
            'periodo_escolar' => $this->periodo,
            'materias' => (int) $this->materias,
            'carga_horaria' => (int) $this->cargaHoraria,
            'mostrar_generaciones' => (int) $this->mostrarGeneraciones,
        ];
        foreach ($this->seleccion as $categoria => $ids) {
            if ($ids !== null) {
                // El ID 0 representa 'ninguna' en URL; jamás es un ID válido de registro.
                $parametros[$categoria] = $ids ?: [0];
            }
        }
        return view('livewire.admin.horario-general.plantilla-docente', [
            'reporte' => $reporte,
            'opciones' => $servicio->opciones($this->ciclo, $this->periodo),
            'ciclos' => $servicio->ciclos(),
            'periodos' => $servicio->periodos($this->ciclo),
            'urlPreview' => route('admin.plantilla-docente.preview', $parametros),
            'urlPdf' => route('admin.plantilla-docente.pdf', $parametros),
            'urlWord' => route('admin.plantilla-docente.word', $parametros),
            'urlExcel' => route('admin.plantilla-docente.excel', $parametros),
        ]);
    }
}
