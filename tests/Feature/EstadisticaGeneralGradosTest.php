<?php

use App\Services\EstadisticaGeneralGradosService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

beforeEach(function (): void {
    // La batería trabaja exclusivamente con SQLite en memoria.
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
    DB::purge('sqlite');
    Carbon::setTestNow('2026-10-28 10:00:00');

    Schema::create('generaciones', function (Blueprint $t) {
        $t->id(); $t->string('generacion'); $t->string('activa');
    });
    Schema::create('licenciaturas', function (Blueprint $t) {
        $t->id(); $t->string('nombre');
    });
    Schema::create('modalidades', function (Blueprint $t) {
        $t->id(); $t->string('nombre');
    });
    Schema::create('cuatrimestres', function (Blueprint $t) {
        $t->id(); $t->string('cuatrimestre');
    });
    Schema::create('periodos', function (Blueprint $t) {
        $t->id(); $t->unsignedBigInteger('generacion_id'); $t->unsignedBigInteger('cuatrimestre_id');
        $t->date('inicio_periodo')->nullable(); $t->date('termino_periodo')->nullable();
    });
    Schema::create('inscripciones', function (Blueprint $t) {
        $t->id(); $t->unsignedBigInteger('generacion_id'); $t->unsignedBigInteger('licenciatura_id');
        $t->unsignedBigInteger('modalidad_id'); $t->unsignedBigInteger('cuatrimestre_id');
        $t->string('sexo'); $t->string('status'); $t->string('egresado');
        $t->string('nombre')->default('Alumno'); $t->string('apellido_paterno')->default('Prueba');
        $t->string('apellido_materno')->nullable(); $t->string('matricula')->nullable(); $t->string('matricula_interna')->nullable();
        $t->dateTime('fecha_baja')->nullable();
    });

    DB::table('generaciones')->insert([
        ['id' => 1, 'generacion' => '2023-2026', 'activa' => 'true'],  // marcada activa, pero terminó
        ['id' => 2, 'generacion' => '2024-2027', 'activa' => 'true'],  // vigente
        ['id' => 3, 'generacion' => '2025-2028', 'activa' => 'true'],  // vigente
        ['id' => 4, 'generacion' => '2026-2029', 'activa' => 'false'], // vigente por fecha, baja administrativa
        ['id' => 5, 'generacion' => '2026-2029 A', 'activa' => 'true'], // sin periodo publicado
        ['id' => 6, 'generacion' => '2025-2028 B', 'activa' => 'true'], // fecha vigente, 9º ya cerrado
    ]);
    DB::table('licenciaturas')->insert([
        ['id' => 1, 'nombre' => 'Criminología'],
        ['id' => 2, 'nombre' => 'Cultura Física'],
    ]);
    DB::table('modalidades')->insert([
        ['id' => 1, 'nombre' => 'Escolarizada'], ['id' => 2, 'nombre' => 'Semiescolarizada'],
    ]);
    // IDs deliberadamente distintos del número: no depender de c.id.
    foreach (range(1, 9) as $numero) {
        DB::table('cuatrimestres')->insert(['id' => $numero + 10, 'cuatrimestre' => (string) $numero]);
    }
    DB::table('periodos')->insert([
        ['generacion_id' => 1, 'cuatrimestre_id' => 19, 'inicio_periodo' => '2026-05-01', 'termino_periodo' => '2026-08-31'],
        ['generacion_id' => 2, 'cuatrimestre_id' => 17, 'inicio_periodo' => '2026-09-01', 'termino_periodo' => '2026-12-18'],
        ['generacion_id' => 3, 'cuatrimestre_id' => 14, 'inicio_periodo' => '2026-09-01', 'termino_periodo' => '2026-12-18'],
        ['generacion_id' => 4, 'cuatrimestre_id' => 11, 'inicio_periodo' => '2026-09-01', 'termino_periodo' => '2026-12-18'],
        ['generacion_id' => 6, 'cuatrimestre_id' => 14, 'inicio_periodo' => '2026-09-01', 'termino_periodo' => '2026-12-18'],
        ['generacion_id' => 6, 'cuatrimestre_id' => 19, 'inicio_periodo' => '2026-01-01', 'termino_periodo' => '2026-08-31'],
    ]);
    $base = [
        'generacion_id' => 2, 'licenciatura_id' => 1, 'modalidad_id' => 1,
        'cuatrimestre_id' => 11, 'sexo' => 'H', 'status' => 'true', 'egresado' => 'false',
        'fecha_baja' => null,
    ];
    DB::table('inscripciones')->insert([
        [...$base, 'id' => 1, 'cuatrimestre_id' => 11], // primero: H
        [...$base, 'id' => 2, 'cuatrimestre_id' => 13, 'sexo' => 'M'], // tercero: M
        [...$base, 'id' => 3, 'cuatrimestre_id' => 14, 'sexo' => 'M'], // cuarto: M (aunque generación esté en 7º)
        [...$base, 'id' => 4, 'cuatrimestre_id' => 16, 'sexo' => 'H', 'modalidad_id' => 2], // sexto: H
        [...$base, 'id' => 5, 'cuatrimestre_id' => 17, 'sexo' => 'H', 'licenciatura_id' => 2], // séptimo: H
        [...$base, 'id' => 6, 'generacion_id' => 3, 'cuatrimestre_id' => 19, 'sexo' => 'M'], // noveno, sin fecha final para generación 3
        [...$base, 'id' => 7, 'status' => 'false'], // baja
        [...$base, 'id' => 8, 'fecha_baja' => '2026-10-01'], // baja con status true
        [...$base, 'id' => 9, 'egresado' => 'true'], // egresado
        [...$base, 'id' => 10, 'generacion_id' => 1], // generación pasada marcada activa
        [...$base, 'id' => 11, 'generacion_id' => 4], // generación inactiva administrativamente
        [...$base, 'id' => 12, 'generacion_id' => 5], // sin periodo
        [...$base, 'id' => 13, 'generacion_id' => 6, 'cuatrimestre_id' => 19], // 9º finalizado
    ]);
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('cuenta por cuatrimestre real, excluye bajas y distingue generaciones realmente vigentes', function (): void {
    $servicio = app(EstadisticaGeneralGradosService::class);
    $reporte = $servicio->generar();

    expect($reporte['totales'])->toBe(['hombres' => 3, 'mujeres' => 3, 'total' => 6]);
    expect(collect($reporte['generaciones'])->pluck('id')->all())->toBe([2, 3, 6]);
    expect($reporte['licenciaturas'][0]['nombre'])->toBe('Criminología');
    expect($reporte['licenciaturas'][0]['grados'])->toBe([
        ['grado' => 1, 'hombres' => 1, 'mujeres' => 1, 'total' => 2],
        ['grado' => 2, 'hombres' => 1, 'mujeres' => 1, 'total' => 2],
        ['grado' => 3, 'hombres' => 0, 'mujeres' => 1, 'total' => 1],
    ]);
    expect($reporte['licenciaturas'][1]['totales']['total'])->toBe(1);
});

it('aplica filtros combinados y permite mostrar grados sin alumnos', function (): void {
    $servicio = app(EstadisticaGeneralGradosService::class);
    $reporte = $servicio->generar([
        'licenciatura_id' => 1, 'modalidad_id' => 2, 'generacion_id' => 2,
        'mostrar_grados_vacios' => true,
    ]);

    expect($reporte['totales'])->toBe(['hombres' => 1, 'mujeres' => 0, 'total' => 1]);
    expect($reporte['licenciaturas'][0]['grados'])->toBe([
        ['grado' => 1, 'hombres' => 0, 'mujeres' => 0, 'total' => 0],
        ['grado' => 2, 'hombres' => 1, 'mujeres' => 0, 'total' => 1],
        ['grado' => 3, 'hombres' => 0, 'mujeres' => 0, 'total' => 0],
    ]);
    expect($servicio->generar(['generacion_id' => 1])['totales']['total'])->toBe(0);
});

it('combina varios valores de licenciaturas modalidades y generaciones sin duplicar alumnos', function (): void {
    $servicio = app(EstadisticaGeneralGradosService::class);

    $reporte = $servicio->generar([
        'licenciatura_ids' => '1,2',
        'modalidad_ids' => [1],
        'generacion_ids' => '2,3',
    ]);

    expect($reporte['totales'])->toBe(['hombres' => 2, 'mujeres' => 3, 'total' => 5]);
    expect(count($reporte['licenciaturas']))->toBe(2);

    $subconjunto = $servicio->generar([
        'licenciatura_ids' => '1',
        'modalidad_ids' => '1,2',
        'generacion_ids' => [2],
    ]);
    expect($subconjunto['totales'])->toBe(['hombres' => 2, 'mujeres' => 2, 'total' => 4]);
});

it('diferencia todas de ninguna y conserva compatibilidad con filtros anteriores', function (): void {
    $servicio = app(EstadisticaGeneralGradosService::class);

    expect($servicio->generar([])['totales']['total'])->toBe(6);
    expect($servicio->generar(['licenciatura_ids' => 'none'])['totales']['total'])->toBe(0);
    expect($servicio->generar(['modalidad_ids' => []])['totales']['total'])->toBe(0);
    expect($servicio->generar(['generacion_ids' => 'none'])['totales']['total'])->toBe(0);
    expect($servicio->generar(['licenciatura_id' => 2])['totales']['total'])->toBe(1);
    expect($servicio->generar(['generacion_ids' => '1,2'])['totales']['total'])->toBe(5);

    expect($servicio->normalizarFiltros([
        'licenciatura_ids' => '1,1,2,foo,-4,0,3',
        'modalidad_ids' => ['2', null, 'x', 2],
    ]))->toMatchArray([
        'licenciatura_ids' => [1, 2, 3],
        'modalidad_ids' => [2],
        'generacion_ids' => null,
    ]);
});

it('detecta atrasos por calendario incluso si las cifras estadísticas aún clasifican al alumno en su grado registrado', function (): void {
    $alertas = app(\App\Services\AcademicProgressAlertService::class)->evaluar(['generacion_ids' => [2]]);

    expect($alertas['total_pendientes'])->toBe(4);
    expect($alertas['total_criticos'])->toBe(4);
    expect(collect($alertas['pendientes'])->pluck('id')->all())->toBe([1, 2, 3, 4]);
    expect($alertas['pendientes'][1]['cuatrimestre_actual'])->toBe(3);
    expect($alertas['pendientes'][1]['cuatrimestre_esperado'])->toBe(7);
    // Lectura únicamente: la inscripción real queda sin modificar.
    expect((int) DB::table('inscripciones')->where('id', 2)->value('cuatrimestre_id'))->toBe(13);
});

it('respeta excepciones justificadas por alumno y periodo, sin cambiar notas ni inscripción', function (): void {
    Schema::create('avance_academico_excepciones', function (Blueprint $t) {
        $t->id(); $t->unsignedBigInteger('inscripcion_id'); $t->unsignedBigInteger('periodo_id');
        $t->text('motivo'); $t->unsignedBigInteger('registrado_por')->nullable(); $t->timestamps();
    });
    $idPeriodo = DB::table('periodos')->where('generacion_id', 2)->value('id');
    DB::table('avance_academico_excepciones')->insert([
        'inscripcion_id' => 1, 'periodo_id' => $idPeriodo, 'motivo' => 'Repite por autorización',
    ]);
    $alertas = app(\App\Services\AcademicProgressAlertService::class)->evaluar(['generacion_id' => 2]);
    expect($alertas['total_pendientes'])->toBe(3);
    expect($alertas['total_justificados'])->toBe(1);
    expect($alertas['justificados'][0]['id'])->toBe(1);
    expect((int) DB::table('inscripciones')->where('id', 1)->value('cuatrimestre_id'))->toBe(11);
});

it('no sugiere promociones de generaciones con periodos vigentes superpuestos', function (): void {
    DB::table('periodos')->insert([
        'generacion_id' => 2, 'cuatrimestre_id' => 18,
        'inicio_periodo' => '2026-10-01', 'termino_periodo' => '2026-11-30',
    ]);
    $alertas = app(\App\Services\AcademicProgressAlertService::class)->evaluar(['generacion_id' => 2]);
    expect($alertas['total_pendientes'])->toBe(0);
    expect($alertas['conflictos'])->toHaveCount(1);
});

it('avisa preventivamente en los quince días anteriores al nuevo periodo', function (): void {
    Carbon::setTestNow('2026-12-10 10:00:00');
    DB::table('periodos')->insert([
        'generacion_id' => 2, 'cuatrimestre_id' => 18,
        'inicio_periodo' => '2026-12-20', 'termino_periodo' => '2027-04-20',
    ]);
    $alertas = app(\App\Services\AcademicProgressAlertService::class)->evaluar(['generacion_id' => 2]);
    expect($alertas['total_proximos'])->toBe(5);
    expect($alertas['proximos'][0]['inicio_proximo'])->toBe('2026-12-20');
});

it('avisa sobre el alumno en tercero cuando el calendario exige cuarto y desaparece al corregirlo', function (): void {
    DB::table('inscripciones')->insert([
        'id' => 20, 'generacion_id' => 3, 'licenciatura_id' => 1,
        'modalidad_id' => 1, 'cuatrimestre_id' => 13,
        'sexo' => 'H', 'status' => 'true', 'egresado' => 'false',
    ]);
    $antes = app(EstadisticaGeneralGradosService::class)->generar(['generacion_id' => 3]);
    expect($antes['alertas_avance']['total_pendientes'])->toBe(1);
    expect($antes['alertas_avance']['pendientes'][0]['cuatrimestre_esperado'])->toBe(4);
    // Las estadísticas no excluyen a los alumnos atrasados: continúan en el grado registrado.
    expect($antes['totales']['total'])->toBe(2);

    DB::table('inscripciones')->where('id', 20)->update(['cuatrimestre_id' => 14]);
    $despues = app(EstadisticaGeneralGradosService::class)->generar(['generacion_id' => 3]);
    expect($despues['alertas_avance']['total_pendientes'])->toBe(0);
    expect($despues['totales']['total'])->toBe(2);
});
