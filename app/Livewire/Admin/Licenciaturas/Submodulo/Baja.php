<?php

namespace App\Livewire\Admin\Licenciaturas\Submodulo;

use App\Models\Inscripcion;
use App\Models\Periodo;
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
    if (!$this->filtrar_generacion) {
        return collect(); // tabla vacía si no hay generación seleccionada
    }

    $query = Inscripcion::with(['generacion', 'cuatrimestre', 'licenciatura', 'modalidad'])
        ->where('modalidad_id', $this->modalidad->id)
        ->where('licenciatura_id', $this->licenciatura->id)
        ->where('generacion_id', $this->filtrar_generacion);

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

      $this->cuatrimestres = Periodo::where('generacion_id', $this->filtrar_generacion)
            ->get();
}


    public function render()
    {


        return view('livewire.admin.licenciaturas.submodulo.baja', [
            'baja' => $this->baja,
        ]);
    }
}
