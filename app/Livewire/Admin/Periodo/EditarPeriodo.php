<?php

namespace App\Livewire\Admin\Periodo;

use App\Models\Cuatrimestre;
use App\Models\Generacion;
use App\Models\Mes;
use App\Models\Periodo;
use Livewire\Attributes\On;
use Livewire\Component;

class EditarPeriodo extends Component
{

    public $periodoId;
    public $ciclo_escolar;
    public $cuatrimestre_id;
    public $generacion_id;
    public $mes_id;
    public $inicio_periodo;
    public $termino_periodo;


    public $open = false;

    #[On('abrirPeriodo')]
    public function abrirModal($id)
    {
        $periodo = Periodo::findOrFail($id);
        $this->periodoId = $periodo->id;
        $this->ciclo_escolar = $periodo->ciclo_escolar;
        $this->cuatrimestre_id = $periodo->cuatrimestre_id;
        $this->generacion_id = $periodo->generacion_id;
        $this->mes_id = $periodo->mes_id;
        $this->inicio_periodo = $periodo->inicio_periodo;
        $this->termino_periodo = $periodo->termino_periodo;
        $this->open = true;
    }


    public function actualizarPeriodo()
    {
        $this->validate([
            'ciclo_escolar' => 'required|string|max:9',
            'cuatrimestre_id' => 'required|exists:cuatrimestres,id',
            'generacion_id' => 'required|exists:generaciones,id',
            'mes_id' => 'required|exists:meses,id',
            'inicio_periodo' => 'nullable|date',
            'termino_periodo' => 'nullable|date|after_or_equal:inicio_periodo',
        ], [
            'ciclo_escolar.required' => 'El ciclo escolar es obligatorio.',
            'ciclo_escolar.string' => 'El ciclo escolar debe ser una cadena de texto.',
            'ciclo_escolar.max' => 'El ciclo escolar no puede tener más de 9 caracteres.',
            'cuatrimestre_id.required' => 'El cuatrimestre es obligatorio.',
            'cuatrimestre_id.exists' => 'El cuatrimestre seleccionado no es válido.',
            'generacion_id.required' => 'La generación es obligatoria.',
            'generacion_id.exists' => 'La generación seleccionada no es válida.',
            'mes_id.required' => 'El mes es obligatorio.',
            'mes_id.exists' => 'El mes seleccionado no es válido.',
            'inicio_periodo.date' => 'La fecha de inicio debe ser una fecha válida.',

            'termino_periodo.date' => 'La fecha de término debe ser una fecha válida.',
            'termino_periodo.after_or_equal' => 'La fecha de término debe ser igual o posterior a la fecha de inicio.',
        ]);


        $periodo = Periodo::find($this->periodoId);
        if ($periodo) {
            $periodo->update([
                'ciclo_escolar' => trim($this->ciclo_escolar),
                'cuatrimestre_id' => $this->cuatrimestre_id,
                'generacion_id' => $this->generacion_id,
                'mes_id' => $this->mes_id,
                'inicio_periodo' => $this->inicio_periodo,
                'termino_periodo' => $this->termino_periodo ?: null,

            ]);

            $this->dispatch('swal', [
                'title' => '¡Periodo actualizado correctamente!',
                'icon' => 'success',
                'position' => 'top-end',
            ]);

            $this->reset(['open', 'ciclo_escolar', 'cuatrimestre_id', 'generacion_id', 'mes_id', 'inicio_periodo', 'termino_periodo']);
            $this->dispatch('refreshPeriodos');
        }
    }
    public function cerrarModal()
    {
        $this->reset([
            'open',
            'ciclo_escolar',
            'cuatrimestre_id',
            'generacion_id',
            'mes_id',
            'inicio_periodo',
            'termino_periodo',
        ]);
    }



    public function render()
    {
        $cuatrimestres = Cuatrimestre::all();
        $generaciones = Generacion::all();
        $meses = Mes::all();
        return view('livewire.admin.periodo.editar-periodo', compact('cuatrimestres', 'generaciones', 'meses'));
    }
}
