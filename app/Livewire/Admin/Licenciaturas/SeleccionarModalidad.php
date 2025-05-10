<?php

namespace App\Livewire\Admin\Licenciaturas;

use App\Livewire\Admin\Licenciaturas\Submodulo\Inscripcion;
use App\Models\Licenciatura;
use App\Models\Modalidad;
use Livewire\Component;

class SeleccionarModalidad extends Component
{
    public $licenciatura;
    public $hombres;
    public $mujeres;

    public function mount($slug)
    {
        $this->licenciatura = Licenciatura::where('slug', $slug)->firstOrFail();




    }

    public function irAModalidad($modalidad_slug)
    {
        return redirect()->route('licenciaturas.submodulo', [
            'slug_licenciatura' => $this->licenciatura->slug,
            'slug_modalidad' => $modalidad_slug,
            'submodulo' => 'inscripcion',
        ]);
    }


    public function render()
    {
        $modalidades = Modalidad::all();
        return view('livewire.admin.licenciaturas.seleccionar-modalidad', compact('modalidades'));

    }
}
