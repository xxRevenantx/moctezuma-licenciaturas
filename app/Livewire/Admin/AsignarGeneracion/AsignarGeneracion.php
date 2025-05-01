<?php

namespace App\Livewire\Admin\AsignarGeneracion;

use App\Models\Generacion;
use App\Models\Licenciatura;
use App\Models\AsignarGeneracion as AsignarGeneracionModel;
use App\Models\Modalidad;
use Livewire\Component;

class AsignarGeneracion extends Component
{

    public $licenciaturas;
    public $modalidades;
    public $generaciones;

    public $licenciatura_id;
    public $modalidad_id;
    public $generacion_id;


    public function mount()
    {
        $this->licenciaturas = Licenciatura::all();
        $this->modalidades = Modalidad::all();
        $this->generaciones = Generacion::all();
    }


    public function asignarGeneracion()
    {

        $this->validate([
            'licenciatura_id' => 'required|exists:licenciaturas,id',
            'modalidad_id' => 'required|exists:modalidades,id',
            'generacion_id' => 'required|exists:generaciones,id',
        ],[
            'licenciatura_id.required' => 'La licenciatura es obligatoria.',
            'licenciatura_id.exists' => 'La licenciatura seleccionada no es válida.',
            'modalidad_id.required' => 'La modalidad es obligatoria.',
            'modalidad_id.exists' => 'La modalidad seleccionada no es válida.',
            'generacion_id.required' => 'La generación es obligatoria.',
            'generacion_id.exists' => 'La generación seleccionada no es válida.',
        ]);

        // Verifica si ya existe una asignación para la combinación seleccionada
        $existingAssignment = AsignarGeneracionModel::where('licenciatura_id', $this->licenciatura_id)
            ->where('modalidad_id', $this->modalidad_id)
            ->where('generacion_id', $this->generacion_id)
            ->first();
        if ($existingAssignment) {
            $this->dispatch('swal', [
                'title' => 'Ya existe una asignación para esta combinación.',
                'icon' => 'error',
                'position' => 'top',
            ]);
            return;
        }


        // Aquí puedes agregar la lógica para asignar la generación a la licenciatura y modalidad seleccionadas
        // Por ejemplo, podrías crear un nuevo registro en la tabla asignar_generaciones
            AsignarGeneracionModel::create([
            'licenciatura_id' => $this->licenciatura_id,
            'modalidad_id' => $this->modalidad_id,
            'generacion_id' => $this->generacion_id,
        ]);

        $this->dispatch('swal', [
            'title' => 'Asignación creada correctamente',
            'icon' => 'success',
            'position' => 'top-end',
        ]);

        $this->reset('licenciatura_id', 'modalidad_id', 'generacion_id');

        $this->dispatch('refreshAsignacion');
    }

    public function render()
    {
        return view('livewire.admin.asignar-generacion.asignar-generacion');
    }
}
