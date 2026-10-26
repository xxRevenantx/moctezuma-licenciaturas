<?php

use App\Services\Matriculas\ExpedicionMatriculasService;
use App\Services\Matriculas\ExpedicionMatriculasExportService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetReader;

beforeEach(function (): void {
    // Nunca se conectan pruebas a la BD institucional.
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
    DB::purge('sqlite');
    Schema::create('generaciones', function (Blueprint $t) {
        $t->id(); $t->string('generacion'); $t->string('activa')->default('true'); $t->timestamps();
    });
    Schema::create('licenciaturas', function (Blueprint $t) {
        $t->id(); $t->string('nombre'); $t->string('RVOE')->nullable(); $t->timestamps();
    });
    Schema::create('modalidades', function (Blueprint $t) { $t->id(); $t->string('nombre'); $t->timestamps(); });
    Schema::create('cuatrimestres', function (Blueprint $t) { $t->id(); $t->string('nombre_cuatrimestre'); $t->timestamps(); });
    Schema::create('asignar_generaciones', function (Blueprint $t) {
        $t->id(); $t->unsignedBigInteger('generacion_id'); $t->unsignedBigInteger('licenciatura_id');
        $t->unsignedBigInteger('modalidad_id'); $t->timestamps();
    });
    Schema::create('inscripciones', function (Blueprint $t) {
        $t->id(); $t->unsignedBigInteger('generacion_id'); $t->unsignedBigInteger('licenciatura_id');
        $t->unsignedBigInteger('modalidad_id'); $t->unsignedBigInteger('cuatrimestre_id');
        $t->string('nombre'); $t->string('apellido_paterno'); $t->string('apellido_materno');
        $t->string('CURP')->nullable(); $t->string('matricula')->nullable();
        $t->string('matricula_interna')->nullable(); $t->string('status'); $t->timestamps();
    });

    DB::table('generaciones')->insert([
        ['id'=>1, 'generacion'=>'2022-2025', 'activa'=>'false'],
        ['id'=>2, 'generacion'=>'2026-2029', 'activa'=>'true'],
    ]);
    DB::table('licenciaturas')->insert([
        ['id'=>1, 'nombre'=>'Cultura Física y Deportes', 'RVOE'=>'SEG/123/2022'],
        ['id'=>2, 'nombre'=>'Ciencias de la Educación', 'RVOE'=>'SEG/222/2022'],
        ['id'=>3, 'nombre'=>'No asignada', 'RVOE'=>'SEG/333/2022'],
    ]);
    DB::table('modalidades')->insert([['id'=>1, 'nombre'=>'Escolarizada'], ['id'=>2, 'nombre'=>'Semiescolarizada']]);
    DB::table('cuatrimestres')->insert([['id'=>1, 'nombre_cuatrimestre'=>'Primero']]);
    DB::table('asignar_generaciones')->insert([
        ['generacion_id'=>1, 'licenciatura_id'=>1, 'modalidad_id'=>1],
        ['generacion_id'=>1, 'licenciatura_id'=>1, 'modalidad_id'=>2],
        ['generacion_id'=>1, 'licenciatura_id'=>2, 'modalidad_id'=>1],
    ]);
    $base = [
        'generacion_id'=>1, 'modalidad_id'=>1, 'cuatrimestre_id'=>1, 'apellido_materno'=>'Mora',
        'status'=>'true', 'created_at'=>'2026-09-15 10:00:00', 'updated_at'=>'2026-09-15 10:00:00',
    ];
    DB::table('inscripciones')->insert([
        [...$base, 'id'=>1, 'licenciatura_id'=>1, 'nombre'=>'Laura', 'apellido_paterno'=>'Zamora', 'CURP'=>'ZAML000101MGRRRR01', 'matricula'=>null],
        [...$base, 'id'=>2, 'licenciatura_id'=>1, 'nombre'=>'Ana', 'apellido_paterno'=>'Acosta', 'CURP'=>'ACOA000101MGRRRR02', 'matricula'=>null],
        [...$base, 'id'=>3, 'licenciatura_id'=>2, 'nombre'=>'Beto', 'apellido_paterno'=>'Nava', 'CURP'=>'NABB000101HGRRRR03', 'matricula'=>'0123456'],
        [...$base, 'id'=>4, 'licenciatura_id'=>1, 'nombre'=>'Otro', 'apellido_paterno'=>'Baja', 'CURP'=>'BAJO000101HGRRRR04', 'matricula'=>null, 'status'=>'false'],
        [...$base, 'id'=>5, 'licenciatura_id'=>3, 'nombre'=>'No', 'apellido_paterno'=>'Asignado', 'CURP'=>'NOAS000101HGRRRR05', 'matricula'=>null],
    ]);
});

it('solo permite licenciaturas asignadas con alumnos y conserva generaciones históricas', function () {
    $s = app(ExpedicionMatriculasService::class);
    expect($s->generaciones()->pluck('id')->all())->toBe([1]);
    expect($s->licenciaturasDisponibles(1)->pluck('id')->all())->toBe([2, 1]);
});

it('separa pendientes y asignados SEG sin cambiar alumnos de baja', function () {
    $s = app(ExpedicionMatriculasService::class);
    expect($s->alumnos(1, [1,2], 'activos', 'pendientes')->pluck('id')->all())->toBe([2,1]);
    expect($s->alumnos(1, [1,2], 'activos', 'asignados')->pluck('id')->all())->toBe([3]);
    expect($s->alumnos(1, [1,2], 'bajas', 'todos')->pluck('id')->all())->toBe([4]);
});

it('impide incluir alumnos o licenciaturas ajenos a la selección autorizada', function () {
    $s = app(ExpedicionMatriculasService::class);
    expect(fn () => $s->alumnos(1, [3]))->toThrow(ValidationException::class);
    expect(fn () => $s->datos(1, [1], [3], ['curp'], 'activos', 'pendientes', null, [], []))
        ->toThrow(ValidationException::class);
});

it('mantiene el orden manual, las observaciones y el mes solo en el documento', function () {
    $s = app(ExpedicionMatriculasService::class);
    $datos = $s->datos(1, [1], [1, 2], ExpedicionMatriculasService::COLUMNAS_PREDETERMINADAS,
        'activos', 'pendientes', null, [1=>'OCTUBRE'], [1=>'REINGRESO']);
    expect(array_column($datos['grupos'][0]['filas'], 'nombre'))->toBe(['Laura', 'Ana']);
    expect($datos['grupos'][0]['filas'][0]['mes_ingreso'])->toBe('OCTUBRE');
    expect($datos['grupos'][0]['filas'][0]['observaciones'])->toBe('REINGRESO');
    expect(DB::table('inscripciones')->where('id', 1)->value('matricula'))->toBeNull();
});

it('escribe Excel con CURP como texto y Word editable', function () {
    $s = app(ExpedicionMatriculasService::class);
    $datos = $s->datos(1, [1, 2], [2, 1, 3], ExpedicionMatriculasService::COLUMNAS_PREDETERMINADAS,
        'activos', 'todos', null, [], []);
    $excel = tempnam(sys_get_temp_dir(), 'matxls');
    $word = tempnam(sys_get_temp_dir(), 'matdoc');
    try {
        $e = app(ExpedicionMatriculasExportService::class);
        $e->excel($datos, 'seg', $excel);
        $e->word($datos, 'cum', $word);
        expect(filesize($excel))->toBeGreaterThan(1000);
        expect(filesize($word))->toBeGreaterThan(1000);
        $book = SpreadsheetReader::load($excel);
        expect($book->getSheetCount())->toBe(2);
        expect($book->getSheet(1)->getCell('E5')->getValue())->toBe('NABB000101HGRRRR03');
        expect($book->getSheet(1)->getCell('E5')->getDataType())->toBe('s');
        $book->disconnectWorksheets();
    } finally {
        @unlink($excel);
        @unlink($word);
    }
});
