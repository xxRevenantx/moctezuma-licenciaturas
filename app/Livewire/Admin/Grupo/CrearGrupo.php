<?php

namespace App\Livewire\Admin\Grupo;

use Livewire\Component;

class CrearGrupo extends Component
{
    public $licenciatura_id;
    public $cuatrimestre_id;
    public $grupo;

    protected $rules = [
        'licenciatura_id' => 'required|exists:licenciaturas,id',
        'cuatrimestre_id' => 'required|exists:cuatrimestres,id',
        'grupo' => 'required|string|max:10',
    ];

    protected $messages = [
        'licenciatura_id.required' => 'La licenciatura es requerida.',
        'licenciatura_id.exists' => 'La licenciatura seleccionada no es válida.',
        'cuatrimestre_id.required' => 'El cuatrimestre es requerido.',
        'cuatrimestre_id.exists' => 'El cuatrimestre seleccionado no es válido.',
        'grupo.required' => 'El grupo es requerido.',
        'grupo.string' => 'El grupo debe ser un texto.',
        'grupo.max' => 'El grupo no puede tener más de 255 caracteres.',
    ];

    public function updatedLicenciaturaId()
    {
        $this->generarGrupo();
    }

    public function updatedCuatrimestreId()
    {
        $this->generarGrupo();
    }

    private function generarGrupo()
    {
        if (!$this->licenciatura_id || !$this->cuatrimestre_id) {
            $this->grupo = '';
            return;
        }

        // Mapear las licenciaturas
        $licMap = [
            1 => 'LN',
            2 => 'LAEM',
            3 => 'LCPAP', // Ciencias Políticas
            4 => 'LCC',
            5 => 'LCE',
            6 => 'LCFD',
            7 => 'LCP', // Contaduría Pública
            8 => 'LAR'
        ];

        // Mapear los cuatrimestres
        $cuaMap = [
            1 => '100',
            2 => '200',
            3 => '300',
            4 => '400',
            5 => '500',
            6 => '600',
            7 => '700',
            8 => '800',
            9 => '900'
        ];

        $licCode = $licMap[$this->licenciatura_id] ?? '';
        $cuaCode = $cuaMap[$this->cuatrimestre_id] ?? '';

        $this->grupo = $licCode . $cuaCode;
    }

    public function crearGrupo()
    {
        $this->validate();

        // No deben repetirse los grupos
        if (\App\Models\Grupo::where('grupo', $this->grupo)->exists()) {
            $this->addError('grupo', 'El grupo ya existe.');
            return;
        }

        \App\Models\Grupo::create([
            'licenciatura_id' => $this->licenciatura_id,
            'cuatrimestre_id' => $this->cuatrimestre_id,
            'grupo' => $this->grupo,
        ]);

        $this->reset(['licenciatura_id', 'cuatrimestre_id', 'grupo']);

        $this->dispatch('swal', [
            'title' => '¡Grupo creado correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
        ]);

        $this->dispatch('refreshGrupo');
    }

    public function render()
    {
        $licenciaturas = \App\Models\Licenciatura::all();
        $cuatrimestres = \App\Models\Cuatrimestre::all();
        return view('livewire.admin.grupo.crear-grupo', compact('licenciaturas', 'cuatrimestres'));
    }
}
