<?php

namespace App\Livewire;

use App\Models\Licenciatura;
use Livewire\Attributes\On;
use Livewire\Component;

class Sidebar extends Component
{


#[On("refreshLicenciaturas")]
    public function render()
    {
        $licenciaturas = Licenciatura::all();
        return view('livewire.sidebar', compact('licenciaturas'));
    }
}
