<?php

namespace App\Livewire\Admin\Periodo;

use App\Models\Cuatrimestre;
use App\Models\Generacion;
use App\Models\Mes;
use App\Models\Periodo;
use Livewire\Component;

class CrearPeriodo extends Component
{

    public $ciclo_escolar;
    public $cuatrimestre_id;
    public $generacion_id;
    public $mes_id;
    public $inicio_periodo;
    public $termino_periodo;




    public function crearPeriodo(){
        $this->validate([
            'ciclo_escolar' => 'required|string|max:9',
            'cuatrimestre_id' => 'required|exists:cuatrimestres,id',
            'generacion_id' => 'required|exists:generaciones,id',
            'mes_id' => 'required|exists:meses,id',
            'inicio_periodo' => 'nullable|date',
            'termino_periodo' => 'nullable|date|after_or_equal:inicio_periodo',
        ],[
            'ciclo_escolar.required' => 'El ciclo escolar es obligatorio.',
            'ciclo_escolar.string' => 'El ciclo escolar debe ser una cadena de texto.',
            'ciclo_escolar.max' => 'El ciclo escolar no puede tener más de 9 caracteres.',
            'cuatrimestre_id.required' => 'El cuatrimestre es obligatorio.',
            'cuatrimestre_id.exists' => 'El cuatrimestre seleccionado no es válido.',
            'generacion_id.required' => 'La generación es obligatoria.',
            'generacion_id.exists' => 'La generación seleccionada no es válida.',
            'mes_id.required' => 'El mes es obligatorio.',
            'mes_id.exists' => 'El mes seleccionado no es válido.',
            'inicio_periodo.required' => 'La fecha de inicio es obligatoria.',
            'inicio_periodo.date' => 'La fecha de inicio debe ser una fecha válida.',

            'termino_periodo.date' => 'La fecha de término debe ser una fecha válida.',
            'termino_periodo.after_or_equal' => 'La fecha de término debe ser igual o posterior a la fecha de inicio.',
        ]);

        // VERIFICA QUE NO EXISTA UN PERIODO CON EL MISMO CICLO ESCOLAR, CUATRIMESTRE, GENERACION, MES, INICIO_PERIODO Y TERMINO_PERIODO
        $periodoExistente = Periodo::where('ciclo_escolar', $this->ciclo_escolar)
            ->where('generacion_id', $this->generacion_id)
            ->where('cuatrimestre_id', $this->cuatrimestre_id)
            ->where('mes_id', $this->mes_id)
            ->first();

        if ($periodoExistente) {
            $this->dispatch('swal', [
                'title' => 'Ya se ha asignado un periodo con el mismo Ciclo Escolar, Cuatrimestre, Generacion y Meses.',
                'icon' => 'error',
                'position' => 'top',
            ]);
            return;
        }





        Periodo::create([
            'ciclo_escolar' => trim($this->ciclo_escolar),
            'cuatrimestre_id' => $this->cuatrimestre_id,
            'generacion_id' => $this->generacion_id,
            'mes_id' => $this->mes_id,
            'inicio_periodo' => $this->inicio_periodo,
            'termino_periodo' => $this->termino_periodo,
        ]);

        $this->reset([
            'ciclo_escolar',
            'cuatrimestre_id',
            'generacion_id',
            'mes_id',
            'inicio_periodo',
            'termino_periodo',
        ]);

        $this->dispatch('refreshPeriodos');

        $this->dispatch('swal', [
            'title' => 'Periodo creado correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
        ]);




    }

    public function render()
    {
        $cuatrimestres = Cuatrimestre::all();
        $generaciones = Generacion::all();
        $meses = Mes::all();

        return view('livewire.admin.periodo.crear-periodo', compact('cuatrimestres', 'generaciones', 'meses'));
    }
}
