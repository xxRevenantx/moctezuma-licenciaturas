<?php

namespace App\Livewire\Admin\Licenciaturas\Submodulo;

use App\Models\AsignarGeneracion;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use App\Models\Modalidad;
use App\Models\Periodo;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Baja extends Component
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



    public function getBajaProperty()
    {
        $query = Inscripcion::with(['generacion', 'cuatrimestre', 'licenciatura', 'modalidad'])
            ->where('modalidad_id', $this->modalidad->id)
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('status', 'false');

        if ($this->filtrar_generacion) {
            $query->where('generacion_id', $this->filtrar_generacion);
        }

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
            });
        }

        return $query
            ->orderBy('generacion_id')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->paginate(10);
    }

    public function limpiarFiltros()
    {
        $this->search = '';
        $this->filtrar_generacion = null;
        $this->filtrar_foraneo = null;
        $this->limpiarSeleccionados();
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

        $this->contarHombreMujeres();
    }

    public function limpiarSeleccionados()
    {
        $this->selected = [];
        $this->selectAll = false;
    }



    public function contarHombreMujeres()
    {

        if(!$this->filtrar_generacion){
            $this->contar_mujeres = Inscripcion::where('modalidad_id', $this->modalidad->id)
                ->where('licenciatura_id', $this->licenciatura->id)
                ->where('status', 'false')
                ->where('sexo', 'M')
                ->count();

            $this->contar_hombres = Inscripcion::where('modalidad_id', $this->modalidad->id)
                ->where('licenciatura_id', $this->licenciatura->id)
                ->where('status', 'false')
                ->where('sexo', 'H')
                ->count();
        }else{
            $this->contar_mujeres = Inscripcion::where('modalidad_id', $this->modalidad->id)
                ->where('licenciatura_id', $this->licenciatura->id)
                ->where('status', 'false')
                ->where('sexo', 'M')
                ->where('generacion_id', $this->filtrar_generacion)
                ->count();

            $this->contar_hombres = Inscripcion::where('modalidad_id', $this->modalidad->id)
                ->where('licenciatura_id', $this->licenciatura->id)
                ->where('status', 'false')
                ->where('sexo', 'H')
                ->where('generacion_id', $this->filtrar_generacion)
                ->count();
        }


    }

    public function updatedFiltrarGeneracion()
    {
        $this->limpiarSeleccionados();
        $this->resetPage();

        $this->contarHombreMujeres();

        $this->cuatrimestres = Periodo::where('generacion_id', $this->filtrar_generacion)->get();
    }


    public function switchStatus($id){

         if ($id) {
                Inscripcion::findOrFail($id)->update([
                    'status' => "true",
                    'fecha_baja' => NULL,
                ]);

                $this->dispatch('refreshNavbar');
                $this->dispatch('refreshBajas');

                $this->contarHombreMujeres();

            }

    }




    #[On('refreshBajas')]
    public function render()
    {


        return view('livewire.admin.licenciaturas.submodulo.baja', [
            'baja' => $this->baja,
        ]);
    }
}
