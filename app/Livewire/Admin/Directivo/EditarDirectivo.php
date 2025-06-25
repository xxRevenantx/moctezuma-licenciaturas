<?php

namespace App\Livewire\Admin\Directivo;

use App\Models\Directivo;
use Livewire\Attributes\On;
use Livewire\Component;

class EditarDirectivo extends Component
{

    public $directivoId;
    public $titulo;
    public $nombre;
    public $apellido_paterno;
    public $apellido_materno;
    public $telefono;
    public $correo;
    public $cargo;
    public $identificador;
    public $status;
    public $open = false;


    // Método para abrir el modal con datos
    #[On('abrirDirectivo')]
    public function abrirModal($id)
    {
        $directivo = Directivo::findOrFail($id);

        $this->directivoId = $directivo->id;
        $this->titulo = $directivo->titulo;
        $this->nombre = $directivo->nombre;
        $this->apellido_paterno = $directivo->apellido_paterno;
        $this->apellido_materno = $directivo->apellido_materno;
        $this->telefono = $directivo->telefono;
        $this->correo = $directivo->correo;
        $this->cargo = $directivo->cargo;
        $this->identificador = $directivo->identificador;
        $this->status = $directivo->status == "true" ? true : false;;

        $this->open = true;

    }



public function actualizarDirectivo()
{
    $this->validate(
        [
            'titulo' => 'required|string|max:255',
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:255',
            'correo' => 'nullable|email|max:255',
            'cargo' => 'required|string|max:255',
            'identificador' => 'required|string|max:100',
        ],
        [
            'titulo.required' => 'El título es obligatorio.',
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido_paterno.required' => 'El apellido paterno es obligatorio.',
            'telefono.max' => 'El teléfono no debe exceder 255 caracteres.',
            'correo.email' => 'El correo electrónico no es válido.',
            'cargo.required' => 'El cargo es obligatorio.',
            'identificador.required' => 'El campo identificador es requerido.',
        ]
    );

    $this->status = $this->status ? 'true' : 'false';
    $identificadorNormalizado = strtolower(trim($this->identificador));

    // ❌ Validar que no haya otro con el mismo identificador y status = true
    if ($this->status === 'true') {
        $existeOtroActivo = Directivo::where('identificador', $identificadorNormalizado)
            ->where('id', '!=', $this->directivoId)
            ->where('status', 'true')
            ->exists();

        if ($existeOtroActivo) {
             $this->dispatch('swal', [
            'title' => 'Ya existe un directivo activo con este identificador. Solo puede haber uno activo',
            'icon' => 'error',
            'position' => 'top',
        ]);
            return;

        }
    }

    $directivo = Directivo::findOrFail($this->directivoId);

    $directivo->update([
        'titulo' => trim($this->titulo),
        'nombre' => trim($this->nombre),
        'apellido_paterno' => trim($this->apellido_paterno),
        'apellido_materno' => trim($this->apellido_materno),
        'telefono' => trim($this->telefono),
        'correo' => trim($this->correo),
        'cargo' => trim($this->cargo),
        'identificador' => $identificadorNormalizado,
        'status' => $this->status,
    ]);

    $this->dispatch('swal', [
        'title' => '¡Directivo actualizado correctamente!',
        'icon' => 'success',
        'position' => 'top-end',
    ]);

    $this->dispatch('refreshDirectivos');
    $this->cerrarModal();
}





    public function cerrarModal()
    {
        $this->reset(['open', 'directivoId', 'titulo', 'nombre', 'apellido_paterno', 'apellido_materno', 'telefono', 'correo', 'cargo']);
        $this->resetValidation();
    }


    public function render()
    {
        return view('livewire.admin.directivo.editar-directivo');
    }
}
