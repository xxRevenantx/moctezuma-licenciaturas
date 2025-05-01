<?php

namespace App\Imports;

use App\Models\Generacion;
use Maatwebsite\Excel\Concerns\ToModel;

use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\ImportFailed;

class GeneracionImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure,
    WithEvents
{

    use SkipsFailures;

    protected $expectedHeadings = [
        'generacion',
        'activa'

    ];

    public function model(array $row)
    {
        return new Generacion([
            'generacion' => $row['generacion'],
            'activa'     => $row['activa'],

        ]);
    }

    public function rules(): array
    {
        return [
            '*.generacion' => ['required', 'string', 'max:255'],
            '*.activa'     => ['required', 'string'],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            '*.generacion.required' => 'El campo generacion es obligatorio.',
            '*.activa.required'     => 'El campo activa es obligatorio.',
            '*.activa.string'       => 'El campo activa debe ser una cadena de texto.',
        ];
    }

    public static function beforeImport(BeforeImport $event)
    {
        $worksheet = $event->getReader()->getActiveSheet();
        $firstRow = $worksheet->toArray(null, true, true, true)[1]; // fila 1 con letras

        $importedKeys = array_map(function ($key) {
            return strtolower(trim($key));
        }, array_values($firstRow));

        $expected = [
            'generacion',
            'activa'
        ];

        $missing = array_diff($expected, $importedKeys);
        $extra = array_diff($importedKeys, $expected);

        if (!empty($missing)) {
            throw new \Exception("Encabezados incorrectos. Faltan: " . implode(', ', $missing));
        }

        if (!empty($extra)) {
            throw new \Exception("Encabezados no reconocidos: " . implode(', ', $extra));
        }
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                logger('Inicio de importación de generaciones.');
            },
            AfterImport::class => function (AfterImport $event) {
                logger('Finalizó la importación de generaciones.');
            },
            ImportFailed::class => function (ImportFailed $event) {
                logger('Fallo en la importación: ' . $event->getException()->getMessage());
            },
        ];
    }
}
