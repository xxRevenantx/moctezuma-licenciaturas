<?php

namespace App\Livewire\Admin\Grupo;

use App\Models\Cuatrimestre;
use App\Models\Grupo;
use App\Models\Licenciatura;
use Livewire\Attributes\On;
use Livewire\Component;

class EditarGrupo extends Component
{
    public $grupoId;
    public $licenciatura_id;
    public $cuatrimestre_id;
    public $grupo;

     public $open = false;

     #[On('abrirGrupo')]
    public function abrirModal($id)
    {
        $grupo = Grupo::findOrFail($id);
        $this->grupoId = $grupo->id;
        $this->licenciatura_id = $grupo->licenciatura_id;
        $this->cuatrimestre_id = $grupo->cuatrimestre_id;
        $this->grupo = $grupo->grupo;
        $this->open = true;
    }

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

       public function actualizarGrupo()
    {
        $this->validate([
            'grupo' => 'required|string|max:10|unique:grupos,grupo,' . $this->grupoId,
        ],[
            'grupo.required' => 'El campo grupo es obligatorio.',
            'grupo.string' => 'El campo grupo debe ser una cadena de texto.',
            'grupo.max' => 'El campo grupo no puede tener más de 10 caracteres.',
            'grupo.unique' => 'El grupo ya existe en la base de datos.',
            'activa.required' => 'El campo activa es obligatorio.',

        ]);

        // Verifica si la licenciatura_id y cuatrimestre_id ya existen en la base de datos


        $grupo = Grupo::find($this->grupoId);
        if ($grupo) {
            $grupo->update([
                'licenciatura_id' => $this->licenciatura_id,
                'cuatrimestre_id' => $this->cuatrimestre_id,
                'grupo' => trim($this->grupo),
            ]);

            $this->dispatch('swal', [
                'title' => '¡Grupo actualizado correctamente!',
                'icon' => 'success',
                'position' => 'top-end',
            ]);

            $this->reset(['open', 'grupoId', 'licenciatura_id', 'cuatrimestre_id', 'grupo']);
            $this->dispatch('refreshGrupo');

        }
    }
    public function cerrarModal()
    {
        $this->reset(['open', 'grupoId', 'licenciatura_id', 'cuatrimestre_id', 'grupo']);
    }



    public function render()
    {
        $licenciaturas = Licenciatura::all();
        $cuatrimestres = Cuatrimestre::all();
        return view('livewire.admin.grupo.editar-grupo', compact('licenciaturas', 'cuatrimestres'));
    }
}
