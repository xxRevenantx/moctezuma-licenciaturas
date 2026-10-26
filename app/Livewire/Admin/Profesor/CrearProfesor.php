<?php

namespace App\Livewire\Admin\Profesor;

use App\Models\User;
use App\Services\CurpService;
use App\Services\Profesores\ProfesorManagementService;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class CrearProfesor extends Component
{
    use WithFileUploads;

    public $user_id = '';
    public $foto;
    public $nombre = '';
    public $apellido_paterno = '';
    public $apellido_materno = '';
    public $telefono = '';
    public $perfil = '';
    public $color = '#0284c7';
    public $CURP = '';
    public $usuario_email = '';
    public bool $cuentaPendiente = false;
    public $usuarios;

    public function mount(ProfesorManagementService $service): void
    {
        $this->usuarios = $service->cuentasElegibles();
    }

    public function updatedUserId($value): void
    {
        $service = app(ProfesorManagementService::class);
        $this->reset(['CURP', 'usuario_email', 'cuentaPendiente', 'nombre', 'apellido_paterno', 'apellido_materno']);
        if (! $this->user_id) {
            return;
        }

        $usuario = $service->cuentasElegibles()->firstWhere('id', (int) $this->user_id);
        if (! $usuario) {
            $this->addError('user_id', 'La cuenta no está disponible para vincular.');
            return;
        }
        $this->resetErrorBag('user_id');
        $this->CURP = (string) $usuario->CURP;
        $this->usuario_email = (string) $usuario->email;
        $this->cuentaPendiente = $usuario->status !== 'true';
        // No ejecutar llamadas externas automáticas: son opcionales y no deben impedir el alta.
    }

    public function consultarCurp(CurpService $curpService): void
    {
        app(ProfesorManagementService::class)->autorizar();
        if (! $this->CURP) {
            $this->addError('user_id', 'Selecciona primero una cuenta con CURP.');
            return;
        }
        $respuesta = $curpService->obtenerDatosPorCurp($this->CURP);
        $datos = data_get($respuesta, 'response.Solicitante');
        if (! is_array($datos) || ! empty($respuesta['error'])) {
            $mensaje = (string) ($respuesta['message'] ?? 'La API CURP no devolvió datos utilizables.');
            $this->dispatch('swal', [
                'title' => $mensaje . ' Puedes capturar el nombre manualmente.',
                'icon' => 'info',
            ]);
            return;
        }
        $this->nombre = (string) ($datos['Nombres'] ?? $this->nombre);
        $this->apellido_paterno = (string) ($datos['ApellidoPaterno'] ?? $this->apellido_paterno);
        $this->apellido_materno = (string) ($datos['ApellidoMaterno'] ?? $this->apellido_materno);
    }

    public function guardarProfesor(ProfesorManagementService $service): void
    {
        $service->autorizar();
        $validated = $this->validate([
            'user_id' => ['required', 'integer', 'exists:users,id', Rule::unique('profesores', 'user_id')],
            'foto' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
            'nombre' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'regex:/^[0-9]{10}$/'],
            'perfil' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ], ['telefono.regex' => 'El teléfono debe contener exactamente 10 dígitos.']);

        $service->crear($validated, $this->foto);
        $this->reset(['user_id', 'foto', 'nombre', 'apellido_paterno', 'apellido_materno', 'telefono', 'perfil', 'CURP', 'usuario_email', 'cuentaPendiente']);
        $this->color = '#0284c7';
        $this->usuarios = $service->cuentasElegibles();
        $this->dispatch('refreshProfesor');
        $this->dispatch('profesor-creado');
        $this->dispatch('swal', ['title' => 'Expediente docente creado correctamente.', 'icon' => 'success', 'position' => 'top-end']);
    }

    public function render()
    {
        return view('livewire.admin.profesor.crear-profesor');
    }
}
