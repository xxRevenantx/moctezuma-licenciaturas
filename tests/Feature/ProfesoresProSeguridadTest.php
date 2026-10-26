<?php

use App\Models\Profesor;
use App\Models\User;
use App\Services\Profesores\ProfesorManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;

uses(RefreshDatabase::class);

function accesoAdministracionDocente(): User
{
    $permiso = Permission::findOrCreate('admin.administracion', 'web');
    $rol = Role::findOrCreate('SuperAdmin', 'web');
    $rol->givePermissionTo($permiso);
    $actor = User::factory()->create(['status' => 'true']);
    $actor->assignRole($rol);
    auth()->login($actor);

    return $actor;
}

function cuentaDocente(string $estado = 'false'): User
{
    $rol = Role::findOrCreate('Profesor', 'web');
    $usuario = User::factory()->create(['status' => $estado]);
    $usuario->assignRole($rol);
    return $usuario;
}

function datosProfesor(User $usuario): array
{
    return [
        'user_id' => $usuario->id, 'nombre' => 'ANA',
        'apellido_paterno' => 'GARCIA', 'apellido_materno' => 'PEREZ',
        'telefono' => '7441234567', 'perfil' => 'MATEMATICAS', 'color' => '#0284c7',
    ];
}

test('permite crear expediente docente con cuenta pendiente sin activar su acceso', function () {
    accesoAdministracionDocente();
    $usuario = cuentaDocente('false');
    $servicio = app(ProfesorManagementService::class);
    $profesor = $servicio->crear(datosProfesor($usuario));

    expect($profesor->user_id)->toBe($usuario->id);
    expect($profesor->status)->toBe('true');
    expect($usuario->fresh()->status)->toBe('false');
    expect($servicio->cuentasElegibles()->contains('id', $usuario->id))->toBeFalse();
});

test('rechaza usuario sin rol Profesor aunque se manipule el ID', function () {
    accesoAdministracionDocente();
    $usuario = User::factory()->create(['status' => 'true']);
    expect(fn () => app(ProfesorManagementService::class)->crear(datosProfesor($usuario)))
        ->toThrow(ValidationException::class);
    expect(Profesor::count())->toBe(0);
});

test('impide duplicar expediente de una misma cuenta docente', function () {
    accesoAdministracionDocente();
    $usuario = cuentaDocente();
    $servicio = app(ProfesorManagementService::class);
    $servicio->crear(datosProfesor($usuario));
    expect(fn () => $servicio->crear(datosProfesor($usuario)))->toThrow(ValidationException::class);
    expect(Profesor::where('user_id', $usuario->id)->count())->toBe(1);
});

test('cambios masivos de disponibilidad no alteran la cuenta ni eliminan expedientes', function () {
    accesoAdministracionDocente();
    $usuario = cuentaDocente('true');
    $servicio = app(ProfesorManagementService::class);
    $profesor = $servicio->crear(datosProfesor($usuario));

    expect($servicio->cambiarDisponibilidad([$profesor->id], false))->toBe(1);
    expect($profesor->fresh()->status)->toBe('false');
    expect($usuario->fresh()->status)->toBe('true');
    expect(Profesor::find($profesor->id))->not->toBeNull();
    expect($servicio->cambiarDisponibilidad([$profesor->id], true))->toBe(1);
    expect($profesor->fresh()->status)->toBe('true');
});

test('una selección masiva con IDs inexistentes se rechaza completa', function () {
    accesoAdministracionDocente();
    $usuario = cuentaDocente('true');
    $servicio = app(ProfesorManagementService::class);
    $profesor = $servicio->crear(datosProfesor($usuario));
    expect(fn () => $servicio->cambiarDisponibilidad([$profesor->id, 9999999], false))
        ->toThrow(ValidationException::class);
    expect($profesor->fresh()->status)->toBe('true');
});

test('sin permiso de administración no se pueden alterar docentes', function () {
    $actor = User::factory()->create(['status' => 'true']);
    auth()->login($actor);
    $usuario = cuentaDocente();
    expect(fn () => app(ProfesorManagementService::class)->crear(datosProfesor($usuario)))
        ->toThrow(HttpException::class);
});

test('editar docente preserva la cuenta y los campos fuera del expediente', function () {
    accesoAdministracionDocente();
    $usuario = cuentaDocente('false');
    $servicio = app(ProfesorManagementService::class);
    $profesor = $servicio->crear(datosProfesor($usuario));
    $actualizados = datosProfesor($usuario);
    $actualizados['perfil'] = 'EDUCACION';
    $actualizados['color'] = '#1a2b3c';
    $servicio->actualizar($profesor->id, $actualizados);
    expect($profesor->fresh()->perfil)->toBe('EDUCACION');
    expect($usuario->fresh()->status)->toBe('false');
});
