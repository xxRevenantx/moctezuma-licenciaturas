<?php

use App\Livewire\Admin\Documentacion\OficiosSolicitud;
use App\Services\Documentacion\DocumentacionExportService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function (): void {
    // Aislamiento obligatorio: nunca conectarse a la BD real de Control Escolar.
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
    DB::purge('sqlite');

    Schema::create('generaciones', function (Blueprint $t) {
        $t->id(); $t->string('generacion'); $t->string('activa')->default('true'); $t->timestamps();
    });
    Schema::create('licenciaturas', function (Blueprint $t) {
        $t->id(); $t->string('nombre'); $t->string('RVOE')->nullable(); $t->timestamps();
    });
    Schema::create('asignar_generaciones', function (Blueprint $t) {
        $t->id(); $t->unsignedBigInteger('generacion_id');
        $t->unsignedBigInteger('licenciatura_id'); $t->unsignedBigInteger('modalidad_id');
        $t->timestamps();
    });
    Schema::create('inscripciones', function (Blueprint $t) {
        $t->id(); $t->unsignedBigInteger('generacion_id');
        $t->unsignedBigInteger('licenciatura_id'); $t->string('status'); $t->timestamps();
    });
    Schema::create('escuelas', function (Blueprint $t) { $t->id(); $t->string('nombre'); $t->timestamps(); });
    Schema::create('directivos', function (Blueprint $t) {
        $t->id(); $t->string('identificador'); $t->string('status')->default('true'); $t->timestamps();
    });

    DB::table('generaciones')->insert([
        ['id' => 1, 'generacion' => '2023-2026', 'activa' => 'false'],
        ['id' => 2, 'generacion' => '2026-2029', 'activa' => 'true'],
    ]);
    DB::table('licenciaturas')->insert([
        ['id' => 1, 'nombre' => 'Administración', 'RVOE' => 'SEG/001/2021'],
        ['id' => 2, 'nombre' => 'Contaduría', 'RVOE' => 'SEG/002/2022'],
        ['id' => 3, 'nombre' => 'Inactiva', 'RVOE' => 'SEG/003/2023'],
        ['id' => 4, 'nombre' => 'Sin RVOE', 'RVOE' => null],
        ['id' => 5, 'nombre' => 'No asignada', 'RVOE' => 'SEG/005/2025'],
    ]);
    DB::table('asignar_generaciones')->insert([
        ['generacion_id' => 1, 'licenciatura_id' => 1, 'modalidad_id' => 1],
        ['generacion_id' => 1, 'licenciatura_id' => 1, 'modalidad_id' => 2], // No duplicar.
        ['generacion_id' => 1, 'licenciatura_id' => 2, 'modalidad_id' => 1],
        ['generacion_id' => 1, 'licenciatura_id' => 3, 'modalidad_id' => 1],
        ['generacion_id' => 1, 'licenciatura_id' => 4, 'modalidad_id' => 1],
    ]);
    DB::table('inscripciones')->insert([
        ['id' => 1, 'generacion_id' => 1, 'licenciatura_id' => 1, 'status' => 'true'],
        ['id' => 2, 'generacion_id' => 1, 'licenciatura_id' => 1, 'status' => 'true'],
        ['id' => 3, 'generacion_id' => 1, 'licenciatura_id' => 2, 'status' => 'true'],
        ['id' => 4, 'generacion_id' => 1, 'licenciatura_id' => 3, 'status' => 'false'],
        ['id' => 5, 'generacion_id' => 1, 'licenciatura_id' => 4, 'status' => 'true'],
        ['id' => 6, 'generacion_id' => 1, 'licenciatura_id' => 5, 'status' => 'true'],
        ['id' => 7, 'generacion_id' => 2, 'licenciatura_id' => 3, 'status' => 'true'],
    ]);
});

it('muestra solo las licenciaturas asignadas con RVOE y alumnos activos de esa generación', function () {
    $licenciaturas = app(DocumentacionExportService::class)->licenciaturasDisponiblesParaOficio(1);

    expect($licenciaturas->pluck('id')->all())->toBe([1, 2])
        ->and($licenciaturas->pluck('alumnos_activos_count')->map(fn ($x) => (int) $x)->all())->toBe([2, 1]);
});

it('ofrece las generaciones con licenciaturas realmente elegibles incluso si ya concluyeron', function () {
    $generaciones = app(DocumentacionExportService::class)->generacionesDisponiblesParaOficios();
    expect($generaciones->pluck('id')->all())->toBe([1]);
});

it('usa exactamente la misma lista filtrada para todos los oficios', function () {
    $service = app(DocumentacionExportService::class);
    $todos = $service->oficio(1, 'matriculas', '2026-10-22');
    $uno = $service->oficio(1, 'kardex', '2026-10-22', [2]);

    expect($todos['data']['licenciaturas']->pluck('id')->all())->toBe([1, 2])
        ->and($uno['data']['licenciaturas']->pluck('id')->all())->toBe([2])
        ->and($uno['filenameBase'])->toContain('_LIC_2');
});

it('bloquea licenciaturas no elegibles y selecciones vacías', function () {
    $service = app(DocumentacionExportService::class);
    foreach ([[3], [4], [5], []] as $ids) {
        try {
            $service->oficio(1, 'matriculas', '2026-10-22', $ids);
            throw new RuntimeException('Se aceptó una selección no válida.');
        } catch (HttpException $e) {
            expect($e->getStatusCode())->toBe(422);
        }
    }
});

it('limpia la selección al cambiar de generación en la interfaz', function () {
    Livewire::test(OficiosSolicitud::class)
        ->set('generacionId', '1')
        ->set('alcance', 'seleccion')
        ->call('seleccionarTodas')
        ->assertSet('licenciaturaIds', [1, 2])
        ->set('generacionId', '2')
        ->assertSet('alcance', 'todas')
        ->assertSet('licenciaturaIds', []);
});
