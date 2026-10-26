<?php

namespace App\Services\Usuarios;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

/**
 * Seguridad centralizada para los componentes de Usuarios PRO.
 * Nunca borra usuarios: inscripciones y profesores tienen relaciones ON DELETE CASCADE.
 */
class UserManagementService
{
    public function authorize(): User
    {
        $actor = auth()->user();
        abort_unless($actor instanceof User && $actor->can('admin.usuarios'), 403);

        return $actor;
    }

    public function rolesVisibles()
    {
        $actor = $this->authorize();

        return Role::query()->where('guard_name', 'web')
            ->when(! $actor->hasRole('SuperAdmin'), fn ($q) => $q->where('name', '!=', 'SuperAdmin'))
            ->orderBy('name')->get();
    }

    /** Valida IDs enviados desde Livewire; nunca confiar en checkboxes ocultos. */
    public function validarRoles(array $ids, bool $masivo = false): array
    {
        $actor = $this->authorize();
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (! count($ids) || in_array(0, $ids, true)) {
            throw ValidationException::withMessages(['rol' => 'Selecciona al menos un rol válido.']);
        }

        $roles = Role::query()->where('guard_name', 'web')->whereIn('id', $ids)->get();
        if ($roles->count() !== count($ids)) {
            throw ValidationException::withMessages(['rol' => 'Uno o más roles no existen.']);
        }

        if ($roles->contains('name', 'SuperAdmin') && (! $actor->hasRole('SuperAdmin') || $masivo)) {
            abort(403, 'No se permite asignar SuperAdmin en esta operación.');
        }

        return $ids;
    }

    private function assertEditable(User $target, User $actor): void
    {
        if ($target->hasRole('SuperAdmin') && ! $actor->hasRole('SuperAdmin')) {
            abort(403, 'Solo un SuperAdmin puede modificar una cuenta SuperAdmin.');
        }
    }

    private function protectCriticalAccess(User $target, User $actor, array $newRoleIds, bool $active): void
    {
        if ($target->id === $actor->id && ! $active) {
            throw ValidationException::withMessages(['status' => 'No puedes desactivar tu propia cuenta.']);
        }
        if ($target->id === $actor->id) {
            $currentIds = $target->roles->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
            $requestedIds = collect($newRoleIds)->map(fn ($id) => (int) $id)->sort()->values()->all();
            if ($currentIds !== $requestedIds) {
                throw ValidationException::withMessages(['rol' => 'Por seguridad no puedes cambiar tus propios roles.']);
            }
        }

        if (! $target->hasRole('SuperAdmin')) {
            return;
        }

        $keepsSuperAdmin = Role::where('name', 'SuperAdmin')->whereIn('id', $newRoleIds)->exists();
        if ($target->id === $actor->id && ! $keepsSuperAdmin) {
            throw ValidationException::withMessages(['rol' => 'No puedes retirar tu propio rol SuperAdmin.']);
        }

        if ((! $keepsSuperAdmin || ! $active) && $target->status === 'true') {
            // Debe quedar al menos otro SuperAdmin activo.
            $others = User::role('SuperAdmin')->where('status', 'true')
                ->whereKeyNot($target->id)->lockForUpdate()->get(['users.id'])->count();
            if ($others < 1) {
                throw ValidationException::withMessages(['rol' => 'Debe permanecer al menos un SuperAdmin activo.']);
            }
        }
    }

    public function crear(array $data, array $roleIds): User
    {
        $actor = $this->authorize();
        $roleIds = $this->validarRoles($roleIds);

        $user = DB::transaction(function () use ($data, $roleIds) {
            $user = User::create([
                'username' => trim($data['username']),
                'email' => Str::lower(trim($data['email'])),
                'CURP' => Str::upper(trim($data['CURP'])),
                // Contraseña secreta aleatoria. Ninguna contraseña reutilizable se muestra ni almacena en texto plano.
                'password' => Str::random(64),
                // Alta pendiente: necesita activación administrativa + definir contraseña propia.
                'status' => 'false',
                'photo' => null,
            ]);
            $user->syncRoles(Role::whereIn('id', $roleIds)->get());

            return $user;
        });

        Log::info('usuarios.crear', ['actor_id' => $actor->id, 'user_id' => $user->id]);

        return $user;
    }

    public function actualizar(int $id, array $data, array $roleIds): User
    {
        $actor = $this->authorize();
        $roleIds = $this->validarRoles($roleIds);

        $target = DB::transaction(function () use ($id, $actor, $data, $roleIds) {
            $target = User::query()->lockForUpdate()->findOrFail($id);
            $target->load('roles');
            $this->assertEditable($target, $actor);
            $this->protectCriticalAccess($target, $actor, $roleIds, (bool) $data['status']);
            $email = Str::lower(trim($data['email']));

            $fields = [
                'username' => trim($data['username']),
                'email' => $email,
                'CURP' => Str::upper(trim($data['CURP'])),
                'status' => $data['status'] ? 'true' : 'false',
            ];
            $target->fill($fields);
            if (strcasecmp($target->getRawOriginal('email'), $email) !== 0) {
                $target->email_verified_at = null;
            }
            $target->save();
            $target->syncRoles(Role::whereIn('id', $roleIds)->get());

            return $target;
        });

        Log::info('usuarios.actualizar', ['actor_id' => $actor->id, 'user_id' => $target->id]);

        return $target;
    }

    /** @return array<int, User> */
    private function validateSelection(array $ids, User $actor): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (! count($ids) || count($ids) > 500 || in_array(0, $ids, true)) {
            throw ValidationException::withMessages(['selected' => 'Selecciona entre 1 y 500 cuentas.']);
        }
        $users = User::query()->with('roles')->whereIn('id', $ids)->lockForUpdate()->get();
        if ($users->count() !== count($ids)) {
            throw ValidationException::withMessages(['selected' => 'La selección cambió. Actualiza la lista.']);
        }
        foreach ($users as $user) {
            $this->assertEditable($user, $actor);
            if ($user->id === $actor->id || $user->hasRole('SuperAdmin')) {
                throw ValidationException::withMessages(['selected' => 'No se permite modificar masivamente cuentas propias ni SuperAdmin.']);
            }
        }
        return $users->all();
    }

    public function cambiarRolesMasivamente(array $ids, int $roleId): int
    {
        $actor = $this->authorize();
        $this->validarRoles([$roleId], true);

        $count = DB::transaction(function () use ($ids, $actor, $roleId) {
            $users = $this->validateSelection($ids, $actor);
            $role = Role::where('guard_name', 'web')->findOrFail($roleId);
            foreach ($users as $user) {
                $user->syncRoles([$role]);
            }
            return count($users);
        });
        Log::info('usuarios.roles.masivos', ['actor_id' => $actor->id, 'cantidad' => $count, 'role_id' => $roleId]);
        return $count;
    }

    public function cambiarEstadoMasivamente(array $ids, bool $active): int
    {
        $actor = $this->authorize();
        $count = DB::transaction(function () use ($ids, $actor, $active) {
            $users = $this->validateSelection($ids, $actor);
            foreach ($users as $user) {
                $user->update(['status' => $active ? 'true' : 'false']);
            }
            return count($users);
        });
        Log::info('usuarios.estado.masivos', ['actor_id' => $actor->id, 'cantidad' => $count, 'activo' => $active]);
        return $count;
    }

    /** Compatibilidad con antiguos botones de eliminar: no borra registros relacionados. */
    public function desactivar(int $id): void
    {
        $actor = $this->authorize();
        DB::transaction(function () use ($id, $actor) {
            $user = User::query()->lockForUpdate()->findOrFail($id);
            $this->assertEditable($user, $actor);
            $this->protectCriticalAccess($user, $actor, $user->roles->pluck('id')->all(), false);
            $user->update(['status' => 'false']);
        });
        Log::info('usuarios.desactivar', ['actor_id' => $actor->id, 'user_id' => $id]);
    }

    public function enviarAcceso(int $id): string
    {
        $actor = $this->authorize();
        $target = User::findOrFail($id);
        $this->assertEditable($target, $actor);
        // Laravel envía un enlace temporal sin revelar la contraseña ni el token al administrador.
        try {
            $status = Password::broker()->sendResetLink(['email' => $target->email]);
        } catch (\Throwable $exception) {
            Log::warning('usuarios.invitacion.error', [
                'actor_id' => $actor->id,
                'user_id' => $target->id,
                'error_type' => get_class($exception),
            ]);
            return Password::INVALID_USER;
        }
        Log::info('usuarios.invitacion', ['actor_id' => $actor->id, 'user_id' => $target->id, 'result' => $status]);

        return $status;
    }
}
