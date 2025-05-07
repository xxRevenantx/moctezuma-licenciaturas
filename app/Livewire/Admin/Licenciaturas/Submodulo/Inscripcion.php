<?php

namespace App\Livewire\Admin\Licenciaturas\Submodulo;


use App\Models\Accion;
use App\Models\AsignarGeneracion;
use App\Models\Generacion;
use App\Models\Licenciatura;
use App\Models\Modalidad;
use App\Models\Periodo;
use App\Models\User;
use Livewire\Component;
use Nnjeim\World\World;

class Inscripcion extends Component
{

    public $slug;
    public $modalidad;
    public $licenciatura;

    public $usuarios;
    public $usuario_id;
    public $generacion_id;
    public $cuatrimestre_id;

    public $matricula;
    public $CURP;

    public $foraneo;
    public $status;

    public $generaciones ;
    public $cuatrimestres = [];





    public function mount($licenciatura, $modalidad)
    {

        $this->licenciatura = Licenciatura::where('slug', $licenciatura)->firstOrFail();
        $this->modalidad = Modalidad::where('slug', $modalidad)->firstOrFail();


        $this->usuarios = User::role('Estudiante')->get();



        $this->generaciones = AsignarGeneracion::where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->whereHas('generacion', function ($query) {
            $query->where('activa', "true");
            })
            ->get();



        // $this->generaciones = Generacion::all();



    }




    public function updated($propertyName)
    {

          // Validar solo si el campo tiene valor
        //   $this->validateOnly($propertyName);

            if ($propertyName === 'usuario_id') {
                $this->usuarios = User::role('Estudiante')->get();
            }



            if ($propertyName === 'generacion_id') {
                $this->cuatrimestres = Periodo::where('generacion_id', $this->generacion_id)
                ->limit(1)
                ->orderBy('id', 'desc')
                    ->get();

                // $generation = Generation::find($this->generation_id);
                // $this->generacion_nombre = $generation->anio_inicio . ' - ' . $generation->anio_termino;
            }



    }

    public function guardarEstudiante()
    {
        $this->validate([
            'usuario_id' => 'required|exists:users,id',



        ],[
            'usuario_id.required' => 'El campo usuario es obligatorio.',
            'usuario_id.exists' => 'El usuario seleccionado no existe.',
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
