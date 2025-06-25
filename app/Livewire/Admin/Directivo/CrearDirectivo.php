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
    public $identificador;


    protected $rules = [
        'titulo' => 'required|string|max:255',
        'nombre' => 'required|string|max:255',
        'apellido_paterno' => 'required|string|max:255',
        'apellido_materno' => 'nullable|string|max:255',
        'telefono' => 'nullable|string|max:10|regex:/^[0-9]{10}$/',
        'correo' => 'nullable|email|max:255',
        'cargo' => 'required|string|max:255',
        'identificador' => 'required|string|max:255',
    ];


   public function crearDirectivo()
{
    $this->validate();

    $identificadorNormalizado = strtolower(trim($this->identificador));
    $statusStr = 'true'; // Siempre será true por defecto

    // 🚫 Validar que no haya otro activo con este identificador
    $yaActivo = Directivo::where('identificador', $identificadorNormalizado)
        ->where('status', 'true')
        ->exists();

    if ($yaActivo) {
         $this->dispatch('swal', [
            'title' => 'Ya existe un directivo activo con este identificador. Solo puede haber uno activo',
            'icon' => 'error',
            'position' => 'top',
        ]);
            return;
    }

    // ✅ Crear directivo
    Directivo::create([
        'titulo' => trim($this->titulo),
        'nombre' => trim($this->nombre),
        'apellido_paterno' => trim($this->apellido_paterno),
        'apellido_materno' => trim($this->apellido_materno),
        'telefono' => trim($this->telefono),
        'correo' => trim($this->correo),
        'cargo' => trim($this->cargo),
        'identificador' => $identificadorNormalizado,
        'status' => $statusStr,
    ]);

    $this->dispatch('swal', [
        'title' => '¡Directivo creado correctamente!',
        'icon' => 'success',
        'position' => 'top-end',
    ]);

    $this->reset([
        'titulo',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'telefono',
        'correo',
        'cargo',
        'identificador'
    ]);

    $this->dispatch('refreshDirectivos');
}



    public function render()
    {
        return view('livewire.admin.directivo.crear-directivo');
    }
}
