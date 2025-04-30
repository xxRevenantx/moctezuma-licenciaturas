<?php

namespace App\Imports;

use App\Models\Directivo;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\ImportFailed;
use Maatwebsite\Excel\Validators\Failure;

class DirectivoImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure,
    WithEvents
{
    use SkipsFailures;

    protected $expectedHeadings = [
        'titulo',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'cargo',
        'telefono',
        'correo',

    ];

    public function model(array $row)
    {
        return new Directivo([
            'titulo'           => $row['titulo'],
            'nombre'           => $row['nombre'],
            'apellido_paterno' => $row['apellido_paterno'],
            'apellido_materno' => $row['apellido_materno'],
            'cargo'            => $row['cargo'],
            'telefono'         => $row['telefono'],
            'correo'           => $row['correo'],

        ]);
    }

    public function rules(): array
    {
        return [
            '*.titulo'            => ['nullable', 'string', 'max:255'],
            '*.nombre'            => ['required', 'string', 'max:255'],
            '*.apellido_paterno'  => ['required', 'string', 'max:255'],
            '*.apellido_materno'  => ['nullable', 'string', 'max:255'],
            '*.telefono'          => ['nullable', 'regex:/^[0-9]{10}$/'],
            '*.correo'            => ['required', 'email', 'max:255', 'unique:directivos,correo'],
            '*.cargo'             => ['required', 'string', 'max:255'],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            '*.nombre.required'           => 'El nombre es obligatorio.',
            '*.apellido_paterno.required' => 'El apellido paterno es obligatorio.',
            '*.correo.required'           => 'El correo es obligatorio.',
            '*.correo.email'              => 'El formato del correo no es válido.',
            '*.correo.unique'             => 'Este correo ya existe.',
            '*.telefono.regex'            => 'El teléfono debe tener exactamente 10 dígitos.',
            '*.cargo.required'            => 'El cargo es obligatorio.',
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
            'titulo',
            'nombre',
            'apellido_paterno',
            'apellido_materno',
            'cargo',
            'telefono',
            'correo',
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
                logger('Inicio de importación de directivos.');
            },
            AfterImport::class => function (AfterImport $event) {
                logger('Finalizó la importación de directivos.');
            },
            ImportFailed::class => function (ImportFailed $event) {
                logger('Fallo en la importación: ' . $event->getException()->getMessage());
            },
        ];
    }
}
