<?php

use App\Models\User;
use App\Services\Usuarios\UserManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;

uses(RefreshDatabase::class);

function usuarioAdminPro(string $nombreRol = 'Admin'): array
{
    $permission = Permission::findOrCreate('admin.usuarios', 'web');
    $role = Role::findOrCreate($nombreRol, 'web');
    $role->givePermissionTo($permission);
    $user = User::factory()->create(['status' => 'true']);
    $user->assignRole($role);
    auth()->login($user);

    return [$user, $role];
}

test('la creación no usa una contraseña compartida y deja el acceso pendiente', function () {
    [$actor, $rol] = usuarioAdminPro();
    $user = app(UserManagementService::class)->crear([
        'username' => 'cuenta_prueba',
        'email' => 'nuevo@example.org',
        'CURP' => 'ABCD010101HGRXYZ09',
    ], [$rol->id]);

    expect($user->fresh()->status)->toBe('false');
    expect(Hash::check('12345678', $user->password))->toBeFalse();
    expect($user->email_verified_at)->toBeNull();
    expect($user->hasRole('Admin'))->toBeTrue();
});

test('un administrador no puede crear una cuenta SuperAdmin por manipulación de Livewire', function () {
    usuarioAdminPro();
    $super = Role::findOrCreate('SuperAdmin', 'web');

    expect(fn () => app(UserManagementService::class)->crear([
        'username' => 'privilegio',
        'email' => 'privilegio@example.org',
        'CURP' => 'ABCD010101HGRXYZ08',
    ], [$super->id]))->toThrow(HttpException::class);
    expect(User::where('username', 'privilegio')->exists())->toBeFalse();
});

test('un administrador no puede editar una cuenta SuperAdmin aunque conozca el ID', function () {
    [$actor, $rol] = usuarioAdminPro();
    $super = Role::findOrCreate('SuperAdmin', 'web');
    $target = User::factory()->create(['status' => 'true']);
    $target->assignRole($super);

    expect(fn () => app(UserManagementService::class)->actualizar($target->id, [
        'username' => $target->username,
        'email' => $target->email,
        'CURP' => $target->CURP,
        'status' => true,
    ], [$rol->id]))->toThrow(HttpException::class);
    expect($target->fresh()->hasRole('SuperAdmin'))->toBeTrue();
});

test('la selección masiva manipulada no puede inactivar a SuperAdmin', function () {
    usuarioAdminPro();
    $super = Role::findOrCreate('SuperAdmin', 'web');
    $target = User::factory()->create(['status' => 'true']);
    $target->assignRole($super);

    expect(fn () => app(UserManagementService::class)->cambiarEstadoMasivamente([$target->id], false))
        ->toThrow(ValidationException::class);
    expect($target->fresh()->status)->toBe('true');
});

test('el antiguo método eliminar se comporta como desactivación, sin borrar la cuenta', function () {
    [$actor, $role] = usuarioAdminPro();
    $target = User::factory()->create(['status' => 'true']);
    $target->assignRole($role);
    app(UserManagementService::class)->desactivar($target->id);

    expect(User::find($target->id))->not->toBeNull();
    expect($target->fresh()->status)->toBe('false');
    expect($target->fresh()->hasRole('Admin'))->toBeTrue();
});

test('no es posible desactivar la propia cuenta administradora', function () {
    [$actor, $role] = usuarioAdminPro();
    expect(fn () => app(UserManagementService::class)->desactivar($actor->id))
        ->toThrow(ValidationException::class);
    expect($actor->fresh()->status)->toBe('true');
});
