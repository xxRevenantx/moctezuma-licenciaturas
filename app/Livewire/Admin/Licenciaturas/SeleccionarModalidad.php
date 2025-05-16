<?php

namespace App\Livewire\Admin\Licenciaturas;

use App\Models\Inscripcion;
use App\Models\Licenciatura;
use App\Models\Modalidad;
use Livewire\Component;

class SeleccionarModalidad extends Component
{
    public $licenciatura;
    public $hombres;
    public $mujeres;

    public $modalidades;

    public function mount($slug)
    {
        $this->licenciatura = Licenciatura::where('slug', $slug)->firstOrFail();
        $this->modalidades = Modalidad::all();




    }

    public function irAModalidad($modalidad_slug)
    {
        return redirect()->route('licenciaturas.submodulo', [
            'slug_licenciatura' => $this->licenciatura->slug,
            'slug_modalidad' => $modalidad_slug,
            'submodulo' => 'inscripcion',
        ]);
    }

    public function obtenerEstadisticasPorModalidad($modalidad)
{
    // Cargar las inscripciones de esta modalidad y licenciatura con la generación relacionada
    $inscripciones = $modalidad->inscripcion()
        ->with('generacion') // para usar generacion->activa sin N+1
        ->where('licenciatura_id', $this->licenciatura->id)
        ->get()
        ->filter(function ($inscripcion) {
            return $inscripcion->generacion && $inscripcion->generacion->activa == "true";
        });

    $total = $inscripciones->count();
    $hombres = $inscripciones->where('sexo', 'H')->where('status', 'true')->count();
    $mujeres = $inscripciones->where('sexo', 'M')->where('status', 'true')->count();

    return compact('total', 'hombres', 'mujeres');
}


    public function render()
    {
        return view('livewire.admin.licenciaturas.seleccionar-modalidad');

    }
}
