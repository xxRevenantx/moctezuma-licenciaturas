<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class Header extends Component
{
    public $dashboard;


    #[On('refreshHeader')]
    public function mount()
    {
        // Aquí puedes inicializar cualquier propiedad o lógica que necesites
        // Por ejemplo, si necesitas cargar datos de un modelo específico
        $this->dashboard = \App\Models\Dashboard::latest('id')->first();
    }

    public function render()
    {
        return view('livewire.header');
    }
}
