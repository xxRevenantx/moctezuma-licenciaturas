<?php

namespace App\Livewire\Admin\Licenciaturas\Submodulo;

use App\Models\Accion;
use App\Models\Licenciatura;
use Livewire\Component;
use Nnjeim\World\World;

class Inscripcion extends Component
{

    public $slug;
    public $modalidad;
    public $licenciatura;

    public $matricula;
    public $CURP;

    public $foraneo;
    public $status;



    public function mount($slug, $modalidad)
    {
        $this->licenciatura = Licenciatura::where('slug', $slug)->firstOrFail();

        $this->slug = $slug;
        $this->modalidad = $modalidad;


    }


    public function guardarEstudiante()
    {
        $this->validate([
            'matricula' => 'required|string|max:8',
            'CURP' => 'required|string|max:18',
            'foraneo' => 'required',
            'status' => 'required',



        ],[
            'matricula.required' => 'El campo matrícula es obligatorio.',
            'matricula.string' => 'El campo matrícula debe ser una cadena de texto.',
            'matricula.max' => 'El campo matrícula no puede tener más de 255 caracteres.',
            'CURP.required' => 'El campo CURP es obligatorio.',
            'CURP.string' => 'El campo CURP debe ser una cadena de texto.',

            'CURP.max' => 'El campo CURP no puede tener más de 18 caracteres.',
            'foraneo.required' => 'El campo foráneo es obligatorio.',
            'foraneo.boolean' => 'El campo foráneo debe ser verdadero o falso.',
            'status.required' => 'El campo status es obligatorio.',


        ]);

        // Aquí puedes guardar la información del estudiante en la base de datos
        // ...

    }

    public function render()
    {
        $acciones = Accion::all();
        return view('livewire.admin.licenciaturas.submodulo.inscripcion', compact('acciones'));
    }
}
