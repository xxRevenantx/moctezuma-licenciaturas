<?php

namespace App\Livewire\Admin\Directivo;

use App\Models\Directivo;
use Livewire\Component;

class CrearDirectivo extends Component
{


    public $titulo;
    public $nombre;
    public $apellido_paterno;
    public $apellido_materno;
    public $telefono;
    public $correo;
    public $cargo;


    protected $rules = [
        'titulo' => 'required|string|max:255',
        'nombre' => 'required|string|max:255',
        'apellido_paterno' => 'required|string|max:255',
        'apellido_materno' => 'nullable|string|max:255',
        'telefono' => 'nullable|string|max:10|regex:/^[0-9]{10}$/',
        'correo' => 'nullable|email|max:255',
        'cargo' => 'required|string|max:255',

    ];


    public function crearDirectivo()
    {
        $this->validate();

        // Aquí puedes agregar la lógica para guardar el directivo en la base de datos
        // Por ejemplo:
        Directivo::create([
            'titulo' => $this->titulo,
            'nombre' => $this->nombre,
            'apellido_paterno' => $this->apellido_paterno,
            'apellido_materno' => $this->apellido_materno,
            'telefono' => $this->telefono,
            'correo' => $this->correo,
            'cargo' => $this->cargo,
        ]);


        $this->dispatch('swal', [
            'title' => '¡Directivo creado correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
        ]);

        // Limpiar los campos después de crear el directivo
        $this->reset([
            'titulo',
            'nombre',
            'apellido_paterno',
            'apellido_materno',
            'telefono',
            'correo',
            'cargo',
        ]);

        // Emitir un evento para refrescar la lista de directivos
        $this->dispatch('refreshDirectivos');



    }


    public function render()
    {
        return view('livewire.admin.directivo.crear-directivo');
    }
}
