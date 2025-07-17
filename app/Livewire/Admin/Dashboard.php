<?php

namespace App\Livewire\Admin;

use App\Models\Dashboard as ModelsDashboard;
use Livewire\Component;
use App\Helpers\Flash;
use App\Models\Generacion;
use App\Models\Inscripcion;
use App\Models\Profesor;

class Dashboard extends Component
{
    public $ciclo_escolar;
    public $periodo_escolar;

    public $licenciaturas;

    public $resumenPorLicenciatura = [];
    public $resumenPorLicenciaturaBaja = [];

    public $totalLocalesActivos;
    public $totalHombresLocalesActivos;
    public $totalMujeresLocalesActivos;


    public $totalLocalesBaja;
    public $totalHombresLocalesBaja;
    public $totalMujeresLocalesBaja;


    public $resumenPorLicenciaturaForaneo = [];
    public $resumenPorLicenciaturaBajaForaneo = [];

    public $totalForaneosActivos;
    public $totalHombresForaneosActivos;
    public $totalMujeresForaneosActivos;


    public $totalForaneosBaja;
    public $totalHombresForaneosBaja;
    public $totalMujeresForaneosBaja;

    public $generacionesActivas;
    public $profesoresActivos;



    public function guardarDatos()
    {
        $this->validate([
            'ciclo_escolar' => 'required',
            'periodo_escolar' => 'required',
        ]);

        // Siempre usa el registro con ID = 1 o crea uno nuevo si no existe
        ModelsDashboard::create(
            [
                'ciclo_escolar' => trim($this->ciclo_escolar),
                'periodo_escolar' => trim($this->periodo_escolar)
            ]
        );


        Flash::success('Datos guardados correctamente');
        $this->dispatch("refreshHeader");
    }

    public function mount()
    {
        $dashboard = ModelsDashboard::latest('id')->first(); // Obtenemos el último registro por ID
        $this->ciclo_escolar = $dashboard->ciclo_escolar ?? '';
        $this->periodo_escolar = $dashboard->periodo_escolar ?? '';

        $this->licenciaturas = \App\Models\Licenciatura::all() ?? '';

        $this->generacionesActivas = Generacion::where('activa', 'true')->get();

        $this->profesoresActivos = Profesor::whereHas('user', function ($query) {
            $query->where('status', 'true');
        })->get();

        $this->resumenPorLicenciatura = $this->licenciaturas->map(function ($licenciatura) {

            $hombres = Inscripcion::where('licenciatura_id', $licenciatura->id)
                ->where('foraneo', "false")
                ->where('status', "true")
                ->where('sexo', 'H')
                ->get()
                ->filter(function ($inscripcion) {
                    return $inscripcion->generacion && $inscripcion->generacion->activa == "true";
                })
                ->count();

            $mujeres = Inscripcion::where('licenciatura_id', $licenciatura->id)
                ->where('foraneo', "false")
                ->where('status', "true")
                ->where('sexo', 'M')
                ->get()
                ->filter(function ($inscripcion) {
                    return $inscripcion->generacion && $inscripcion->generacion->activa == "true";
                })
                ->count();

            return [
                'licenciatura' => $licenciatura->nombre,
                'hombres' => $hombres,
                'mujeres' => $mujeres,
                'total' => $hombres + $mujeres
            ];
        });

        $this->totalLocalesActivos = Inscripcion::where('foraneo', "false")
            ->where('status', "true")
            ->get()
            ->filter(function ($inscripcion) {
            return $inscripcion->generacion && $inscripcion->generacion->activa == "true";
            })
            ->count();

        $this->totalHombresLocalesActivos = Inscripcion::where('foraneo', "false")
            ->where('status', "true")
            ->where('sexo', 'H')
            ->get()
            ->filter(function ($inscripcion) {
            return $inscripcion->generacion && $inscripcion->generacion->activa == "true";
            })
            ->count();

        $this->totalMujeresLocalesActivos = Inscripcion::where('foraneo', "false")
            ->where('status', "true")
            ->where('sexo', 'M')
            ->get()
            ->filter(function ($inscripcion) {
            return $inscripcion->generacion && $inscripcion->generacion->activa == "true";
            })
            ->count();


        $this->resumenPorLicenciaturaBaja = $this->licenciaturas->map(function ($licenciatura) {

            $hombres = Inscripcion::where('licenciatura_id', $licenciatura->id)
                ->where('foraneo', "false")
                ->where('status', "false")
                ->where('sexo', 'H')
                ->get()
                ->filter(function ($inscripcion) {
                    return $inscripcion->generacion && $inscripcion->generacion->activa == "true";
                })
                ->count();

            $mujeres = Inscripcion::where('licenciatura_id', $licenciatura->id)
                ->where('foraneo', "false")
                ->where('status', "false")
                ->where('sexo', 'M')
                ->get()
                ->filter(function ($inscripcion) {
                    return $inscripcion->generacion && $inscripcion->generacion->activa == "true";
                })
                ->count();

                return [
                    'licenciatura' => $licenciatura->nombre,
                    'hombres' => $hombres,
                    'mujeres' => $mujeres,
                    'total' => $hombres + $mujeres
                ];
            });


        $this->totalLocalesBaja = Inscripcion::where('foraneo', "false")
            ->where('status', "false")
            ->get()
            ->filter(function ($inscripcion) {
                    return $inscripcion->generacion && $inscripcion->generacion->activa == "true";
                })
            ->count();

        $this->totalHombresLocalesBaja = Inscripcion::where('foraneo', "false")
            ->where('status', "false")
            ->where('sexo', 'H')
            ->get()
            ->filter(function ($inscripcion) {
                    return $inscripcion->generacion && $inscripcion->generacion->activa == "true";
                })
            ->count();

        $this->totalMujeresLocalesBaja = Inscripcion::where('foraneo', "false")
        ->where('status', "false")
        ->where('sexo', 'M')
        ->get()
        ->filter(function ($inscripcion) {
                return $inscripcion->generacion && $inscripcion->generacion->activa == "true";
            })
        ->count();









        $this->alumnosForeaneos();

    }


    public function alumnosForeaneos(){
         $this->resumenPorLicenciaturaForaneo = $this->licenciaturas->map(function ($licenciatura) {

            $hombres = Inscripcion::where('licenciatura_id', $licenciatura->id)
                ->where('foraneo', "true")
                ->where('status', "true")
                ->where('sexo', 'H')
                ->get()
                ->filter(function ($inscripcion) {
                    return $inscripcion->generacion && $inscripcion->generacion->activa == "true";
                })
                ->count();

            $mujeres = Inscripcion::where('licenciatura_id', $licenciatura->id)
                ->where('foraneo', "true")
                ->where('status', "true")
                ->where('sexo', 'M')
                ->get()
                ->filter(function ($inscripcion) {
                    return $inscripcion->generacion && $inscripcion->generacion->activa == "true";
                })
                ->count();

            return [
                'licenciatura' => $licenciatura->nombre,
                'hombres' => $hombres,
                'mujeres' => $mujeres,
                'total' => $hombres + $mujeres
            ];
        });

        $this->totalForaneosActivos = Inscripcion::where('foraneo', "true")
            ->where('status', "true")
            ->get()
            ->filter(function ($inscripcion) {
            return $inscripcion->generacion && $inscripcion->generacion->activa == "true";
            })
            ->count();

        $this->totalHombresForaneosActivos = Inscripcion::where('foraneo', "true")
            ->where('status', "true")
            ->where('sexo', 'H')
            ->get()
            ->filter(function ($inscripcion) {
            return $inscripcion->generacion && $inscripcion->generacion->activa == "true";
            })
            ->count();

        $this->totalMujeresForaneosActivos = Inscripcion::where('foraneo', "true")
            ->where('status', "true")
            ->where('sexo', 'M')
            ->get()
            ->filter(function ($inscripcion) {
            return $inscripcion->generacion && $inscripcion->generacion->activa == "true";
            })
            ->count();


        $this->resumenPorLicenciaturaBajaForaneo = $this->licenciaturas->map(function ($licenciatura) {

            $hombres = Inscripcion::where('licenciatura_id', $licenciatura->id)
                ->where('foraneo', "true")
                ->where('status', "false")
                ->where('sexo', 'H')
                ->get()
                ->filter(function ($inscripcion) {
                    return $inscripcion->generacion && $inscripcion->generacion->activa == "true";
                })
                ->count();

            $mujeres = Inscripcion::where('licenciatura_id', $licenciatura->id)
                ->where('foraneo', "true")
                ->where('status', "false")
                ->where('sexo', 'M')
                ->get()
                ->filter(function ($inscripcion) {
                    return $inscripcion->generacion && $inscripcion->generacion->activa == "true";
                })
                ->count();

                return [
                    'licenciatura' => $licenciatura->nombre,
                    'hombres' => $hombres,
                    'mujeres' => $mujeres,
                    'total' => $hombres + $mujeres
                ];
            });


        $this->totalForaneosBaja = Inscripcion::where('foraneo', "true")
            ->where('status', "false")
            ->get()
            ->filter(function ($inscripcion) {
                    return $inscripcion->generacion && $inscripcion->generacion->activa == "true";
                })
            ->count();

        $this->totalHombresForaneosBaja = Inscripcion::where('foraneo', "true")
            ->where('status', "false")
            ->where('sexo', 'H')
            ->get()
            ->filter(function ($inscripcion) {
                    return $inscripcion->generacion && $inscripcion->generacion->activa == "true";
                })
            ->count();

        $this->totalMujeresForaneosBaja = Inscripcion::where('foraneo', "true")
            ->where('status', "false")
            ->where('sexo', 'M')
            ->get()
            ->filter(function ($inscripcion) {
                return $inscripcion->generacion && $inscripcion->generacion->activa == "true";
            })
            ->count();

    }



    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
