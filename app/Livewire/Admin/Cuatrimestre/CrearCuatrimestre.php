<?php

namespace App\Livewire\Admin\Cuatrimestre;

use App\Models\Cuatrimestre;
use App\Models\Mes;
use Livewire\Component;

class CrearCuatrimestre extends Component
{

    public $cuatrimestre;
    public $mes_id;



    protected $rules = [
        'cuatrimestre' => 'required|numeric|min:1|max:9',
        'mes_id' => 'required|exists:meses,id',
    ];

    protected $messages = [
        'cuatrimestre.required' => 'El campo cuatrimestre es obligatorio.',
        'cuatrimestre.numeric' => 'El campo cuatrimestre debe ser un número.',
        'cuatrimestre.min' => 'El cuatrimestre debe ser al menos 1.',
        'cuatrimestre.max' => 'El cuatrimestre no puede ser mayor a 10.',
        'mes_id.required' => 'El campo meses es obligatorio.',
        'mes_id.exists' => 'El mes seleccionado no es válido.',
    ];

    public function crearCuatrimestre()
    {
        $this->validate();

        // Aquí puedes agregar la lógica para verificar si el cuatrimestre ya existe
        $cuatrimestreExistente = Cuatrimestre::where('cuatrimestre', $this->cuatrimestre)
            ->first();
        if ($cuatrimestreExistente) {
            $this->dispatch('swal', [
                'title' => '¡El cuatrimestre ya existe!',
                'icon' => 'error',
                'position' => 'top',
            ]);
            return;
        }

        // Aquí puedes agregar la lógica para crear el cuatrimestre

        Cuatrimestre::create([
            'cuatrimestre' => $this->cuatrimestre,
            'mes_id' => $this->mes_id,
        ]);
        // Luego, puedes restablecer los campos
        $this->reset(['cuatrimestre', 'mes_id']);
        // O redirigir a otra página

        $this->dispatch('swal', [
            'title' => '¡Cuatrimestre creado correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
        ]);


        $this->dispatch('refreshCuatrimestre');


    }


    public function render()
    {
        $meses = Mes::all();
        return view('livewire.admin.cuatrimestre.crear-cuatrimestre', compact('meses'));
    }
}
