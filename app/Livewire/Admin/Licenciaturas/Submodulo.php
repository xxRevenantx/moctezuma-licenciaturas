<?php

namespace App\Livewire\Admin\Licenciaturas;

use App\Models\Licenciatura;
use Livewire\Component;

class Submodulo extends Component
{


    public $licenciatura;
    public $modalidad;
    public $submodulo;

    public function mount($slug, $modalidad, $submodulo)
    {
        $this->licenciatura = Licenciatura::where('slug', $slug)->firstOrFail();
        $this->modalidad = $modalidad;
        $this->submodulo = $submodulo;
    }

    public function render()
    {
        return view("livewire.admin.licenciaturas.submodulo.{$this->submodulo}", [
            'licenciatura' => $this->licenciatura,
            'modalidad' => $this->modalidad
        ]);
    }
}
