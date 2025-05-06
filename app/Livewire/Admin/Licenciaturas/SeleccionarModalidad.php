<?php

namespace App\Livewire\Admin\Licenciaturas;

use App\Models\Licenciatura;
use App\Models\Modalidad;
use Livewire\Component;

class SeleccionarModalidad extends Component
{
    public $licenciatura;

    public function mount($slug)
    {
        $this->licenciatura = Licenciatura::where('slug', $slug)->firstOrFail();
    }

    public function irAModalidad($modalidad)
    {
        return redirect()->route('licenciaturas.submodulo', [
            'slug' => $this->licenciatura->slug,
            'modalidad' => $modalidad,
            'submodulo' => 'inscripcion',
        ]);
    }


    public function render()
    {
        $modalidades = Modalidad::all();
        return view('livewire.admin.licenciaturas.seleccionar-modalidad', compact('modalidades'));

    }
}
