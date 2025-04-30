<?php

namespace App\Imports;

use App\Models\Directivo;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;

use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\ImportFailed;
use Maatwebsite\Excel\Validators\Failure;

class DirectivoImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, WithEvents
{
    use SkipsFailures;

    protected $expectedHeadings = [
        'titulo',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'telefono',
        'correo',
        'cargo',
    ];

    public function model(array $row)
    {
        return new Directivo([
            'titulo'           => $row['titulo'],
            'nombre'           => $row['nombre'],
            'apellido_paterno' => $row['apellido_paterno'],
            'apellido_materno' => $row['apellido_materno'],
            'telefono'         => $row['telefono'],
            'correo'           => $row['correo'],
            'cargo'            => $row['cargo'],
        ]);
    }

    public  function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                // Código que se ejecuta antes de la importación
                logger('La importación está a punto de comenzar.');
            },
            AfterImport::class => function (AfterImport $event) {
                // Código que se ejecuta después de la importación
                logger('La importación ha finalizado.');
            },
            ImportFailed::class => function (ImportFailed $event) {
                // Código que se ejecuta si la importación falla
                logger('La importación falló: ' . $event->getException()->getMessage());
            },
        ];
    }

    public function rules(): array
    {
        return [
            '*.correo' => ['required', 'email'],
            '*.nombre' => ['required'],
            '*.apellido_paterno' => ['required'],
            '*.cargo' => ['required'],
        ];
    }

    public static function beforeImport(BeforeImport $event)
    {
        $headings = $event->reader->getHeadingRow()->toArray();
        $firstRow = reset($headings);

        $expected = [
            'titulo',
            'nombre',
            'apellido_paterno',
            'apellido_materno',
            'telefono',
            'correo',
            'cargo',
        ];

        $missing = array_diff($expected, array_keys($firstRow));
        if (!empty($missing)) {
            throw new \Exception("Encabezados incorrectos. Faltan: " . implode(', ', $missing));
        }
    }



}
