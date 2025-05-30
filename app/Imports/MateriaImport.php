<?php

namespace App\Imports;

use App\Models\Materia;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;

use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\ImportFailed;

class MateriaImport implements   ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure,
    WithEvents
{

 use SkipsFailures;
use Importable;

     protected $expectedHeadings = [
        'nombre',
        'slug',
        'clave',
        'creditos',
        'cuatrimestre_id',
        'licenciatura_id',
        'orden',
        'calificable'

    ];


      public function model(array $row)
    {
        return new Materia([
            'nombre' => $row['nombre'],
            'slug' => $row['slug'],
            'clave' => $row['clave'],
            'creditos' => $row['creditos'],
            'cuatrimestre_id' => $row['cuatrimestre_id'],
            'licenciatura_id' => $row['licenciatura_id'],
            'orden' => $row['orden'],
            'calificable' => $row['calificable'],

        ]);
    }

       public function rules(): array
    {
        return [
            '*.nombre' => ['required', 'string', 'max:255'],
            '*.slug' => ['required', 'string', 'max:255'],
            '*.clave' => ['required', 'string', 'max:255'],
            '*.creditos' => ['required', 'integer'],
            '*.cuatrimestre_id' => ['required', 'exists:cuatrimestres,id'],
            '*.licenciatura_id' => ['required', 'exists:licenciaturas,id'],
            '*.orden' => ['required', 'integer'],
            '*.calificable' => ['required', 'in:true,false'],
        ];
    }


    public static function beforeImport(BeforeImport $event)
    {
        logger('Inicio de importación de materias.');
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                logger('Importación de materias completada.');
            },
            ImportFailed::class => function (ImportFailed $event) {
                logger('Error en la importación de materias: ' . $event->getException()->getMessage());
            },
        ];
    }



}
