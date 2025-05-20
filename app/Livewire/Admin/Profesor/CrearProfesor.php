<?php

namespace App\Livewire\Admin\Profesor;

use App\Models\Profesor;
use App\Models\User;
use App\Services\CurpService;
use Livewire\Component;
use Livewire\WithFileUploads;

class CrearProfesor extends Component
{
    use WithFileUploads;

    public $usuarios;
    public $user_id;
    public $foto;
    public $nombre;
    public $apellido_paterno;
    public $apellido_materno;
    public $telefono;
    public $perfil;
    public $color;
    public $status;
    public $CURP;

    public $usuario_email;

    public $datosCurp;


    public function mount()
    {
        $this->usuarios = User::role('Profesor')
        ->whereNotIn('id', Profesor::pluck('user_id'))
        ->where('status', "true")
        ->orderBy('id', 'desc')
        ->get();
    }

      public function updated($propertyName)
    {
            if ($propertyName === 'user_id') {

                if (!$this->user_id ) {
                       // Reiniciar campos si no hay usuario seleccionado
                $this->reset([
                    'foto',
                    'CURP',
                    'nombre',
                    'apellido_paterno',
                    'apellido_materno',
                    'telefono',
                    'perfil',
                    'color',
                    'status',

                ]);
                return;
            }

                $this->usuarios = User::role('Profesor')
                ->whereNotIn('id', Profesor::pluck('user_id'))
                ->where('status', "true")
                ->orderBy('id', 'desc')
                ->get();

                $this->usuario_email = User::where('id', $this->user_id)->value('email');
                $this->CURP = User::where('id', $this->user_id)->value('CURP');
                $servicio = new CurpService();
                $this->datosCurp = $servicio->obtenerDatosPorCurp($this->CURP);

               // Validar que la respuesta sea válida y no haya error
            if (!$this->datosCurp['error'] && isset($this->datosCurp['response'])) {
                $info = $this->datosCurp['response']['Solicitante'];

                $this->nombre = $info['Nombres'] ?? '';
                $this->apellido_paterno = $info['ApellidoPaterno'] ?? '';
                $this->apellido_materno = $info['ApellidoMaterno'] ?? '';
            } else {
                // Enviar un mensaje de error si hay un problema con los datos de la CURP
                $this->dispatch('swal', [
                    'title' => 'Este CURP no se encuentra en RENAPO.',
                    'icon' => 'error',
                    'position' => 'top-end',
                ]);
            }
            }

                //  $this->validateOnly($propertyName);
    }



    public function guardarProfesor()
    {
        $this->validate([
            'user_id' => 'required|unique:profesores,user_id',
            'foto' => 'nullable|image|max:2048|mimes:jpeg,jpg,png',
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:10',
            'perfil' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
        ],[
            'user_id.required' => 'El campo usuario es obligatorio.',
            'user_id.unique' => 'El usuario ya está registrado como profesor.',
            'foto.image' => 'El archivo debe ser una imagen.',
            'foto.max' => 'La imagen no debe exceder 2 MB.',
            'foto.mimes' => 'La imagen debe ser de tipo jpeg, jpg o png.',
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser una cadena de texto.',
            'nombre.max' => 'El nombre no debe exceder 255 caracteres.',
            'apellido_paterno.required' => 'El campo apellido paterno es obligatorio.',
            'apellido_paterno.string' => 'El apellido paterno debe ser una cadena de texto.',
            'apellido_paterno.max' => 'El apellido paterno no debe exceder 255 caracteres.',
            'apellido_materno.string' => 'El apellido materno debe ser una cadena de texto.',
            'apellido_materno.max' => 'El apellido materno no debe exceder 255 caracteres.',
            'telefono.string' => 'El teléfono debe ser una cadena de texto.',
            'telefono.max' => 'El teléfono no debe exceder 10 caracteres.',
            'perfil.string' => 'El perfil debe ser una cadena de texto.',
            'perfil.max' => 'El perfil no debe exceder 255 caracteres.',
        ]);


        //validar foto
        if ($this->foto) {
            $foto = $this->foto->store('profesores');
            $datos["foto"] = str_replace('profesores/', '', $foto);
        } else {
            $datos["foto"] = NULL;
        }



 try {
        Profesor::create([
            'user_id' => $this->user_id,
            'nombre' => strtoupper(trim($this->nombre)),
            'apellido_paterno' => strtoupper(trim($this->apellido_paterno)),
            'apellido_materno' => strtoupper(trim($this->apellido_materno)),
            'telefono' => trim($this->telefono),
            'perfil' => strtoupper(trim($this->perfil)),
            'color' => $this->color,
            'status' => 'true'
        ]);
        // Reiniciar campos después de guardar
        $this->reset([
            'user_id',
            'foto',
            'nombre',
            'apellido_paterno',
            'apellido_materno',
            'telefono',
            'perfil',
            'color',
            'status'
        ]);

        $this->dispatch('refreshProfesor');

        $this->dispatch('swal', [
            'title' => 'Profesor creado correctamente.',
            'icon' => 'success',
            'position' => 'top-end',
        ]);

            $this->usuarios = User::role('Profesor')
                ->whereNotIn('id', Profesor::pluck('user_id'))
                ->where('status', "true")
                ->orderBy('id', 'desc')
                ->get();

 } catch (\Exception $e) {
        // Manejar errores y mostrar un mensaje de error
        $this->dispatch('swal', [
            'title' => 'Error al crear el profesor'.$e->getMessage(),
            'icon' => 'error',
            'position' => 'top-end',
        ]);
    }
}



    public function render()
    {
        return view('livewire.admin.profesor.crear-profesor');
    }
}
