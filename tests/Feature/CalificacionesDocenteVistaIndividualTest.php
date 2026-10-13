<?php

use App\Models\User;
use App\Services\CalificacionesDocenteService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    // Nunca utiliza ni restaura el SQL real de Control Escolar.
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
    DB::purge('sqlite');

    Schema::create('profesores', function (Blueprint $t) {
        $t->id(); $t->string('nombre'); $t->string('apellido_paterno')->nullable(); $t->string('apellido_materno')->nullable();
    });
    Schema::create('licenciaturas', function (Blueprint $t) {
        $t->id(); $t->string('nombre'); $t->string('nombre_corto')->nullable();
    });
    Schema::create('modalidades', function (Blueprint $t) { $t->id(); $t->string('nombre'); });
    Schema::create('generaciones', function (Blueprint $t) { $t->id(); $t->string('generacion'); });
    Schema::create('cuatrimestres', function (Blueprint $t) { $t->id(); $t->string('cuatrimestre'); });
    Schema::create('materias', function (Blueprint $t) {
        $t->id(); $t->string('nombre'); $t->string('clave')->nullable(); $t->string('calificable');
    });
    Schema::create('asignacion_materias', function (Blueprint $t) {
        $t->id();
        foreach (['profesor_id', 'licenciatura_id', 'modalidad_id', 'cuatrimestre_id', 'materia_id'] as $col) $t->unsignedBigInteger($col);
    });
    Schema::create('horarios', function (Blueprint $t) {
        $t->id();
        foreach (['licenciatura_id', 'modalidad_id', 'generacion_id', 'cuatrimestre_id', 'asignacion_materia_id'] as $col) $t->unsignedBigInteger($col);
    });
    Schema::create('inscripciones', function (Blueprint $t) {
        $t->id();
        foreach (['licenciatura_id', 'modalidad_id', 'generacion_id'] as $col) $t->unsignedBigInteger($col);
        foreach (['nombre', 'apellido_paterno', 'apellido_materno', 'status', 'foraneo'] as $col) $t->string($col);
        $t->string('matricula')->nullable(); $t->string('matricula_interna')->nullable();
    });
    Schema::create('calificaciones', function (Blueprint $t) {
        $t->id(); $t->unsignedBigInteger('alumno_id'); $t->unsignedBigInteger('cuatrimestre_id'); $t->string('calificacion')->nullable();
    });

    DB::table('profesores')->insert([
        ['id' => 1, 'nombre' => 'Ana', 'apellido_paterno' => 'Morales'],
        ['id' => 2, 'nombre' => 'Luis', 'apellido_paterno' => 'Pérez'],
    ]);
    DB::table('licenciaturas')->insert([['id' => 1, 'nombre' => 'Ciencias de la Educación', 'nombre_corto' => 'LCE']]);
    DB::table('modalidades')->insert([['id' => 1, 'nombre' => 'Escolarizada']]);
    DB::table('generaciones')->insert([['id' => 1, 'generacion' => '2026-2029'], ['id' => 2, 'generacion' => '2025-2028']]);
    DB::table('cuatrimestres')->insert([['id' => 1, 'cuatrimestre' => '1'], ['id' => 2, 'cuatrimestre' => '2']]);
    DB::table('materias')->insert([
        ['id' => 1, 'nombre' => 'Didáctica', 'clave' => 'LCE1', 'calificable' => 'true'],
        ['id' => 2, 'nombre' => 'Investigación', 'clave' => 'LCE2', 'calificable' => 'true'],
    ]);
    DB::table('asignacion_materias')->insert([
        ['id' => 1, 'profesor_id' => 1, 'licenciatura_id' => 1, 'modalidad_id' => 1, 'cuatrimestre_id' => 1, 'materia_id' => 1],
        ['id' => 2, 'profesor_id' => 2, 'licenciatura_id' => 1, 'modalidad_id' => 1, 'cuatrimestre_id' => 1, 'materia_id' => 2],
    ]);
    foreach ([1, 2] as $id) {
        DB::table('horarios')->insert([
            'asignacion_materia_id' => $id, 'generacion_id' => 1,
            'licenciatura_id' => 1, 'modalidad_id' => 1, 'cuatrimestre_id' => 1,
        ]);
    }
    DB::table('inscripciones')->insert([
        ['id' => 1, 'nombre' => 'Marta', 'apellido_paterno' => 'García', 'apellido_materno' => 'López', 'matricula' => 'A1', 'matricula_interna' => '12345', 'status' => 'true', 'foraneo' => 'false', 'licenciatura_id' => 1, 'modalidad_id' => 1, 'generacion_id' => 1],
        ['id' => 2, 'nombre' => 'Marta', 'apellido_paterno' => 'Pérez', 'apellido_materno' => 'Ruiz', 'matricula' => 'A2', 'matricula_interna' => '12346', 'status' => 'true', 'foraneo' => 'false', 'licenciatura_id' => 1, 'modalidad_id' => 1, 'generacion_id' => 2],
    ]);
});

it('localiza únicamente alumnos de grupos con horario calificable y autorizado', function () {
    $admin = Mockery::mock(User::class)->makePartial();
    $admin->shouldReceive('hasRole')->with('Profesor')->andReturn(false);

    $alumnos = app(CalificacionesDocenteService::class)->buscarAlumnosAutorizados($admin, 'Marta');
    expect($alumnos)->toHaveCount(1)
        ->and($alumnos->first()['id'])->toBe(1)
        ->and($alumnos->first()['asignacion_id'])->toBeIn([1, 2]);
});

it('limita al profesor a sus propias materias autorizadas', function () {
    $docente = Mockery::mock(User::class)->makePartial();
    $docente->shouldReceive('hasRole')->with('Profesor')->andReturn(true);
    $docente->shouldReceive('hasAnyRole')->andReturn(false);
    $docente->setRelation('profesor', (object) ['id' => 1]);

    $service = app(CalificacionesDocenteService::class);
    expect($service->consultaAsignaciones($docente)->pluck('asignacion_materia_id')->all())->toBe([1]);
    expect($service->buscarAlumnosAutorizados($docente, '12345')->first()['asignacion_id'])->toBe(1);
});

it('calcula alertas descriptivas usando sólo notas numéricas registradas', function () {
    DB::table('calificaciones')->insert([
        ['alumno_id' => 1, 'cuatrimestre_id' => 1, 'calificacion' => '9'],
        ['alumno_id' => 1, 'cuatrimestre_id' => 1, 'calificacion' => '8'],
        ['alumno_id' => 1, 'cuatrimestre_id' => 2, 'calificacion' => '5'],
        ['alumno_id' => 1, 'cuatrimestre_id' => 2, 'calificacion' => 'NP'],
        ['alumno_id' => 2, 'cuatrimestre_id' => 2, 'calificacion' => '10'],
    ]);

    $resumen = app(CalificacionesDocenteService::class)->diagnosticoAlumno(1, 2);
    expect($resumen['actual']['promedio'])->toEqual(5)
        ->and($resumen['actual']['reprobadas'])->toBe(1)
        ->and($resumen['alertas'])->toHaveCount(3)
        ->and($resumen['historial'])->toHaveCount(2);
});
