<?php

use App\Services\AcademicMovementService;
use App\Services\Boletas\BoletaService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    // Nunca se utiliza la base de datos del centro escolar en esta suite.
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
    DB::purge('sqlite');

    Schema::create('users', function (Blueprint $table) {
        $table->id(); $table->string('email')->nullable(); $table->timestamps();
    });
    Schema::create('licenciaturas', function (Blueprint $table) {
        $table->id(); $table->string('nombre'); $table->timestamps();
    });
    Schema::create('modalidades', function (Blueprint $table) {
        $table->id(); $table->string('nombre'); $table->timestamps();
    });
    Schema::create('generaciones', function (Blueprint $table) {
        $table->id(); $table->string('generacion'); $table->string('activa'); $table->timestamps();
    });
    Schema::create('cuatrimestres', function (Blueprint $table) {
        $table->id(); $table->string('nombre_cuatrimestre'); $table->integer('cuatrimestre'); $table->timestamps();
    });
    Schema::create('inscripciones', function (Blueprint $table) {
        $table->id(); $table->unsignedBigInteger('user_id');
        foreach (['licenciatura_id', 'generacion_id', 'modalidad_id', 'cuatrimestre_id'] as $campo) {
            $table->unsignedBigInteger($campo);
        }
        $table->string('matricula')->nullable(); $table->string('matricula_interna')->nullable();
        $table->string('nombre'); $table->string('apellido_paterno'); $table->string('apellido_materno')->nullable();
        $table->string('status'); $table->string('egresado'); $table->timestamps();
    });
    Schema::create('asignar_generaciones', function (Blueprint $table) {
        $table->id(); $table->unsignedBigInteger('licenciatura_id');
        $table->unsignedBigInteger('modalidad_id'); $table->unsignedBigInteger('generacion_id');
        $table->timestamps();
    });
    Schema::create('periodos', function (Blueprint $table) {
        $table->id(); $table->unsignedBigInteger('generacion_id');
        $table->unsignedBigInteger('cuatrimestre_id'); $table->string('ciclo_escolar');
        $table->integer('order'); $table->timestamps();
    });
    Schema::create('materias', function (Blueprint $table) {
        $table->id(); $table->unsignedBigInteger('licenciatura_id');
        $table->unsignedBigInteger('cuatrimestre_id');
        $table->string('nombre'); $table->string('clave');
        $table->string('calificable'); $table->integer('orden'); $table->timestamps();
    });
    Schema::create('asignacion_materias', function (Blueprint $table) {
        $table->id(); $table->unsignedBigInteger('licenciatura_id');
        $table->unsignedBigInteger('modalidad_id'); $table->unsignedBigInteger('cuatrimestre_id');
        $table->unsignedBigInteger('materia_id'); $table->unsignedBigInteger('profesor_id')->nullable();
        $table->timestamps();
    });
    Schema::create('calificaciones', function (Blueprint $table) {
        $table->id();
        foreach (['alumno_id', 'licenciatura_id', 'generacion_id', 'modalidad_id', 'cuatrimestre_id', 'asignacion_materia_id'] as $campo) {
            $table->unsignedBigInteger($campo);
        }
        $table->unsignedBigInteger('profesor_id')->nullable();
        $table->string('calificacion'); $table->timestamps();
    });
    Schema::create('movimientos_academicos', function (Blueprint $table) {
        $table->id(); $table->string('lote'); $table->string('tipo');
        $table->unsignedBigInteger('inscripcion_id')->nullable();
        foreach (['licenciatura_id', 'generacion_id', 'generacion_origen_id', 'generacion_destino_id',
            'modalidad_origen_id', 'modalidad_destino_id', 'cuatrimestre_origen_id', 'cuatrimestre_destino_id',
            'ejecutado_por', 'revertido_por'] as $campo) $table->unsignedBigInteger($campo)->nullable();
        $table->string('matricula_snapshot')->nullable(); $table->string('matricula_interna_snapshot')->nullable();
        $table->string('alumno_snapshot')->nullable(); $table->string('motivo');
        $table->text('observaciones')->nullable(); $table->string('estado');
        $table->integer('calificaciones_trasladadas'); $table->text('detalles')->nullable();
        $table->timestamp('revertido_at')->nullable(); $table->timestamps();
    });

    DB::table('users')->insert(['id' => 1, 'email' => 'prueba@ejemplo.test']);
    DB::table('licenciaturas')->insert(['id' => 1, 'nombre' => 'Licenciatura prueba']);
    DB::table('modalidades')->insert(['id' => 1, 'nombre' => 'Escolarizada']);
    DB::table('generaciones')->insert([
        ['id' => 1, 'generacion' => '2026-2029', 'activa' => 'true'],
        ['id' => 2, 'generacion' => '2025-2028', 'activa' => 'true'],
    ]);
    DB::table('cuatrimestres')->insert([
        ['id' => 1, 'nombre_cuatrimestre' => '1º CUATRIMESTRE', 'cuatrimestre' => 1],
        ['id' => 2, 'nombre_cuatrimestre' => '2º CUATRIMESTRE', 'cuatrimestre' => 2],
    ]);
    DB::table('inscripciones')->insert([
        'id' => 1, 'user_id' => 1, 'matricula' => '123456', 'matricula_interna' => 'ABC01',
        'nombre' => 'Elena', 'apellido_paterno' => 'López', 'apellido_materno' => 'Pérez',
        'licenciatura_id' => 1, 'generacion_id' => 1, 'modalidad_id' => 1, 'cuatrimestre_id' => 1,
        'status' => 'true', 'egresado' => 'false',
    ]);
    DB::table('asignar_generaciones')->insert([
        ['licenciatura_id' => 1, 'modalidad_id' => 1, 'generacion_id' => 1],
        ['licenciatura_id' => 1, 'modalidad_id' => 1, 'generacion_id' => 2],
    ]);
    DB::table('periodos')->insert([
        ['generacion_id' => 1, 'cuatrimestre_id' => 1, 'ciclo_escolar' => '2026-2027', 'order' => 1],
        ['generacion_id' => 2, 'cuatrimestre_id' => 1, 'ciclo_escolar' => '2025-2026', 'order' => 1],
        ['generacion_id' => 2, 'cuatrimestre_id' => 2, 'ciclo_escolar' => '2025-2026', 'order' => 2],
    ]);
    DB::table('materias')->insert([
        ['id' => 1, 'licenciatura_id' => 1, 'cuatrimestre_id' => 1, 'nombre' => 'Materia I', 'clave' => 'M1', 'calificable' => 'true', 'orden' => 1],
        ['id' => 2, 'licenciatura_id' => 1, 'cuatrimestre_id' => 2, 'nombre' => 'Materia II', 'clave' => 'M2', 'calificable' => 'true', 'orden' => 2],
    ]);
    DB::table('asignacion_materias')->insert([
        ['id' => 1, 'licenciatura_id' => 1, 'modalidad_id' => 1, 'cuatrimestre_id' => 1, 'materia_id' => 1, 'profesor_id' => 1],
        ['id' => 2, 'licenciatura_id' => 1, 'modalidad_id' => 1, 'cuatrimestre_id' => 2, 'materia_id' => 2, 'profesor_id' => 1],
    ]);
    DB::table('calificaciones')->insert([
        'id' => 1, 'alumno_id' => 1, 'licenciatura_id' => 1, 'generacion_id' => 1,
        'modalidad_id' => 1, 'cuatrimestre_id' => 1, 'asignacion_materia_id' => 1,
        'profesor_id' => 1, 'calificacion' => '9',
    ]);
});

it('transfiere generación y cuatrimestre sin alterar calificaciones, boleta histórica ni matrícula', function () {
    $servicio = app(AcademicMovementService::class);
    expect($servicio->analizarCambioGeneracion([1], 2, 2)['valido'])->toBeTrue();
    $resultado = $servicio->cambiarGeneracion([1], 2, 2, 'solicitud_alumno');
    expect($resultado['total_alumnos'])->toBe(1)
        ->and($resultado['calificaciones_trasladadas'])->toBe(0)
        ->and(DB::table('inscripciones')->value('generacion_id'))->toBe(2)
        ->and(DB::table('inscripciones')->value('cuatrimestre_id'))->toBe(2)
        ->and(DB::table('inscripciones')->value('matricula'))->toBe('123456')
        ->and(DB::table('calificaciones')->where('id', 1)->value('generacion_id'))->toBe(1)
        ->and(DB::table('calificaciones')->where('id', 1)->value('calificacion'))->toBe('9');
    expect(app(BoletaService::class)->validarAlumnoEnContexto(1, 1, 1, 1)->id)->toBe(1);
    expect(DB::table('movimientos_academicos')->value('generacion_origen_id'))->toBe(1)
        ->and(DB::table('movimientos_academicos')->value('generacion_destino_id'))->toBe(2);
});

it('bloquea un destino con calificaciones existentes, sin cambios parciales', function () {
    DB::table('calificaciones')->insert([
        'id' => 2, 'alumno_id' => 1, 'licenciatura_id' => 1, 'generacion_id' => 2,
        'modalidad_id' => 1, 'cuatrimestre_id' => 2, 'asignacion_materia_id' => 2,
        'profesor_id' => 1, 'calificacion' => '8',
    ]);
    $servicio = app(AcademicMovementService::class);
    expect($servicio->analizarCambioGeneracion([1], 2, 2)['valido'])->toBeFalse();
    expect(fn () => $servicio->cambiarGeneracion([1], 2, 2, 'solicitud_alumno'))
        ->toThrow(DomainException::class);
    expect(DB::table('inscripciones')->value('generacion_id'))->toBe(1)
        ->and(DB::table('movimientos_academicos')->count())->toBe(0);
});

it('revierte una transferencia sin capturas posteriores', function () {
    $servicio = app(AcademicMovementService::class);
    $res = $servicio->cambiarGeneracion([1], 2, null, 'ajuste_administrativo');
    $servicio->revertir($res['movimientos_ids'][0]);
    expect(DB::table('inscripciones')->value('generacion_id'))->toBe(1)
        ->and(DB::table('inscripciones')->value('cuatrimestre_id'))->toBe(1)
        ->and(DB::table('calificaciones')->value('calificacion'))->toBe('9')
        ->and(DB::table('movimientos_academicos')->value('estado'))->toBe('revertido');
});

it('no revierte si se capturó una calificación en la generación destino', function () {
    $servicio = app(AcademicMovementService::class);
    $res = $servicio->cambiarGeneracion([1], 2, 2, 'solicitud_alumno');
    DB::table('calificaciones')->insert([
        'id' => 2, 'alumno_id' => 1, 'licenciatura_id' => 1, 'generacion_id' => 2,
        'modalidad_id' => 1, 'cuatrimestre_id' => 2, 'asignacion_materia_id' => 2,
        'profesor_id' => 1, 'calificacion' => '10',
    ]);
    expect(fn () => $servicio->revertir($res['movimientos_ids'][0]))->toThrow(DomainException::class);
    expect(DB::table('inscripciones')->value('generacion_id'))->toBe(2)
        ->and(DB::table('movimientos_academicos')->value('estado'))->toBe('aplicado');
});

it('bloquea una generación sin periodo para el cuatrimestre solicitado', function () {
    DB::table('periodos')->where('generacion_id', 2)->where('cuatrimestre_id', 2)->delete();
    expect(app(AcademicMovementService::class)->analizarCambioGeneracion([1], 2, 2)['valido'])->toBeFalse();
});
