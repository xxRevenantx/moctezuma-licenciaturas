<?php

use App\Models\User;
use App\Services\ReasignacionDocenteService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    // BD aislada: nunca importar ni utilizar la base de datos escolar para estas pruebas.
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
    DB::purge('sqlite');
    foreach (['licenciaturas', 'modalidades', 'cuatrimestres', 'profesores', 'materias', 'asignacion_materias', 'horarios', 'calificaciones', 'calificacion_docente_capturas', 'dias', 'generaciones', 'asignar_generaciones'] as $nombre) {
        Schema::create($nombre, function (Blueprint $t) use ($nombre) {
            $t->id();
            $t->timestamps();
            if (in_array($nombre, ['licenciaturas', 'modalidades', 'profesores', 'materias'])) $t->string('nombre');
            if ($nombre === 'licenciaturas') $t->string('nombre_corto')->nullable();
            if ($nombre === 'dias') $t->string('dia');
            if ($nombre === 'generaciones') $t->string('generacion');
            if ($nombre === 'cuatrimestres') { $t->integer('cuatrimestre'); $t->string('nombre_cuatrimestre')->nullable(); }
            if ($nombre === 'profesores') {
                $t->string('apellido_paterno')->nullable(); $t->string('apellido_materno')->nullable(); $t->string('status');
            }
            if (in_array($nombre, ['materias', 'asignacion_materias'])) {
                $t->unsignedBigInteger('licenciatura_id'); $t->unsignedBigInteger('cuatrimestre_id');
            }
            if ($nombre === 'materias') $t->string('clave');
            if ($nombre === 'asignacion_materias') {
                $t->unsignedBigInteger('materia_id'); $t->unsignedBigInteger('modalidad_id'); $t->unsignedBigInteger('profesor_id');
            }
            if (in_array($nombre, ['horarios', 'calificaciones', 'calificacion_docente_capturas'])) $t->unsignedBigInteger('asignacion_materia_id');
            if ($nombre === 'asignar_generaciones') { $t->unsignedBigInteger('licenciatura_id'); $t->unsignedBigInteger('modalidad_id'); $t->unsignedBigInteger('generacion_id')->nullable(); }
            if ($nombre === 'calificaciones') { $t->unsignedBigInteger('generacion_id')->nullable(); $t->unsignedBigInteger('profesor_id'); $t->decimal('calificacion', 4, 1); }
            if ($nombre === 'calificacion_docente_capturas') { $t->unsignedBigInteger('profesor_id'); $t->string('estado'); $t->unsignedBigInteger('entregado_por')->nullable(); }
            if ($nombre === 'horarios') {
                foreach (['modalidad_id', 'dia_id', 'generacion_id', 'licenciatura_id'] as $campo) $t->unsignedBigInteger($campo)->nullable();
                $t->string('hora')->nullable();
            }
        });
    }
    (require database_path('migrations/2026_09_23_120000_create_reasignacion_docente_auditorias_table.php'))->up();
    $usuario = new User(); $usuario->id = 99; $usuario->username = 'Administrador de prueba';
    $this->actingAs($usuario);
    Gate::before(fn ($user) => (int) $user->id === 99);
    DB::table('profesores')->insert([
        ['id' => 1, 'nombre' => 'Origen', 'status' => 'true'],
        ['id' => 2, 'nombre' => 'Destino', 'status' => 'true'],
        ['id' => 3, 'nombre' => 'Otro', 'status' => 'true'],
    ]);
    DB::table('modalidades')->insert([['id' => 1, 'nombre' => 'Escolarizada'], ['id' => 2, 'nombre' => 'Semiescolarizada']]);
    DB::table('cuatrimestres')->insert(['id' => 1, 'cuatrimestre' => 1]);
    foreach ([1, 2] as $id) {
        DB::table('licenciaturas')->insert(['id' => $id, 'nombre' => 'Licenciatura '.$id]);
        DB::table('materias')->insert(['id' => $id, 'nombre' => 'Materia '.$id, 'clave' => 'M'.$id, 'licenciatura_id' => $id, 'cuatrimestre_id' => 1]);
        DB::table('asignacion_materias')->insert(['id' => $id, 'materia_id' => $id, 'licenciatura_id' => $id, 'modalidad_id' => $id, 'cuatrimestre_id' => 1, 'profesor_id' => 1]);
    }
    DB::table('calificaciones')->insert(['id' => 1, 'asignacion_materia_id' => 1, 'profesor_id' => 1, 'calificacion' => 9.5]);
    DB::table('calificacion_docente_capturas')->insert(['id' => 1, 'asignacion_materia_id' => 1, 'profesor_id' => 1, 'estado' => 'validada', 'entregado_por' => 1]);
});

it('reemplaza entre licenciaturas y conserva calificaciones y autoría', function () {
    $servicio = app(ReasignacionDocenteService::class);
    $calificaciones = DB::table('calificaciones')->get()->toJson();
    $revision = $servicio->preparar([1, 2], 1, 2);
    expect($servicio->ejecutar($revision, true, false))->toBe(2)
        ->and(DB::table('asignacion_materias')->where('profesor_id', 2)->count())->toBe(2)
        ->and(DB::table('calificaciones')->get()->toJson())->toBe($calificaciones)
        ->and(DB::table('calificacion_docente_capturas')->value('estado'))->toBe('validada')
        ->and(DB::table('calificacion_docente_capturas')->value('entregado_por'))->toBe(1)
        ->and(DB::table('calificacion_docente_capturas')->value('profesor_id'))->toBe(2)
        ->and(DB::table('reasignacion_docente_auditorias')->value('total'))->toBe(2);
});

it('conserva materias excluidas y rechaza repetir la misma confirmación', function () {
    $s = app(ReasignacionDocenteService::class);
    $r = $s->preparar([1], 1, 2);
    $s->ejecutar($r, true, false);
    expect(DB::table('asignacion_materias')->where('id', 2)->value('profesor_id'))->toBe(1);
    expect(fn () => $s->ejecutar($r, true, false))->toThrow(ValidationException::class);
    expect(DB::table('reasignacion_docente_auditorias')->count())->toBe(1);
});

it('rechaza toda la operación si cambió un profesor después de revisar', function () {
    $s = app(ReasignacionDocenteService::class);
    $r = $s->preparar([1, 2], 1, 2);
    DB::table('asignacion_materias')->where('id', 2)->update(['profesor_id' => 3]);
    expect(fn () => $s->ejecutar($r, true, false))->toThrow(ValidationException::class);
    expect(DB::table('asignacion_materias')->where('id', 1)->value('profesor_id'))->toBe(1)
        ->and(DB::table('reasignacion_docente_auditorias')->count())->toBe(0);
});

it('detecta traslapes entre modalidades y exige confirmación explícita', function () {
    DB::table('dias')->insert(['id' => 1, 'dia' => 'Lunes']);
    foreach ([1, 2] as $id) DB::table('horarios')->insert(['id' => $id, 'asignacion_materia_id' => $id, 'modalidad_id' => $id, 'dia_id' => 1, 'licenciatura_id' => $id, 'hora' => '8:00am-9:00am']);
    $s = app(ReasignacionDocenteService::class);
    $r = $s->preparar([1, 2], 1, 2);
    expect($r['conflictos'])->toHaveCount(1);
    expect(fn () => $s->ejecutar($r, true, false))->toThrow(ValidationException::class);
    expect(DB::table('reasignacion_docente_auditorias')->count())->toBe(0);
    $horarios = DB::table('horarios')->get()->toJson();
    expect($s->ejecutar($r, true, true))->toBe(2)
        ->and(DB::table('horarios')->get()->toJson())->toBe($horarios);
});

it('exige revisar de nuevo cuando se agrega un horario', function () {
    $s = app(ReasignacionDocenteService::class);
    $r = $s->preparar([1], 1, 2);
    DB::table('horarios')->insert(['asignacion_materia_id' => 1, 'modalidad_id' => 1, 'hora' => '8:00am-9:00am']);
    expect(fn () => $s->ejecutar($r, true, false))->toThrow(ValidationException::class);
    expect(DB::table('asignacion_materias')->where('id', 1)->value('profesor_id'))->toBe(1);
});


it('rechaza profesores iguales o inactivos', function () {
    $s = app(ReasignacionDocenteService::class);
    expect(fn () => $s->preparar([1], 1, 1))->toThrow(ValidationException::class);
    DB::table('profesores')->where('id', 2)->update(['status' => 'false']);
    expect(fn () => $s->preparar([1], 1, 2))->toThrow(ValidationException::class);
});

it('rechaza acceso sin permiso administrativo', function () {
    $usuario = new User(); $usuario->id = 100;
    $this->actingAs($usuario);
    expect(fn () => app(ReasignacionDocenteService::class)->preparar([1], 1, 2))
        ->toThrow(\Illuminate\Auth\Access\AuthorizationException::class);
});

it('selecciona todas las páginas y limpia la selección al cambiar el alcance', function () {
    foreach (range(3, 25) as $id) {
        DB::table('materias')->insert(['id' => $id, 'nombre' => 'Materia '.$id, 'clave' => 'M'.$id, 'licenciatura_id' => 1, 'cuatrimestre_id' => 1]);
        DB::table('asignacion_materias')->insert(['id' => $id, 'materia_id' => $id, 'licenciatura_id' => 1, 'modalidad_id' => 1, 'cuatrimestre_id' => 1, 'profesor_id' => 1]);
    }
    \Livewire\Livewire::test(\App\Livewire\Admin\AsignacionDocente\ReasignarProfesor::class)
        ->set('abierto', true)->set('origen', '1')
        ->call('seleccionarTodas')->assertSet('seleccionadas', fn ($ids) => count($ids) === 25)
        ->call('setPage', 2, 'reasignacionPage')->assertSet('seleccionadas', fn ($ids) => count($ids) === 25)
        ->set('alcance', 'seleccionadas')->assertSet('seleccionadas', []);
});
