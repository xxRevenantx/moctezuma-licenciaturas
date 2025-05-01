<?php

namespace App\Livewire\Admin\AsignarGeneracion;

use App\Models\AsignarGeneracion;
use Livewire\Attributes\On;
use Livewire\Component;

class EditarGeneracion extends Component
{
    public $asignacionId;
    public $licenciatura_id;
    public $modalidad_id;
    public $generacion_id;
    public $open = false;


    #[On('abrirAsignacion')]
    public function abrirModal($id)
    {
        $asignacion = AsignarGeneracion::findOrFail($id);
        $this->asignacionId = $asignacion->id;
        $this->licenciatura_id = $asignacion->licenciatura_id;
        $this->modalidad_id = $asignacion->modalidad_id;
        $this->generacion_id = $asignacion->generacion_id;
        $this->open = true;


    }

    public function actualizarAsignacion()
    {
        $this->validate([
            'licenciatura_id' => 'required|exists:licenciaturas,id',
            'modalidad_id' => 'required|exists:modalidades,id',
            'generacion_id' => 'required|exists:generaciones,id',
        ],[
            'licenciatura_id.required' => 'El campo licenciatura es obligatorio.',
            'licenciatura_id.exists' => 'La licenciatura seleccionada no es válida.',
            'modalidad_id.required' => 'El campo modalidad es obligatorio.',
            'modalidad_id.exists' => 'La modalidad seleccionada no es válida.',
            'generacion_id.required' => 'El campo generación es obligatorio.',
            'generacion_id.exists' => 'La generación seleccionada no es válida.',
        ]);

        // Verifica si ya existe una asignación para la combinación seleccionada
        $existingAssignment = AsignarGeneracion::where('licenciatura_id', $this->licenciatura_id)
            ->where('modalidad_id', $this->modalidad_id)
            ->where('generacion_id', $this->generacion_id)
            ->where('id', '!=', $this->asignacionId) // Excluye la asignación actual
            ->first();
        if ($existingAssignment) {
            $this->dispatch('swal', [
                'title' => 'Ya existe una asignación para esta combinación.',
                'icon' => 'error',
                'position' => 'top',
            ]);
            return;
        }

        $asignacion = AsignarGeneracion::findOrFail($this->asignacionId);
        $asignacion->update([
            'licenciatura_id' => $this->licenciatura_id,
            'modalidad_id' => $this->modalidad_id,
            'generacion_id' => $this->generacion_id,
        ]);

        $this->dispatch('refreshAsignacion');
        $this->dispatch('swal', [
            'title' => 'Asignación actualizada correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
        ]);



        $this->cerrarModal();
    }




    public function cerrarModal()
    {
        $this->reset(['open', 'asignacionId', 'licenciatura_id', 'modalidad_id', 'generacion_id']);
    }


    public function render()
    {
        $licenciaturas = \App\Models\Licenciatura::all();
        $generaciones = \App\Models\Generacion::all();
        $modalidades = \App\Models\Modalidad::all();
        return view('livewire.admin.asignar-generacion.editar-generacion', compact('licenciaturas', 'generaciones', 'modalidades'));
    }
}
