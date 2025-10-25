<?php

namespace App\Livewire\Admin\Documentacion;

use App\Exports\EstadisticaExport;
use Livewire\Component;
use App\Models\Generacion;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use Illuminate\Support\Facades\DB;


class Estadistica extends Component
{

    public $filtrar_licenciatura;
    public $licenciaturas;
    public ?string $rango_edad = null;



     // exportar Estadística
    public function exportarEstadistica()
    {


         // Estadísticas por licenciatura
    $estadistica = \App\Models\Inscripcion::select(
            'licenciatura_id',
            DB::raw('COUNT(*) as total_inscritos'),
            DB::raw("SUM(CASE WHEN sexo = 'H' THEN 1 ELSE 0 END) as total_masculinos"),
            DB::raw("SUM(CASE WHEN sexo = 'M' THEN 1 ELSE 0 END) as total_femeninos"),
            DB::raw("SUM(CASE WHEN egresado = 'true' THEN 1 ELSE 0 END) as total_egresados")
        )
        ->where('status', 'true')
        ->whereHas('generacion', function ($query) {
            $query->where('activa', 'true');
        })
        // Filtra por licenciatura solo si hay selección; si no, trae TODAS
        ->when($this->filtrar_licenciatura, fn ($q) =>
            $q->where('licenciatura_id', $this->filtrar_licenciatura)
        )

        // Filtro dinámico por rango(s) de edad
        ->when(filled($this->rango_edad), function ($q) {
            $expresiones = $this->parseAgeRanges($this->rango_edad);
            $q->where(function ($qq) use ($expresiones) {
                foreach ($expresiones as $r) {
                    $expr = DB::raw("TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE())");
                    switch ($r['op']) {
                        case 'between':
                            $qq->orWhereBetween($expr, [$r['min'], $r['max']]);
                            break;
                        case 'gte':
                            $qq->orWhere($expr, '>=', $r['min']);
                            break;
                        case 'lte':
                            $qq->orWhere($expr, '<=', $r['max']);
                            break;
                        case 'gte_only': // '46+' → >= 46
                            $qq->orWhere($expr, '>=', $r['min']);
                            break;
                    }
                }
            });
        })

        ->groupBy('licenciatura_id')
        ->get();

    // 🟢 Creamos la variable de rango de edad
    $rango_edad = $this->rango_edad ?? 'Todos';

    dd($estadistica);

    // 🔸 Pasamos la variable al export
    return Excel::download(new EstadisticaExport($estadistica, $rango_edad), 'estadisticas.xlsx');
    }


    public function mount()
    {
        $this->licenciaturas = \App\Models\Licenciatura::all();
    }
    public function render()
    {


        return view('livewire.admin.documentacion.estadistica');
    }
}
