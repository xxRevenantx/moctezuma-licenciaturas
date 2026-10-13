<?php

use App\Models\User;
use App\Services\CalificacionesHistoricasService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    // Aislamiento: jamás importar/modificar la base MySQL real del plantel.
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
    DB::purge('sqlite');

    Schema::create('inscripciones', function (Blueprint $t) {
        $t->id();
        $t->string('nombre'); $t->string('apellido_paterno'); $t->string('apellido_materno')->nullable();
        $t->string('matricula')->nullable(); $t->string('matricula_interna')->nullable();
        $t->string('CURP')->nullable(); $t->string('status'); $t->unsignedBigInteger('cuatrimestre_id');
    });
    Schema::create('calificaciones', function (Blueprint $t) {
        $t->id();
        foreach (['alumno_id', 'asignacion_materia_id', 'profesor_id', 'licenciatura_id',
            'modalidad_id', 'generacion_id', 'cuatrimestre_id'] as $campo) $t->unsignedBigInteger($campo);
        $t->string('calificacion')->nullable(); $t->timestamps();
    });
    Schema::create('calificacion_auditorias', function (Blueprint $t) {
        $t->id(); $t->unsignedBigInteger('calificacion_id')->nullable();
        foreach (['alumno_id', 'asignacion_materia_id', 'profesor_id', 'licenciatura_id',
            'modalidad_id', 'generacion_id', 'cuatrimestre_id', 'user_id'] as $campo) $t->unsignedBigInteger($campo)->nullable();
        $t->string('valor_anterior')->nullable(); $t->string('valor_nuevo')->nullable();
        $t->string('origen'); $t->string('accion'); $t->text('detalle')->nullable(); $t->timestamps();
    });
    foreach (['asignacion_materias', 'materias', 'cuatrimestres', 'generaciones', 'licenciaturas', 'modalidades'] as $tabla) {
        Schema::create($tabla, function (Blueprint $t) {
            $t->id(); $t->string('nombre')->nullable(); $t->string('clave')->nullable();
            $t->string('cuatrimestre')->nullable(); $t->string('generacion')->nullable();
            $t->unsignedBigInteger('materia_id')->nullable();
        });
    }
    DB::table('inscripciones')->insert([
        ['id' => 11, 'nombre' => 'María', 'apellido_paterno' => 'Castillo', 'matricula_interna' => 'ABC01', 'CURP' => 'ABC111', 'status' => 'true', 'cuatrimestre_id' => 7],
        ['id' => 12, 'nombre' => 'Luis', 'apellido_paterno' => 'Pérez', 'matricula_interna' => 'ABC02', 'CURP' => 'ABC222', 'status' => 'false', 'cuatrimestre_id' => 4],
    ]);
    DB::table('calificaciones')->insert([
        ['id' => 100, 'alumno_id' => 11, 'asignacion_materia_id' => 21, 'profesor_id' => 5, 'licenciatura_id' => 1, 'modalidad_id' => 2, 'generacion_id' => 3, 'cuatrimestre_id' => 1, 'calificacion' => '8'],
        ['id' => 101, 'alumno_id' => 11, 'asignacion_materia_id' => 22, 'profesor_id' => 6, 'licenciatura_id' => 1, 'modalidad_id' => 1, 'generacion_id' => 3, 'cuatrimestre_id' => 7, 'calificacion' => '9'],
        ['id' => 102, 'alumno_id' => 12, 'asignacion_materia_id' => 23, 'profesor_id' => 5, 'licenciatura_id' => 1, 'modalidad_id' => 2, 'generacion_id' => 3, 'cuatrimestre_id' => 2, 'calificacion' => '10'],
    ]);
});

function administradorHistorico(): User
{
    $usuario = Mockery::mock(User::class)->makePartial();
    $usuario->id = 1;
    $usuario->shouldReceive('hasAnyRole')->with(['SuperAdmin', 'Admin'])->andReturn(true);
    $usuario->shouldReceive('can')->with('calificaciones-docente.ver')->andReturn(true);
    return $usuario;
}

it('lista las notas reales de todos los cuatrimestres, independientemente del cursado', function () {
    $servicio = app(CalificacionesHistoricasService::class);
    $usuario = administradorHistorico();
    $ids = $servicio->notas($usuario, 11)->pluck('id')->all();
    expect($ids)->toBe([100, 101]);
    expect($servicio->buscarAlumnos($usuario, 'ABC02')->first()->status)->toBe('false');
});

it('corrige sólo el valor de un cuatrimestre anterior y audita con motivo', function () {
    $servicio = app(CalificacionesHistoricasService::class);
    $cambio = $servicio->corregirNota(administradorHistorico(), 11, 100, '10', 'Corrección según acta escolar autorizada', '8');
    expect($cambio)->toBeTrue();
    $registro = DB::table('calificaciones')->where('id', 100)->first();
    expect($registro->calificacion)->toBe('10')
        ->and($registro->cuatrimestre_id)->toBe(1)
        ->and($registro->modalidad_id)->toBe(2)
        ->and($registro->asignacion_materia_id)->toBe(21)
        ->and(DB::table('calificaciones')->where('id', 101)->value('calificacion'))->toBe('9');
    $auditoria = DB::table('calificacion_auditorias')->first();
    expect($auditoria->calificacion_id)->toBe(100)
        ->and($auditoria->origen)->toBe('historico')
        ->and($auditoria->valor_anterior)->toBe('8')
        ->and($auditoria->valor_nuevo)->toBe('10')
        ->and(json_decode($auditoria->detalle, true)['motivo'])->toBe('Corrección según acta escolar autorizada');
});

it('impide modificar una nota de otro alumno o una versión desactualizada', function () {
    $servicio = app(CalificacionesHistoricasService::class);
    expect(fn () => $servicio->corregirNota(administradorHistorico(), 12, 100, '7', 'Motivo documentado', '8'))
        ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    expect(fn () => $servicio->corregirNota(administradorHistorico(), 11, 100, '7', 'Motivo documentado', '9'))
        ->toThrow(ValidationException::class);
    expect(DB::table('calificaciones')->where('id', 100)->value('calificacion'))->toBe('8');
});

it('no permite al profesor corregir el historial completo', function () {
    $profesor = Mockery::mock(User::class)->makePartial();
    $profesor->shouldReceive('hasAnyRole')->with(['SuperAdmin', 'Admin'])->andReturn(false);
    expect(fn () => app(CalificacionesHistoricasService::class)->corregirNota(
        $profesor, 11, 100, '9', 'Motivo documentado', '8'
    ))->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
});
