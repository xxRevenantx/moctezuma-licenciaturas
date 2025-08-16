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

    /**
     * Reglas y mensajes (para validación en vivo y al enviar)
     * Nota: Hice CCT, calle, colonia, CP, ciudad, municipio y estado requeridos.
     * Si alguno debe ser opcional en tu flujo, dímelo y lo ajustamos.
     */
    protected $rules = [
        'nombre'         => 'required|string|min:3|max:180',
        'CCT'            => 'required|string|size:10',      // ajusta size si tu CCT usa otra longitud
        'calle'          => 'required|string|min:3|max:180',
        'no_exterior'    => 'nullable|string|max:20',
        'no_interior'    => 'nullable|string|max:20',
        'colonia'        => 'required|string|min:3|max:120',
        'codigo_postal'  => 'required|digits:5',
        'ciudad'         => 'required|string|min:2|max:120',
        'municipio'      => 'required|string|min:2|max:120',
        'estado'         => 'required|string|min:2|max:120',
        'telefono'       => 'nullable|digits:10',
        'correo'         => 'nullable|email:rfc,dns|max:180',
        'pagina_web'     => 'nullable|url|max:255',
    ];

    protected $messages = [
        'required'      => 'Este campo es obligatorio.',
        'min'           => 'Debe contener al menos :min caracteres.',
        'max'           => 'No puede exceder :max caracteres.',
        'digits'        => 'Debe contener exactamente :digits dígitos.',
        'size'          => 'Debe tener exactamente :size caracteres.',
        'email'         => 'Introduce un correo válido.',
        'url'           => 'Introduce una URL válida (con https://).',
    ];

    /** Validación en vivo por campo */
    public function updated($property)
    {
        $this->validateOnly($property);
    }

    public function mount()
    {
        $escuela = Escuela::first();

        if ($escuela) {
            $this->nombre        = $escuela->nombre;
            $this->CCT           = $escuela->CCT;
            $this->calle         = $escuela->calle;
            $this->no_exterior   = $escuela->no_exterior;
            $this->no_interior   = $escuela->no_interior;
            $this->colonia       = $escuela->colonia;
            $this->codigo_postal = $escuela->codigo_postal;
            $this->ciudad        = $escuela->ciudad;
            $this->municipio     = $escuela->municipio;
            $this->estado        = $escuela->estado;
            $this->telefono      = $escuela->telefono;
            $this->correo        = $escuela->correo;
            $this->pagina_web    = $escuela->pagina_web;
        }
    }

    public function guardarEscuela()
    {
        // Valida con las reglas definidas arriba
        $validated = $this->validate();

        // Sanitiza: trim a todos los strings
        $data = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $validated);

        $escuela = Escuela::first();

        if ($escuela) {
            // Actualizar datos existentes
            $escuela->update($data);

            // Mensaje para tu banner/alert y tu SweetAlert
            session()->flash('ok', '¡Datos de la escuela actualizados!');
            $this->dispatch('swal', [
                'title'    => '¡Datos de la escuela actualizados!',
                'icon'     => 'success',
                'position' => 'top-end',
            ]);
        } else {
            // Crear nueva escuela
            Escuela::create($data);

            session()->flash('ok', '¡Datos de la escuela guardados!');
            $this->dispatch('swal', [
                'title'    => '¡Datos de la escuela guardados!',
                'icon'     => 'success',
                'position' => 'top-end',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.escuela.datos-escuela');
    }
}
