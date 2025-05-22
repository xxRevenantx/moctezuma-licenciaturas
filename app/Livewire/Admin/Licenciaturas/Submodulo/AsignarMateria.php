<?php

namespace App\Livewire\Admin\Licenciaturas\Submodulo;

use App\Models\AsignacionMateria;
use App\Models\Licenciatura;
use App\Models\Materia;
use App\Models\Modalidad;
use App\Models\Profesor;
use Livewire\Component;

class AsignarMateria extends Component
{

    use \Livewire\WithPagination;

    public $search = '';
    public $modalidad;
    public $licenciatura;
    public $submodulo;
    public $profesores;

    public $profesor_seleccionado = [];


     public function getMateriasProperty()
    {
        $query = Materia::with(['cuatrimestre', 'licenciatura'])
        ->where('licenciatura_id', $this->licenciatura->id);


        // if ($this->filtrar_licenciatura) {
        //     $query->where('licenciatura_id', $this->filtrar_licenciatura);
        // }


        // if ($this->filtrar_cuatrimestre) {
        //     $query->where('cuatrimestre_id', $this->filtrar_cuatrimestre);
        // }


        // if ($this->filtrar_calificable == 'true') {
        //     $query->where('calificable', "true");
        // } elseif ($this->filtrar_calificable == 'false') {
        //     $query->where('calificable', "false");
        // }




        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', '%' . trim($this->search) . '%');
            });

        }

        return $query
            ->orderBy('licenciatura_id', 'asc')
            ->orderBy('cuatrimestre_id', 'asc')
            ->orderBy('clave', 'asc')
            ->paginate(20);
    }


    // ASIGNAR PROFESOR A MATERIA
    public function updatedProfesorSeleccionado($profesor_id, $materia_id)
    {
        $materia = Materia::findOrFail($materia_id);
        $cuatrimestre_id = $materia ? $materia->cuatrimestre_id : null;

            AsignacionMateria::updateOrCreate(
                [
                    'materia_id' => $materia_id,
                    'licenciatura_id' => $this->licenciatura->id,
                    'modalidad_id' => $this->modalidad->id,
                    'cuatrimestre_id' => $cuatrimestre_id,
                ],
                [
                    'profesor_id' => $profesor_id,
                ]
            );

             $this->dispatch('swal', [
            'title' => '¡Materia asignada correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
         ]);

        }



     public function mount($licenciatura, $modalidad, $submodulo)
    {

        $this->licenciatura = Licenciatura::where('slug', $licenciatura)->firstOrFail();
        $this->modalidad = Modalidad::where('slug', $modalidad)->firstOrFail();

        $this->profesores = Profesor::orderBy('Apellido_paterno', 'asc')
            ->orderBy('Apellido_materno', 'asc')
            ->orderBy('Nombre', 'asc')
            ->get();



    }

    public function cargarProfesoresAsignados($materias)
    {
        foreach ($materias as $materia) {
            $asignacion = \App\Models\AsignacionMateria::where([
                'materia_id' => $materia->id,
                'licenciatura_id' => $this->licenciatura->id,
                'modalidad_id' => $this->modalidad->id,
                'cuatrimestre_id' => $materia->cuatrimestre_id,
            ])->first();

            $this->profesor_seleccionado[$materia->id] = $asignacion ? $asignacion->profesor_id : '';
        }
    }



    public function render()
    {

           $materias = $this->materias; // Así tienes la colección de materias de la página actual

         $this->cargarProfesoresAsignados($materias);

        return view('livewire.admin.licenciaturas.submodulo.asignar-materia', [
             'materias' => $materias,

        ]);
    }
}
