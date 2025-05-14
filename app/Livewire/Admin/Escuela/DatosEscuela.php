<?php

namespace App\Livewire\Admin\Escuela;

use App\Models\Escuela;
use Livewire\Component;

class DatosEscuela extends Component
{
    public $nombre;
    public $CCT;
    public $calle;
    public $no_exterior;
    public $no_interior;
    public $colonia;
    public $codigo_postal;
    public $ciudad;
    public $municipio;
    public $estado;
    public $telefono;
    public $correo;
    public $pagina_web;



    public function mount()
    {
        $escuela = Escuela::first();

        if ($escuela) {
            $this->nombre = $escuela->nombre;
            $this->CCT = $escuela->CCT;
            $this->calle = $escuela->calle;
            $this->no_exterior = $escuela->no_exterior;
            $this->no_interior = $escuela->no_interior;
            $this->colonia = $escuela->colonia;
            $this->codigo_postal = $escuela->codigo_postal;
            $this->ciudad = $escuela->ciudad;
            $this->municipio = $escuela->municipio;
            $this->estado = $escuela->estado;
            $this->telefono = $escuela->telefono;
            $this->correo = $escuela->correo;
            $this->pagina_web = $escuela->pagina_web;

        }
    }





    public function guardarEscuela(){
        $this->validate([
            'nombre' => 'required|string|max:100',
            'CCT' => 'nullable|string|max:20',
            'calle' => 'nullable|string|max:255',
            'no_exterior' => 'nullable|string|max:5',
            'no_interior' => 'nullable|string|max:5',
            'colonia' => 'nullable|string|max:10',
            'codigo_postal' => 'nullable|string|max:6',
            'ciudad' => 'nullable|string|max:100',
            'municipio' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:10',
            'correo' => 'nullable|email|max:50',
            'pagina_web' => 'nullable|url|max:255'
        ],[
            'nombre.required' => 'El nombre de la escuela es obligatorio.',
            'nombre.string' => 'El nombre de la escuela debe ser una cadena de texto.',
            'nombre.max' => 'El nombre de la escuela no puede tener más de 100 caracteres.',
            'CCT.string' => 'El CCT debe ser una cadena de texto.',
            'CCT.max' => 'El CCT no puede tener más de 20 caracteres.',
            'calle.string' => 'La calle debe ser una cadena de texto.',
            'calle.max' => 'La calle no puede tener más de 255 caracteres.',
            // Agregar mensajes para los demás campos...
            'telefono.string' => 'El teléfono debe ser una cadena de texto.',
            'telefono.max' => 'El teléfono no puede tener más de 10 caracteres.',
            'correo.email' => 'El correo electrónico no es válido.',
            'correo.max' => 'El correo electrónico no puede tener más de 50 caracteres.',
            'pagina_web.url' => 'La página web no es válida.',
            'pagina_web.max' => 'La página web no puede tener más de 255 caracteres.',
        ]);

        // Guardar o actualizar datos de la escuela

        $escuela = Escuela::first();

        if ($escuela) {
            // Actualizar datos existentes
            $escuela->update([
            'nombre' => trim($this->nombre),
            'CCT' => trim($this->CCT),
            'calle' => trim($this->calle),
            'no_exterior' => trim($this->no_exterior),
            'no_interior' => trim($this->no_interior),
            'colonia' => trim($this->colonia),
            'codigo_postal' => trim($this->codigo_postal),
            'ciudad' => trim($this->ciudad),
            'municipio' => trim($this->municipio),
            'estado' => trim($this->estado),
            'telefono' => trim($this->telefono),
            'correo' => trim($this->correo),
            'pagina_web' => trim($this->pagina_web),
            ]);


             $this->dispatch('swal', [
            'title' => '¡Datos de la escuela Actualizados!',
            'icon' => 'success',
            'position' => 'top-end',
          ]);



        } else {
            // Crear nueva escuela
            Escuela::create([
            'nombre' => trim($this->nombre),
            'CCT' => trim($this->CCT),
            'calle' => trim($this->calle),
            'no_exterior' => trim($this->no_exterior),
            'no_interior' => trim($this->no_interior),
            'colonia' => trim($this->colonia),
            'codigo_postal' => trim($this->codigo_postal),
            'ciudad' => trim($this->ciudad),
            'municipio' => trim($this->municipio),
            'estado' => trim($this->estado),
            'telefono' => trim($this->telefono),
            'correo' => trim($this->correo),
            'pagina_web' => trim($this->pagina_web),
            ]);

             $this->dispatch('swal', [
            'title' => '¡Datos de la escuela gardados!',
            'icon' => 'success',
            'position' => 'top-end',
        ]);

        }








    }


    public function render()
    {
        return view('livewire.admin.escuela.datos-escuela');
    }
}
