<?php

namespace App\Livewire\Admin\Usuarios;

use App\Exports\UsersExport;
use App\Models\User;
use App\Services\Usuarios\UserManagementService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class MostrarUsuarios extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filtrar_status = '';
    public string $filtrar_roles = '';
    public array $selected = [];
    public bool $selectAll = false;

    public function mount(): void
    {
        $service = app(UserManagementService::class);
        $service->authorize();
    }

    private function queryBase(UserManagementService $service): Builder
    {
        $actor = $service->authorize();
        $search = trim($this->search);

        return User::query()
            ->when(! $actor->hasRole('SuperAdmin'), fn (Builder $query) =>
                $query->whereDoesntHave('roles', fn (Builder $q) => $q->where('name', 'SuperAdmin')))
            ->when(in_array($this->filtrar_status, ['Activo', 'Inactivo'], true), fn (Builder $q) =>
                $q->where('status', $this->filtrar_status === 'Activo' ? 'true' : 'false'))
            ->when($this->filtrar_roles !== '', fn (Builder $q) =>
                $q->whereHas('roles', fn (Builder $r) => $r->where('name', $this->filtrar_roles)))
            ->when($search !== '', function (Builder $q) use ($search) {
                $q->where(function (Builder $nested) use ($search) {
                    $like = '%'.$search.'%';
                    $nested->where('username', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('CURP', 'like', $like)
                        ->orWhereHas('roles', fn (Builder $r) => $r->where('name', 'like', $like))
                        ->orWhereHas('inscripcion', fn (Builder $i) => $i->where('matricula', 'like', $like));
                });
            });
    }

    public function updatedSelectAll($value): void
    {
        $service = app(UserManagementService::class);
        $service->authorize();
        if (! $value) {
            $this->selected = [];
            return;
        }

        $ids = $this->queryBase($service)->whereKeyNot(auth()->id())
            ->whereDoesntHave('roles', fn (Builder $q) => $q->where('name', 'SuperAdmin'))
            ->orderBy('id')->limit(501)->pluck('id')->all();

        if (count($ids) > 500) {
            $this->selectAll = false;
            $this->selected = [];
            $this->addError('selected', 'Hay más de 500 resultados. Reduce los filtros antes de seleccionar todos.');
            return;
        }
        $this->resetErrorBag('selected');
        $this->selected = $ids;
    }

    public function updatedSelected(): void
    {
        $this->selectAll = false;
        $this->resetErrorBag('selected');
    }

    public function updatedSearch(): void { $this->resetSelectionAndPage(); }
    public function updatedFiltrarStatus(): void { $this->resetSelectionAndPage(); }
    public function updatedFiltrarRoles(): void { $this->resetSelectionAndPage(); }

    private function resetSelectionAndPage(): void
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
        $this->resetErrorBag('selected');
    }

    private function validSelection(UserManagementService $service): array
    {
        $service->authorize();
        $selected = array_map('intval', $this->selected);
        if (! count($selected) || count($selected) > 500) {
            throw ValidationException::withMessages(['selected' => 'Selecciona entre 1 y 500 usuarios.']);
        }
        // Revalidar contra los filtros efectivos: nunca ejecutar selecciones obsoletas.
        $matching = $this->queryBase($service)->whereKey($selected)->pluck('id')->map(fn ($id) => (int) $id)->all();
        if (count($matching) !== count(array_unique($selected))) {
            throw ValidationException::withMessages(['selected' => 'Los filtros cambiaron: selecciona los usuarios nuevamente.']);
        }
        return $selected;
    }

    public function cambioRolUsuariosSeleccionados($id): void
    {
        $service = app(UserManagementService::class);
        $count = $service->cambiarRolesMasivamente($this->validSelection($service), (int) $id);
        $this->finalizarOperacion("Se cambió el rol de {$count} cuentas.");
    }

    public function inactivarUsuarios(): void
    {
        $service = app(UserManagementService::class);
        $count = $service->cambiarEstadoMasivamente($this->validSelection($service), false);
        $this->finalizarOperacion("Se desactivaron {$count} cuentas sin borrar expedientes.");
    }

    public function activarUsuarios(): void
    {
        $service = app(UserManagementService::class);
        $count = $service->cambiarEstadoMasivamente($this->validSelection($service), true);
        $this->finalizarOperacion("Se activaron {$count} cuentas.");
    }

    public function eliminarUsuario($id): void
    {
        $service = app(UserManagementService::class);
        // Método heredado: desactivación segura, jamás DELETE.
        $service->desactivar((int) $id);
        $this->finalizarOperacion('Cuenta desactivada. El expediente permanece intacto.');
    }

    public function enviarAcceso($id): void
    {
        $service = app(UserManagementService::class);
        $status = $service->enviarAcceso((int) $id);
        $this->dispatch('swal', [
            'title' => $status === Password::RESET_LINK_SENT
                ? 'Enlace de configuración de contraseña enviado al correo registrado.'
                : 'No se pudo enviar el enlace. Comprueba el correo y la configuración SMTP.',
            'icon' => $status === Password::RESET_LINK_SENT ? 'success' : 'warning',
            'position' => 'top-end',
        ]);
    }

    private function finalizarOperacion(string $mensaje): void
    {
        $this->selected = [];
        $this->selectAll = false;
        $this->dispatch('refreshUsuarios');
        $this->dispatch('swal', ['title' => $mensaje, 'icon' => 'success', 'position' => 'top-end']);
    }

    public function limpiarSeleccion(): void
    {
        $this->selected = [];
        $this->selectAll = false;
        $this->resetErrorBag('selected');
    }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->filtrar_status = '';
        $this->filtrar_roles = '';
        $this->resetSelectionAndPage();
    }

    public function exportarUsuarios()
    {
        $service = app(UserManagementService::class);
        $usuarios = $this->queryBase($service)->with('roles')->orderByDesc('id')->get();
        return Excel::download(new UsersExport($usuarios), 'usuarios_filtrados.xlsx');
    }

    public static function placeholder() { return view('placeholder'); }

    #[On('refreshUsuarios')]
    public function render()
    {
        $service = app(UserManagementService::class);
        $service->authorize();
        $q = $this->queryBase($service);
        $stats = [
            'total' => (clone $q)->count(),
            'activos' => (clone $q)->where('status', 'true')->count(),
            'inactivos' => (clone $q)->where('status', 'false')->count(),
            'sinVerificar' => (clone $q)->whereNull('email_verified_at')->count(),
        ];
        return view('livewire.admin.usuarios.mostrar-usuarios', [
            'usuarios' => (clone $q)->with(['roles', 'inscripcion', 'profesor'])->orderByDesc('id')->paginate(15),
            'roles' => $service->rolesVisibles(),
            'stats' => $stats,
            'superAdmins' => auth()->user()->hasRole('SuperAdmin')
                ? User::role('SuperAdmin')->orderBy('username')->get(['id', 'username', 'email', 'status'])
                : collect(),
        ]);
    }
}
