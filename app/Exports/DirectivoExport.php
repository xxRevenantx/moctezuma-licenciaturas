<?php

namespace App\Exports;

use App\Models\Directivo;
use Maatwebsite\Excel\Concerns\FromCollection;

class DirectivoExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Directivo::select('id', 'titulo', 'nombre', 'apellido_paterno', 'apellido_materno', 'telefono', 'correo', 'cargo')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Título',
            'Nombre',
            'Apellido Paterno',
            'Apellido Materno',
            'Teléfono',
            'Correo',
            'Cargo',
        ];
    }
}
