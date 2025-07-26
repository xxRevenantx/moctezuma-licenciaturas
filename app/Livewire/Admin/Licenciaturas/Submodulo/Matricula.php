<?php

namespace App\Livewire\Admin\Licenciaturas\Submodulo;

use App\Exports\MatriculaExport;
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

use Barryvdh\DomPDF\Facade\Pdf;


class Matricula extends Component
{
    public $modalidad;
    public $licenciatura;
    public $submodulo;

    public $generaciones;
    public $cuatrimestres;

    use WithPagination;

    public $search = '';

    public $selected = [];
    public $selectAll = false;

    public $filtrar_generacion;
    public $filtrar_foraneo;

    public $contar_mujeres;
    public $contar_hombres;


    public function updatedSelectAll($value)
{
    if ($value) {
        $query = Inscripcion::query()
            ->where('modalidad_id', $this->modalidad->id)
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('status', 'true')
            ->where('generacion_id', $this->filtrar_generacion);

        if ($this->filtrar_generacion) {
            $query->where('generacion_id', $this->filtrar_generacion);

        }

        if (($this->filtrar_foraneo)) {
            $query->where('foraneo', $this->filtrar_foraneo);
        }

        if ($this->search) {
            $query->where(function ($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('apellido_paterno', 'like', '%' . $this->search . '%')
                    ->orWhere('apellido_materno', 'like', '%' . $this->search . '%')
                    ->orWhere('matricula', 'like', '%' . $this->search . '%')
                    ->orWhere('CURP', 'like', '%' . $this->search . '%');
                    // ->orWhereHas('cuatrimestre', fn($q) => $q->where('nombre_cuatrimestre', 'like', '%' . $this->search . '%'));
            });
        }

        $this->selected = $query->pluck('id')->toArray();
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
    if (!$this->filtrar_generacion) {
        return collect(); // tabla vacía si no hay generación seleccionada
    }

    $query = Inscripcion::with(['generacion', 'cuatrimestre', 'licenciatura', 'modalidad'])
        ->where('modalidad_id', $this->modalidad->id)
        ->where('licenciatura_id', $this->licenciatura->id)
        ->where('generacion_id', $this->filtrar_generacion)
        ->where('status', 'true');
        ;

    if ($this->filtrar_foraneo) {
        $query->where('foraneo', $this->filtrar_foraneo);


    }


    if ($this->search) {
        $query->where(function ($query) {
            $query->where('nombre', 'like', '%' . $this->search . '%')
                ->orWhere('apellido_paterno', 'like', '%' . $this->search . '%')
                ->orWhere('apellido_materno', 'like', '%' . $this->search . '%')
                ->orWhere('matricula', 'like', '%' . $this->search . '%')
                ->orWhere('CURP', 'like', '%' . $this->search . '%');
                // ->orWhereHas('cuatrimestre', function ($q) {
                //     $q->where('nombre_cuatrimestre', 'like', '%' . $this->search . '%');
                // });
        });
    }

    return $query
        ->orderBy('apellido_paterno')
        ->orderBy('apellido_materno')
        ->orderBy('nombre')
        ->get(); // usa get() en lugar de paginate() si no vas a paginar
}


// CONTAR HOMBRES Y MUJERES
public function contarHombreMujeres(){
 $this->contar_mujeres = Inscripcion::where('generacion_id', $this->filtrar_generacion)
        ->where('modalidad_id', $this->modalidad->id)
        ->where('licenciatura_id', $this->licenciatura->id)
        ->where('sexo', 'M')
        ->count();

    $this->contar_hombres = Inscripcion::where('generacion_id', $this->filtrar_generacion)

        ->where('modalidad_id', $this->modalidad->id)
        ->where('licenciatura_id', $this->licenciatura->id)
        ->where('sexo', 'H')
        ->count();
}




public function updatedFiltrarGeneracion()
{
    $this->limpiarSeleccionados();
    $this->resetPage();
    $this->contarHombreMujeres();

    $this->cuatrimestres = Periodo::where('generacion_id', $this->filtrar_generacion)->get();

   $this->dispatch('cerrarModalPdf');
}




// CAMBIAR DE CUATRIMESTRE

public function cambiarCuatrimestreSeleccionados($id){



    if (count($this->selected) > 0) {
        Inscripcion::whereIn('id', $this->selected)
            ->update([
                'cuatrimestre_id' => $id,
            ]);

        $this->dispatch('swal', [
            'title' => '¡Estudiantes cambiados correctamente!',
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

   // LIMPIAR FILTROS
 public function limpiarFiltros()
{
    $this->search = '';
    $this->filtrar_generacion = null;
    $this->filtrar_foraneo = null;
    $this->limpiarSeleccionados();

   $this->dispatch('cerrarModalPdf');
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


    public function limpiarSeleccionados(){
         $this->selected = []; // Reset selected items
         $this->selectAll = false; // Reset select all checkbox
    }


    // ELIMINAR ESTUDIANTE
        public function eliminarEstudiante($id)
        {
          $this->limpiarSeleccionados();

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
    public function exportarMatricula()
    {
        $query = Inscripcion::with(['user', 'licenciatura', 'generacion', 'cuatrimestre', 'modalidad'])
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('generacion_id', $this->filtrar_generacion);

        if (!empty($this->selected)) {
            // Filtrar solo los seleccionados si hay seleccionados
            $query->whereIn('id', $this->selected);
        } else {
            // Aplicar filtros generales si no hay seleccionados
            $query->where(function ($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('apellido_paterno', 'like', '%' . $this->search . '%')
                    ->orWhere('apellido_materno', 'like', '%' . $this->search . '%')
                    ->orWhere('matricula', 'like', '%' . $this->search . '%')
                    ->orWhere('CURP', 'like', '%' . $this->search . '%');
            });
        }

        $alumnosFiltrados = $query
            ->orderBy('apellido_paterno', 'asc')
            ->orderBy('apellido_materno', 'asc')
            ->orderBy('nombre', 'asc')
            ->get();

        return Excel::download(new MatriculaExport($alumnosFiltrados), 'matricula.xlsx');
    }


    // exportar alumnos pdf
   public function exportarMatriculaPDF()
    {
        $query = Inscripcion::with(['user', 'licenciatura', 'generacion', 'cuatrimestre', 'modalidad'])
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->where('generacion_id', $this->filtrar_generacion);

        if (!empty($this->selected)) {
            $query->whereIn('id', $this->selected);
        } else {
            $query->where(function ($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('apellido_paterno', 'like', '%' . $this->search . '%')
                    ->orWhere('apellido_materno', 'like', '%' . $this->search . '%')
                    ->orWhere('matricula', 'like', '%' . $this->search . '%')
                    ->orWhere('CURP', 'like', '%' . $this->search . '%');
            });
        }

        $alumnosFiltrados = $query
            ->orderBy('apellido_paterno', 'asc')
            ->orderBy('apellido_materno', 'asc')
            ->orderBy('nombre', 'asc')
            ->get();

        $alumnosSanitizados = $alumnosFiltrados->map(function ($alumno) {
            $array = $alumno->toArray();

            array_walk_recursive($array, function (&$value) {
                if (is_string($value)) {
                    $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                }
            });

            return $array;
        });

        $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.matriculaPDF', [
            'alumnos' => $alumnosSanitizados,
        ])->setPaper('letter', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Matrícula.pdf');
    }




    #[On('refreshMatricula')]
    public function render()
    {



        return view('livewire.admin.licenciaturas.submodulo.matricula', [
              'matricula' => $this->matricula,
        ]);
    }
}
