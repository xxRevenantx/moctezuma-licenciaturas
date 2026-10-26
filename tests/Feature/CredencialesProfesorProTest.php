<?php

use App\Livewire\Admin\Profesor\CredencialProfesor;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $permiso = Permission::findOrCreate('admin.administracion', 'web');
    $rol = Role::findOrCreate('SuperAdmin', 'web');
    $rol->givePermissionTo($permiso);
    $administrador = User::factory()->create(['status' => 'true']);
    $administrador->assignRole($rol);
    $this->actingAs($administrador);
});

function profesorCredencialPro(): Profesor
{
    $usuario = User::factory()->create(['status' => 'false']);

    return Profesor::query()->create([
        'user_id' => $usuario->id,
        'nombre' => 'ANA',
        'apellido_paterno' => 'GARCIA',
        'apellido_materno' => 'PEREZ',
        'status' => 'true',
        'color' => '#0284c7',
    ]);
}

test('la búsqueda y selección no alteran el acceso de una cuenta docente', function () {
    $profesor = profesorCredencialPro();

    Livewire::test(CredencialProfesor::class)
        ->set('busqueda', 'GARCIA')
        ->assertSee('GARCIA')
        ->call('seleccionarProfesor', $profesor->id)
        ->assertSet('seleccionados', [$profesor->id])
        ->call('abrirVistaPrevia')
        ->assertSet('vistaPrevia', true);

    expect($profesor->fresh()->user->status)->toBe('false');
    expect($profesor->fresh()->status)->toBe('true');
});

test('la previsualización rechaza identificadores manipulados', function () {
    Livewire::test(CredencialProfesor::class)
        ->set('seleccionados', [999999])
        ->call('abrirVistaPrevia')
        ->assertHasErrors('seleccionados');
});

test('el endpoint docente exige al menos un identificador válido', function () {
    $this->get(route('admin.pdf.documentacion.credencial_profesor'))
        ->assertSessionHasErrors('profesores_ids');
});

test('el endpoint docente rechaza IDs duplicados', function () {
    $profesor = profesorCredencialPro();

    $this->get(route('admin.pdf.documentacion.credencial_profesor', [
        'profesores_ids' => [$profesor->id, $profesor->id],
    ]))->assertSessionHasErrors('profesores_ids.0'); // distinct falla en ambas entradas
});

test('el formato especial exige licenciatura válida', function () {
    $profesor = profesorCredencialPro();

    $this->get(route('admin.pdf.documentacion.credencial_profesor_estudiante', [
        'profesores_ids' => [$profesor->id],
    ]))->assertSessionHasErrors('licenciatura_id');
});
