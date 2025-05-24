<?php

namespace App\Livewire\Admin\Licenciaturas\Submodulo;

use App\Models\Horario;
use App\Models\Licenciatura;
use App\Models\Modalidad;
use Livewire\Attributes\On;
use Livewire\Component;

class HorarioEscolarizada extends Component
{

    public $modalidad;
    public $licenciatura;


    public $horasDisponibles = [];
    public $horarios = [];
    public $hora;

    public function mount($modalidad, $licenciatura)
    {
        $this->licenciatura = Licenciatura::where('slug', $licenciatura)->firstOrFail();
        $this->modalidad = Modalidad::where('slug', $modalidad)->firstOrFail();

        $this->horasDisponibles = [
            "8:00am-9:00am",
            "9:00am-10:00am",
            "10:00am-10:30am",
            "10:30am-11:30am",
            "11:30am-12:30pm",
            "12:30pm-1:30pm",
            "1:30pm-2:30pm",
            "2:30pm-3:30pm",
        ];

        // Obtener los horarios y convertirlos a un arreglo simple
    $horarios = Horario::all();
    foreach ($horarios as $horario) {
        $this->horarios[$horario->id] = [
            'id' => $horario->id,
            'hora' => $horario->hora,
            'lunes' => $horario->lunes,
            'martes' => $horario->martes,
            'miercoles' => $horario->miercoles,
            'jueves' => $horario->jueves,
            'viernes' => $horario->viernes,
            'group_id' => $horario->group_id, // <-- Agregado
            'observacion' => $horario->observacion,
        ];
    }




    }

    // Guardar Hora
    public function guardarHora()
    {
        $this->validate([
            'hora' => 'required',
        ],[
            'hora.required' => 'La hora es requerida',
        ]);
        // Verificar si la hora es válida
        if (!in_array($this->hora, $this->horasDisponibles)) {
            $this->dispatch('swal', [
                'title' => 'La hora no es válida',
                'icon' => 'error',
                'position' => 'top',
            ]);
            return;
        }

        // Verificar si la hora ya existe para la misma licenciatura y modalidad
        if (Horario::where('hora', $this->hora)
            ->where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->exists()) {
            $this->dispatch('swal', [
            'title' => 'La hora ya está asignada en esta licenciatura y modalidad',
            'icon' => 'error',
            'position' => 'top',
            ]);
            return;
        }

        // Guardar la hora en la base de datos
        Horario::create([
            'hora' => $this->hora,
        ]);
        // Limpiar el campo
        $this->hora = null;
        // Mostrar mensaje de éxito
       $this->dispatch('swal', [
            'title' => 'Hora asignada',
            'icon' => 'success',
            'position' => 'top-end',
        ]);

        // Emitir evento para actualizar la lista de horas
        $this->dispatch('refresh-tabla');

    }


    #[On('refresh-tabla')]
    public function render()
    {
        return view('livewire.admin.licenciaturas.submodulo.horario-escolarizada');
    }
}
