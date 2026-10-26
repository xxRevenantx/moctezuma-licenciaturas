<?php

use App\Livewire\Admin\Documentacion\Identidad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $rol = Role::findOrCreate('SuperAdmin', 'web');
    foreach (['documentos-identidad.ver', 'documentos-identidad.descargar', 'documentos-identidad.auditar'] as $nombre) {
        $rol->givePermissionTo(Permission::findOrCreate($nombre, 'web'));
    }
    $usuario = User::factory()->create(['status' => 'true']);
    $usuario->assignRole($rol);
    $this->actingAs($usuario);
});

test('el gestor abre sin alumno y conserva filtros y exportaciones institucionales', function () {
    Livewire::test(Identidad::class)
        ->assertSee('Expediente de identidad')
        ->assertSee('Localizar expediente estudiantil')
        ->assertSee('Control PDF')
        ->assertSee('Control Word')
        ->assertSee('Comienza seleccionando un estudiante')
        ->assertSet('selectedAlumno', null);
});

test('la selección por ID rechaza registros que no aparecen en los resultados vigentes', function () {
    Livewire::test(Identidad::class)
        ->call('seleccionarAlumnoPorId', 99999999)
        ->assertSet('selectedAlumno', null)
        ->assertDispatched('swal');
});

test('los filtros se pueden cambiar sin seleccionar ni modificar expedientes', function () {
    Livewire::test(Identidad::class)
        ->set('estado', 'todos')
        ->assertSet('estado', 'todos')
        ->call('cambiarVistaDocumentos', true)
        ->assertSet('mostrarSoloPendientes', true)
        ->call('limpiarAlumno')
        ->assertSet('mostrarSoloPendientes', false)
        ->assertSet('selectedAlumno', null);
});
