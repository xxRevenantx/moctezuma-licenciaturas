<?php

namespace App\Livewire\Admin\Licenciaturas\Submodulo;

use App\Models\AsignarGeneracion;
use App\Models\Cuatrimestre;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use App\Models\Modalidad;
use App\Models\Periodo;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class Matricula extends Component
{
    public $modalidad;
    public $licenciatura;
    public $submodulo;

    public $generaciones;

    use WithPagination;

    public $search = '';

    public $selected = [];
    public $selectAll = false;


    public $filtrar_generacion;


    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selected = Inscripcion::pluck('id')->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function cambiarEstudiantesSeleccionados()
    {
        if (count($this->selected) > 0) {
            Inscripcion::whereIn('id', $this->selected)
                ->update([
                    'cuatrimestre_id' => $this->cuatrimestre_id,

                ]);

            $this->dispatch('swal', [
                'title' => 'Estudiantes cambiados correctamente!',
                'icon' => 'success',
                'position' => 'top-end',
            ]);

            $this->selected = []; // Reset selected items
            $this->selectAll = false; // Reset select all checkbox
        } else {
            $this->dispatch('swal', [
                'title' => '¡No se han seleccionado estudiantes!',
                'icon' => 'warning',
                'position' => 'top-end',
            ]);
        }

        $this->dispatch('refreshMatricula');
    }


    public function updatingSearch()
    {
        $this->resetPage();
    }



     public function getMatriculaProperty()
    {
        $query = Inscripcion::with(['generacion', 'cuatrimestre']);

        if ($this->filtrar_generacion) {
            $query->where('generacion_id', $this->filtrar_generacion);
        }

        if ($this->search) {
            $query->where(function ($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('apellido_paterno', 'like', '%' . $this->search . '%')
                    ->orWhere('apellido_materno', 'like', '%' . $this->search . '%')
                    ->orWhere('matricula', 'like', '%' . $this->search . '%')
                    ->orWhere('CURP', 'like', '%' . $this->search . '%')
                    ->orWhere(function ($query) {
                        if (strtolower($this->search) === 'hombre') {
                            $query->where('sexo', 'H');
                        } elseif (strtolower($this->search) === 'mujer') {
                            $query->where('sexo', 'M');
                        } else {
                            $query->where('sexo', 'like', '%' . $this->search . '%');
                        }
                    });
                    $query->orWhereHas('cuatrimestre', function ($query) {
                        $query->where('nombre_cuatrimestre', 'like', '%' . $this->search . '%');
                    });

                    $query->orWhere(function ($query) {
                        if (strtolower($this->search) === 'activo') {
                            $query->where('status', 'true');
                        } elseif (strtolower($this->search) === 'Baja') {
                            $query->where('status', 'false');
                        } else {
                            $query->where('status', 'like', '%' . $this->search . '%');
                        }
                    });


            });
        }



        return $query
             ->where('modalidad_id', $this->modalidad->id)
            ->orderBy('apellido_paterno', 'asc')
            ->orderBy('apellido_materno', 'asc')
            ->orderBy('nombre', 'asc')
            ->paginate(10);
    }




    public function mount($licenciatura, $modalidad, $submodulo)
    {
        $this->licenciatura = Licenciatura::where('slug', $licenciatura)->firstOrFail();
        $this->modalidad = Modalidad::where('slug', $modalidad)->firstOrFail();

        $this->generaciones = AsignarGeneracion::where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->whereHas('generacion', function ($query) {
            $query->where('activa', "true");
            })
            ->get();

    }


    // ELIMINAR ESTUDIANTE
        public function eliminarEstudiante($id)
        {
          $this->selected = []; // Reset selected items
        $this->selectAll = false; // Reset select all checkbox

        $alumno = Inscripcion::find($id);

        if ($alumno) {
            $alumno->delete();

            $this->dispatch('swal', [
            'title' => '¡Alumno eliminado correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
            ]);
        }

         $this->dispatch('refreshMatricula');
    }



    // exportar alumnos excel
     public function exportarAlumnos()
    {

        $alumnosFiltrados = Inscripcion::with(['user', 'licenciatura', 'generacion', 'cuatrimestre', 'modalidad'])
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->where(function ($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('apellido_paterno', 'like', '%' . $this->search . '%')
                    ->orWhere('apellido_materno', 'like', '%' . $this->search . '%')
                    ->orWhere('matricula', 'like', '%' . $this->search . '%')
                    ->orWhere('CURP', 'like', '%' . $this->search . '%');
            })
            ->orderBy('apellido_paterno', 'asc')
            ->orderBy('apellido_materno', 'asc')
            ->orderBy('nombre', 'asc')
            ->get();

    // return Excel::download(new MatriculaExport($alumnosFiltrados), 'alumnos_filtrado.xlsx');
    }


    #[On('refreshMatricula')]
    public function render()
    {

        // $matricula = Inscripcion::with(['user', 'licenciatura', 'generacion', 'cuatrimestre', 'modalidad'])
        //     ->where('licenciatura_id', $this->licenciatura->id)
        //     ->where('modalidad_id', $this->modalidad->id)
        //     ->where(function ($query) {
        //         $query->where('nombre', 'like', '%' . $this->search . '%')
        //             ->orWhere('apellido_paterno', 'like', '%' . $this->search . '%')
        //             ->orWhere('apellido_materno', 'like', '%' . $this->search . '%')
        //             ->orWhere('matricula', 'like', '%' . $this->search . '%')
        //             ->orWhere('CURP', 'like', '%' . $this->search . '%');
        //     })
        //     ->orderBy('apellido_paterno', 'asc')
        //     ->orderBy('apellido_materno', 'asc')
        //     ->orderBy('nombre', 'asc')
        //     ->paginate(10);


        return view('livewire.admin.licenciaturas.submodulo.matricula', [
              'matricula' => $this->matricula,
        ]);
    }
}
