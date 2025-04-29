<?php

namespace App\Exports;

use App\Models\Licenciatura;
use Maatwebsite\Excel\Concerns\FromCollection;

class LicenciaturaExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Licenciatura::select('id', 'nombre', 'nombre_corto', 'RVOE')->get();
    }


    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Nombre Corto',
            'RVOE',
        ];
    }

}
