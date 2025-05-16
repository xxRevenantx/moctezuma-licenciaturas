<?php

namespace App\Livewire\Admin\Licenciaturas;

use App\Models\Inscripcion;
use App\Models\Licenciatura;
use App\Models\Modalidad;
use Livewire\Attributes\On;
use Livewire\Component;

class Navbar extends Component
{


    public $licenciatura_id;
    public $modalidad_id;

    public $slug_licenciatura;
    public $slug_modalidad;
    public $acciones;

    public $contar_bajas = 0;


    public function mount($slug_licenciatura, $slug_modalidad, $acciones)
    {

        $this->licenciatura_id = Licenciatura::where('slug', $slug_licenciatura)->first()->id;
        $this->modalidad_id = Modalidad::where('slug', $slug_modalidad)->first()->id;

        $this->slug_licenciatura = $slug_licenciatura;
        $this->slug_modalidad = $slug_modalidad;



    }






    #[On('refreshNavbar')]
    public function render()
    {
        $this->contar_bajas = Inscripcion::where('licenciatura_id', $this->licenciatura_id)
        ->where('modalidad_id', $this->modalidad_id)
        ->where('status', 'false')
        ->count();

        return view('livewire.admin.licenciaturas.navbar');
    }
}
