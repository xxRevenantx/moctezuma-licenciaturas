<?php

use App\Models\AsignacionMateria;
use App\Models\Cuatrimestre;
use App\Models\Generacion;
use App\Models\Horario;
use App\Models\Licenciatura;
use App\Models\Materia;
use App\Models\Modalidad;
use App\Models\Profesor;
use App\Services\PlantillaDocenteLicenciaturaService;
use App\Services\PlantillaDocenteWordService;

function horarioDocentePrueba(int $lic, int $prof, int $materia, string $rango, int $gen = 1): Horario
{
    $licenciatura = (new Licenciatura)->forceFill(['id' => $lic, 'nombre' => $lic === 1 ? 'CIENCIAS DE LA EDUCACIÓN' : 'ADMINISTRACIÓN']);
    $docente = (new Profesor)->forceFill(['id' => $prof, 'nombre' => $prof === 1 ? 'ANA' : 'BERTA', 'apellido_paterno' => 'GARCÍA', 'apellido_materno' => 'LÓPEZ']);
    $mat = (new Materia)->forceFill(['id' => $materia, 'nombre' => 'MATERIA '.$materia]);
    $asignacion = (new AsignacionMateria)->forceFill(['profesor_id' => $prof, 'materia_id' => $materia]);
    $asignacion->setRelation('profesor', $docente);
    $asignacion->setRelation('materia', $mat);
    $horario = (new Horario)->forceFill(['licenciatura_id' => $lic, 'modalidad_id'=>2, 'cuatrimestre_id'=>4, 'generacion_id'=>$gen, 'hora'=>$rango]);
    $horario->setRelation('licenciatura', $licenciatura);
    $horario->setRelation('modalidad', (new Modalidad)->forceFill(['id'=>2, 'nombre'=>'SEMIESCOLARIZADA']));
    $horario->setRelation('cuatrimestre', (new Cuatrimestre)->forceFill(['id'=>4,'cuatrimestre'=>4]));
    $horario->setRelation('generacion', (new Generacion)->forceFill(['id'=>$gen,'generacion'=>'2025-2028']));
    $horario->setRelation('asignacionMateria', $asignacion);
    return $horario;
}

test('el docente no se duplica por licenciatura aunque tenga materias y bloques diferentes', function () {
    $horarios = collect([
        horarioDocentePrueba(1, 1, 5, '8:00AM-9:00AM'),
        horarioDocentePrueba(1, 1, 6, '9:00AM-10:00AM'),
        horarioDocentePrueba(1, 1, 5, '10:30AM-11:30AM'),
        horarioDocentePrueba(1, 2, 7, '8:00AM-9:00AM'),
        horarioDocentePrueba(2, 1, 8, '11:30AM-12:30PM'),
    ]);
    $grupos = app(PlantillaDocenteLicenciaturaService::class)->agruparHorarios($horarios);
    expect($grupos)->toHaveCount(2);
    expect($grupos[1]['licenciatura'])->toBe('CIENCIAS DE LA EDUCACIÓN');
    expect($grupos[1]['total_docentes'])->toBe(2);
    expect($grupos[1]['bloques'])->toBe(4);
    expect($grupos[1]['docentes'][0]['materias'])->toHaveCount(2);
    expect($grupos[1]['docentes'][0]['horas'])->toBe('03:00');
    expect($grupos[1]['horas'])->toBe('04:00');
    expect($grupos[0]['total_docentes'])->toBe(1);
});

test('las columnas de los tres reportes pueden ocultar materias, horas o generaciones', function () {
    $reporte = ['mostrar_materias'=>false, 'mostrar_horas'=>false, 'mostrar_generaciones'=>false];
    expect(array_keys(PlantillaDocenteWordService::columnas($reporte)))->toBe(['no','nombre','cuatrimestres','modalidades']);
});
