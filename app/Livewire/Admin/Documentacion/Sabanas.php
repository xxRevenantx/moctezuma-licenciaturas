<?php

namespace App\Livewire\Admin\Documentacion;

use App\Exports\SabanaExport;
use App\Models\Generacion;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
class Sabanas extends Component
{


    public $licenciaturas;
    public $licenciatura;
    public $modalidad;
    public $filtrar_generacion;
    public $search = '';
    public $selected = [];
    public $selectAll = false;
    public $generaciones;



    public function mount()
    {
        $this->licenciaturas = Licenciatura::all();
        $this->generaciones = Generacion::all();

    }

    // exportar Sabanas
    public function exportarSabanas()
    {


           // Subquery: estadísticas por alumno EN LA GENERACIÓN (promedio, materias, créditos obtenidos)
$stats = DB::table('calificaciones as c')
    ->leftJoin('asignacion_materias as am', 'am.id', '=', 'c.asignacion_materia_id')
    ->leftJoin('materias as m', 'm.id', '=', 'am.materia_id')
    ->select([
        'c.alumno_id',         // = inscripciones.id
        'c.generacion_id',     // para unir también por generación
        DB::raw("
            TRUNCATE(AVG(
                CASE
                    WHEN c.calificacion REGEXP '^[0-9]+(\\.[0-9]+)?$'
                    THEN c.calificacion + 0
                    ELSE NULL
                END
            ), 1) AS promedio_final
        "),
        DB::raw("
            COUNT(DISTINCT CASE
                WHEN c.calificacion REGEXP '^[0-9]+(\\.[0-9]+)?$'
                THEN c.asignacion_materia_id
                ELSE NULL
            END) AS total_materias
        "),
        DB::raw("
            SUM(CASE
                WHEN c.calificacion REGEXP '^[0-9]+(\\.[0-9]+)?$'
                 AND m.creditos REGEXP '^[0-9]+(\\.[0-9]+)?$'
                THEN (m.creditos + 0)
                ELSE 0
            END) AS total_creditos
        "),
    ])
    ->groupBy('c.alumno_id', 'c.generacion_id');

// Subquery: créditos TOTALES DEL PLAN por licenciatura (desde materias)
$plan = DB::table('materias as m')
    ->select('m.licenciatura_id')
    ->selectRaw("
        SUM(
            CASE
                WHEN m.creditos REGEXP '^[0-9]+(\\.[0-9]+)?$'
                THEN (m.creditos + 0)
                ELSE 0
            END
        ) AS total_creditos_licenciatura
    ")
    ->groupBy('m.licenciatura_id');

// Query principal: solo relaciona subqueries ⇔ inscripciones
$sabana = Inscripcion::query()
    ->with(['user','licenciatura','generacion','cuatrimestre','modalidad'])
    // stats por alumno/generación
    ->leftJoinSub($stats, 'stats', function ($join) {
        $join->on('stats.alumno_id', '=', 'inscripciones.id')
             ->on('stats.generacion_id', '=', 'inscripciones.generacion_id');
    })
    // créditos totales del plan por licenciatura
    ->leftJoinSub($plan, 'plan', function ($join) {
        $join->on('plan.licenciatura_id', '=', 'inscripciones.licenciatura_id');
    })
    ->where('inscripciones.generacion_id', $this->filtrar_generacion)
    ->where('inscripciones.status', 'true')
    ->whereRaw("CAST(SUBSTRING(inscripciones.matricula, 1, 2) AS UNSIGNED) BETWEEN 20 AND 99")
    ->whereHas('licenciatura', fn ($q) => $q->whereNotNull('RVOE')->where('RVOE','!=',''))
    ->select('inscripciones.*')
    ->addSelect([
        'stats.promedio_final',
        'stats.total_materias',
        'stats.total_creditos',                // créditos obtenidos por el alumno en esa generación
        'plan.total_creditos_licenciatura',    // créditos totales del plan de su licenciatura
    ])
    ->orderBy('inscripciones.folio')
    ->get();

        return Excel::download(new SabanaExport($sabana), 'sabanas.xlsx');
    }


    public function render()
    {
        return view('livewire.admin.documentacion.sabanas');
    }
}
